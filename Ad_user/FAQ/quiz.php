<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "study");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");


// Fetch courses
function getCoursesFromDB($conn) {
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc ORDER BY khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    return $courses;
}

// Fetch tests for a course
function getTestsFromDB($conn, $ten_khoa) {
    $sql = "SELECT DISTINCT id_baitest FROM quiz WHERE ten_khoa = ? ORDER BY id_baitest";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ten_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    $tests = [];
    while ($row = $result->fetch_assoc()) {
        $tests[] = $row['id_baitest'];
    }
    $stmt->close();
    return $tests;
}

// Get max attempts for a test
function getTestInfo($conn, $ten_test, $ten_khoa) {
    $courses = getCoursesFromDB($conn);
    $id_khoa = array_search($ten_khoa, $courses);
    if ($id_khoa === false) {
        return 1;
    }
    $sql = "SELECT lan_thu FROM test WHERE ten_test = ? AND id_khoa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ten_test, $id_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['lan_thu'];
    }
    $stmt->close();
    return 1;
}

// Save progress to database
function saveProgress($conn, $ma_khoa, $ten_khoa, $id_baitest, $score, $highest_score, $attempts) {
    $user_id = 1; // Replace with actual user ID in production
    $completed_at = date('Y-m-d H:i:s');
    $sql = "INSERT INTO user_progress (user_id, id_khoa, ten_test, score, highest_score, attempts, completed_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE score = ?, highest_score = ?, attempts = ?, completed_at = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param("isssiiisiiis", $user_id, $ma_khoa, $id_baitest, $score, $highest_score, $attempts, $completed_at, 
                     $score, $highest_score, $attempts, $completed_at);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    $stmt->close();
    return true;
}

// Initialize variables
$courses = getCoursesFromDB($conn);
if (empty($courses)) {
    die("Lỗi: Không có khóa học nào trong cơ sở dữ liệu.");
}
$ma_khoa = isset($_POST['ma_khoa']) ? $_POST['ma_khoa'] : (isset($_SESSION['ma_khoa']) ? $_SESSION['ma_khoa'] : array_key_first($courses));
$ten_khoa = '';
$tests = [];
$pass_score = 4;

// Validate course code
if (!array_key_exists($ma_khoa, $courses)) {
    die("Lỗi: Mã khóa học không hợp lệ.");
}
$ten_khoa = $courses[$ma_khoa];

// Fetch tests
$tests = getTestsFromDB($conn, $ten_khoa);
$id_baitest = isset($_POST['id_baitest']) ? $_POST['id_baitest'] : (isset($_SESSION['id_baitest']) ? $_SESSION['id_baitest'] : (count($tests) > 0 ? $tests[0] : ''));

// Session key for isolation
$session_key = $ma_khoa . '_' . $id_baitest;

// Reset session on course/test change
if (isset($_POST['ma_khoa']) && $_POST['ma_khoa'] !== $_SESSION['ma_khoa'] ||
    isset($_POST['id_baitest']) && $_POST['id_baitest'] !== $_SESSION['id_baitest']) {
    $_SESSION[$session_key] = [];
}

// Store selections
$_SESSION['ma_khoa'] = $ma_khoa;
$_SESSION['id_baitest'] = $id_baitest;

// Initialize session data
$_SESSION[$session_key] = isset($_SESSION[$session_key]) ? $_SESSION[$session_key] : [];
$current_index = isset($_SESSION[$session_key]['current_index']) ? (int)$_SESSION[$session_key]['current_index'] : 0;
$answers = isset($_SESSION[$session_key]['answers']) ? $_SESSION[$session_key]['answers'] : [];
$score = isset($_SESSION[$session_key]['score']) ? (int)$_SESSION[$session_key]['score'] : 0;
$highest_score = isset($_SESSION[$session_key]['highest_score']) ? (int)$_SESSION[$session_key]['highest_score'] : 0;
$attempts = isset($_SESSION[$session_key]['attempts']) ? (int)$_SESSION[$session_key]['attempts'] : 1;

// Load questions
if (empty($_SESSION[$session_key]['questions']) || isset($_POST['reset'])) {
    if (empty($id_baitest)) {
        die("Lỗi: Không có bài thi nào cho khóa học '$ten_khoa'.");
    }
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
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
    if (count($questions) < 1) {
        die("Lỗi: Không có câu hỏi nào cho khóa học '$ten_khoa' và bài thi '$id_baitest'.");
    }
    $_SESSION[$session_key]['questions'] = $questions;
    $_SESSION[$session_key]['ten_khoa'] = $ten_khoa;
    $_SESSION[$session_key]['id_baitest'] = $id_baitest;
    $_SESSION[$session_key]['current_index'] = 0;
    $_SESSION[$session_key]['answers'] = [];
    $_SESSION[$session_key]['score'] = 0;
    $_SESSION[$session_key]['attempts'] = isset($_POST['reset']) ? $attempts + 1 : 1;
    $current_index = 0;
    $answers = [];
    $score = 0;
}

// Handle answer submission
if (isset($_POST['answer']) && isset($_SESSION[$session_key]['questions'])) {
    $user_answer = $_POST['answer'];
    $current_question = $_SESSION[$session_key]['questions'][$current_index];
    $is_correct = ($user_answer === $current_question['correct']);
    $answers[$current_index] = [
        'selected' => $user_answer,
        'is_correct' => $is_correct
    ];
    $_SESSION[$session_key]['answers'] = $answers;
    if ($is_correct) {
        $score++;
        $_SESSION[$session_key]['score'] = $score;
        if ($score > $highest_score) {
            $_SESSION[$session_key]['highest_score'] = $score;
        }
    }
    $current_index++;
    $_SESSION[$session_key]['current_index'] = $current_index;
}

// Handle reset
if (isset($_POST['reset'])) {
    $attempts++;
    $_SESSION[$session_key]['attempts'] = $attempts;
}

// Get max attempts
$max_attempts = getTestInfo($conn, $id_baitest, $ten_khoa);

// Save progress
if ($current_index >= count($_SESSION[$session_key]['questions'])) {
    saveProgress($conn, $ma_khoa, $ten_khoa, $id_baitest, $score, $highest_score, $attempts);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo htmlspecialchars($ten_khoa . ' - ' . $id_baitest); ?></title>
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
        h1, h2, h3 {
            color: #2c3e50;
            text-align: center;
        }
        .selection-form {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }
        select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            min-width: 200px;
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
        .question-box:hover {
            box-shadow: 0 4px 16px rgba(0,123,255,0.12);
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
        button:hover, a.try-again:hover, a.back-to-quiz:hover {
            background-color: #0056b3;
        }
        button:disabled, a.try-again.disabled {
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
        .no-answers, .error {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .selection-form {
                flex-direction: column;
                align-items: center;
            }
            select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quiz Học Tập</h1>
        <form method="POST" class="selection-form">
            <div>
                <label for="ma_khoa">Khóa học:</label>
                <select name="ma_khoa" id="ma_khoa" onchange="this.form.submit()">
                    <?php foreach ($courses as $id => $name): ?>
                        <option value="<?php echo htmlspecialchars($id); ?>" <?php echo $id === $ma_khoa ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($tests)): ?>
                <div>
                    <label for="id_baitest">Bài thi:</label>
                    <select name="id_baitest" id="id_baitest" onchange="this.form.submit()">
                        <?php foreach ($tests as $test): ?>
                            <option value="<?php echo htmlspecialchars($test); ?>" <?php echo $test === $id_baitest ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($test); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <p class="error">Không có bài thi nào cho khóa học này.</p>
            <?php endif; ?>
        </form>

        <?php if (!empty($tests) && $current_index < count($_SESSION[$session_key]['questions'])): ?>
            <?php $question = $_SESSION[$session_key]['questions'][$current_index]; ?>
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($ten_khoa); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($id_baitest); ?></span>
            </h2>
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li><label><input type="radio" name="answer" value="<?php echo $key; ?>" required> <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?></label></li>
                        <?php endforeach; ?>
                    </ul>
                    <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
                    <button type="submit">Trả lời »</button>
                </div>
            </form>
        <?php elseif (!empty($tests)): ?>
            <h1>Kết quả - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>
            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài thi:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION[$session_key]['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?> / <?php echo count($_SESSION[$session_key]['questions']); ?></p>
            <p><strong>Số lần làm bài:</strong> <?php echo $attempts; ?> / <?php echo $max_attempts; ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>
            <hr>
            <h2>Chi tiết câu trả lời</h2>
            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION[$session_key]['questions'] as $index => $question): ?>
                    <div class="question-block">
                        <p class="question-text">Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></p>
                        <?php if (!empty($question['image'])): ?>
                            <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                        <?php endif; ?>
                        <ul>
                            <?php foreach ($question['choices'] as $key => $value): ?>
                                <?php
                                $style = '';
                                if (isset($answers[$index]['selected']) && $key === $answers[$index]['selected']) {
                                    $style = $answers[$index]['is_correct'] ? 'correct' : 'incorrect';
                                }
                                ?>
                                <li class="<?php echo $style; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (isset($answers[$index]['selected'])): ?>
                            <div class="explanation-block" style="border-color: <?php echo $answers[$index]['is_correct'] ? 'green' : 'red'; ?>;">
                                <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$question['correct']]); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="explanation-block" style="border-color: orange;">
                                <p style="color: orange; font-weight: bold;">Bạn chưa trả lời câu hỏi này!</p>
                                <p><strong>Đáp án đúng:</strong> <span class="correct-answer"><?php echo $question['correct']; ?>. <?php echo htmlspecialchars($question['choices'][$question['correct']]); ?></span></p>
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