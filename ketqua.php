<?php
session_start();
require_once "cauhoi.php";

// Initialize session variables if not set
if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["answers"])) $_SESSION["answers"] = [];
if (!isset($_SESSION["attempts"])) $_SESSION["attempts"] = 0;
if (!isset($_SESSION["highest_score"])) $_SESSION["highest_score"] = 0;
if (!isset($_SESSION["time"])) $_SESSION["time"] = date("d-m-Y H:i:s");
if (!isset($_SESSION["selected_questions"])) $_SESSION["selected_questions"] = [];

$score = $_SESSION["score"];
$total = 5; // Fixed to 5 questions
$attempts = $_SESSION["attempts"];
$highest = $_SESSION["highest_score"];
$time = $_SESSION["time"];
$answers = $_SESSION["answers"];
$selected_question_indices = $_SESSION["selected_questions"];

// Check for limit exceeded message
$showLimitMessage = isset($_GET["limit_exceeded"]) || $attempts >= 3;

// Map answer keys to A, B, C, D
$answer_labels = ['A', 'B', 'C', 'D'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả bài kiểm tra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f4f4f9;
            margin: 0;
            font-size: 16px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
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
    </style>
</head>
<body>
    <div class="container">
        <h2>🎉 Kết quả làm bài kiểm tra 🎉</h2>
        <p><strong>Bài test về môn Lập Trình</strong></p>
        <p><strong>Tổng điểm:</strong> <?= $score ?> / <?= $total ?></p>
        <p><strong>Ngày làm bài kiểm tra:</strong> <?= htmlspecialchars($time) ?></p>
        <p><strong>Số lần làm bài:</strong> <?= $attempts ?> / 3</p>
        <p><strong>Điểm cao nhất:</strong> <?= $highest ?> / <?= $total ?></p>
        <hr>

        <?php
        $answeredQuestions = 0; // Track the number of answered questions
        foreach ($selected_question_indices as $index => $question_index) {
            // Only display questions that were answered
            if (isset($answers[$index]["selected"])) {
                $answeredQuestions++;
                $userAnswer = $answers[$index]["selected"];
                $isCorrect = $answers[$index]["is_correct"];
                $question_data = $questions[$question_index];
        ?>
            <div class="question-block">
                <p class="question-text">Câu <?= $index + 1 ?>: <?= htmlspecialchars($question_data["question"]) ?></p>
                <?php if (!empty($question_data["image"])): ?>
                    <div class="question-image-container">
                        <img src="<?= htmlspecialchars($question_data["image"]) ?>" alt="Hình ảnh câu hỏi" style="max-width: 100%; height: auto; margin: 10px 0;">
                    </div>
                <?php endif; ?>
                <ul>
                    <?php foreach ($question_data["choices"] as $key => $value): ?>
                        <?php
                        $label = $answer_labels[array_search($key, array_keys($question_data["choices"]))];
                        $style = '';
                        $icon = '';
                        if ($key === $userAnswer) {
                            // Only highlight the user's answer
                            $style = $isCorrect ? 'correct' : 'incorrect';
                            $icon = $isCorrect ? '✅ (Đáp án của bạn)' : '❌ (Đáp án của bạn)';
                        }
                        ?>
                        <li class="<?= $style ?>">
                            <?= $label ?>. <?= htmlspecialchars($value) ?> <?= $icon ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <hr>
            </div>
        <?php
            }
        }
        ?>

        <?php if ($answeredQuestions === 0): ?>
            <p class="no-answers">Bạn chưa trả lời câu hỏi nào!</p>
        <?php endif; ?>

        <?php if ($showLimitMessage): ?>
            <p class="limit-message">Bạn đã sử dụng hết 3 lần làm bài!</p>
        <?php endif; ?>

        <a href="<?= $attempts >= 3 ? '#' : 'FAQ.php?reset=1' ?>" class="try-again <?= $attempts >= 3 ? 'disabled' : '' ?>">🔁 Thử lại (<?= $attempts ?> / 3)</a>
    </div>
</body>
</html>