<?php
session_start();

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['bai_hoc']) || !isset($_SESSION['ten_khoa'])) {
    header("Location: index.php");
    exit;
}

// Hàm kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy thông tin khóa học
function getCoursesFromDB() {
    $conn = dbconnect();
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    $conn->close();
    return $courses;
}

// Lấy số lần thử
function getTestInfo($ten_test, $ten_khoa) {
    $conn = dbconnect();
    $courses = getCoursesFromDB();
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
        $conn->close();
        return $row['lan_thu'];
    }
    $stmt->close();
    $conn->close();
    return 1;
}

// Lấy câu hỏi
function getQuestionsFromDB($ten_khoa, $id_baitest) {
    $conn = dbconnect();
    $sql = "SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    if ($result->num_rows > 0) {
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
    }
    $stmt->close();
    $conn->close();

    
    if (count($questions) < 5) {
        die("Lỗi: Không đủ 5 câu hỏi cho '$ten_khoa' và '$id_baitest'.");
    }
    
    return $questions;
}

// Lấy tham số
$ten_khoa = $_GET['ten_khoa'] ?? $_SESSION['ten_khoa'];
$id_baitest = $_GET['id_baitest'] ?? $_SESSION ['id_baitest'];


// Kiểm tra quyền truy cập
if ($ten_khoa !== $_SESSION['ten_khoa']) {
    die("Lỗi: Bạn không có quyền truy cập kết quả của khóa học '$ten_khoa'");
}

// Lấy số lần thử tối đa
$max_attempts = getTestInfo($id_baitest, $ten_khoa);

// Lấy danh sách câu hỏi
$questions = getQuestionsFromDB($ten_khoa, $id_baitest);

// Lấy dữ liệu từ session
$score = $_SESSION["score"] ?? 0;
$attempts = $_SESSION["attempts"] ?? 0;
$highest_score = $_SESSION["highest_score"] ?? 0;
$time = htmlspecialchars($_SESSION["time"] ?? date("d-m-Y H:i:s"));
$answers = $_SESSION["answers"] ?? [];
$selected_question_indices = $_SESSION["selected_questions"] ?? [];
$total = count($selected_question_indices);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả Quiz - <?= htmlspecialchars($ten_khoa) ?> thuộc bài  - <?= htmlspecialchars($id_baitest) ?></title>
</head>
<body>
    <div class="container">
        <h1>🎉 Kết quả Quiz - <?= htmlspecialchars($ten_khoa)?> - <?= htmlspecialchars($id_baitest)?>🎉</h1>
        <p><strong>Khóa học:</strong> <?= htmlspecialchars($ten_khoa) ?></p>
        <p><strong>Bài test:</strong> <?= htmlspecialchars($id_baitest) ?></p>
        <p><strong>Tổng điểm:</strong> <?= $score ?> / <?= $total ?></p>
        <p><strong>Điểm cao nhất:</strong> <?= $highest_score ?> / <?= $total ?></p>
        <p><strong>Ngày làm bài:</strong> <?= $time ?></p>
        <p><strong>Số lần làm bài:</strong> <?= $attempts ?> / <?= $max_attempts ?></p>
        <hr>
        <h2>Chi tiết câu trả lời</h2>

        <?php if ($total === 0): ?>
            <p class="no-answers">Không có câu hỏi nào được chọn! <a href="FAQ.php?ten_khoa=<?= urlencode($ten_khoa) ?>&id_baitest=<?= urlencode($id_baitest) ?>">Quay lại làm bài</a></p>
        <?php elseif (empty($answers) || !is_array($answers)): ?>
            <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a href="FAQ.php?ten_khoa=<?= urlencode($ten_khoa) ?>&id_baitest=<?= urlencode($id_baitest) ?>">Quay lại làm bài</a></p>
        <?php else: ?>
            <?php foreach ($selected_question_indices as $index => $question_index): ?>
                <?php if (!isset($questions[$question_index])) continue; ?>
                <?php $question_data = $questions[$question_index]; ?>
                <?php $userAnswer = isset($answers[$index]["selected"]) ? $answers[$index]["selected"] : null; ?>
                <?php $isCorrect = isset($answers[$index]["is_correct"]) ? $answers[$index]["is_correct"] : false; ?>
                <div class="question-block">
                    <p class="question-text">Câu <?= $index + 1 ?>: <?= htmlspecialchars($question_data["question"]) ?></p>
                    <?php if (!empty($question_data['image'])): ?>
                        <img src="<?= htmlspecialchars($question_data['image']) ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question_data["choices"] as $key => $value): ?>
                            <?php
                            $style = '';
                            $icon = '';
                            if ($key === $userAnswer) {
                                $style = $isCorrect ? 'correct' : 'incorrect';
                                $icon = $isCorrect ? '✅' : '❌';
                            }
                            ?>
                            <li class="<?= $style ?>">
                                <?= $key ?>. <?= htmlspecialchars($value) ?> <?= $icon ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($userAnswer !== null): ?>
                        <div class="explanation-block" style="border-color: <?= $isCorrect ? 'green' : 'red' ?>;">
                            <?php if ($isCorrect): ?>
                                <p><strong>Giải thích:</strong> <?= htmlspecialchars($question_data["explanations"][$question_data["correct"]] ?? 'Không có giải thích') ?></p>
                            <?php else: ?>
                                <!-- <p><strong>Đáp án đúng:</strong> <span class="correct-answer"><?= $question_data["correct"] ?>. <?= htmlspecialchars($question_data["choices"][$question_data["correct"]]) ?></span></p> -->
                                <p><strong>Giải thích:</strong> <?= htmlspecialchars($question_data["explanations"][$question_data["correct"]] ?? 'Không có giải thích') ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="explanation-block" style="border-color: orange;">
                            <p style="color: orange; font-weight: bold;">Bạn chưa trả lời câu hỏi này!</p>
                            <!-- <p><strong>Đáp án đúng:</strong> <span class="correct-answer"><?= $question_data["correct"] ?>. <?= htmlspecialchars($question_data["choices"][$question_data["correct"]]) ?></span></p> -->
                            <p><strong>Giải thích:</strong> <?= htmlspecialchars($question_data["explanations"][$question_data["correct"]] ?? 'Không có giải thích') ?></p>
                        </div>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?= $attempts >= $max_attempts ? '#' : 'FAQ.php?reset=1&ten_khoa=' . urlencode($ten_khoa) . '&id_baitest=' . urlencode($id_baitest) ?>" 
           class="try-again <?= $attempts >= $max_attempts ? 'disabled' : '' ?>">🔁 Thử lại (<?= $attempts ?> / <?= $max_attempts ?>)</a>
           
        <!-- <a href="" class="back-to-login">🏠 Quay lại bài học</a> -->
    </div>
</body>
</html>
    <style>
        body {
            font-family: 'Arial', sans-serif;            
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
            color: #333;

        }
        .container {
            max-width: 1100px;
            margin: auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #2c3e50;
            text-align: center;
        }
        p {
            line-height: 1.6;
            margin-bottom: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 6px;
            background-color: #f1f1f1;
            transition: background-color 0.3s;
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
        .question-block {
            margin-bottom: 30px;
            padding: 20px;
            border-left: 6px solid #3498db;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .question-text {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
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
        a.try-again, a.back-to-login {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        a.try-again {
            background-color: #3498db;
        }
        a.try-again:hover {
            background-color: #2980b9;
        }
        a.try-again.disabled {
            background-color: #ccc;
            pointer-events: none;
            cursor: not-allowed;
        }
        a.back-to-login {
            background-color: #e74c3c;
            margin-left: 10px;
        }
        a.back-to-login:hover {
            background-color: #c0392b;
        }
        img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .no-answers {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
    </style>
