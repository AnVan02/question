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
                'question' => $row['cauhoi'],
                'choices' => [
                    'A' => $row['cau_a'],
                    'B' => $row['cau_b'],
                    'C' => $row['cau_c'],
                    'D' => $row['cau_d']
                ],
                'explanations' => [
                    'A' => $row['giaithich_a'],
                    'B' => $row['giaithich_b'],
                    'C' => $row['giaithich_c'],
                    'D' => $row['giaithich_d']
                ],
                'correct' => $row['dap_an'],
                'image' => $row['hinhanh']
            ];
        }
    }
    $conn->close();
    if (empty($questions)) {
        die("Lỗi: Không có câu hỏi nào trong cơ sở dữ liệu. Vui lòng thêm ít nhất 5 câu hỏi.");
    }
    return $questions;
}

// Lấy danh sách câu hỏi
$questions = getQuestionsFromDB();

// Khởi tạo biến session
if (!isset($_SESSION["current"])) $_SESSION["current"] = 0;
if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["feedback"])) $_SESSION["feedback"] = "";
if (!isset($_SESSION["answers"])) $_SESSION["answers"] = [];
if (!isset($_SESSION["attempts"])) $_SESSION["attempts"] = 0;
if (!isset($_SESSION["highest_score"])) $_SESSION["highest_score"] = 0;
if (!isset($_SESSION["time"])) $_SESSION["time"] = date("d-m-Y H:i:s");

// Chọn ngẫu nhiên 5 câu hỏi khi bắt đầu hoặc reset
if (!isset($_SESSION["selected_questions"]) || isset($_GET["reset"])) {
    $question_keys = array_keys($questions);
    if (count($question_keys) < 5) {
        die("Lỗi: Cần ít nhất 5 câu hỏi trong cơ sở dữ liệu.");
    }
    shuffle($question_keys);
    $_SESSION["selected_questions"] = array_slice($question_keys, 0, 5);
}

// Kiểm tra giới hạn số lần thử
if ($_SESSION["attempts"] >= 3) {
    header("Location: ketqua.php?limit_exceeded=1");
    exit;
}

// Xử lý reset
if (isset($_GET["reset"]) && $_SESSION["attempts"] < 3) {
    $_SESSION["current"] = 0;
    $_SESSION["score"] = 0;
    $_SESSION["feedback"] = "";
    $_SESSION["answers"] = [];
    $_SESSION["time"] = date("d-m-Y H:i:s");
    $question_keys = array_keys($questions);
    if (count($question_keys) < 5) {
        die("Lỗi: Cần ít nhất 5 câu hỏi trong cơ sở dữ liệu.");
    }
    shuffle($question_keys);
    $_SESSION["selected_questions"] = array_slice($question_keys, 0, 5);
    header("Location: FAQ.php");
    exit;
}

$current = $_SESSION["current"];
$total = 5;

// Xử lý gửi biểu mẫu
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["next"])) {
    if (isset($_POST["answer"])) {
        $selected = $_POST["answer"];
        $question_index = $_SESSION["selected_questions"][$current];
        $correct = $questions[$question_index]["correct"];

        // Lưu câu trả lời
        $_SESSION["answers"][$current] = [
            "selected" => $selected,
            "is_correct" => ($selected === $correct),
            "question_index" => $question_index
        ];

        // Cập nhật điểm
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

// Kiểm tra nếu hoàn thành bài kiểm tra
if ($current >= $total) {
    $_SESSION["attempts"]++;
    if ($_SESSION["score"] > $_SESSION["highest_score"]) {
        $_SESSION["highest_score"] = $_SESSION["score"];
    }
    $_SESSION["time"] = date("d-m-Y H:i:s");
    header("Location: ketqua.php");
    exit;
}

// Lấy câu hỏi hiện tại
if (!isset($_SESSION["selected_questions"][$current])) {
    error_log("Lỗi: selected_questions không hợp lệ tại chỉ số $current");
    $_SESSION["selected_questions"] = [];
    header("Location: FAQ.php?reset=1");
    exit;
}

$question_index = $_SESSION["selected_questions"][$current];
if (!isset($questions[$question_index])) {
    error_log("Lỗi: question_index không hợp lệ: $question_index");
    $_SESSION["selected_questions"] = [];
    header("Location: FAQ.php?reset=1");
    exit;
}

$question_data = $questions[$question_index];

// Kiểm tra dữ liệu câu hỏi
if (!isset($question_data['question']) || !isset($question_data['choices']) || !is_array($question_data['choices']) || !isset($question_data['correct'])) {
    error_log("Lỗi: Dữ liệu câu hỏi không hợp lệ cho question_index $question_index: " . print_r($question_data, true));
    $_SESSION["selected_questions"] = [];
    header("Location: FAQ.php?reset=1");
    exit;
}

// Gán nhãn cho các đáp án
$answer_labels = ['A', 'B', 'C', 'D'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Lập Trình</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form method="post">
            <div class="question">Câu hỏi: <?= htmlspecialchars($question_data['question']) ?></div>
            <?php if (!empty($question_data['image'])): ?>
                <div class="question-image-container">
                    <img src="<?= htmlspecialchars($question_data['image']) ?>" alt="Hình ảnh câu hỏi" class="question-image">
                </div>
            <?php endif; ?>

            <?php foreach ($question_data['choices'] as $key => $value): ?>
                <?php $label = $answer_labels[array_search($key, array_keys($question_data['choices']))]; ?>
                <div class="answer">
                    <input type="radio" name="answer" value="<?= htmlspecialchars($key) ?>" id="<?= htmlspecialchars($key) ?>"
                        <?php if (isset($_SESSION['answers'][$current]['selected']) && $_SESSION['answers'][$current]['selected'] === $key): ?>
                            checked
                        <?php endif; ?>
                    >
                    <label for="<?= htmlspecialchars($key) ?>"><?= $label ?>. <?= htmlspecialchars($value) ?></label>
                </div>
            <?php endforeach; ?>

            <div class="content-area">
                <div class="left-area">
                    <div class="progress">Câu <?= $current + 1 ?> / <?= $total ?></div>
                    <?php if ($_SESSION['feedback']): ?>
                        <div class="result-box">
                            <?= $_SESSION['feedback'] ?>
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
</body>
</html>