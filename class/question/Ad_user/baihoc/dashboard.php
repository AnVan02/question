<?php
session_start();
require_once("dbconnect.php");

if (!isset($_SESSION['student_id']) || !isset($_SESSION['Khoahoc'])) {
    header("Location: login.php");
    exit();
}

$allowed_courses = explode(',', $_SESSION['Khoahoc'] ?? '');
if (empty($allowed_courses) || $allowed_courses[0] === '') {
    $no_courses = "Bạn chưa đăng ký khóa học nào.";
}

$conn = dbconnect();
$placeholders = str_repeat('?,', count($allowed_courses) - 1) . '?';
$sql = "SELECT DISTINCT t.id_test, t.ten_test, t.id_khoa, k.khoa_hoc 
        FROM test t 
        JOIN khoa_hoc k ON t.id_khoa = k.id 
        WHERE t.id_khoa IN ($placeholders)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}
$stmt->bind_param(str_repeat('i', count($allowed_courses)), ...$allowed_courses);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Danh sách bài test</title>
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
        .test-container a {
            color: #007bff;
            text-decoration: none;
        }
        .test-container a:hover {
            text-decoration: underline;
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

    <h2>Danh sách bài test</h2>
    <?php
    if (isset($no_courses)) {
        echo "<p>$no_courses</p>";
    } elseif ($result->num_rows > 0) {
        $displayed_tests = []; // Mảng để theo dõi các id_test đã hiển thị
        while ($row = $result->fetch_assoc()) {
            if (!in_array($row['id_test'], $displayed_tests)) {
                echo "<div class='test-container'>";
                echo "<a href='quiz.php?id_test={$row['id_test']}&ma_khoa={$row['id_khoa']}'>{$row['ten_test']} ({$row['khoa_hoc']})</a>";
                echo "</div>";
                $displayed_tests[] = $row['id_test'];
            }
        }
    } else {
        echo "<p>Không có bài test nào cho các khóa học của bạn.</p>";
    }
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>