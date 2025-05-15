<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    echo "Đăng nhập thất bại";
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "study";


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = 3"; // YOLO
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
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .content-container {
            background-color: white;
            padding: 20px;
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
        a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h2>Trang Content 3</h2>
        <p>Welcome <?php echo htmlspecialchars($_SESSION['student_id']); ?> - <?php echo htmlspecialchars($khoa_hoc); ?></p>
        <p>
            <a href="content1.php">Content 1</a> |
            <a href="content2.php">Content 2</a> |
            <a href="logout.php">Đăng xuất</a>
        </p>
    </div>
</body>
</html>