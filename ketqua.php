<?php
session_start();
require_once "cauhoi.php";

// Initialize session variables if not set
if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["answers"])) $_SESSION["answers"] = [];
if (!isset($_SESSION["attempts"])) $_SESSION["attempts"] = 0;
if (!isset($_SESSION["highest_score"])) $_SESSION["highest_score"] = 0;
if (!isset($_SESSION["time"])) $_SESSION["time"] = date("d-m-Y H:i:s");

$score = $_SESSION["score"];
$total = count($questions);
$attempts = $_SESSION["attempts"];
$highest = $_SESSION["highest_score"];
$time = $_SESSION["time"];
$answers = $_SESSION["answers"];

// Check for limit exceeded message
$showLimitMessage = isset($_GET["limit_exceeded"]) || $attempts >= 3;
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
            background: #fff;
            margin: 0;
            font-size: 19px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #1a1a1a;
        }
        p {
            font-size: 16px;
            color: #1a1a1a;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin-bottom: 10px;
            font-size: 16px;
        }
        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin: 20px 0;
        }
        a.try-again {
            display: inline-block;
            padding: 8px 20px;
            background: rgb(128, 157, 255);
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
        }
        a.try-again.disabled {
            background: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎉 Kết quả làm bài kiểm tra 🎉</h2>
        <p><strong>Bài test về môn Lập Trình </strong></p>
        <p><strong>Tổng điểm </strong> <?= $score ?> / <?= $total ?></p>
        <p><strong>Ngày làm bài kiểm tra </strong> <?= htmlspecialchars($time) ?></p>
        <p><strong>Số lần làm thứ:</strong> <?= $attempts ?> / 3</p>
        <p><strong>Điểm cao nhất:</strong> <?= $highest ?> / <?= $total ?></p>
        <hr>
        <?php
        $answeredQuestions = 0; // Track the number of answered questions
        foreach ($questions as $index => $q):
            // Only display questions that were answered
            if (isset($answers[$index]["selected"])):
                $answeredQuestions++;
                $userAnswer = $answers[$index]["selected"];
                $isCorrect = $answers[$index]["is_correct"];
                $reason = $answers[$index]["reason"] ?? "";
                $correct = $q["correct"];
        ?>
            <div style="margin-bottom: 25px;">
                <p><strong>Câu <?= $index + 1 ?>:</strong> <?= htmlspecialchars($q["question"]) ?></p>
                <ul>
                    <?php foreach ($q["choices"] as $key => $value): ?>
                        <li style="
                            <?= $key === $correct ? 'font-size: 19px; font-weight: bold; color: green;' : 
                                ($key === $userAnswer && !$isCorrect ? 'font-size: 19px; color: red;' : '') ?>
                        ">
                            <?= $key ?>. <?= htmlspecialchars($value) ?>
                            <?php if ($key === $userAnswer): ?>
                                <?= $isCorrect ? "✅" : "❌" ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($isCorrect): ?>
                    <p><strong>Giải thích:</strong> <?= htmlspecialchars($q["explanation"]) ?></p>
                <?php endif; ?>
                <hr>
            </div>
        <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($answeredQuestions === 0): ?>
            <p style="color: orange;">Bạn chưa trả lời câu hỏi nào!</p>
        <?php endif; ?>

        <?php if ($showLimitMessage): ?>
            <p style="color: red; font-weight: bold;">Bạn đã sử dụng hết 3 lần làm bài!</p>
        <?php endif; ?>

        <a href="<?= $attempts >= 3 ? '#' : 'FAQ.php?reset=1' ?>" class="try-again <?= $attempts >= 3 ? 'disabled' : '' ?>">🔁 Thử lại <?= $attempts ?> / 3</a>
    </div>
</body>
</html>