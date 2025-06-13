<?php
// Thiết lập múi giờ cho Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Bật hiển thị lỗi để hỗ trợ gỡ lỗi (nên tắt trong môi trường sản xuất)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bắt đầu phiên làm việc
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để truy cập!'); window.location.href = 'login.php';</script>";
    exit();
}

// Kiểm tra xem người dùng có quyền truy cập không (đồng bộ với take_test.php)
if ($_SESSION['student_id'] != 1) {
    echo "<script>alert('Bạn không có quyền truy cập!'); window.location.href = 'login.php';</script>";
    exit();
}

// Kiểm tra xem biến Khoahoc có được thiết lập trong session không
if (!isset($_SESSION['Khoahoc']) || empty($_SESSION['Khoahoc'])) {
    // Hiển thị thông báo nếu không có khóa học nào được gán
    echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Không có khóa học</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2); /* Nền gradient giống take_test.php */
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            text-align: center;
        }
        .logout {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Không có khóa học nào được gán cho tài khoản của bạn.</h2>
        <p>Vui lòng liên hệ với quản trị viên để được gán khóa học.</p>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
</body>
</html>';
    exit();
}

// Kết nối với cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy danh sách các khóa học được phép từ session
$allowed_courses = array_filter(explode(',', $_SESSION['Khoahoc']), function($value) {
    return is_numeric($value) && (int)$value > 0; // Lọc các giá trị không hợp lệ
});

// Kiểm tra nếu danh sách khóa học rỗng
if (empty($allowed_courses)) {
    echo "<script>alert('Danh sách khóa học không hợp lệ!'); window.location.href = 'logout.php';</script>";
    exit();
}

// Xóa dữ liệu session liên quan đến bài test để tránh xung đột với take_test.php
unset($_SESSION['test_id'], $_SESSION['questions'], $_SESSION['answers'], 
      $_SESSION['score'], $_SESSION['highest_score'], $_SESSION['attempts'], 
      $_SESSION['current_index']);

// Lấy danh sách bài test cho các khóa học được phép
$placeholders = str_repeat('?,', count($allowed_courses) - 1) . '?';
$sql = "SELECT t.id_test, t.ten_test, t.lan_thu, t.Pass, t.so_cau_hien_thi, kh.khoa_hoc 
        FROM test t 
        JOIN khoa_hoc kh ON t.id_khoa = kh.id 
        WHERE t.id_khoa IN ($placeholders)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}
$types = str_repeat('i', count($allowed_courses));
$stmt->bind_param($types, ...$allowed_courses);
$stmt->execute();
$tests_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Bài Test</title>
    <style>
        /* CSS được đồng bộ với take_test.php để đảm bảo giao diện nhất quán */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logout {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .logout:hover {
            background-color: #c82333;
        }
        .test-list {
            padding: 20px;
        }
        .test-item {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }
        .test-item:hover {
            background-color: #e9ecef;
        }
        .test-item h3 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .test-item p {
            margin: 5px 0;
            color: #666;
        }
        .start-test {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .start-test:hover {
            background-color: #218838;
        }
        .no-tests {
            text-align: center;
            color: #e74c3c;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Tiêu đề hiển thị tên người dùng -->
        <div class="header">
            <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['student_name']); ?></h2>
            <a href="logout.php" class="logout">Đăng xuất</a>
        </div>
        <!-- Danh sách bài test -->
        <div class="test-list">
            <h2>Danh sách bài test</h2>
            <?php if ($tests_result->num_rows > 0): ?>
                <?php while ($test = $tests_result->fetch_assoc()): ?>
                    <div class="test-item">
                        <h3><?php echo htmlspecialchars($test['ten_test']); ?> - <?php echo htmlspecialchars($test['khoa_hoc']); ?></h3>
                        <p>Số lần thi: <?php echo htmlspecialchars($test['lan_thu']); ?></p>
                        <p>Điểm đạt: <?php echo htmlspecialchars($test['Pass']); ?>%</p>
                        <p>Số câu hiển thị: <?php echo htmlspecialchars($test['so_cau_hien_thi']); ?></p>
                        <a href="take_test.php?test_id=<?php echo $test['id_test']; ?>" class="start-test">Bắt đầu làm bài</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-tests">Không có bài test nào cho khóa học của bạn.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
// Đóng câu lệnh và kết nối cơ sở dữ liệu
$stmt->close();
$conn->close();
?>