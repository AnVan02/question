<?php
session_start();

// Kết nối cơ sở dữ liệu bằng PDO
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Kiểm tra session
$student_id = $_SESSION['student_id'] ?? null;
if (!$student_id) {
    header("Location: login.php");
    exit();
}

// Dữ liệu đầu vào
$ma_khoa = '1'; // Mã khóa học của Python cơ bản
$id_test = '19'; // ID bài kiểm tra
$allowed_khoa = [4, 3, 8]; // Danh sách mã khóa học được phép cho id_test = 19
$allowed_khoa_string = implode(',', $allowed_khoa);

// Kiểm tra khóa học của sinh viên bằng PDO
$sql = "SELECT Khoahoc FROM students WHERE Student_ID = :student_id AND Khoahoc IN ($allowed_khoa_string)";
$stmt = $conn->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $khoa_hoc = $row['Khoahoc'];
    // Sinh viên được phép truy cập khóa học
    $message = "Sinh viên $student_id thuộc khóa học được phép ($khoa_hoc).";
} else {
    // Lấy khóa học hiện tại của sinh viên
    $sql_khoa = "SELECT Khoahoc FROM students WHERE Student_ID = :student_id";
    $stmt_khoa = $conn->prepare($sql_khoa);
    $stmt_khoa->execute(['student_id' => $student_id]);
    $row_khoa = $stmt_khoa->fetch(PDO::FETCH_ASSOC);
    $khoa_hoc = $row_khoa['Khoahoc'] ?? 'không xác định';

    $message = "Sinh viên $student_id KHÔNG thuộc khóa học được phép! Khóa học hiện tại: $khoa_hoc.";
    // Hiển thị thông báo lỗi và dừng
    echo "<!DOCTYPE html>
    <html lang='vi'>
    <head>
        <meta charset='UTF-8'>
        <title>Lỗi truy cập</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .error { color: #dc3545; font-size: 18px; }
        </style>
    </head>
    <body>
        <div class='error'>$message</div>
        <p><a href='theme_list.php'>Quay lại danh sách khóa học</a></p>
    </body>
    </html>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khóa học Python cơ bản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            width: 400px;
        }
        .success {
            color: #28a745;
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Khóa học Python cơ bản</h2>
        <div class="success"><?php echo $message; ?></div>
        <p>Chào mừng bạn đến với khóa học Python cơ bản!</p>
        <p><a href="theme_list.php">Quay lại danh sách khóa học</a></p>
    </div>
</body>
</html>