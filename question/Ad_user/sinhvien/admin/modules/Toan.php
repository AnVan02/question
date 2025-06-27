<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set timezone

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Kết nối cơ sỡ dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ma_khoa = '4'; // ID khoá học
$id_test = '23'; // ID bai test
$student_id = $_SESSION['student_id'];

// Lấy mã khoá học từ bảng students và kiểm tra 
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc']; // e.g., "6,4"
    $khoahoc_list = array_map('intval', explode(',', $khoahoc));
    if (!in_array(intval($ma_khoa), $khoahoc_list)) {
        echo "<script>
            alert('Bạn không có quyền truy cập khóa học này!');
            window.location.href = 'login.php';
        </script>";
        exit();
    }
} else {
    echo "<script>
        alert('Không tìm thấy thông tin sinh viên!');
        window.location.href = 'login.php';
    </script>";
    exit();
}
$stmt->close();

// Kiểm tra quyền truy cập khoá học
$stmt = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
$stmt->bind_param("i", $id_test);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>alert('ID bài test ($id_test) không tồn tại trong hệ thống. Vui lòng kiểm tra lại!');</script>";
    exit();
}
$row = $result->fetch_assoc();
$id_baitest = $row['ten_test'];
$stmt->close();

// Lấy khoá học từ bảng khoa_hoc
function getCoursesFromDB($conn) {
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    return $courses;
}

// Lấy tên bài test từ id_test
function getTestInfo($conn, $ten_test, $ten_khoa) {
    $courses = getCoursesFromDB($conn);
    $id_khoa = array_search($ten_khoa, $courses);
    if ($id_khoa === false) {
        die("Lỗi: Không tìm thấy khóa học '$ten_khoa'");
    }
    $sql = "SELECT lan_thu FROM test WHERE ten_test = ? AND id_khoa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $ten_test, $id_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['lan_thu'];
    }
    $stmt->close();
    return 1;
}

// Hàm tính toán lại điểm số sau khi xóa câu hỏi
function recalculateScoreAfterDeletion($conn, $student_id, $khoa_id, $test_id, $tt_bai_test) {
    try {
        // Lấy thông tin bài test
        $test_info = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
        $test_info->bind_param("s", $test_id);
        $test_info->execute();
        $test_result = $test_info->get_result();
        $test_row = $test_result->fetch_assoc();
        $ten_test = $test_row['ten_test'];
        
        // Lấy thông tin khóa học
        $khoa_info = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
        $khoa_info->bind_param("i", $khoa_id);
        $khoa_info->execute();
        $khoa_result = $khoa_info->get_result();
        $khoa_row = $khoa_result->fetch_assoc();
        $ten_khoa = $khoa_row['khoa_hoc'];
        
        // Lấy tất cả câu hỏi hiện có
        $quiz_stmt = $conn->prepare("SELECT Id_cauhoi, dap_an FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
        $quiz_stmt->bind_param("ss", $ten_khoa, $ten_test);
        $quiz_stmt->execute();
        $quiz_result = $quiz_stmt->get_result();
        
        $valid_questions = [];
        while ($row = $quiz_result->fetch_assoc()) {
            $valid_questions[$row['Id_cauhoi']] = strtoupper(trim($row['dap_an']));
        }
        
        // Tính toán lại điểm số dựa trên câu hỏi còn lại
        $new_score = 0;
        $pairs = explode(';', $tt_bai_test);
        
        foreach ($pairs as $pair) {
            if (empty($pair)) continue;
            
            $parts = explode(':', $pair);
            if (count($parts) === 2) {
                $question_id = trim($parts[0]);
                $user_answer = strtoupper(trim($parts[1]));
                
                if (isset($valid_questions[$question_id]) && $user_answer === $valid_questions[$question_id]) {
                    $new_score++;
                }
            }
        }
        
        // Cập nhật điểm số mới
        $update_stmt = $conn->prepare("UPDATE ket_qua SET kq_cao_nhat = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $update_stmt->bind_param("iiss", $new_score, $student_id, $khoa_id, $test_id);
        $update_stmt->execute();
        
        return $new_score;
    } catch (Exception $e) {
        error_log("Lỗi khi tính toán lại điểm số: " . $e->getMessage());
        return false;
    }
}

// Hàm xử lý khi xóa câu hỏi
function cleanDeletedQuestionsFromResults($conn, $deleted_question_id) {
    try {
        $conn->begin_transaction();
        
        // Lấy tất cả bản ghi có chứa câu hỏi đã xóa
        $stmt = $conn->prepare("SELECT student_id, khoa_id, test_id, tt_bai_test FROM ket_qua WHERE tt_bai_test LIKE CONCAT('%', ?, ':%')");
        $stmt->bind_param("i", $deleted_question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $update_stmt = $conn->prepare("UPDATE ket_qua SET tt_bai_test = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $affected_rows = 0;
        
        while ($row = $result->fetch_assoc()) {
            $pairs = explode(';', $row['tt_bai_test']);
            $new_pairs = [];
            
            foreach ($pairs as $pair) {
                if (empty($pair)) continue;
                
                $parts = explode(':', $pair);
                if (count($parts) === 2 && $parts[0] != $deleted_question_id) {
                    $new_pairs[] = $pair;
                }
            }
            
            $new_tt_bai_test = implode(';', $new_pairs);
            
            if ($new_tt_bai_test !== $row['tt_bai_test']) {
                // Tính toán lại điểm số trước khi cập nhật
                $new_score = recalculateScoreAfterDeletion($conn, $row['student_id'], $row['khoa_id'], $row['test_id'], $new_tt_bai_test);
                
                $update_stmt->bind_param("siis", $new_tt_bai_test, $row['student_id'], $row['khoa_id'], $row['test_id']);
                $update_stmt->execute();
                $affected_rows += $update_stmt->affected_rows;
            }
        }
        
        $conn->commit();
        return $affected_rows;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Lỗi khi làm sạch câu hỏi đã xóa: " . $e->getMessage());
        return false;
    }
}

// Hàm xóa câu hỏi
function deleteQuizQuestion($conn, $question_id, $student_id, $khoa_id, $test_id) {
    try {
        $conn->begin_transaction();
        
        // 1. Lấy tt_bai_test trước khi xóa
        $get_tt_stmt = $conn->prepare("SELECT tt_bai_test FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $get_tt_stmt->bind_param("sis", $student_id, $khoa_id, $test_id);
        $get_tt_stmt->execute();
        $tt_result = $get_tt_stmt->get_result();
        $tt_row = $tt_result->fetch_assoc();
        $original_tt_bai_test = $tt_row['tt_bai_test'] ?? '';
        
        // 2. Xóa câu hỏi từ bảng quiz
        $delete_stmt = $conn->prepare("DELETE FROM quiz WHERE Id_cauhoi = ?");
        $delete_stmt->bind_param("i", $question_id);
        $delete_stmt->execute();
        
        if ($delete_stmt->affected_rows === 0) {
            throw new Exception("Không tìm thấy câu hỏi với ID: $question_id");
        }
        
        // 3. Làm sạch dữ liệu trong bảng ket_qua
        $cleaned_rows = cleanDeletedQuestionsFromResults($conn, $question_id);
        
        // 4. Tính toán lại điểm số
        $new_score = recalculateScoreAfterDeletion($conn, $student_id, $khoa_id, $test_id, $original_tt_bai_test);
        
        $conn->commit();
        
        return [
            'success' => true,
            'deleted_question' => $delete_stmt->affected_rows,
            'cleaned_results' => $cleaned_rows,
            'new_score' => $new_score
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Khởi tạo biến 
$ten_khoa = '';
$current_index = isset($_SESSION['current_index']) ? intval($_SESSION['current_index']) : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$highest_score = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;
$attempts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 0;
$pass_score = 4; // Passing score

// Kiểm tra current_index hợp lệ
if ($current_index >= count($_SESSION['questions'])) {
    $current_index = 0;
    $_SESSION['current_index'] = 0;
}

// lấy tên khoá học và câu hỏi 
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $ten_khoa = $row['khoa_hoc'];
    $stmt2 = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
    $stmt2->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $questions = [];
    while ($row2 = $result2->fetch_assoc()) {
        $questions[] = [
            'id' => $row2['Id_cauhoi'],
            'question' => $row2['cauhoi'],
            'choices' => [
                'A' => $row2['cau_a'],
                'B' => $row2['cau_b'],
                'C' => $row2['cau_c'],
                'D' => $row2['cau_d']
            ],
            'explanations' => [
                'A' => $row2['giaithich_a'],
                'B' => $row2['giaithich_b'],
                'C' => $row2['giaithich_c'],
                'D' => $row2['giaithich_d']
            ],
            'correct' => $row2['dap_an'],
            'image' => $row2['hinhanh']
        ];
    }
    if (count($questions) < 1) {
        die("Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$id_baitest'.");
    }
    $_SESSION['questions'] = $questions;
    $_SESSION['ten_khoa'] = $ten_khoa;
    $_SESSION['id_baitest'] = $id_baitest;
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 1;
    }
    
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();
$stmt2->close();

// Xử lý việc gửi câu trả lời 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answer']) && isset($_SESSION['questions'])) {
    $user_answer = $_POST['answer'];
    $current_question = $_SESSION['questions'][$current_index];
    $is_correct = ($user_answer === $current_question['correct']);
    $answers[$current_index] = [
        'selected' => $user_answer,
        'is_correct' => $is_correct
    ];
    $_SESSION['answers'] = $answers;
    if ($is_correct) {
        $score++;
        $_SESSION['score'] = $score;
        if ($score > $highest_score) {
            $_SESSION['highest_score'] = $score;
        }
    }
    $current_index++;
    $_SESSION['current_index'] = $current_index;
}

// Xử lý câu tiếp
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["skip"])) {
    if ($current_index < count($_SESSION['questions']) - 1) {
        $current_index++;
        $_SESSION['current_index'] = $current_index;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý câu trước
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["goBack"])) {
    if ($current_index > 0) {
        $current_index--;
        $_SESSION['current_index'] = $current_index;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý yêu cầu xóa câu hỏi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    $question_id = (int)$_POST['question_id'];
    $result = deleteQuizQuestion($conn, $question_id, $student_id, $ma_khoa, $id_test);
    
    if ($result['success']) {
        $_SESSION['message'] = "Đã xóa câu hỏi #$question_id. Điểm số đã được cập nhật từ {$result['new_score']}.";
        
        // Cập nhật lại session questions sau khi xóa
        $questions = array_filter($_SESSION['questions'], function($q) use ($question_id) {
            return $q['id'] != $question_id;
        });
        $_SESSION['questions'] = array_values($questions);
        
        // Cập nhật lại điểm số trong session
        $_SESSION['score'] = $result['new_score'];
        $_SESSION['highest_score'] = max($_SESSION['highest_score'], $result['new_score']);
    } else {
        $_SESSION['error'] = "Lỗi: " . $result['error'];
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Xử lý thiết lập lại
if (isset($_POST['reset'])) {
    $attempts++;
    $_SESSION['attempts'] = $attempts;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
    $current_index = 0;
    $score = 0;
    $answers = [];
}

// Số lần thử tối đa
$max_attempts = getTestInfo($conn, $id_baitest, $ten_khoa);
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo htmlspecialchars($ten_khoa); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
            font-size: 17px;
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #2c3e50;
            text-align: center;
        }
        .question-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 24px;
            margin-bottom: 30px;
            border-left: 6px solid #007bff;
            transition: box-shadow 0.2s;
        }
        .question-box h3 {
            color: #007bff;
            margin-top: 0;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f1f1f1;
        }
        ul li label {
            font-size: 17px;
            cursor: pointer;
        }
        li.correct {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        li.incorrect {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        button, a.try-again, a.back-to-quiz {
            padding: 10px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        a.try-again.disabled {
            background-color: #ccc;
            pointer-events: none;
            cursor: not-allowed;
        }
        img {
            max-width: 300px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #eee;
            display: block;
        }
        .explanation-block {
            margin-top: 10px;
            padding: 15px;
            border-left: 6px solid;
            background-color: #fff3cd;
            border-radius: 6px;
        }
        .correct-answer {
            color: #2e7d32;
            font-weight: bold;
        }
        .no-answers {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
        .btn-area {
            display: flex;
            justify-content: space-between;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f8d7da;
            color: #721c24;
        }
        .delete-form {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if ($current_index < count($_SESSION['questions'])): ?>
            <?php $question = $_SESSION['questions'][$current_index]; ?>
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($ten_khoa); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($id_baitest); ?></span>
            </h2>
            
            <!-- Form xóa câu hỏi (chỉ hiển thị cho admin) -->
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <div class="delete-form">
                    <form method="POST" action="" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">
                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                        <button type="submit" name="delete_question" style="background-color: #dc3545;">Xóa câu hỏi này</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li>
                                <label>
                                    <input type="radio" name="answer" value="<?php echo $key; ?>" required> <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="btn-area">
                        <button type="submit" name="goBack" <?php echo $current_index == 0 ? 'disabled' : ''; ?>>Câu trước</button>
                        <button type="submit" name="skip" <?php echo $current_index == count($_SESSION['questions']) - 1 ? 'disabled' : ''; ?>>Câu tiếp</button>
                    </div>
                    <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
                    <button type="submit">Trả lời »</button>
                </div>
            </form>
        <?php else: ?>
            <?php
            // Xây dựng tt_bai_test thành "ID_cauhoi:dapan;ID_cauhoi:dapan
                $tt_bai_test = '';
                $display_answers = '';
                if (!empty($answers)) {
                    $answer_pairs = [];
                    $display_pairs = [];
                    foreach ($answers as $index => $answer) {
                        if (isset($_SESSION['questions'][$index]['id'])) {
                            $question_id = $_SESSION['questions'][$index]['id'];
                            $answer_pairs[] = $question_id . ":" . $answer['selected'];
                            $display_pairs[] = "Câu " . ($index + 1) . " (ID:$question_id): " . $answer['selected'];
                        }
                    }
                    $tt_bai_test = implode(";", $answer_pairs);
                    $display_answers = implode(", ", $display_pairs);
                    
                    //lấy dữ liệu tt_bai_test VARCHAR(1000)
                    if (strlen($tt_bai_test) > 1000) {
                        $tt_bai_test = substr($tt_bai_test, 0, 997) . '...';
                    }
                } else {
                    $tt_bai_test = 'Không có câu trả lời';
                    $display_answers = 'Không có câu trả lời';
                }

        // Lưu dữ liệu vào bảng ket_qua
            $conn = new mysqli("localhost", "root", "", "student");
            if ($conn->connect_error) {
                die("Kết nối thất bại: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("SELECT kq_cao_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
            $stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($highest_score > $row['kq_cao_nhat']) {
                    $stmt = $conn->prepare("UPDATE ket_qua SET kq_cao_nhat = ?, tt_bai_test = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
                    $stmt->bind_param("issis", $highest_score, $tt_bai_test, $student_id, $ma_khoa, $id_test);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("UPDATE ket_qua SET tt_bai_test = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
                    $stmt->bind_param("siss", $tt_bai_test, $student_id, $ma_khoa, $id_test);
                    $stmt->execute();
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO ket_qua (student_id, khoa_id, test_id, kq_cao_nhat, tt_bai_test) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isiss", $student_id, $ma_khoa, $id_test, $highest_score, $tt_bai_test);
                $stmt->execute();
            }
            $stmt->close();

            // kiểm tra kêt qua
            $question_ids =[]; 
            if (!empty ($tt_bai_test) && $tt_bai_test !== 'Không có câu trả lời nào') {
                $pairs = explode (';',$tt_bai_test);
                foreach ($pairs as $pair) {
                    if (!empty($pair) && strpos ($pair,':') !== false) {
                        list($id , $answer)=explode (':', $pair,2);
                        $id = trim($id);
                        if (!empty($id)){
                            $question_ids[]= $id;

                        }
                    }
                }
            }
            
            // khai báo dữ liệu hiện thị từ bảng quiz
            $valid_questio_ids =[]; // khởi tạo mảng rỗng để tranh lỗi null 
            $stmt = $conn -> prepare ("SELECT id_cauhoi FROM quiz WHERE id_baitest = ? AND ten_khoa = ?");
            $stmt -> bind_param ("ss", $id_baitest, $tenkhoa);
            $stmt-> execute ();
            $stmt = $stmt -> get_result ();
            if ($result && $result -> num_rows > 0) {
                while ($row = $result -> fetch_assoc()){
                    $valid_questio_ids [] = $row['ID_cauhoi'];

                }
            }   else {
                error_log ("Không tim thấy câu hỏi cho id_baitest ='id_baitest' và tên_khoa='$ten_khoa'");
               
                
            }
            $conn->close();

            
            ?>
            <h1>Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>
            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Số lần làm bài:</strong> <?php echo $attempts; ?> / <?php echo $max_attempts; ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>
            <hr>
            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                    <div class="question-block">
                        <p class="question-text">Câu <?php echo $index + 1; ?> (ID:<?php echo $question['id']; ?>): <?php echo htmlspecialchars($question['question']); ?></p>
                        <?php if (!empty($question['image'])): ?>
                            <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                        <?php endif; ?>
                        <ul>
                            <?php foreach ($question['choices'] as $key => $value): ?>
                                <?php
                                $style = '';
                                $is_selected = isset($answers[$index]) && $key === $answers[$index]['selected'];
                                $is_correct = $key === $question['correct'];
                                if ($is_selected) {
                                    $style = $answers[$index]['is_correct'] ? 'correct' : 'incorrect';
                                } elseif ($is_correct) {
                                    $style = 'correct';
                                }
                                ?>
                                <li class="<?php echo $style; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="explanation-block" style="border-color: <?php echo isset($answers[$index]) && $answers[$index]['is_correct'] ? '#28a745' : '#dc3545'; ?>;">
                            <?php if (isset($answers[$index]['selected']) && !$answers[$index]['is_correct']): ?>
                                <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$answers[$index]['selected']]); ?></p>
                            <?php endif; ?>
                        </div>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <form method="POST" action="">
                <button type="submit" name="reset" value="1" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>🔁 Làm lại (<?php echo $attempts; ?> / <?php echo $max_attempts; ?>)</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>