<?php
session_start();

// Danh sách người dùng cố định (thay thế bằng cơ sở dữ liệu trong thực tế)
$users = [
    'admin' => '123456',
    'user' => '456789'
];

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (isset($users[$username]) && $users[$username] === $password) {
        $_SESSION['username'] = $username;
        header('Location: content1.php');
        exit;
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
    }
}

// Nếu đã đăng nhập, chuyển hướng đến content1.php
if (isset($_SESSION['username'])) {
    header('Location: content1.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; }
        .error { color: red; }
        form { display: flex; flex-direction: column; gap: 10px; }
        input { padding: 8px; }
        button { padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h2>Đăng nhập</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Đăng nhập</button>
    </form>
</body>
</html>






