<?php
session_start();

// Kiểm tra có câu hỏi không
if (!isset($_SESSION["questions"]) || empty($_SESSION["questions"])) {
    echo "<h2>Chưa có câu hỏi nào được thêm!</h2>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách câu hỏi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .question-item {
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .question-title {
            font-weight: bold;
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .choices {
            margin-left: 20px;
        }
        .choice {
            margin: 5px 0;
        }
        .correct {
            color: green;
            font-weight: bold;
        }
        img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .explanation {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Danh sách câu hỏi</h2>

    <?php foreach ($_SESSION["questions"] as $index => $q): ?>
        <div class="question-item">
            <div class="question-title">
                <?= ($index + 1) . ". " . htmlspecialchars($q["question"]) ?>
            </div>

            <?php if (!empty($q["image"])): ?>
                <img src="<?= htmlspecialchars($q["image"]) ?>" alt="Hình ảnh câu hỏi">
            <?php endif; ?>

            <div class="choices">
                <?php foreach ($q["choices"] as $key => $value): ?>
                    <div class="choice <?= ($key == $q["correct"]) ? 'correct' : '' ?>">
                        <?= strtoupper($key) ?>. <?= htmlspecialchars($value) ?>
                        <?php if ($key == $q["correct"]): ?>
                            (Đáp án đúng)
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($q["reason"])): ?>
                <div class="explanation">
                    <strong>Giải thích:</strong> <?= htmlspecialchars($q["reason"]) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>
</body>
</html>
