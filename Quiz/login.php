<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "study";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];

    // Query the students table for authentication
    $sql = "SELECT * FROM students WHERE Student_ID = :student_id AND Password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['student_id' => $student_id, 'password' => $password]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Hiển thị giá trị Khoahoc từ CSDL
        echo "<p>Debug: Giá trị Khoahoc từ CSDL: " . htmlspecialchars($user['Khoahoc'] ?? 'NULL/Không tồn tại') . "</p>";

        // Lưu thông tin người dùng vào session
        $_SESSION['student_id'] = $user['Student_ID'];
        $_SESSION['student_name'] = $user['Ten'];
        $_SESSION['Khoahoc'] = isset($user['Khoahoc']) ? (string)$user['Khoahoc'] : '';

        // Debug: Kiểm tra giá trị Khoahoc ngay trước khi chuyển hướng
        echo "<p style='color: blue; font-weight: bold;'>DEBUG: Giá trị Khoahoc trong Login.php: " . htmlspecialchars($_SESSION['Khoahoc']) . "</p>";
        die("Kiểm tra giá trị trên và cung cấp cho AI.");

        // Chuyển hướng đến trang dashboard
        header("Location: theme.php");
        exit();
    } else {
        $error = "Mã sinh viên hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            max-width: 400px;
            margin: 50px auto;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        <?php if (isset($error)) : ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="student_id" placeholder="Mã sinh viên" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="submit" value="Đăng nhập">
        </form>
    </div>
</body>
</html>