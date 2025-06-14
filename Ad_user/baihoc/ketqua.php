<?php
// Thiết lập múi giờ cho Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Bật hiển thị lỗi để hỗ trợ gỡ lỗi (nên tắt trong môi trường sản xuất)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bắt đầu phiên làm việc
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để truy cập!'); window.location.href = 'login.php';</script>";
    exit();
}

// Kiểm tra quyền truy cập
if ($_SESSION['student_id'] != 1) {
    echo "<script>alert('Bạn không có quyền truy cập!'); window.location.href = 'login.php';</script>";
    exit();
}

// Kiểm tra xem test_id có được cung cấp không
if (!isset($_GET['test_id']) || !is_numeric($_GET['test_id'])) {
    echo "<script>alert('Không tìm thấy ID bài test!'); window.location.href = 'quiz.php';</script>";
    exit();
}

// Kiểm tra dữ liệu session
if (!isset($_SESSION['test_id']) || $_SESSION['test_id'] != $_GET['test_id'] || empty($_SESSION['questions'])) {
    echo "<script>alert('Dữ liệu bài test không hợp lệ!'); window.location.href = 'quiz.php';</script>";
    exit();
}

$test_id = (int)$_GET['test_id'];

// Kết nối với cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy thông tin bài test
$sql_test = "SELECT t.id_test, t.ten_test, t.id_khoa, t.so_cau_hien_thi, t.lan_thu, t.Pass, kh.khoa_hoc 
             FROM test t 
             JOIN khoa_hoc kh ON t.id_khoa = kh.id 
             WHERE t.id_test = ?";
$stmt_test = $conn->prepare($sql_test);
if (!$stmt_test) {
    $conn->close();
    die("Lỗi chuẩn bị truy vấn bài test: " . $conn->error);
}
$stmt_test->bind_param("i", $test_id);
$stmt_test->execute();
$test_result = $stmt_test->get_result();

if ($test_result->num_rows === 0) {
    $stmt_test->close();
    $conn->close();
    echo "<script>alert('ID bài test ($test_id) không tồn tại!'); window.location.href = 'quiz.php';</script>";
    exit();
}

$test = $test_result->fetch_assoc();
$ten_test = $test['ten_test'];
$ten_khoa = $test['khoa_hoc'];
$max_attempts = $test['lan_thu'];
$pass_score = $test['Pass'];
$so_cau_hien_thi = $test['so_cau_hien_thi'];
$stmt_test->close();

// Kiểm tra quyền truy cập khóa học
$allowed_courses = isset($_SESSION['Khoahoc']) ? array_filter(explode(',', $_SESSION['Khoahoc']), function($value) {
    return is_numeric($value) && (int)$value > 0;
}) : [];
if (!in_array($test['id_khoa'], $allowed_courses)) {
    $conn->close();
    echo "<script>alert('Bạn không có quyền truy cập bài test này!'); window.location.href = 'quiz.php';</script>";
    exit();
}

// Lấy dữ liệu từ session
$questions = $_SESSION['questions'];
$answers = $_SESSION['answers'] ?? [];
$score = $_SESSION['score'] ?? 0;
$highest_score = $_SESSION['highest_score'] ?? 0;
$attempts = $_SESSION['attempts'] ?? 1;

// Tính điểm đạt dựa trên phần trăm
$pass_score_absolute = ceil(($pass_score / 100) * $so_cau_hien_thi);

// Xử lý reset bài test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset']) && $attempts < $max_attempts) {
    $_SESSION['attempts']++;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['questions'] = [];
    $_SESSION['current_index'] = 0;
    $conn->close();
    header("Location: take_test.php?test_id=" . $test_id);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả bài test - <?php echo htmlspecialchars($ten_test); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logout {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .logout:hover {
            background-color: #c82333;
        }
        .question-block {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 24px;
            margin-bottom: 30px;
            border-left: 6px solid #007bff;
        }
        .question-text {
            font-size: 1.2em;
            margin-bottom: 15px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        li.correct {
            background-color: #e0f7fa; /* Màu xanh nhạt cho đáp án đúng */
            color: #00695c; /* Màu chữ tối để dễ đọc */
            font-weight: bold;
        }
        li.incorrect {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        .explanation-block {
            margin-top: 10px;
            padding: 15px;
            border-left: 6px solid;
            background-color: #fff3cd;
            border-radius: 6px;
        }
        button, .back-to-quiz {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin: 10px 5px;
            display: inline-block;
        }
        button:hover, .back-to-quiz:hover {
            background-color: #0056b3;
        }
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        img {
            max-width: 300px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #eee;
            display: block;
        }
        .no-answers {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['student_name']); ?></h2>
            <a href="logout.php" class="logout">Đăng xuất</a>
        </div>
        <h1>Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($ten_test); ?></h1>
        <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
        <p><strong>Bài test:</strong> <?php echo htmlspecialchars($ten_test); ?></p>
        <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
        <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION['questions']); ?></p>
        <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?> / <?php echo count($_SESSION['questions']); ?></p>
        <p><strong>Số lần làm bài:</strong> <?php echo $attempts; ?> / <?php echo $max_attempts; ?></p>
        <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score_absolute ? 'Đạt' : 'Không đạt'; ?></p>
        <hr>
        <?php if (empty($answers)): ?>
            <p class="no-answers">Bạn chưa trả lời câu hỏi nào! 
                <a class="back-to-quiz" href="take_test.php?test_id=<?php echo $test_id; ?>&reset=1">Quay lại làm bài</a>
            </p>
        <?php else: ?>
            <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                <div class="question-block">
                    <p class="question-text">Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></p>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <?php
                            $style = '';
                            if (isset($answers[$question['id']]['selected']) && $key === $answers[$question['id']]['selected']) {
                                $style = $answers[$question['id']]['is_correct'] ? 'correct' : 'incorrect';
                            }
                            ?>
                            <li class="<?php echo $style; ?>">
                                <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                <?php if ($key === $question['correct']) echo ' (Đáp án đúng)'; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="explanation-block" style="border-color: <?php echo isset($answers[$question['id']]['is_correct']) && $answers[$question['id']]['is_correct'] ? 'orange' : 'red'; ?>;">
                        <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$question['correct']]); ?></p>
                    </div>
                    <hr>
                </div>
            <?php endforeach; ?>
            <form method="POST" action="">
                <button type="submit" name="reset" value="1" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>
                    🔁 Làm lại (<?php echo $attempts; ?> / <?php echo $max_attempts; ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>