<?php
ob_start(); // Bật output buffering để tránh lỗi headers already sent
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
$link_quay_lai = "index.php"; // Thay bằng URL thực tế
$link_tiep_tuc = "dashboard.php"; // Thay bằng URL thực tế
$page="page2.php";

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

// Lấy kết quả lần thử gần nhất và số lần thử
$last_attempt_score = 0;
$last_attempt_answers = 'Không có câu trả lời';
$attempts = 0;
$stmt = $conn->prepare("SELECT kq_cao_nhat, test_gan_nhat, so_lan_thu FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ? ORDER BY student_id DESC LIMIT 1");
$stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_attempt_score = $row['kq_cao_nhat'];
    $last_attempt_answers = $row['test_gan_nhat'];
    $attempts = (int)$row['so_lan_thu'];
}
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
function saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score, $attempts) {
    // Xây dựng tt_bai_test
    $tt_bai_test = '';
    if (!empty($answers)) {
        $answer_pairs = [];
        foreach ($answers as $index => $answer) {
            if (isset($_SESSION['questions'][$index]['id'])) {
                $question_id = $_SESSION['questions'][$index]['id'];
                $answer_pairs[] = $question_id . ":" . $answer['selected'];
            }
        }
        $tt_bai_test = implode(";", $answer_pairs);
        if (strlen($tt_bai_test) > 1000) {
            $tt_bai_test = substr($tt_bai_test, 0, 997) . '...';
        }
    } else {
        $tt_bai_test = 'Không có câu trả lời';
    }

    // Tính lại điểm số từ answers để đảm bảo chính xác
    $calculated_score = 0;
    foreach ($answers as $index => $answer) {
        if (isset($_SESSION['questions'][$index]['correct']) && $answer['is_correct']) {
            $calculated_score++;
        }
    }

    // Lưu hoặc cập nhật vào bảng ket_qua
    $stmt = $conn->prepare("SELECT kq_cao_nhat, test_cao_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
    $stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $highest_score = max($calculated_score, $row['kq_cao_nhat']);
        $test_cao_nhat = $highest_score > $row['kq_cao_nhat'] ? $tt_bai_test : $row['test_cao_nhat'];
        $stmt = $conn->prepare("UPDATE ket_qua SET kq_cao_nhat = ?, test_cao_nhat = ?, test_gan_nhat = ?, so_lan_thu = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $stmt->bind_param("issisis", $highest_score, $test_cao_nhat, $tt_bai_test, $attempts, $student_id, $ma_khoa, $id_test);
        $stmt->execute();
    } else {
        $highest_score = $calculated_score;
        $stmt = $conn->prepare("INSERT INTO ket_qua (student_id, khoa_id, test_id, so_lan_thu, kq_cao_nhat, test_cao_nhat, test_gan_nhat) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisiss", $student_id, $ma_khoa, $id_test, $attempts, $highest_score, $tt_bai_test, $tt_bai_test);
        $stmt->execute();
    }
    $stmt->close();
    return $highest_score; // Trả về điểm cao nhất
}

// Khởi tạo biến
$ten_khoa = '';
$current_index = isset($_SESSION['current_index']) ? intval($_SESSION['current_index']) : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$highest_score = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;
$pass_score = 4; // Điểm đạt

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
    if (count($questions) < 1) {
        die("Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$id_baitest'.");
    }
    $_SESSION['questions'] = $questions;
    $_SESSION['ten_khoa'] = $ten_khoa;
    $_SESSION['id_baitest'] = $id_baitest;
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = $attempts + 1;
    }
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();
$stmt2->close();

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
    // Lưu câu trả lời vào cơ sở dữ liệu
    $highest_score = saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score, $_SESSION['attempts']);
    $_SESSION['highest_score'] = $highest_score;
    
    // Chỉ tăng current_index nếu chưa phải câu cuối
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
    // Lưu lại toàn bộ câu trả lời trước khi hiển thị kết quả
    $highest_score = saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score, $_SESSION['attempts']);
    $_SESSION['highest_score'] = $highest_score;
    $current_index = count($_SESSION['questions']);
    $_SESSION['current_index'] = $current_index;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Xử lý thiết lập lại
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reset'])) {
    $_SESSION['attempts'] = $attempts + 1;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
    $current_index = 0;
    $score = 0;
    $answers = [];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Hàm làm sạch câu hỏi đã xóa và tính lại điểm cao nhất
function cleanDeletedQuestionsFromResults($conn, $deleted_question_id, $id_baitest, $ten_khoa) {
    try {
        $conn->begin_transaction();

        // Lấy tất cả bản ghi có chứa câu hỏi đã xóa
        $stmt = $conn->prepare("SELECT student_id, khoa_id, test_id, test_gan_nhat, test_cao_nhat, kq_cao_nhat, so_lan_thu FROM ket_qua WHERE test_gan_nhat LIKE CONCAT('%', ?, ':%') OR test_cao_nhat LIKE CONCAT('%', ?, ':%')");
        $stmt->bind_param("ii", $deleted_question_id, $deleted_question_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Lấy danh sách câu hỏi hợp lệ và đáp án đúng
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

        $update_stmt = $conn->prepare("UPDATE ket_qua SET test_gan_nhat = ?, test_cao_nhat = ?, kq_cao_nhat = ?, so_lan_thu = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $affected_rows = 0;

        while ($row = $result->fetch_assoc()) {
            $student_id = $row['student_id'];
            $khoa_id = $row['khoa_id'];
            $test_id = $row['test_id'];
            $test_gan_nhat = $row['test_gan_nhat'];
            // $test_cao_nhat = $row['test_cao_nhat'];
            $current_highest_score = $row['kq_cao_nhat'];
            $so_lan_thu = $row['so_lan_thu'];

            // Xóa câu hỏi đã bị xóa khỏi test_gan_nhat
            $pairs = explode(';', $test_gan_nhat);
            $new_pairs = [];
            foreach ($pairs as $pair) {
                if (empty($pair)) continue;
                $parts = explode(':', $pair);
                if (count($parts) === 2 && $parts[0] != $deleted_question_id) {
                    $new_pairs[] = $pair;
                }
            }
            $new_test_gan_nhat = implode(';', $new_pairs);

            // Xóa câu hỏi đã bị xóa khỏi test_cao_nhat
            $pairs = explode(';', $test_cao_nhat);
            $new_pairs = [];
            foreach ($pairs as $pair) {
                if (empty($pair)) continue;
                $parts = explode(':', $pair);
                if (count($parts) === 2 && $parts[0] != $deleted_question_id) {
                    $new_pairs[] = $pair;
                }
            }
            $new_test_cao_nhat = implode(';', $new_pairs);

            // Tính lại điểm số cho test_cao_nhat
            $new_score = 0;
            if (!empty($new_test_cao_nhat) && $new_test_cao_nhat !== 'Không có câu trả lời') {
                $pairs = explode(';', $new_test_cao_nhat);
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

            // Điều chỉnh điểm cao nhất
            $new_highest_score = min($new_score, $current_highest_score, $total_questions);

            // Cập nhật ket_qua
            $update_stmt->bind_param("ssisisi", $new_test_gan_nhat, $new_test_cao_nhat, $new_highest_score, $so_lan_thu, $student_id, $khoa_id, $test_id);
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

        // Xóa câu hỏi từ bảng quiz
        $delete_stmt = $conn->prepare("DELETE FROM quiz WHERE Id_cauhoi = ?");
        $delete_stmt->bind_param("i", $question_id);
        $delete_stmt->execute();

        if ($delete_stmt->affected_rows === 0) {
            throw new Exception("Không tìm thấy câu hỏi với ID: $question_id");
        }

        // Làm sạch dữ liệu trong bảng ket_qua và tính lại điểm
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
        // Cập nhật lại danh sách câu hỏi từ database
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

        // Cập nhật lại current_index nếu cần
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
            max-width: 70%;
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
            padding: 14px;
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
            max-width: 40%;
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
        .last-attempt {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($current_index < count($_SESSION['questions'])): ?>
            <!-- Hiển thị link quay lại khi đang làm bài test -->
            <div class="navigation-links">
                <a href="<?php echo htmlspecialchars($link_quay_lai); ?>" class="nav-link" style="margin-right: 85%;">← Quay lại</a>
            </div>
            
            <?php $question = $_SESSION['questions'][$current_index]; ?>
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($ten_khoa); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($id_baitest); ?></span>
            </h2>
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?> / <?php echo count($_SESSION['questions']); ?>: <?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <div style="display: flex; justify-content: center">
                            <img src="<?php echo 'admin/' . htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                        </div>
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li>
                                <label>
                                    <input type="radio" name="answer" value="<?php echo $key; ?>" 
                                        <?php echo isset($answers[$current_index]) && $answers[$current_index]['selected'] === $key ? 'checked' : ''; ?> 
                                        required> 
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </label>
                                <?php if (!empty($question['images'][$key])): ?>
                                    <br><img src="<?php echo 'admin/' . htmlspecialchars($question['images'][$key]); ?>" alt="Ảnh đáp án <?php echo htmlspecialchars($key); ?>">
                                <?php endif; ?>
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
            // Xây dựng tt_bai_test
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

            // Lưu dữ liệu vào bảng ket_qua
            $conn = new mysqli("localhost", "root", "", "student");
            if ($conn->connect_error) {
                die("Kết nối thất bại: " . $conn->connect_error);
            }
            $highest_score = saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score, $_SESSION['attempts']);
            $_SESSION['highest_score'] = $highest_score;
            $conn->close();
            
            
            ?>
            
            
            <h1>Kết quả bài test gần nhất - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>
            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo htmlspecialchars($score); ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo htmlspecialchars($highest_score); ?></p>
            <p><strong>Số lần thử:</strong> <?php echo htmlspecialchars($_SESSION['attempts']); ?> / <?php echo $max_attempts; ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>

            <div class="navigation-actions" style="display: flex; align-items: center;">
                <a href="<?php echo htmlspecialchars ($page) ;?>" class="nav-link" style="margin-left: 50%; text-decoration: none; padding: 8px 14px; background-color: #3182ce; color: white; border-radius: 5px;">
                    Bắt đầu làm bài 
                </a>
            </div>
            <hr>
          
            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                    <div class="question-block">
                        <p class="question-text" style="font-size:18px">Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></p>
                        <?php if (!empty($question['image'])): ?>
                            <div style="display: flex; justify-content: center; margin-top: 15px;">
                                <img src="<?php echo 'admin/' . htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                            </div>
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
                                        <br><img src="<?php echo 'admin/' . htmlspecialchars($question['images'][$key]); ?>" alt="Ảnh đáp án <?php echo $key; ?>">
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
            
        <?php endif; ?>
    </div>
</body>
</html>
<?php ob_end_flush(); // Kết thúc output buffering ?>