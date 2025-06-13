<?php
session_start();

// Debug: Bật hiển thị lỗi để dễ dàng gỡ lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (!isset($_SESSION['student_id']) || !isset($_SESSION['Khoahoc'])) {
    header("Location: login.php");
    exit();
}

$allowed_courses_raw = explode(',', $_SESSION['Khoahoc'] ?? '');
$allowed_courses = [];
foreach ($allowed_courses_raw as $course_id) {
    $trimmed_id = trim($course_id);
    if (is_numeric($trimmed_id) && (int)$trimmed_id > 0) { // Đảm bảo là số nguyên dương
        $allowed_courses[] = (int)$trimmed_id;
    }
}

$no_courses_message = "";
$test = null; // Khởi tạo biến $test

if (empty($allowed_courses)) {
    $no_courses_message = "Bạn chưa đăng ký khóa học nào hoặc các khóa học không hợp lệ.";
} else {
    $placeholders = implode(',', array_fill(0, count($allowed_courses), '?'));
    $sql = "SELECT DISTINCT t.id_test, t.ten_test, t.id_khoa, k.khoa_hoc, t.lan_thu, t.Pass, t.so_cau_hien_thi 
            FROM test t 
            JOIN khoa_hoc k ON t.id_khoa = k.id 
            WHERE t.id_khoa IN ($placeholders) LIMIT 1"; // Thêm LIMIT 1 để chỉ lấy một bài test
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Lỗi chuẩn bị truy vấn: " . $conn->error);
    }
    
    $types = str_repeat('i', count($allowed_courses));
    $stmt->bind_param($types, ...$allowed_courses);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $test = $result->fetch_assoc(); // Lấy bài test đầu tiên
    } else {
        $no_courses_message = "Không có bài test nào phù hợp với các khóa học của bạn.";
    }
    $stmt->close();
}

// Đóng kết nối CSDL
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Bài Test của bạn</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .test-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .test-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .test-info {
            margin-bottom: 5px;
            color: #555;
        }
        .start-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1em;
            margin-top: 10px;
        }
        .start-button:hover {
            background-color: #218838;
        }
        .logout {
            padding: 8px 16px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Người dùng'); ?></h2>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>

    <h2>Bài test của bạn</h2>
    <?php
    if (!empty($no_courses_message)) {
        echo "<p>$no_courses_message</p>";
    } elseif ($test) {
        echo "<div class='test-container'>";
        echo "<div class='test-title'>" . htmlspecialchars($test['ten_test']) . " - " . htmlspecialchars($test['khoa_hoc']) . "</div>";
        echo "<div class='test-info'>Số lần thi: " . htmlspecialchars($test['lan_thu']) . "</div>";
        echo "<div class='test-info'>Điểm đạt: " . htmlspecialchars($test['Pass']) . "%</div>";
        echo "<div class='test-info'>Số câu hiển thị: " . htmlspecialchars($test['so_cau_hien_thi']) . "</div>";
        echo "<a href='take_test.php?test_id={$test['id_test']}' class='start-button'>Bắt đầu làm bài</a>";
        echo "</div>";
    }
    ?>
</body>
</html>