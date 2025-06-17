<?php
// Hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Kết nối CSDL
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Không thể kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

//  Lấy danh sách khóa học 
function getCoursesFromDB() {
    $conn = dbconnect();
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    $conn->close();
    return $courses;
}

// Lấy thông tin bài test 
function getTestInfo($ten_test, $ten_khoa) {
    $conn = dbconnect();
    $courses = getCoursesFromDB();
    $id_khoa = array_search($ten_khoa, $courses);
    if ($id_khoa === false) die("Không tìm thấy khóa học '$ten_khoa'");

    $stmt = $conn->prepare("SELECT lan_thu FROM test WHERE ten_test = ? AND id_khoa = ?");
    $stmt->bind_param("si", $ten_test, $id_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    $info = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $info ?: ['lan_thu' => 1];
}

//  Lấy câu hỏi 
function getQuestionsFromDB($ten_khoa, $id_baitest, $required_count = 5) {
    $conn = dbconnect();
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
    $stmt->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
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
            'correct' => $row['dap_an'],
            'image' => $row['hinhanh']
        ];
    }
    $stmt->close();
    $conn->close();

    if (count($questions) < $required_count) {
        die("Số câu hỏi không hợp lệ (" . count($questions) . ").");
    }
    return $questions;
}

//  Lưu kết quả 
function saveTestResult($student_id, $khoa_id, $test_id, $best_score, $max_score, $pass, $trial, $max_trial) {
    $conn = dbconnect();
    $sql = "INSERT INTO kiem_tra (Student_ID, Khoa_ID, Test_ID, Best_Score, Max_Score, Pass, Trial, Max_trial)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE Best_Score = ?, Trial = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisssissi", $student_id, $khoa_id, $test_id, $best_score, $max_score, $pass, $trial, $max_trial, $best_score, $trial);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Kiểm tra session 
if (!isset($_SESSION['user_id'], $_SESSION['ten_khoa'], $_SESSION['id_baitest'])) {
    header("Location: login.php");
    exit;
}

$ten_khoa = $_GET['ten_khoa'] ?? $_SESSION['ten_khoa'];
$id_baitest = $_GET['id_baitest'] ?? $_SESSION['id_baitest'];
$courses = getCoursesFromDB();
$student_khoahoc = explode(',', $_SESSION['students']['Khoahoc'] ?? '');
$khoa_id = array_search($ten_khoa, $courses);
if ($khoa_id === false || !in_array($khoa_id, $student_khoahoc)) {
    die("Bạn không có quyền vào khóa học '$ten_khoa'");
}

// Thông tin test
$test_info = getTestInfo($id_baitest, $ten_khoa);
$max_attempts = $test_info['lan_thu'];
$total_questions = 5; // Số câu hỏi mặc định là 5
$questions = getQuestionsFromDB($ten_khoa, $id_baitest, $total_questions);

// Session init
$_SESSION["current"] ??= 0;
$_SESSION["score"] ??= 0;
$_SESSION["answers"] ??= [];
$_SESSION["feedback"] ??= "";
$_SESSION["attempts"] ??= 0;
$_SESSION["highest_score"] ??= 0;
$_SESSION["time"] ??= date("d-m-Y H:i:s");

// Chọn câu hỏi 
if (!isset($_SESSION["selected_questions"])) {
    $keys = array_keys($questions);
    shuffle($keys);
    $_SESSION["selected_questions"] = array_slice($keys, 0, $total_questions);
}

$current = $_SESSION["current"];
$total = $total_questions;

//  Xử lý form 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["next"])) {
        if (isset($_POST["answer"])) {
            $selected = $_POST["answer"];
            $q_index = $_SESSION["selected_questions"][$current];
            $correct = $questions[$q_index]["correct"];
            $_SESSION["answers"][$current] = [
                "selected" => $selected,
                "is_correct" => ($selected === $correct),
                "question" => $q_index
            ];
            if ($selected === $correct) $_SESSION["score"]++;
            $_SESSION["current"]++;
            $_SESSION["feedback"] = "";
            header("Location: FAQ.php?ten_khoa=$ten_khoa&id_baitest=$id_baitest");
            exit;
        } else {
            $_SESSION["feedback"] = "<div style='color: orange;'>⚠️ Vui lòng chọn một đáp án!</div>";
        }
    }

    if (isset($_POST["goBack"])) {
        if ($_SESSION["current"] > 0) {
            $_SESSION["current"]--;
        }
        header("Location: FAQ.php?ten_khoa=$ten_khoa&id_baitest=$id_baitest");
        exit;
    }
}
// kiêm tra xem có câu hỏi nào chưa trả lời không
$unanswered = array_filter($_SESSION["answers"], function($answer) {
    return !isset($answer["selected"]);
});
if (count($unanswered) > 0) {
    $_SESSION["feedback"] = "<div style='color: red;'>⚠️ Bạn chưa trả lời tất cả câu hỏi!</div>";
}

// Kết thúc test 
if ($current >= $total) {
    $_SESSION["attempts"]++;
    if ($_SESSION["score"] > $_SESSION["highest_score"]) {
        $_SESSION["highest_score"] = $_SESSION["score"];
    }

    $student_id = $_SESSION['user_id'];
    $test_id = $id_baitest;
    $best_score = $_SESSION["highest_score"];
    $max_score = $total > 0 ? $total : 1;
    $pass = ($best_score / $max_score * 100 >= 80) ? 'Đạt' : 'Không đạt';
    saveTestResult($student_id, $khoa_id, $test_id, $best_score, $max_score, $pass, $_SESSION["attempts"], $max_attempts);
    header("Location: ketqua.php");
    exit;
}
// bài thì sô 2 hoặc những bài khác học xong mới ktra
if ($test_info['lan_thu'] > 1) {
    $sql = "SELECT COUNT(*) FROM kiem_tra WHERE Student_ID = ? AND Khoa_ID = ? AND Test_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $_SESSION['user_id'], $khoa_id, $id_baitest);
    $stmt->execute();
    $stmt->bind_result($attempts);
    $stmt->fetch();
    $stmt->close();

    if ($attempts >= $max_attempts) {
        die("Bạn đã hoàn thành bài kiểm tra này.");
    }
}


// Hiển thị câu hỏi
$question_index = $_SESSION["selected_questions"][$current] ?? null;
$question_data = isset($question_index, $questions[$question_index]) ? $questions[$question_index] : null;
$answer_labels = ['A', 'B', 'C', 'D'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kiểm tra - <?= htmlspecialchars($ten_khoa) ?></title>
</head>
<body>
    <!-- lấy tên từ bảng students  -->
    <h2>Xin chào học viên ID: <?= htmlspecialchars($_SESSION['user_id']) ?> - bạn đang học khóa: <?= htmlspecialchars($ten_khoa) ?></h2>
    <form method="post">
        <?php if ($question_data): ?>
            <p><strong>Câu hỏi <?= $current + 1 ?>/<?= $total ?>:</strong> <?= htmlspecialchars($question_data['question']) ?></p>

            <?php if (!empty($question_data['image']) && file_exists($question_data['image'])): ?>
                <img src="<?= htmlspecialchars($question_data['image']) ?>" alt="Hình minh họa" style="max-width: 400px;"><br>
            <?php endif; ?>

            <?php foreach ($question_data['choices'] as $key => $value): ?>
                <div>
                    <label>
                        <input type="radio" name="answer" value="<?= $key ?>"
                            <?= (isset($_SESSION['answers'][$current]) && $_SESSION['answers'][$current]['selected'] === $key) ? 'checked' : '' ?>>
                        <?= $key ?>. <?= htmlspecialchars($value) ?>
                    </label>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <p style="color: red;">⚠️ Không thể tải câu hỏi. Vui lòng thử lại hoặc liên hệ quản trị viên.</p>
        <?php endif; ?>
    
        <?php if ($_SESSION['feedback']): ?>
            <p style="color: red;"><?= $_SESSION['feedback'] ?></p>
        <?php endif; ?>

        <button type="submit" name="goBack">⬅️ Quay lại</button>
        <button type="submit" name="next">Tiếp theo ➡️</button>
        
        <?php if ($current >= $total - 1): ?>
            <button type="submit" name="finish">Nộp bài</button>
        <?php endif; ?>
        
    </form>
</body>
</html>

<style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 20px auto;
        max-width: 1300px;
        background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
        color: #333;
        line-height: 1.6;
    }
    h2 {
        margin-bottom: 25px;
        color: #2d3748;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
        padding: 10px;
        background-color: #edf2f7;
        border-radius: 8px;
    }
   
    form {
        max-width: 800px;
        width: 100%;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin: 0 auto;
    }
    p {
        font-size: 1.1em;
    }
    label {
        display: block;
        margin: 10px 0;
        cursor: pointer;
    }
    input[type="radio"] {
        margin-right: 8px;
    }
    button {
        background: #007bff;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        margin: 10px 8px 0 0;
        font-size: 1em;
        cursor: pointer;
        transition: background 0.2s;
    }
   
    img {
        margin: 16px 0;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.10);
    }
    .feedback {
        color: #d9534f;
        font-weight: bold;
        margin: 12px 0;
    }
</style>
