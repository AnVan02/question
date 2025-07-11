<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    echo "Bạn chưa đăng nhập.";
    exit();
}

// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}

// Lấy student_id từ session
$ma_khoa = '1'; // Mã khóa học Hóa học
$id_test = '19'; // ID bài test
$student_id = $_SESSION['student_id'];

// Truy vấn lấy chuỗi khoá học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $khoahoc_str = $row['Khoahoc']; // VD: "20,1,5"
    $khoahoc_arr = array_map('intval', explode(',', $khoahoc_str)); // [20, 1, 5]
} else {
    echo "Không tìm thấy sinh viên.";
    exit();
}
$stmt->close();
$conn->close();


// Kiểm tra nếu người dùng đã nhập mã môn học để kiểm tra
$thongbao = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ma_mon'])) {
    $ma_mon = intval($_POST['ma_mon']);

    if (in_array($ma_mon, $khoahoc_arr)) {
        $thongbao = "✅ Bạn được học môn có mã: $ma_mon";
    } else {
        $thongbao = "❌ Bạn KHÔNG được học môn có mã: $ma_mon";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kiểm tra môn học</title>
</head>
<body>
    <h2>Xin chào sinh viên: <?php echo $_SESSION['student_id']; ?></h2>
    
    <form method="POST" action="">
        <label for="ma_mon">Nhập mã môn học muốn kiểm tra:</label>
        <input type="number" name="ma_mon" id="ma_mon" required>
        <button type="submit">Kiểm tra</button>
    </form>

    <?php if ($thongbao): ?>
        <p><strong><?php echo $thongbao; ?></strong></p>
    <?php endif; ?>

    <!-- <h3>📚 Danh sách mã môn học bạn đã đăng ký:</h3>
    <ul>
        <?php foreach ($khoahoc_arr as $mon): ?>
            <li>Môn <?php echo $mon; ?></li>
        <?php endforeach; ?>
    </ul> -->
</body>
</html>
