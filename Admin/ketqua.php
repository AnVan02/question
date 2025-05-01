<?php
session_start();

// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
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
            $questions[] = [
                'id' => $row['Id_cauhoi'],
                'cauhoi' => $row['cauhoi'],
                'hinhanh' => $row['hinhanh'],
                'cau_a' => $row['cau_a'],
                'giaithich_a' => $row['giaithich_a'],
                'cau_b' => $row['cau_b'],
                'giaithich_b' => $row['giaithich_b'],
                'cau_c' => $row['cau_c'],
                'giaithich_c' => $row['giaithich_c'],
                'cau_d' => $row['cau_d'],
                'giaithich_d' => $row['giaithich_d'],
                'dap_an' => $row['dap_an']
            ];
        }
    }
    $conn->close();
    if (empty($questions)) {
        die("Lỗi: Không có câu hỏi nào trong cơ sở dữ liệu.");
    }
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
                    <?php if (!empty($question_data['hinhanh'])): ?>
                        <img src="<?= htmlspecialchars($question_data['hinhanh']) ?>" alt="Hình ảnh câu hỏi" style="max-width: 100%; margin-top: 10px;">
                    <?php endif; ?>
                    <ul>
                        <?php
                        $choices = [
                            'A' => $question_data["cau_a"],
                            'B' => $question_data["cau_b"],
                            'C' => $question_data["cau_c"],
                            'D' => $question_data["cau_d"]
                        ];
                        $explanations = [
                            'A' => $question_data["giaithich_a"],
                            'B' => $question_data["giaithich_b"],
                            'C' => $question_data["giaithich_c"],
                            'D' => $question_data["giaithich_d"]
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
                                <p><strong>🥰👍 Giải thích:</strong> <?= htmlspecialchars($explanations[$question_data["dap_an"]]) ?></p>
                            <?php else: ?>
                                <p><strong>😱👎 Giải thích:</strong> <?= htmlspecialchars($explanations[$question_data["dap_an"]]) ?></p>
                                <p><strong>Bạn chọn:</strong> <?= htmlspecialchars($choices[$userAnswer]) ?> (Giải thích: <?= htmlspecialchars($explanations[$userAnswer]) ?>)</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="explanation-block" style="border-color: orange;">
                            <p style="color: orange; font-weight: bold;">⚠️ Bạn chưa trả lời câu hỏi này!</p>
                            <p><strong>Đáp án đúng:</strong> <span class="correct-answer"><?= $question_data["dap_an"] ?>. <?= htmlspecialchars($choices[$question_data["dap_an"]]) ?></span></p>
                            <p><strong>Giải thích:</strong> <?= htmlspecialchars($explanations[$question_data["dap_an"]]) ?></p>
                        </div>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?= $attempts >= 3 ? '#' : 'FAQ.php?reset=1' ?>" class="try-again <?= $attempts >= 3 ? 'disabled' : '' ?>">🔁 Thử lại (<?= $attempts ?> / 3)</a>
    </div>
</body>
</html>

<Style>
    body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 20px;
    color: #333;
}

.container {
    max-width: 900px;
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

a.try-again {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 25px;
    background-color: #3498db;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    text-align: center;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

a.try-again:hover {
    background-color: #2980b9;
}

a.try-again.disabled {
    background-color: #ccc;
    pointer-events: none;
    cursor: not-allowed;
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

</Style>