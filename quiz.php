<?php
session_start();

// Bật báo lỗi PHP để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Hàm kết nối CSDL
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy test_id và khoa_id từ URL
$test_id = $_GET['test_id'] ?? '';
$khoa_id = $_GET['khoa_id'] ?? '';
$enrolled_courses = $_SESSION['courses'] ?? [];
$message = "";
$questions = [];

// Kiểm tra quyền và dữ liệu đầu vào
if (empty($test_id) || empty($khoa_id)) {
    $message = "<div style='color:red;'>Thiếu test_id hoặc khoa_id trong URL!</div>";
} elseif (!in_array($khoa_id, $enrolled_courses)) {
    $message = "<div style='color:red;'>Bạn không có quyền vào khóa học này!</div>";
} else {
    $conn = dbconnect();

    // Lấy tên khóa học từ id
    $stmt_khoa = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
    $stmt_khoa->bind_param("i", $khoa_id);
    $stmt_khoa->execute();
    $stmt_khoa->bind_result($ten_khoa);
    $stmt_khoa->fetch();
    $stmt_khoa->close();

    if (!$ten_khoa) {
        $message = "<div style='color:red;'>Không tìm thấy tên khóa học với id này!</div>";
    } else {
        // Lấy danh sách câu hỏi cho bài kiểm tra
        $sql = "SELECT * FROM quiz WHERE id_baitest = ? AND ten_khoa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $test_id, $ten_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row;
            }
        } else {
            $message = "<div style='color:red;'>Không tìm thấy câu hỏi nào cho bài kiểm tra này.</div>";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài kiểm tra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f4f4f4;
            margin: 0;
        }
        h2 {
            text-align: center;
            color: rgb(247, 18, 18);
            margin-bottom: 25px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .question {
            margin-bottom: 20px;
        }
        .options {
            margin-top: 10px;
        }
        .options label {
            display: block;
            margin: 5px 0;
        }
        .btn-submit {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bài kiểm tra (ID: <?= htmlspecialchars($test_id) ?>)</h2>
        <p>Xin chào, <?= htmlspecialchars($_SESSION['student_name'] ?? $_SESSION['student_id']) ?> | <a href="logout.php">Đăng xuất</a></p>
        <p><a href="tests.php">Quay lại danh sách bài kiểm tra</a></p>
        <?php if (!empty($message)) echo $message; ?>

        <?php if (empty($questions) && empty($message)): ?>
            <p>Không có câu hỏi nào cho bài kiểm tra này.</p>
        <?php elseif (empty($message)): ?>
            <form method="POST" action="submit_quiz.php?test_id=<?= urlencode($test_id) ?>&khoa_id=<?= urlencode($khoa_id) ?>">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question">
                        <h3>Câu hỏi <?= $index + 1 ?>: <?= htmlspecialchars($question['cauhoi']) ?></h3>
                        <?php if (!empty($question['hinhanh'])): ?>
                            <img src="<?= htmlspecialchars($question['hinhanh']) ?>" alt="Hình ảnh câu hỏi" style="max-width: 300px;">
                        <?php endif; ?>
                        <div class="options">
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="A" required> A. <?= htmlspecialchars($question['cau_a']) ?></label><br>
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="B"> B. <?= htmlspecialchars($question['cau_b']) ?></label><br>
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="C"> C. <?= htmlspecialchars($question['cau_c']) ?></label><br>
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="D"> D. <?= htmlspecialchars($question['cau_d']) ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn-submit">Nộp bài</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>