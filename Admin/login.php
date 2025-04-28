<?php
session_start();

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Simple authentication (replace with secure method in production)
    if ($username === "admin" && $password === "123456") {
        $_SESSION["is_admin"] = true;
        header("Location: admin.php");
        exit;
    } else {
        $message = "<div style='color: red;'>Tên đăng nhập hoặc mật khẩu sai!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Đăng nhập Admin</h1>
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <button type="submit" name="login" class="btn btn-save">Đăng nhập</button>
            </div>
        </form>
    </div>
</body>
</html>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

.container {
    width: 100%;
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

label {
    font-size: 14px;
    color: #555;
    display: block;
    margin-bottom: 5px;
}

input[type="text"], input[type="password"] {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: #0056b3;
}

.message {
    padding: 10px;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    margin-bottom: 20px;
}


</style>