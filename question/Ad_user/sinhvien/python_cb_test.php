<?php
ob_start(); // Bật output buffering
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ma_khoa = '10'; // Mã khóa học
$id_test = '71'; // Mã bài test
$student_id = $_SESSION['student_id'];
$link_quay_lai = "khoahoc.php"; // URL thực tế
$link_tiep_tuc = "add_khoahoc.php"; // URL thực tế

// Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc']; // Ví dụ: "6,4"
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

// Kiểm tra ID bài test
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

// Lấy danh sách khóa học
function getCoursesFromDB($conn) {
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    return $courses;
}

// Lấy thông tin bài test (số lần thử tối đa)
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

// Hàm lưu câu trả lời vào bảng ket_qua
function saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score) {
    $test_cao_nhat= '';
    $test_gan_nhat= '';
    if (!empty($answers)) {
        $answer_pairs = [];
        foreach ($answers as $index => $answer) {
            if (isset($_SESSION['questions'][$index]['id'])) {
                $question_id = $_SESSION['questions'][$index]['id'];
                $answer_pairs[] = $question_id . ":" . $answer['selected'];
            }
        }
        $test_cao_nhat = implode(";", $answer_pairs);
        if (strlen($test_cao_nhat) > 1000) {
            $test_cao_nhat = substr($test_cao_nhat, 0, 997) . '...';
        }
    } else {
        $tt_bai_test = 'Không có câu trả lời';
    }

    // Kiểm tra xem đã có bản ghi trong ket_qua chưa
    $stmt = $conn->prepare("SELECT kq_cao_nhat, so_lan_thu, test_gan_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
    $stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $highest_score = max($score, $row['kq_cao_nhat']);
        $so_lan_thu = $row['so_lan_thu'] + 1;
        $test_gan_nhat = $test_cao_nhat;
        $stmt = $conn->prepare("UPDATE ket_qua SET kq_cao_nhat = ?, so_lan_thu = ?, tt_bai_test = ?, test_gan_nhat = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $stmt->bind_param("iisssis", $highest_score, $so_lan_thu, $tt_bai_test, $test_gan_nhat, $student_id, $ma_khoa, $id_test);
        $stmt->execute();
    } else {
        $so_lan_thu = 1;
        $highest_score = $score;
        $test_gan_nhat = $tt_bai_test;
        $stmt = $conn->prepare("INSERT INTO ket_qua (student_id, khoa_id, test_id, so_lan_thu, kq_cao_nhat, tt_bai_test, test_gan_nhat) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisiiss", $student_id, $ma_khoa, $id_test, $so_lan_thu, $highest_score, $tt_bai_test, $test_gan_nhat);
        $stmt->execute();
    }
    $stmt->close();
    return $highest_score;
}

// Khởi tạo biến
$ten_khoa = '';
$current_index = isset($_SESSION['current_index']) ? intval($_SESSION['current_index']) : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$highest_score = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;
$attempts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 0;
$pass_score = 4; // Điểm đạt
$test_gan_nhat = 'Chưa có bài test'; // Default value

// Lấy tên khóa học và câu hỏi
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
            'images' => [
                'A' => $row2['hinhanh_a'],
                'B' => $row2['hinhanh_b'],
                'C' => $row2['hinhanh_c'],
                'D' => $row2['hinhanh_d']
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
    if (empty($questions)) {
        die("Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$id_baitest'.");
    }
    $_SESSION['questions'] = $questions;
    $_SESSION['ten_khoa'] = $ten_khoa;
    $_SESSION['id_baitest'] = $id_baitest;
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 1;
        $attempts = 1;
    }
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();
$stmt2->close();

// Lấy thông tin số lần thử, điểm cao nhất và bài test gần nhất
$so_lan_thu = $attempts;
$kq_cao_nhat = $highest_score;
$stmt = $conn->prepare("SELECT so_lan_thu, kq_cao_nhat, test_gan_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
$stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $so_lan_thu = $row['so_lan_thu'];
    $kq_cao_nhat = $row['kq_cao_nhat'];
    $test_gan_nhat = $row['test_gan_nhat'] ?: 'Chưa có bài test';
}
$stmt->close();

// Xử lý gửi câu trả lời
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answer_submit']) && isset($_POST['answer']) && isset($_SESSION['questions'])) {
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
    }
    $highest_score = saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score);
    $_SESSION['highest_score'] = $highest_score;

    if ($current_index < count($_SESSION['questions']) - 1) {
        $current_index++;
        $_SESSION['current_index'] = $current_index;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý câu sau
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['next'])) {
    if (isset($_POST['answer']) && isset($_SESSION['questions'][$current_index])) {
        $user_answer = $_POST['answer'];
        $current_question = $_SESSION['questions'][$current_index];
        $is_correct = ($user_answer === $current_question['correct']);
        $answers[$current_index] = [
            'selected' => $user_answer,
            'is_correct' => $is_correct
        ];
        $_SESSION['answers'] = $answers;
        if ($is_correct && !isset($_SESSION['score_saved'][$current_index])) {
            $score++;
            $_SESSION['score'] = $score;
            $_SESSION['score_saved'][$current_index] = true;
        }
    }

    if ($current_index < count($_SESSION['questions']) - 1) {
        $current_index++;
        $_SESSION['current_index'] = $current_index;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý câu trước
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['previous'])) {
    if (isset($_POST['answer']) && isset($_SESSION['questions'][$current_index])) {
        $user_answer = $_POST['answer'];
        $current_question = $_SESSION['questions'][$current_index];
        $is_correct = ($user_answer === $current_question['correct']);
        $answers[$current_index] = [
            'selected' => $user_answer,
            'is_correct' => $is_correct
        ];
        $_SESSION['answers'] = $answers;
        if ($is_correct && !isset($_SESSION['score_saved'][$current_index])) {
            $score++;
            $_SESSION['score'] = $score;
            $_SESSION['score_saved'][$current_index] = true;
        }
    }

    if ($current_index > 0) {
        $current_index--;
        $_SESSION['current_index'] = $current_index;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý nộp bài
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    if (isset($_POST['answer']) && isset($_SESSION['questions'][$current_index])) {
        $user_answer = $_POST['answer'];
        $current_question = $_SESSION['questions'][$current_index];
        $is_correct = ($user_answer === $current_question['correct']);
        $answers[$current_index] = [
            'selected' => $user_answer,
            'is_correct' => $is_correct
        ];
        $_SESSION['answers'] = $answers;
        if ($is_correct && !isset($_SESSION['score_saved'][$current_index])) {
            $score++;
            $_SESSION['score'] = $score;
            $_SESSION['score_saved'][$current_index] = true;
        }
    }

    $highest_score = saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score);
    $_SESSION['highest_score'] = $highest_score;
    $current_index = count($_SESSION['questions']);
    $_SESSION['current_index'] = $current_index;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý thiết lập lại (bắt đầu làm bài)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reset'])) {
    $attempts++;
    $_SESSION['attempts'] = $attempts;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
    $_SESSION['score_saved'] = [];
    $current_index = 0;
    $score = 0;
    $answers = [];
    // Không xóa dữ liệu cũ trong ket_qua để giữ lịch sử
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Hàm làm sạch câu hỏi đã xóa và tính lại điểm cao nhất
function cleanDeletedQuestionsFromResults($conn, $deleted_question_id, $id_baitest, $ten_khoa) {
    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT student_id, khoa_id, test_id, tt_bai_test, kq_cao_nhat, test_gan_nhat FROM ket_qua WHERE tt_bai_test LIKE CONCAT('%', ?, ':%') OR test_gan_nhat LIKE CONCAT('%', ?, ':%')");
        $stmt->bind_param("ii", $deleted_question_id, $deleted_question_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $stmt_quiz = $conn->prepare("SELECT Id_cauhoi, dap_an FROM quiz WHERE id_baitest = ? AND ten_khoa = ?");
        $stmt_quiz->bind_param("ss", $id_baitest, $ten_khoa);
        $stmt_quiz->execute();
        $quiz_result = $stmt_quiz->get_result();
        $correct_answers = [];
        $total_questions = 0;
        while ($quiz_row = $quiz_result->fetch_assoc()) {
            $correct_answers[$quiz_row['Id_cauhoi']] = $quiz_row['dap_an'];
            $total_questions++;
        }
        $stmt_quiz->close();

        $update_stmt = $conn->prepare("UPDATE ket_qua SET tt_bai_test = ?, kq_cao_nhat = ?, test_gan_nhat = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $affected_rows = 0;

        while ($row = $result->fetch_assoc()) {
            $student_id = $row['student_id'];
            $khoa_id = $row['khoa_id'];
            $test_id = $row['test_id'];
            $tt_bai_test = $row['tt_bai_test'];
            $test_gan_nhat = $row['test_gan_nhat'];
            $kq_cao_nhat = $row['kq_cao_nhat'];

            // Xử lý tt_bai_test
            $pairs = explode(';', $tt_bai_test);
            $new_pairs = [];
            foreach ($pairs as $pair) {
                if (empty($pair)) continue;
                $parts = explode(':', $pair);
                if (count($parts) === 2 && $parts[0] != $deleted_question_id) {
                    $new_pairs[] = $pair;
                }
            }
            $new_tt_bai_test = implode(';', $new_pairs);

            // Xử lý test_gan_nhat
            $gan_nhat_pairs = explode(';', $test_gan_nhat);
            $new_gan_nhat_pairs = [];
            foreach ($gan_nhat_pairs as $pair) {
                if (empty($pair)) continue;
                $parts = explode(':', $pair);
                if (count($parts) === 2 && $parts[0] != $deleted_question_id) {
                    $new_gan_nhat_pairs[] = $pair;
                }
            }
            $new_test_gan_nhat = implode(';', $new_gan_nhat_pairs);

            $new_score = 0;
            if (!empty($new_tt_bai_test) && $new_tt_bai_test !== 'Không có câu trả lời') {
                $pairs = explode(';', $new_tt_bai_test);
                foreach ($pairs as $pair) {
                    if (empty($pair)) continue;
                    $parts = explode(':', $pair);
                    if (count($parts) === 2 && isset($correct_answers[$parts[0]])) {
                        if ($parts[1] === $correct_answers[$parts[0]]) {
                            $new_score++;
                        }
                    }
                }
            }

            $new_highest_score = min($new_score, $kq_cao_nhat, $total_questions);
            $update_stmt->bind_param("sssis", $new_tt_bai_test, $new_highest_score, $new_test_gan_nhat, $student_id, $khoa_id, $test_id);
            $update_stmt->execute();
            $affected_rows += $update_stmt->affected_rows;
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
function deleteQuizQuestion($conn, $question_id, $id_baitest, $ten_khoa) {
    try {
        $conn->begin_transaction();

        $delete_stmt = $conn->prepare("DELETE FROM quiz WHERE Id_cauhoi = ?");
        $delete_stmt->bind_param("i", $question_id);
        $delete_stmt->execute();

        if ($delete_stmt->affected_rows === 0) {
            throw new Exception("Không tìm thấy câu hỏi với ID: $question_id");
        }

        $cleaned_rows = cleanDeletedQuestionsFromResults($conn, $question_id, $id_baitest, $ten_khoa);
        $conn->commit();

        return [
            'success' => true,
            'deleted_question' => $delete_stmt->affected_rows,
            'cleaned_results' => $cleaned_rows
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Xử lý yêu cầu xóa câu hỏi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    $question_id = (int)$_POST['delete_question'];
    $result = deleteQuizQuestion($conn, $question_id, $id_baitest, $ten_khoa);
    if ($result['success']) {
        $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
        $stmt->bind_param("ss", $ten_khoa, $id_baitest);
        $stmt->execute();
        $result2 = $stmt->get_result();
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
                'images' => [
                    'A' => $row2['hinhanh_a'],
                    'B' => $row2['hinhanh_b'],
                    'C' => $row2['hinhanh_c'],
                    'D' => $row2['hinhanh_d']
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
        $stmt->close();
        $_SESSION['questions'] = $questions;
        $_SESSION['current_index'] = min($_SESSION['current_index'], count($questions) - 1);
        $_SESSION['message'] = "Đã xóa câu hỏi #$question_id, cập nhật lại số câu hỏi.";
    } else {
        $_SESSION['error'] = "Lỗi: " . $result['error'];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Số lần thử tối đa
$max_attempts = getTestInfo($conn, $id_baitest, $ten_khoa);
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
        button, a.try-again, a.back-to-quiz, a.nav-link {
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
            margin-right: 10px;
        }
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        a.try-again.disabled {
            background-color: #ccc;
            pointer-events: none;
            cursor: not-allowed;
        }
        a.nav-link {
            background-color: #28a745;
        }
        button:hover:not(:disabled), a.try-again:hover:not(.disabled), a.back-to-quiz:hover {
            background-color: #0056b3;
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
            align-items: center;
        }
        .navigation-links {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .debug-info {
            background-color: #f8d7da;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Debug info -->
        <div class="debug-info">
            <p>Current Index: <?php echo $current_index; ?></p>
            <p>Total Questions: <?php echo count($_SESSION['questions']); ?></p>
            <p>Answers: <?php echo json_encode($_SESSION['answers']); ?></p>
            <p>POST Data: <?php echo json_encode($_POST); ?></p>
        </div>

        <?php if ($current_index < count($_SESSION['questions'])): ?>
            <div class="navigation-links">
                <a href="<?php echo htmlspecialchars($link_quay_lai); ?>" class="nav-link" style="margin-right: 85%;">← Quay lại</a>
            </div>
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($ten_khoa); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($id_baitest); ?></span>
            </h2>

            <h1>Thông tin bài test - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>

            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo $kq_cao_nhat; ?></p>
            <p><strong>Số lần làm bài:</strong> <?php echo $so_lan_thu; ?></p>
            <p><strong>Bài test gần nhất:</strong> <?php echo htmlspecialchars($test_gan_nhat); ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>
            <hr>
            <form method="POST" action="">
                <button type="submit" name="reset" value="1" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>
                    Bắt đầu làm bài
                </button>
            </form>

            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                    <div class="question-block">
                        <p class="question-text">Câu <?php echo $index + 1; ?> <?php echo htmlspecialchars($question['question']); ?></p>
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
                                }
                                ?>
                                <li class="<?php echo $style; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                    <?php if (!empty($question['images'][$key])): ?>
                                        <br><img src="<?php echo htmlspecialchars($question['images'][$key]); ?>" alt="Hình ảnh đáp án <?php echo $key; ?>">
                                    <?php endif; ?>
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

            <?php $question = $_SESSION['questions'][$current_index]; ?>
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?> / <?php echo count($_SESSION['questions']); ?>: <?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li>
                                <label>
                                    <input type="radio" name="answer" value="<?php echo $key; ?>" 
                                        <?php echo isset($answers[$current_index]) && $answers[$current_index]['selected'] === $key ? 'checked' : ''; ?> 
                                        required> 
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                    <?php if (!empty($question['images'][$key])): ?>
                                        <br><img src="<?php echo htmlspecialchars($question['images'][$key]); ?>" alt="Hình ảnh đáp án <?php echo $key; ?>">
                                    <?php endif; ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="btn-area">
                        <button type="submit" name="previous" <?php echo $current_index == 0 ? 'disabled' : ''; ?>>Câu trước</button>
                        <?php if ($current_index == count($_SESSION['questions']) - 1): ?>
                            <button type="submit" name="submit">Nộp bài</button>
                        <?php else: ?>
                            <button type="submit" name="next">Câu sau</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

        <?php else: ?>
            <?php
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
                if (strlen($tt_bai_test) > 1000) {
                    $tt_bai_test = substr($tt_bai_test, 0, 997) . '...';
                }
            } else {
                $tt_bai_test = 'Không có câu trả lời';
                $display_answers = 'Không có câu trả lời';
            }

            $highest_score = saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score);
            $_SESSION['highest_score'] = $highest_score;

            $question_ids = [];
            if (!empty($tt_bai_test) && $tt_bai_test !== 'Không có câu trả lời') {
                $pairs = explode(';', $tt_bai_test);
                foreach ($pairs as $pair) {
                    if (!empty($pair) && strpos($pair, ':') !== false) {
                        list($id, $answer) = explode(':', $pair, 2);
                        $id = trim($id);
                        if (!empty($id)) {
                            $question_ids[] = $id;
                        }
                    }
                }
            }

            $valid_question_ids = [];
            $stmt = $conn->prepare("SELECT Id_cauhoi FROM quiz WHERE id_baitest = ? AND ten_khoa = ?");
            $stmt->bind_param("ss", $id_baitest, $ten_khoa);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $valid_question_ids[] = $row['Id_cauhoi'];
                }
            } else {
                error_log("Không tìm thấy câu hỏi cho id_baitest='$id_baitest' và ten_khoa='$ten_khoa'");
            }
            $stmt->close();
            ?>

            <h1>Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>

            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?></p>
            <p><strong>Số lần làm bài:</strong> <?php echo $so_lan_thu; ?></p>
            <p><strong>Bài test gần nhất:</strong> <?php echo htmlspecialchars($test_gan_nhat); ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>
            <hr>
            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                    <div class="question-block">
                        <p class="question-text">Câu <?php echo $index + 1; ?> <?php echo htmlspecialchars($question['question']); ?></p>
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
                                }
                                ?>
                                <li class="<?php echo $style; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                    <?php if (!empty($question['images'][$key])): ?>
                                        <br><img src="<?php echo htmlspecialchars($question['images'][$key]); ?>" alt="Hình ảnh đáp án <?php echo $key; ?>">
                                    <?php endif; ?>
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
            <div class="navigation-actions" style="display: flex; align-items: center;">
                <form method="POST" action="">
                    <button type="submit" name="reset" value="1" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>
                        🔁 Làm lại (<?php echo $attempts; ?> / <?php echo $max_attempts; ?>)
                    </button>
                </form>
                <a href="<?php echo htmlspecialchars($link_tiep_tuc); ?>" class="nav-link" style="margin-left: 72%; text-decoration: none; padding: 8px 14px; background-color: #3182ce; color: white; border-radius: 5px;">
                    → Tiếp tục
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
<?php ob_end_flush(); ?>