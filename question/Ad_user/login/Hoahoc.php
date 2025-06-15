<?php
// Set timezone and enable error reporting
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo "<script>
        alert('Vui lòng đăng nhập để truy cập!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Configure course and test
$ma_khoa = '10'; // Chemistry course ID
$id_test = '24'; // Test ID
$student_id = $_SESSION['student_id'];

// Verify course access
$stmt = $conn->prepare("SELECT s.Student_ID, s.Ten, s.Khoahoc, kh.khoa_hoc 
                       FROM students s 
                       LEFT JOIN khoa_hoc kh ON FIND_IN_SET(kh.id, s.Khoahoc)
                       WHERE s.Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student_info = $result->fetch_assoc();
    $allowed_courses = explode(',', $student_info['Khoahoc']);
    
    if (!in_array($ma_khoa, $allowed_courses)) {
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

// Get course information
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
$course_info = $result->fetch_assoc();
$ten_khoa = $course_info['khoa_hoc'];
$stmt->close();

// Get test information
$stmt = $conn->prepare("SELECT * FROM test WHERE id_test = ? AND id_khoa = ?");
$stmt->bind_param("is", $id_test, $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
$test_info = $result->fetch_assoc();

if (!$test_info) {
    echo "<script>
        alert('Không tìm thấy bài test này!');
        window.location.href = 'login.php';
    </script>";
    exit();
}
$stmt->close();

// Get questions
$stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
$stmt->bind_param("ss", $ten_khoa, $test_info['ten_test']);
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
$stmt->close();

// Store information in session
$_SESSION['questions'] = $questions;
$_SESSION['ten_khoa'] = $ten_khoa;
$_SESSION['id_baitest'] = $test_info['ten_test'];
$_SESSION['current_index'] = isset($_SESSION['current_index']) ? $_SESSION['current_index'] : 0;
$_SESSION['attempts'] = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 1;
$_SESSION['score'] = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$_SESSION['highest_score'] = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;

// Handle question navigation
if (isset($_GET['question'])) {
    $requested_index = (int)$_GET['question'];
    if ($requested_index >= 0 && $requested_index < count($questions)) {
        $_SESSION['current_index'] = $requested_index;
    } else {
        header("Location: ?question=" . $_SESSION['current_index']);
        exit();
    }
}

// Handle test submission
if (isset($_GET['submit']) && $_GET['submit'] == 1) {
    $_SESSION['current_index'] = count($questions); // Mark test as completed
}

// Handle answer submission or navigation
if (isset($_POST['answer']) && isset($_SESSION['questions'])) {
    $current_index = $_SESSION['current_index'];
    $user_answer = $_POST['answer'];
    $current_question = $_SESSION['questions'][$current_index];
    
    if (!isset($_SESSION['answers'])) {
        $_SESSION['answers'] = [];
    }
    
    $_SESSION['answers'][$current_index] = [
        'selected' => $user_answer,
        'is_correct' => ($user_answer === $current_question['correct'])
    ];
    
    if ($_SESSION['answers'][$current_index]['is_correct']) {
        $_SESSION['score']++;
        if ($_SESSION['score'] > $_SESSION['highest_score']) {
            $_SESSION['highest_score'] = $_SESSION['score'];
        }
    }
    
    // Handle navigation after saving answer
    if (isset($_POST['navigate'])) {
        $direction = $_POST['navigate'];
        if ($direction === 'next' && $current_index < count($questions) - 1) {
            $_SESSION['current_index']++;
        } elseif ($direction === 'prev' && $current_index > 0) {
            $_SESSION['current_index']--;
        }
        header("Location: ?question=" . $_SESSION['current_index']);
        exit();
    } elseif (isset($_POST['save_answer'])) {
        // Only save answer, don't navigate
        header("Location: ?question=" . $_SESSION['current_index']);
        exit();
    } elseif (isset($_POST['submit_test'])) {
        // Handle test submission
        $_SESSION['current_index'] = count($questions);
        header("Location: ?submit=1");
        exit();
    }
}

// Handle test reset
if (isset($_POST['reset'])) {
    $_SESSION['attempts']++;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
}

// Check attempt limit
if ($_SESSION['attempts'] > $test_info['lan_thu']) {
    echo "<script>
        alert('Bạn đã hết số lần thử cho phép!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Check if test is completed
$current_index = $_SESSION['current_index'];
$is_completed = $current_index >= count($questions);
$pass_score = $test_info['Pass'];
$is_passed = $_SESSION['score'] >= $pass_score;

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài kiểm tra <?php echo htmlspecialchars($ten_khoa); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .question {
            margin-bottom: 30px;
        }
        .question img {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
        }
        .choices {
            display: grid;
            gap: 10px;
            margin-top: 15px;
        }
        .choice {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .choice:hover {
            background-color: #f8f9fa;
        }
        .choice.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .choice.correct {
            background-color: #c8e6c9;
            border-color: #4caf50;
        }
        .choice.incorrect {
            background-color: #ffcdd2;
            border-color: #f44336;
        }
        .explanation {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: none;
        }
        .explanation.show {
            display: block;
        }
        .progress {
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .progress-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #007bff;
            transition: width 0.3s;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .result {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .result h2 {
            color: #28a745;
        }
        .navigation-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .question-list {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .question-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .question-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .question-number:hover {
            background-color: #dee2e6;
        }
        .question-number.answered {
            background-color: #007bff;
            color: white;
        }
        .question-number.current {
            border: 2px solid #28a745;
        }
    </style>
    <script>
        function confirmSubmit() {
            return confirm('Bạn có chắc chắn muốn nộp bài?');
        }

        function validateForm() {
            const radios = document.getElementsByName('answer');
            let isChecked = false;
            for (let radio of radios) {
                if (radio.checked) {
                    isChecked = true;
                    break;
                }
            }
            if (!isChecked) {
                alert('Vui lòng chọn một đáp án trước khi lưu!');
                return false;
            }
            return true;
        }

        // Warn user if navigating without saving answer
        let formModified = false;
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.getElementsByName('answer');
            for (let radio of radios) {
                radio.addEventListener('change', function() {
                    formModified = true;
                });
            }

            const navButtons = document.querySelectorAll('.btn-secondary, .question-number');
            navButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (formModified && !confirm('Bạn chưa lưu câu trả lời. Bạn có muốn tiếp tục?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bài kiểm tra: <?php echo htmlspecialchars($ten_khoa); ?></h1>
            <p>Lần thử: <?php echo $_SESSION['attempts']; ?>/<?php echo $test_info['lan_thu']; ?></p>
            <p>Thời gian: <?php echo date('h:i A d/m/Y'); ?></p>
        </div>

        <?php if (!$is_completed): ?>
            <div class="progress">
                <p>Câu hỏi: <?php echo $current_index + 1; ?>/<?php echo count($questions); ?></p>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: <?php echo (($current_index + 1) / count($questions)) * 100; ?>%"></div>
                </div>
            </div>

            <div class="question">
                <?php
                $current_question = $questions[$current_index];
                ?>
                <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($current_question['question']); ?></h3>
                
                <?php if (!empty($current_question['image'])): ?>
                    <img src="<?php echo htmlspecialchars($current_question['image']); ?>" alt="Hình ảnh câu hỏi">
                <?php endif; ?>

                <form method="post" class="choices" onsubmit="return validateForm()" id="answer-form">
                    <?php 
                    $previous_answer = isset($_SESSION['answers'][$current_index]) ? $_SESSION['answers'][$current_index]['selected'] : null;
                    
                    foreach ($current_question['choices'] as $key => $choice): 
                        $is_selected = ($previous_answer === $key);
                    ?>
                        <label class="choice <?php echo $is_selected ? 'selected' : ''; ?>">
                            <input type="radio" name="answer" value="<?php echo $key; ?>" <?php echo $is_selected ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($choice); ?>
                        </label>
                    <?php endforeach; ?>

                    <div class="navigation-buttons">
                        <?php if ($current_index > 0): ?>
                            <button type="submit" name="navigate" value="prev" class="btn btn-secondary">
                                ← Câu trước
                            </button>
                        <?php endif; ?>
                        <?php if ($current_index < count($questions) - 1): ?>
                            <button type="submit" name="navigate" value="next" class="btn btn-secondary">
                                Câu sau →
                            </button>
                        <?php endif; ?>

                        <?php if ($current_index == count($questions) - 1): ?>
                            <button type="submit" name="submit_test" value="1" onclick="return confirmSubmit()" class="btn btn-success">
                                Nộp bài
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

          <?php else: ?>
            <h1> Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?> </h1>
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
                        <p class="question-text">Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></p>
                        <?php if (!empty($question['image'])): ?>
                            <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                        <?php endif; ?>
                        <ul>
                            <?php foreach ($question['choices'] as $key => $value): ?>
                                <?php
                                $style = '';
                                $icon = '';
                                if (isset($answers[$index]['selected']) && $key === $answers[$index]['selected']) {
                                    $style = $answers[$index]['is_correct'] ? 'correct' : 'incorrect';
                                    $icon = $answers[$index]['is_correct'] ? '' : '';
                                }
                                ?>
                                <li class="<?php echo $style; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?> <?php echo $icon; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (isset($answers[$index]['selected'])): ?>
                            
                            <div class="explanation-block" style="border-color: <?php echo $answers[$index]['is_correct'] ? 'orange' : 'red'; ?>;">
                                <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$question['correct']]); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="explanation-block" style="border-color: orange;">
                                <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$question['correct']]); ?></p>
                            </div>
                        <?php endif; ?>
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

<?php
$conn->close();
?>