<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student');

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    die("<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>");
}

// Kiểm tra khóa học được gán
if (!isset($_SESSION['Khoahoc']) || empty($_SESSION['Khoahoc'])) {
    showNoCourseTemplate();
    exit();
}

// Lấy danh sách khóa học được phép
$allowed_courses = array_filter(explode(',', $_SESSION['Khoahoc']), function($value) {
    return is_numeric($value) && (int)$value > 0;
});

if (empty($allowed_courses)) {
    die("<script>alert('Danh sách khóa học không hợp lệ!'); window.location.href='logout.php';</script>");
}

// Kết nối database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Kiểm tra nếu có yêu cầu truy cập bài test cụ thể
if (isset($_GET['test_id'])) {
    $id_test = (int)$_GET['test_id'];
    
    // Kiểm tra bài test có thuộc khóa học được phép không
    $stmt_check = $conn->prepare("SELECT t.id_test FROM test t 
                                JOIN khoa_hoc kh ON t.id_khoa = kh.id 
                                WHERE t.id_test = ? AND t.id_khoa IN (".implode(',', array_fill(0, count($allowed_courses), '?')).")");
    
    $params = array_merge([$id_test], $allowed_courses);
    $types = str_repeat('i', count($params));
    $stmt_check->bind_param($types, ...$params);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if ($check_result->num_rows === 0) {
        die("<script>alert('Bạn không có quyền truy cập bài test này!'); window.location.href='select_test.php';</script>");
    }
    $stmt_check->close();
}

// Xác định khóa học hiện tại
$current_course = isset($_GET['course_id']) && in_array($_GET['course_id'], $allowed_courses) 
                ? $_GET['course_id'] 
                : $allowed_courses[0];

// Lấy thông tin khóa học hiện tại
$stmt_khoa = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt_khoa->bind_param('i', $current_course);
$stmt_khoa->execute();
$khoa_result = $stmt_khoa->get_result();
$current_theme = [
    'name' => 'Khóa học ' . $current_course,
    'color' => '#607D8B',
    'class' => 'theme-default'
];

if ($khoa_result->num_rows > 0) {
    $khoa_data = $khoa_result->fetch_assoc();
    $current_theme['name'] = $khoa_data['khoa_hoc'];
}
$stmt_khoa->close();

// Lấy danh sách bài test cho khóa học được phép
$stmt = $conn->prepare("SELECT t.id_test, t.ten_test, t.lan_thu, t.Pass, t.so_cau_hien_thi 
                       FROM test t WHERE t.id_khoa = ?");
$stmt->bind_param('i', $current_course);
$stmt->execute();
$tests_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Phần head giữ nguyên -->
</head>
<body>
    <div class="container">
        <!-- Phần giao diện giữ nguyên -->
    </div>
</body>
</html>

<?php
// Đóng kết nối
$stmt->close();
$conn->close();

function showNoCourseTemplate() {
    // Giữ nguyên như trước
}
?>