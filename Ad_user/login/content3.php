<?php
session_start();

// Kiểm tra trạng thái session
if (!isset($_SESSION['username'])) {
    echo "Đăng nhập thất bại";
    exit;
}
$username = htmlspecialchars($_SESSION['username']);
$session_id = session_id(); // Lấy session ID để debug
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOLO</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; }
        .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
        h2 { color: #4CAF50; }
        a { color: #4CAF50; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        Người dùng: <?php echo $student_Id; ?>
    </div>
    <h2>Xin chào bạn <?php echo $username; ?> + Tên đăng nhập: <?php echo $khoa_hoc; ?></h2>
    <p>Yolo</p>
    <p><a href="content1.php">Khoá học Yolo</a></p>
    <p><a href="logout.php">Đăng xuất</a></p>
</body>
</html>


