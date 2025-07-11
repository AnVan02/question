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

$score = (int)$_SESSION["score"];
$attempts = (int)$_SESSION["attempts"];
$highest = (int)$_SESSION["highest_score"];
$time = htmlspecialchars($_SESSION["time"]);
$answers = $_SESSION["answers"];
$pass_status = $_SESSION['pass_status'] ?? '';
$selected_question_indices = $_SESSION["selected_questions"];
$total = count($selected_question_indices); // Dynamic total based on selected questions

// Check for limit exceeded
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
</head>
<body>
    <div class="container">
        <h2>🎉 Kết quả làm bài kiểm tra 🎉</h2>
        <p><strong>Bài test về môn Lập Trình</strong></p>
        <p><strong>Tổng điểm:</strong> <?= $score ?> / <?= $total ?></p>
        <p><strong>Ngày làm bài kiểm tra:</strong> <?= $time ?></p>
        <p><strong>Số lần làm bài:</strong> <?= $attempts ?> / <?= $lanthu ?></p>
        <p><strong>Điểm cao nhất:</strong> <?= $highest ?> / <?= $total ?></p>
        <p><strong>Trạng thái:</strong> <?= $pass_status ?>
        
        <hr>
        

        <?php
        $answeredQuestions = 0;
        foreach ($selected_question_indices as $index => $question_index) {
            if (!isset($questions[$question_index])) {
                continue; // Skip invalid question indices
            }
            $question_data = $questions[$question_index];
            $userAnswer = isset($answers[$index]["selected"]) ? $answers[$index]["selected"] : null;
            $isCorrect = isset($answers[$index]["is_correct"]) ? $answers[$index]["is_correct"] : false;

            if ($userAnswer !== null) {
                $answeredQuestions++;
            }
        ?>
            <div class="question-block">
                <p class="question-text">Câu <?= $index + 1 ?>: <?= htmlspecialchars($question_data["question"]) ?></p>

                <ul>
                    <?php foreach ($question_data["choices"] as $key => $value): ?>
                        <?php
                        $label = $answer_labels[array_search($key, array_keys($question_data["choices"]))];
                        $style = '';
                        $icon = '';
                        if ($key === $userAnswer) {
                            $style = $isCorrect ? 'correct' : 'incorrect';
                            $icon = $isCorrect ? '✅' : '❌ ';
                        }
                        ?>
                        <li class="<?= $style ?>">
                            <?= $label ?>. <?= htmlspecialchars($value) ?> <?= $icon ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($userAnswer !== null): ?>
                    <div class="explanation-block" style="border-color: <?= $isCorrect ? 'green' : 'red' ?>;">
                        <?php if ($isCorrect): ?>
                            <p><strong>👍 Giải thích:</strong> <?= htmlspecialchars($question_data["explanations"][$question_data["correct"]]) ?></p>
                        <?php else: ?>
                            <p><strong>👎 Giải thích: </strong> <?= htmlspecialchars($question_data["explanations"][$userAnswer]) ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="explanation-block" style="border-color: orange;">
                        <p style="color:orange; font-weight:bold;">⚠️ Bạn chưa trả lời câu hỏi này!</p>
                    </div>
                <?php endif; ?>
                <hr>
            </div>
        <?php
        }
        ?>

        <?php if ($answeredQuestions === 0 && $total > 0): ?>
            <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a href="FAQ.php">Quay lại làm bài</a></p>
        <?php elseif ($total === 0): ?>
            <p class="no-answers">Không có câu hỏi nào được chọn! <a href="FAQ.php">Quay lại làm bài</a></p>
        <?php endif; ?>

        <!-- <?php if ($showLimitMessage): ?>
            <p class="limit-message">Bạn đã sử dụng hết 3 lần làm bài! Liên hệ giáo viên để đặt lại lượt.</p>
        <?php endif; ?> -->

        <a href="<?= $attempts >= 3 ? '#' : 'FAQ.php?reset=1' ?>" class="try-again <?= $attempts >= 3 ? 'disabled' : '' ?>">
            🔁 Thử lại (<?= $attempts ?> / 3)
        </a>
    </div>
</body>
</html>