<?php
session_start();
require_once("dbconnect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];

    $conn = dbconnect();
    $stmt = $conn->prepare("SELECT Student_ID, Ten, Khoahoc FROM students WHERE Student_ID = ? AND Password = ?");
    $stmt->bind_param("ss", $student_id, $password);
    $stmt->execute();
    $stmt->bind_result($db_student_id, $student_name, $khoahoc);
    if ($stmt->fetch() && $db_student_id) {
        $_SESSION['student_id'] = $db_student_id;
        $_SESSION['student_name'] = $student_name;
        $_SESSION['Khoahoc'] = $khoahoc; // Lưu danh sách khóa học
        header("Location: quiz.php?course_id=$id_test ");
        exit();
    } else {
        $error = "Đăng nhập thất bại. Vui lòng kiểm tra ID hoặc mật khẩu.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
    <meta charset="UTF-8">
    <style>
        body {
            FONT-FAMILY: Arial, sans-serif;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            text-align: center;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="login.php">
            <input type="text" name="student_id" placeholder="Student ID" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>