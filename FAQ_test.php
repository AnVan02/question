<?php
session_start();
require_once "cauhoi.php";

// Khởi tạo
if (!isset($_SESSION["current"])) $_SESSION["current"] = 0;
if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["showNext"])) $_SESSION["showNext"] = false;
if (!isset($_SESSION["feedback"])) $_SESSION["feedback"] = "";
if (!isset($_SESSION["explanation"])) $_SESSION["explanation"] = "";

$current = $_SESSION["current"];
$total = count($questions);

// Xử lý kiểm tra đáp án
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["skip"])) {
    if (isset($_POST["answer"])) {
        $selected = $_POST["answer"];
        $correct = $questions[$current]["correct"];

        if ($selected === $correct) {
            $_SESSION["score"]++;
            $_SESSION["feedback"] = "<div style='color: green;'>✅ Chính xác!</div>";
        } else {
            $_SESSION["feedback"] = "<div style='color: red;'>❌ Sai rồi!</div>";
        }

        
        $_SESSION["explanation"] = "<div><strong>Giải thích:</strong> " . $questions[$current]["explanation"] . "</div>";
        $_SESSION["showNext"] = true;
    } else {
        $_SESSION["feedback"] = "<div style='color: orange;'>⚠️ Vui lòng chọn một đáp án!</div>";
        $_SESSION["showNext"] = false;
    }

}

// Xử lý bỏ qua
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["skip"])) {
    $_SESSION["current"]++;
    $_SESSION["showNext"] = false;
    $_SESSION["feedback"] = "";
    $_SESSION["explanation"] = "";
    header("Location: FAQ.php");
    exit;
}

// Chuyển sang câu tiếp theo
if (isset($_GET["next"]) && $_SESSION["showNext"]) {
    $_SESSION["current"]++;
    $_SESSION["showNext"] = false;
    $_SESSION["feedback"] = "";
    $_SESSION["explanation"] = "";
    header("Location: FAQ.php");
    exit;
}

$current = $_SESSION["current"];
if ($current >= $total) {
    $final_score = $_SESSION["score"];
    session_destroy();
    echo "<h2>Bạn đã hoàn thành quiz!</h2>";
    echo "<p>Điểm của bạn: <strong>$final_score / $total</strong></p>";
    echo "<a href='FAQ.php'>Làm lại</a>";
    exit;
}

$question_data = $questions[$current];
$selectedAnswer = $_POST["answer"] ?? ""; // ✅ Thêm dòng này ở đây!

?>
    <div class="container">
        <form method="post">
            <div class="question">Câu hỏi: <?= $question_data["question"] ?></div>
            <?php if (!empty($question_data["image"])): ?>
                <div class="question-image-container">
                    <img src="<?= htmlspecialchars($question_data["image"]) ?>" alt="Hình ảnh câu hỏi" class="question-image">
                </div>
            <?php endif; ?>

            <?php foreach ($question_data["choices"] as $key => $value): ?>
                <div class="answer">
                    <input type="radio" name="answer" value="<?= $key ?>" id="<?= $key ?>"
                        <?= ($selectedAnswer === $key) ? "checked" : "" ?>>
                    <label for="<?= $key ?>"><?= $value ?></label>
                </div>
            <?php endforeach; ?>

            <?php if ($_SESSION["explanation"] && $_SESSION["showNext"]): ?>
                <div class="explanation"><?= $_SESSION["explanation"] ?></div>
            <?php endif; ?>

            <div class="content-area">
                <div class="left-area">
                    <div class="progress">Câu <?= $current + 1 ?> / <?= $total ?></div>
                    <?php if ($_SESSION["feedback"]): ?>
                        <div class="result-box">
                            <?= $_SESSION["feedback"] ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$_SESSION["showNext"]): ?>
                <div class="btn-area">
                    <button type="submit" name="skip" value="1" class="btn-skip">⏭️ Bỏ qua</button>
                    <button type="submit" class="btn-check">✔️ Kiểm tra</button>
                </div>
                <?php endif; ?>
            </div>
            
        </form>

        <?php if ($_SESSION["showNext"]): ?>
            <div class="btn-area">
                <a href="?next=1" class="btn-next">Tiếp theo</a>
            </div>
        <?php endif; ?>

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
    /* .answer input[type="radio"] {
        display: none;
    } */
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
    .explanation {
        margin: 15px 0;
        font-size: 19px;
        color: #1a1a1a;
    }
    .content-area {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-top: 120px;
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
        background:#f9f9f9 ;
        border-left:5px solid #ccc ; 
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
    .btn-check, .btn-skip, .btn-next {
        padding: 8px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: background-color 0.2s, color 0.2s, border-color 0.2s;
    }
    .btn-check {
        background: #fff;
        border: 2px solid #6f42c1;
        color: #6f42c1;
    }
    .btn-check:hover {
        background: #6f42c1;
        color: #fff;
        border-color: #6f42c1;
    }
    .btn-check:disabled {
        border-color: #d1d1d1;
        color: #d1d1d1;
        cursor: not-allowed;
    }
    .btn-skip {
        background: #fff;
        border: 2px solid #6f42c1;
        color: #6f42c1;
    }
    .btn-skip:hover {
        background: #6f42c1;
        color: #fff;
        border-color: #6f42c1;
    }

    .btn-next {
        background: #6f42c1;
        border: none;
        color: #fff;
        padding: 8px 20px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-next:hover {
        background: #563d7c;
    }
    
</style>
   