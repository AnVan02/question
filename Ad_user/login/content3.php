<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = intval($_SESSION['student_id']);

// Kiểm tra quyền truy cập
if ($student_id == 1 || $student_id == 2) {
    // Cho phép truy cập
} else {
    echo "Bạn không có quyền truy cập khoá học này";
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "study";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = 3"; // PYTHON CHUYÊN SÂU
    $stmt = $conn->query($sql);
    $khoa_hoc = $stmt->fetchColumn();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Content 3</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #e6f3fa, #f4f4f9);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .content-container {
            background-color: white;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            color: #007bff;
        }
        p {
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h2>Khoá học <?php echo htmlspecialchars($khoa_hoc); ?></h2>
        <p>Xin chào, <?htmlspecialchars($_SESSION ['student_name'])?></p>

        <?php foreach ($questions as $index => $question): ?>
        <div class =" question">
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
        <button type="submit" class="btn-sucmit">Nộp bài</button>        
        
   
   
    </div>
</body>
</html>