<?php

session_start();

// Kiểm tra đăng nhập (giả sử đã có session 'admin')
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang quản trị</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h1>Chào mừng đến trang quản trị</h1>
    <nav>
        <ul>
            <li><a href="user_list.php">Quản lý người dùng</a></li>
            <li><a href="settings.php">Cài đặt</a></li>
            <li><a href="logout.php">Đăng xuất</a></li>
        </ul>
    </nav>
    <div>
        
    </div>
</body>
</html>