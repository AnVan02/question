<?php
session_start();
require_once "cauhoi.php";


// Initialize session variables
if (!isset($_SESSION["current"])) $_SESSION["current"] = 0;
if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["feedback"])) $_SESSION["feedback"] = "";
if (!isset($_SESSION["answers"])) $_SESSION["answers"] = [];
if (!isset($_SESSION["attempts"])) $_SESSION["attempts"] = 0;
if (!isset($_SESSION["highest_score"])) $_SESSION["highest_score"] = 0;
if (!isset($_SESSION["time"])) $_SESSION["time"] = date("d-m-Y H:i:s");

// Select 5 random questions at the start of the quiz
if (!isset($_SESSION["selected_questions"]) || isset($_GET["reset"])) {
    $question_keys = array_keys($questions);
    shuffle($question_keys);
    $_SESSION["selected_questions"] = array_slice($question_keys, 0, 5);
}

// Check attempt limit
if ($_SESSION["attempts"] >= 3) {
    header("Location: ketqua.php?limit_exceeded=1");
    exit;
}

// Handle reset
if (isset($_GET["reset"]) && $_SESSION["attempts"] < 3) {
    $_SESSION["current"] = 0;
    $_SESSION["score"] = 0;
    $_SESSION["feedback"] = "";
    $_SESSION["answers"] = [];
    $_SESSION["time"] = date("d-m-Y H:i:s");
    // Re-select random questions
    $question_keys = array_keys($questions);
    shuffle($question_keys);
    $_SESSION["selected_questions"] = array_slice($question_keys, 0, 5);
    header("Location: FAQ.php");
    exit;
}
//

$current = $_SESSION["current"];
$total = 5; // Fixed to 5 questions

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["next"])) {
    if (isset($_POST["answer"])) {
        $selected = $_POST["answer"];
        $reason = isset($_POST["reason"]) ? trim($_POST["reason"]) : "";
        $question_index = $_SESSION["selected_questions"][$current];
        $correct = $questions[$question_index]["correct"];

        // Store answer
        $_SESSION["answers"][$current] = [
            "selected" => $selected,
            "is_correct" => ($selected === $correct),
            "reason" => $reason
        ];

        // Update score
        if ($selected === $correct) {
            $_SESSION["score"]++;
        }

        $_SESSION["current"]++;
        $_SESSION["feedback"] = "";
        header("Location: FAQ.php");
        exit;
    } else {
        $_SESSION["feedback"] = "<div style='color: orange;'>⚠️ Vui lòng chọn một đáp án!</div>";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["skip"])) {
    $_SESSION["current"]++;
    $_SESSION["feedback"] = "";
    header("Location: FAQ.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["goBack"])) {
    if ($_SESSION["current"] > 0) {
        $_SESSION["current"]--;
        $_SESSION["feedback"] = "";
    }
    header("Location: FAQ.php");
    exit;
}

// Check if quiz is complete
if ($current >= $total) {
    $_SESSION["attempts"]++;
    if ($_SESSION["score"] > $_SESSION["highest_score"]) {
        $_SESSION["highest_score"] = $_SESSION["score"];
    }
    $_SESSION["time"] = date("d-m-Y H:i:s");
    header("Location: ketqua.php");
    exit;
}

// Get current question
$question_index = $_SESSION["selected_questions"][$current];
$question_data = $questions[$question_index];

// Map answer keys to A, B, C, D
$answer_labels = ['A', 'B', 'C', 'D'];
?>

<div class="container">
    <form method="post">
        <div class="question">Câu hỏi: <?= htmlspecialchars($question_data["question"]) ?></div>
        
        <?php if (!empty($question_data["image"])): ?>
            <div class="question-image-container">
                <img src="<?= htmlspecialchars($question_data["image"]) ?>" alt="Hình ảnh câu hỏi" class="question-image">
            </div>
        <?php endif; ?>

        <?php foreach ($question_data["choices"] as $key => $value): ?>
            <?php $label = $answer_labels[array_search($key, array_keys($question_data["choices"]))]; ?>
            <div class="answer">
                <input type="radio" name="answer" value="<?= $key ?>" id="<?= $key ?>"
                    <?php if (isset($_SESSION["answers"][$current]["selected"]) && $_SESSION["answers"][$current]["selected"] === $key): ?>
                        checked
                    <?php endif; ?>>

                <label for="<?= $key ?>"><?= $label ?>. <?= htmlspecialchars($value) ?></label>
            </div>
        <?php endforeach; ?>
        <div class="content-area">
            <div class="left-area">
                <div class="progress">Câu <?= $current + 1 ?> / <?= $total ?></div>
                <?php if ($_SESSION["feedback"]): ?>
                    <div class="result-box">
                        <?= $_SESSION["feedback"] ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="btn-area">
                <button type="submit" name="goBack" class="btn-prev">⬅️ Quay lại</button>
                <button type="submit" name="next" class="btn-next">Tiếp theo ➡️</button>
            </div>

        </form>
    </div>
</div>

<style>
    /* Your existing CSS remains unchanged */
      body {
        font-family: 'Arial', sans-serif;
        margin: 20px auto;
        max-width: 1300px;
        background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
        color: #333;
        line-height: 1.6;
    }
    .container {
        max-width: 800px;
        margin: auto;
        background: #fff;
        padding: 20px;
    }
    .question {
        font-size: 20px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 15px;
    }
    .question-image-container {
        text-align: center;
        margin-bottom: 15px;
    }
    .question-image {
        max-width: 100%;
        height: auto;
        display: inline-block;
    }
    .answer {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    .answer label {
        display: block;
        padding: 10px 15px;
        border: 1px solid #d1d1d1;
        border-radius: 25px;
        font-size: 16px;
        color: #1a1a1a;
        cursor: pointer;
        width: 100%;
        transition: border-color 0.2s, color 0.2s;
    }
    .answer input[type="radio"]:checked + label {
        border-color: #6f42c1;
        color: #6f42c1;
    }
    .answer label:hover {
        border-color: #6f42c1;
    }
    .content-area {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-top: 20px;
    }
    .left-area {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .progress {
        font-size: 14px;
        font-weight: bold;
        color: #1a1a1a;
    }
    .result-box {
        padding: 15px;
        background: #f9f9f9;
        border-left: 5px solid #ccc;
        flex: 1;
        font-size: 14px;
    }
    .btn-area {
        flex: 1;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        align-items: center;
    }
    .btn-skip, .btn-next, .btn-prev {
        padding: 8px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: background-color 0.2s, color 0.2s, border-color 0.2s;
    }
    .btn-skip, .btn-prev {
        background: #fff;
        border: 2px solid #6f42c1;
        color: #6f42c1;
    }
    .btn-skip:hover, .btn-prev:hover {
        background: #6f42c1;
        color: #fff;
        border-color: #6f42c1;
    }
    .btn-next {
        background: #6f42c1;
        border: none;
        color: #fff;
    }
    .btn-next:hover {
        background: #563d7c;
    }
</style>