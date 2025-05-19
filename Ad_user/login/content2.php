<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
// user2 
$student_id = $_SESSION['student_id'];
if ($student_id != 1 && $student_id != 3) {
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
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = 2"; // Python Nâng Cao
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
    <title>Content 2</title>
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
        <p>Hello bạn user<?php echo htmlspecialchars($student_id); ?> - bạn học khoá <?php echo htmlspecialchars($khoa_hoc); ?></p>
    </div>
</body>
</html>