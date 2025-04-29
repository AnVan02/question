<?php
session_start();

// Kết nối cơ sở dữ liệu MySQL
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study"); // Đổi thông tin kết nối nếu cần
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy câu hỏi từ cơ sở dữ liệu
function getQuestionsFromDB() {
    $conn = dbconnect();
    $sql = "SELECT * FROM quiz";
    $result = $conn->query($sql);
    $questions = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row; // Lưu câu hỏi vào mảng
        }
    }
    $conn->close();
    return $questions;
}

$questions = getQuestionsFromDB();

// Lấy dữ liệu từ session
$score = $_SESSION["score"] ?? 0;
$attempts = $_SESSION["attempts"] ?? 0;
$highest = $_SESSION["highest_score"] ?? 0;
$time = htmlspecialchars($_SESSION["time"] ?? date("d-m-Y H:i:s"));
$answers = $_SESSION["answers"] ?? [];
$selected_question_indices = $_SESSION["selected_questions"] ?? [];
$total = count($selected_question_indices);
// $showLimitMessage = $message !== "" || $attempts >= 3;

// Đảm bảo câu hỏi đã được lấy từ cơ sở dữ liệu
?>




<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả Quiz</title>
   
</head>
<body>
    <div class="container">
        <h1>🎉 Kết quả Quiz Lập Trình 🎉</h1>
        <p><strong>Bài test về môn Lập Trình</strong></p>
        
        <p><strong>Tổng điểm:</strong> <?= $score ?> / <?= $total ?></p>
        <p><strong>Điểm cao nhất:</strong> <?= $highest ?> / <?= $total ?></p>
        <p><strong>Ngày làm bài:</strong> <?= $time ?></p>
        <p><strong>Số lần làm bài:</strong> <?= $attempts ?> / 3</p>
        <hr>

        <h2>Chi tiết câu trả lời</h2>
        
        <?php if ($total === 0): ?>
            <p class="no-answers">Không có câu hỏi nào được chọn! <a href="FAQ.php">Quay lại làm bài</a></p>
        <?php elseif (empty($answers) || !is_array($answers)): ?>
            <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a href="FAQ.php">Quay lại làm bài</a></p>
        <?php else: ?>
            <?php foreach ($selected_question_indices as $index => $question_index): ?>
                <?php if (!isset($questions[$question_index])) continue; ?>
                <?php $question_data = $questions[$question_index]; ?>
                <?php $userAnswer = isset($answers[$index]["selected"]) ? $answers[$index]["selected"] : null; ?>
                <?php $isCorrect = isset($answers[$index]["is_correct"]) ? $answers[$index]["is_correct"] : false; ?>
                <div class="question-block">
                    <p class="question-text">Câu <?= $index + 1 ?>: <?= htmlspecialchars($question_data["cauhoi"]) ?></p>
                    <ul>
                        <?php
                        $choices = [
                            'A' => $question_data["cau_a"],
                            'B' => $question_data["cau_b"],
                            'C' => $question_data["cau_c"],
                            'D' => $question_data["cau_d"]
                        ];
                        foreach ($choices as $key => $value) {
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
                        <?php } ?>
                    </ul>
                    <?php if ($userAnswer !== null): ?>
                        <div class="explanation-block" style="border-color: <?= $isCorrect ? 'green' : 'red' ?>;">
                            <?php if ($isCorrect): ?>
                                <p><strong>🥰👍 Giải thích:</strong> <?= htmlspecialchars($question_data["giaithich"]) ?></p>
                            <?php else: ?>
                                <p><strong>😱👎 Giải thích:</strong> <?= htmlspecialchars($question_data["giaithich"]) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="explanation-block" style="border-color: orange;">
                            <p style="color: orange; font-weight: bold;">⚠️ Bạn chưa trả lời câu hỏi này!</p>
                            <p><strong>Đáp án đúng:</strong> <span class="correct-answer"><?= $question_data["dap_an"] ?>. <?= htmlspecialchars($question_data[$question_data["dap_an"]]) ?></span></p>
                            <p><strong>Giải thích:</strong> <?= htmlspecialchars($question_data["giaithich"]) ?></p>
                        </div>
                    <?php endif; ?>
                    <hr>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?= $attempts >= 3 ? '#' : 'FAQ.php?reset=1' ?>" class="try-again <?= $attempts >= 3 ? 'disabled' : '' ?>">🔁 Thử lại (<?= $attempts ?> / 3)</a>
    </div>

<style>
        /* CSS cho giao diện */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f4f4f9;
            margin: 0;
            font-size: 16px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            color: #1a1a1a;
            margin: 10px 0;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin-bottom: 8px;
            font-size: 16px;
        }
        hr {
            border: 0;
            border-top: 1px solid #e0e0e0;
            margin: 20px 0;
        }
        a.try-again {
            display: inline-block;
            padding: 10px 25px;
            background: #6f42c1;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        a.try-again:hover {
            background: #563d7c;
        }
        a.try-again.disabled {
            background: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }
        .question-block {
            margin-bottom: 25px;
        }
        .question-text {
            font-weight: bold;
            color: #1a1a1a;
        }
        .correct {
            font-weight: bold;
            color: green;
        }
        .incorrect {
            font-weight: bold;
            color: red;
        }
        .correct-answer {
            font-weight: bold;
            color: green;
            background: #e6ffe6;
            padding: 5px;
        }
        .limit-message {
            color: red;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
        }
        .no-answers {
            color: orange;
            text-align: center;
            font-weight: bold;
        }
        .explanation-block {
            margin-top: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-left: 5px solid;
        }
    </style>
