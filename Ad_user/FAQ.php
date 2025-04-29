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
    if (count($question_keys) < 5) {
        die("Lỗi: Không đủ câu hỏi trong cauhoi.php. Cần ít nhất 5 câu hỏi.");
    }
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
    $question_keys = array_keys($questions);
    if (count($question_keys) < 5) {
        die("Lỗi: Không đủ câu hỏi trong sql. Cần ít nhất 5 câu hỏi.");
    }
    shuffle($question_keys);
    $_SESSION["selected_questions"] = array_slice($question_keys, 0, 5);
    header("Location: FAQ.php");
    exit;
}

$current = $_SESSION["current"];
$total = 5;

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["next"])) {
    if (isset($_POST["answer"])) {
        $selected = $_POST["answer"];
        $question_index = $_SESSION["selected_questions"][$current];
        $correct = $questions[$question_index]["correct"];

        // Store answer
        $_SESSION["answers"][$current] = [
            "selected" => $selected,
            "is_correct" => ($selected === $correct),
            "question_index" => $question_index
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
if (!isset($_SESSION["selected_questions"][$current])) {
    // Reset session and redirect if selected_questions is invalid
    $_SESSION["selected_questions"] = [];
    header("Location: FAQ.php?reset=1");
    exit;
}

$question_index = $_SESSION["selected_questions"][$current];
if (!isset($questions[$question_index])) {
    // Reset session if question_index is invalid
    $_SESSION["selected_questions"] = [];
    header("Location: FAQ.php?reset=1");
    exit;
}

$question_data = $questions[$question_index];

// Map answer keys to A, B, C, D
$answer_labels = ['A', 'B', 'C', 'D'];
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Lập Trình</title>
    <link rel="stylesheet" href="style.css">

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
                        <?php endif; ?>
                    >
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
            </div>
        </form>
    </div>
