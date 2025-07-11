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

// Kiểm tra xem test_id có được cung cấp không
if (!isset($_GET['test_id']) || !is_numeric($_GET['test_id'])) {
    echo "<script>alert('Không tìm thấy ID bài test!'); window.location.href = 'quiz.php';</script>";
    exit();
}

// Lấy và làm sạch test_id
$test_id = (int)$_GET['test_id'];

// Kết nối với cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy thông tin bài test tư file khoa học
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
$so_cau_hien_thi = $test['so_cau_hien_thi'];
$max_attempts = $test['lan_thu'];
$pass_score = $test['Pass'];
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

// Khởi tạo session nếu cần
if (!isset($_SESSION['test_id']) || $_SESSION['test_id'] != $test_id) {
    $_SESSION['test_id'] = $test_id;
    $_SESSION['questions'] = [];
    $_SESSION['answers'] = [];
    $_SESSION['score'] = 0;
    $_SESSION['highest_score'] = 0;
    $_SESSION['attempts'] = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 1;
    $_SESSION['current_index'] = 0;
}

// Lấy câu hỏi nếu chưa có trong session
if (empty($_SESSION['questions'])) {
    $sql_questions = "SELECT * FROM quiz 
                     WHERE ten_khoa = ? AND id_baitest = ? 
                     ORDER BY RAND() 
                     LIMIT ?";
    $stmt_questions = $conn->prepare($sql_questions);
    if (!$stmt_questions) {
        $conn->close();
        die("Lỗi chuẩn bị truy vấn câu hỏi: " . $conn->error);
    }
    $stmt_questions->bind_param("ssi", $ten_khoa, $ten_test, $so_cau_hien_thi);
    $stmt_questions->execute();
    $questions_result = $stmt_questions->get_result();

    while ($row = $questions_result->fetch_assoc()) {
        $_SESSION['questions'][] = [
            'id' => $row['Id_cauhoi'],
            'question' => $row['cauhoi'],
            'choices' => [
                'A' => $row['cau_a'],
                'B' => $row['cau_b'],
                'C' => $row['cau_c'],
                'D' => $row['cau_d']
            ],
            'explanations' => [
                'A' => $row['giaithich_a'],
                'B' => $row['giaithich_b'],
                'C' => $row['giaithich_c'],
                'D' => $row['giaithich_d']
            ],
            'correct' => $row['dap_an'],
            'image' => $row['hinhanh']
        ];
    }
    if ($questions_result->num_rows === 0) {
        $stmt_questions->close();
        $conn->close();
        echo "<script>alert('Không có câu hỏi nào cho bài test này!'); window.location.href = 'quiz.php';</script>";
        exit();
    }
    $stmt_questions->close();
}

$questions = $_SESSION['questions'];
$total_questions = count($questions);
$current_index = isset($_GET['question']) ? max(1, min((int)$_GET['question'], $total_questions)) : 1;
$_SESSION['current_index'] = $current_index - 1;
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$highest_score = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;
$attempts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 1;

// Tính điểm đạt dựa trên phần trăm
$pass_score_absolute = ceil(($pass_score / 100) * $so_cau_hien_thi);

// Hàm lưu kết quả bài thi
function saveTestResult($conn, $student_id, $test_id, $score, $highest_score, $attempts, $total_questions, $pass_score_absolute) {
    $pass_status = $score >= $pass_score_absolute ? 'Đạt' : 'Không đạt';
    $timestamp = date('Y-m-d H:i:s');

    $sql = "INSERT INTO test_history (student_id, test_id, score, highest_score, attempts, total_questions, pass_status, timestamp) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("iiiiisss", $student_id, $test_id, $score, $highest_score, $attempts, $total_questions, $pass_status, $timestamp);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Xử lý nộp câu trả lời
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    if ($attempts > $max_attempts) {
        $conn->close();
        echo "<script>alert('Bạn đã vượt quá số lần thi cho phép!'); window.location.href = 'quiz.php';</script>";
        exit();
    }
    $question_id = (int)$_POST['question_id'];
    $user_answer = $_POST['answer'];
    $current_question = $_SESSION['questions'][$_SESSION['current_index']];
    $is_correct = ($user_answer === $current_question['correct']);
    $_SESSION['answers'][$question_id] = [
        'selected' => $user_answer,
        'is_correct' => $is_correct
    ];
    if ($is_correct) {
        $score++;
        $_SESSION['score'] = $score;
        if ($score > $highest_score) {
            $_SESSION['highest_score'] = $score;
        }
    }
    $_SESSION['current_index']++;
    if ($_SESSION['current_index'] >= $total_questions) {
        // Lưu kết quả trước khi chuyển hướng
        if (!saveTestResult($conn, $_SESSION['student_id'], $test_id, $score, $highest_score, $attempts, $total_questions, $pass_score_absolute)) {
            $conn->close();
            echo "<script>alert('Lỗi khi lưu kết quả bài thi!'); window.location.href = 'quiz.php';</script>";
            exit();
        }
        $conn->close();
        header("Location: ketqua.php?test_id=" . $test_id);
        exit();
    } else {
        $conn->close();
        header("Location: take_test.php?test_id");
        exit();
    }
    
}

// Xử lý làm lại bài test
if (isset($_POST['reset']) && $attempts < $max_attempts) {
    $_SESSION['attempts']++;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['questions'] = [];
    $_SESSION['current_index'] = 0;
    $conn->close();
    header("Location: take_test.php?test_id=" . $test_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài test - <?php echo htmlspecialchars($ten_test); ?></title>
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
        .quiz-container {
            padding: 20px;
            border-left: 6px solid #007bff;
        }
        .question {
            margin-bottom: 20px;
        }
        .options {
            margin-left: 20px;
            list-style: none;
            padding: 0;
        }
        .option {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f1f1f1;
        }
        .option label {
            display: block;
            cursor: pointer;
        }
        .option label:hover {
            background-color: #e9ecef;
        }
        .option input[type="radio"] {
            margin-right: 10px;
        }
        .submit-btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .nav-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .nav-btn:hover {
            background-color: #0056b3;
        }
        .nav-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .question-number {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2em;
            color: #666;
        }
        img {
            max-width: 300px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #eee;
            display: block;
        }
        .no-tests {
            text-align: center;
            color: #e74c3c;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['student_name']); ?></h2>
            <a href="logout.php" class="logout">Đăng xuất</a>
        </div>
        <div class="quiz-container">
            <h2><?php echo htmlspecialchars($ten_test); ?> - <?php echo htmlspecialchars($ten_khoa); ?></h2>
            <?php if ($total_questions > 0): ?>
                <?php
                $question = $questions[$current_index - 1];
                ?>
                <div class="question-number">Câu <?php echo $current_index; ?> / <?php echo $total_questions; ?></div>
                <div class="question">
                    <h3><?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <form method="POST" action="?test_id=<?php echo $id_test; ?>&ma_khoa=<?php echo $ma_khoa; ?>&question=<?php echo $current_index; ?>">
                        <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                        <ul class="options">
                            <?php foreach ($question['choices'] as $key => $value): ?>
                                <li class="option">
                                    <label>
                                        <input type="radio" name="answer" value="<?php echo $key; ?>" 
                                            <?php echo isset($_SESSION['answers'][$question['id']]['selected']) && $_SESSION['answers'][$question['id']]['selected'] === $key ? 'checked' : ''; ?> required>
                                        <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="navigation-buttons">
                            <a href="?test_id=<?php echo $test_id; ?>&question=<?php echo $current_index - 1; ?>" 
                               class="nav-btn" <?php echo $current_index <= 1 ? 'style="visibility: hidden;"' : ''; ?>>Câu trước</a>
                            <button type="submit" class="submit-btn"><?php echo $current_index >= $total_questions ? 'Kết thúc bài thi' : 'Nộp câu trả lời'; ?></button>
                            <a href="?test_id=<?php echo $test_id; ?>&question=<?php echo $current_index + 1; ?>" 
                               class="nav-btn" <?php echo $current_index >= $total_questions ? 'style="visibility: hidden;"' : ''; ?>>Câu tiếp theo</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <p class="no-tests">Không có câu hỏi nào cho bài test này.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
$conn->close();
?>