<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);
    
    $conn = dbconnect();

    
    $stmt = $conn->prepare("INSERT INTO login (Student_ID, Password) VALUES (?, ?)");
    $stmt->bind_param("ss", $student_id, $password);

    if ($stmt->execute()) {
        $success = "Đăng ký thành công!";
    } else {
        $error = "Tài khoản đã tồn tại hoặc lỗi: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
</head>
<body>
    <div class="container">
        <h2>Đăng ký tài khoản</h2>
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="student_id">Tên đăng nhập:</label>
            <input type="text" name="student_id" required>

            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" required>
            <button type="submit">Đăng ký</button>
        </form>
    </div>
</body>
</html>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family : Arial , sans-serif;
        background: linear-gradient(135deg, #74ebd5, #ACB6E5);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container {
        background: #ffffff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    label {
        font-weight: bold;
        color: #333;
    }

    input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        transition: border-color 0.3s;
    }

    input:focus {
        border-color: #4CAF50;
        outline: none;
    }

    button {
        padding: 12px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #45a049;
    }

    .error {
        color: #d8000c;
        background-color: #ffbaba;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .success {
        color: #4F8A10;
        background-color: #DFF2BF;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
</style>