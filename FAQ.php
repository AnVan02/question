<?php
session_start();
require_once "cauhoi.php";

// Khởi tạo
if (!isset($_SESSION["current"])) $_SESSION["current"] = 0;
if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["feedback"])) $_SESSION["feedback"] = "";
if (!isset($_SESSION["answers"])) $_SESSION["answers"] = [];
if (!isset($_SESSION["attempts"])) $_SESSION["attempts"] = 0;
if (!isset($_SESSION["highest_score"])) $_SESSION["highest_score"] = 0;
if (!isset($_SESSION["time"])) $_SESSION["time"] = date("d-m-Y H:i:s");


if ($_SESSION["attempts"] >= 3) {
    header("Location: ketqua.php?limit_exceeded=1");
    exit;
}

if (isset($_GET["reset"]) && $_SESSION["attempts"] < 3) {
    $_SESSION["current"] = 0;
    $_SESSION["score"] = 0;
    $_SESSION["feedback"] = "";
    $_SESSION["answers"] = [];
    $_SESSION["time"] = date("d-m-Y H:i:s");
    header("Location: FAQ.php");
    exit;
}

$current = $_SESSION["current"];
$total = count($questions);


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["next"])) {
    if (isset($_POST["answer"])) {
        $selected = $_POST["answer"];
        $reason = isset($_POST["reason"]) ? trim($_POST["reason"]) : "";
        $correct = $questions[$current]["correct"];

       
        $_SESSION["answers"][$current] = [
            "selected" => $selected,
            "is_correct" => ($selected === $correct),
            "reason" => $reason
        ];

     
        if ($selected === $correct) {
            $_SESSION["score"]++;
        }

      
        $_SESSION["current"]++;
        $_SESSION["feedback"] = "";
        header("Location: FAQ.php");
        exit;

    } else {
        $_SESSION["feedback"] = "<div style='color: orange;'>⚠️ Vui lòng chọn một đáp án!</div>";
        $_SESSION["showNext"]= false;
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


$current = $_SESSION["current"];
if ($current >= $total) {
    $_SESSION["attempts"]++;
    if ($_SESSION["score"] > $_SESSION["highest_score"]) {
        $_SESSION["highest_score"] = $_SESSION["score"];
    }

    $_SESSION["time"] = date("d-m-Y H:i:s");
    header("Location: ketqua.php");
    exit;
}
    $question_data = $questions[$current];
    
?>


    <div class="container">
        <form method="post">
            <div class="question">Câu hỏi: <?= htmlspecialchars($question_data["question"]) ?></div>
            <?php if (!empty($question_data["image"])): ?>
                <div class="question-image-container">
                    <img src="<?= htmlspecialchars($question_data["image"]) ?>" alt="Hình ảnh câu hỏi" class="question-image">
                </div>
            <?php endif; ?>

            <?php foreach ($question_data["choices"] as $key => $value):?>
                <div class="answer">
                    <input type="radio"name="answer"value="<?=$key?>"id="<?=$key?>"
                    <?php if (isset($_SESSION["answers"][$current]["selected"]) && $_SESSION["answers"][$current]["selected"] === $key): ?>
                        checked
                    <?php endif; ?>
                    >
                <label for="<?= $key ?>"><?= htmlspecialchars($value) ?></label>
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
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #fff;
            margin: 0;
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
        .reason {
            margin: 15px 0;
        }
        .reason label {
            font-size: 16px;
            color: #1a1a1a;
        }
        .reason textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d1d1;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
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