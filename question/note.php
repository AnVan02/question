<?php
ob_start(); // Bật output buffering
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tăng thời gian sống của phiên
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
session_start();

// Định nghĩa link quay lại
$link_quay_lai = "add_khoahoc.php";

// Kiểm tra phiên
if (!isset($_SESSION['student_id'])) {
    header("Location: $link_quay_lai");
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ma_khoa = '10';
$id_test = '12';
$student_id = $_SESSION['student_id'];
$link_tiep_tuc = "baihoc.php";

// Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc'];
    $khoahoc_list = array_map('intval', explode(',', $khoahoc));
    if (!in_array(intval($ma_khoa), $khoahoc_list)) {
        echo "<script>
            alert('Bạn không có quyền truy cập khóa học này!');
            window.location.href = '$link_quay_lai';
        </script>";
        exit();
    }
} else {
    echo "<script>
        alert('Không tìm thấy thông tin sinh viên!');
        window.location.href = '$link_quay_lai';
    </script>";
    exit();
}
$stmt->close();

// (Phần còn lại của mã PHP giữ nguyên)
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <!-- (Giữ nguyên phần CSS của bạn) -->
</head>
<body>
    <!-- (Giữ nguyên phần HTML của bạn) -->

    <script>
        // Đẩy trạng thái ban đầu vào lịch sử trình duyệt
        history.pushState({ page: 'quiz' }, null, location.href);

        // Xử lý sự kiện popstate khi nhấn nút Back
        window.onpopstate = function(event) {
            console.log("Popstate triggered:", event.state); // Debug
            window.location.href = "<?php echo htmlspecialchars($link_quay_lai); ?>";
        };
    </script>
</body>
</html>

<?php
ob_end_flush();
?>