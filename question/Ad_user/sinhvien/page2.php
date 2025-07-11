<?php
// Bắt đầu session và bật output buffering
session_start();
ob_start();

// Thiết lập múi giờ và báo lỗi
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập các biến
$ma_khoa = '10'; // Mã khóa học
$id_test = '71'; // Mã bài test
$student_id = $_SESSION['student_id'];
$link_quay_lai = "index.php";
$link_tiep_tuc = "dashboard.php";
$pass_score = 4; // Điểm đạt

// Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
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

// Lấy thông tin khóa học
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $ten_khoa = $row['khoa_hoc'];
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();

// Hàm lấy danh sách câu hỏi từ database
function getQuestionsFromDB($conn, $ten_khoa, $id_baitest) {
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ? ORDER BY Id_cauhoi");
    $stmt->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'id' => $row['Id_cauhoi'],
            'question' => $row['cauhoi'],
            'choices' => [
                'A' => $row['cau_a'],
                'B' => $row['cau_b'],
                'C' => $row['cau_c'],
                'D' => $row['cau_d']
            ],
            'correct' => $row['dap_an'],
            'image' => $row['hinhanh']
        ];
    }
    $stmt->close();
    return $questions;
}

// Khởi tạo biến session
if (!isset($_SESSION['questions'])) {
    $_SESSION['questions'] = getQuestionsFromDB($conn, $ten_khoa, $id_baitest);
    $_SESSION['current_index'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['score'] = 0;
    $_SESSION['attempts'] = 1;
}

$questions = $_SESSION['questions'];
$current_index = $_SESSION['current_index'];
$answers = $_SESSION['answers'];
$score = $_SESSION['score'];
$attempts = $_SESSION['attempts'];

// Xử lý khi người dùng gửi câu trả lời
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['answer_submit'])) {
        $user_answer = $_POST['answer'];
        $current_question = $questions[$current_index];
        $is_correct = ($user_answer === $current_question['correct']);
        
        $answers[$current_index] = [
            'selected' => $user_answer,
            'is_correct' => $is_correct
        ];
        
        if ($is_correct) {
            $score++;
        }
        
        $_SESSION['answers'] = $answers;
        $_SESSION['score'] = $score;
        
        // Chuyển đến câu tiếp theo hoặc kết thúc
        if ($current_index < count($questions) - 1) {
            $_SESSION['current_index']++;
        } else {
            // Lưu kết quả vào database khi hoàn thành bài test
            saveTestResult($conn, $student_id, $ma_khoa, $id_test, $answers, $score);
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Xử lý nút "Nộp bài"
    if (isset($_POST['submit'])) {
        saveTestResult($conn, $student_id, $ma_khoa, $id_test, $answers, $score);
        $_SESSION['current_index'] = count($questions); // Kết thúc bài test
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Xử lý nút "Làm lại"
    if (isset($_POST['reset'])) {
        $_SESSION['current_index'] = 0;
        $_SESSION['answers'] = [];
        $_SESSION['score'] = 0;
        $_SESSION['attempts']++;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Hàm lưu kết quả vào database
function saveTestResult($conn, $student_id, $ma_khoa, $id_test, $answers, $score) {
    $tt_bai_test = '';
    foreach ($answers as $index => $answer) {
        $tt_bai_test .= $index . ":" . $answer['selected'] . ";";
    }
    $tt_bai_test = rtrim($tt_bai_test, ";");
    
    // Kiểm tra xem đã có kết quả chưa
    $stmt = $conn->prepare("SELECT kq_cao_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
    $stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $highest_score = max($score, $row['kq_cao_nhat']);
        
        $stmt = $conn->prepare("UPDATE ket_qua SET kq_cao_nhat = ?, test_gan_nhat = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $stmt->bind_param("issis", $highest_score, $tt_bai_test, $student_id, $ma_khoa, $id_test);
    } else {
        $stmt = $conn->prepare("INSERT INTO ket_qua (student_id, khoa_id, test_id, kq_cao_nhat, test_gan_nhat) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisis", $student_id, $ma_khoa, $id_test, $score, $tt_bai_test);
    }
    
    $stmt->execute();
    $stmt->close();
}

// Đóng kết nối database
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài kiểm tra - <?php echo htmlspecialchars($ten_khoa); ?></title>
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
        .debug-info {
            background-color: #f8d7da;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            display: none; /* Bật khi cần debug */
        }
        .result-summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .result-summary p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($current_index < count($questions)): ?>
            <!-- Hiển thị câu hỏi hiện tại -->
            <div class="navigation-links">
                <a href="<?php echo htmlspecialchars($link_quay_lai); ?>" class="nav-link">← Quay lại</a>
            </div>
            
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($ten_khoa); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($id_baitest); ?></span>
            </h2>
            
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?> / <?php echo count($questions); ?>: <?php echo htmlspecialchars($questions[$current_index]['question']); ?></h3>
                    
                    <?php if (!empty($questions[$current_index]['image'])): ?>
                        <img src="admin/<?php echo htmlspecialchars($questions[$current_index]['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    
                    <ul>
                        <?php foreach ($questions[$current_index]['choices'] as $key => $value): ?>
                            <li>
                                <label>
                                    <input type="radio" name="answer" value="<?php echo $key; ?>" 
                                        <?php echo isset($answers[$current_index]) && $answers[$current_index]['selected'] === $key ? 'checked' : ''; ?> 
                                        required>
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="btn-area">
                        <button type="button" onclick="window.history.back()" <?php echo $current_index == 0 ? 'disabled' : ''; ?>>Câu trước</button>
                        
                        <?php if ($current_index == count($questions) - 1): ?>
                            <button type="submit" name="submit">Nộp bài</button>
                        <?php else: ?>
                            <button type="submit" name="answer_submit">Câu sau</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
    <?php else: ?>
            <h1>Kết quả bài test gần nhất - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>
            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo htmlspecialchars($score); ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo htmlspecialchars($kq_cao_nhat); ?></p>
            <p><strong>Số lần thử:</strong> <?php echo htmlspecialchars($_SESSION['']); ?> / <?php echo $max_attempts; ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>

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

<?php ob_end_flush(); ?>