<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Check if Khoahoc is set in session
if (!isset($_SESSION['Khoahoc']) || empty($_SESSION['Khoahoc'])) {
    echo "<div style='text-align: center; margin-top: 50px;'>";
    echo "<h2>Không có khóa học nào được gán cho tài khoản của bạn.</h2>";
    echo "<p>Vui lòng liên hệ với quản trị viên để được gán khóa học.</p>";
    echo "<a href='logout.php' style='display: inline-block; padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px;'>Đăng xuất</a>";
    echo "</div>";
    exit();
}



// Connect to database
$conn = new mysqli("localhost", "root", "", "student");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get allowed courses from session
$allowed_courses = explode(',', $_SESSION['Khoahoc']);

// First, get available tests for the student's courses
$placeholders = str_repeat('?,', count($allowed_courses) - 1) . '?';
$sql = "SELECT t.*, kh.khoa_hoc 
        FROM test t 
        JOIN khoa_hoc kh ON t.id_khoa = kh.id 
        WHERE t.id_khoa IN ($placeholders)";
$stmt = $conn->prepare($sql);
$types = str_repeat('i', count($allowed_courses));
$stmt->bind_param($types, ...$allowed_courses);
$stmt->execute();
$tests_result = $stmt->get_result();

// Display test selection form
echo '<!DOCTYPE html>
<html>
<head>
    <title>Chọn Bài Test</title>
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
        .test-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .test-item {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .test-item:hover {
            background-color: #f8f9fa;
        }
        .test-item h3 {
            margin: 0 0 10px 0;
        }
        .test-item p {
            margin: 5px 0;
            color: #666;
        }
        .start-test {
            display: inline-block;
            padding: 8px 16px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .start-test:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Xin chào, ' . htmlspecialchars($_SESSION['student_name']) . '</h2>
    </div>
    <div class="test-list">
        <h2>Danh sách bài test</h2>';

if ($tests_result->num_rows > 0) {
    while($test = $tests_result->fetch_assoc()) {
        echo '<div class="test-item">';
        echo '<h3>' . htmlspecialchars($test['ten_test']) . ' - ' . htmlspecialchars($test['khoa_hoc']) . '</h3>';
        echo '<p>Số lần thi: ' . htmlspecialchars($test['lan_thu']) . '</p>';
        echo '<p>Điểm đạt: ' . htmlspecialchars($test['Pass']) . '%</p>';
        echo '<p>Số câu hiển thị: ' . htmlspecialchars($test['so_cau_hien_thi']) . '</p>';
        echo '<a href="take_test.php?test_id=' . $test['id_test'] . '" class="start-test">Bắt đầu làm bài</a>';
        echo '</div>';
    }
} else {
    echo '<p>Không có bài test nào cho khóa học của bạn.</p>';
}


echo '</div></body></html>';

$stmt->close();
$conn->close();
?>