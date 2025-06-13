<?php
session_start();

// Hiển thị lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Hàm kết nối CSDL
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối CSDL thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$student_name = $_SESSION['student_name'] ?? $_SESSION['student_id'];
$allowed_khoa_ids = $_SESSION['courses'] ?? [];

$tests = [];

if (!empty($allowed_khoa_ids)) {
    $conn = dbconnect();

    // Lấy tên khóa học từ bảng `khoa_hoc`
    $placeholders = implode(',', array_fill(0, count($allowed_khoa_ids), '?'));
    $types = str_repeat('i', count($allowed_khoa_ids));

    $stmt_khoa = $conn->prepare("SELECT id, khoa_hoc FROM khoa_hoc WHERE id IN ($placeholders)");
    $stmt_khoa->bind_param($types, ...$allowed_khoa_ids);
    $stmt_khoa->execute();
    $result_khoa = $stmt_khoa->get_result();

    $khoa_map = [];
    while ($row = $result_khoa->fetch_assoc()) {
        $khoa_map[$row['id']] = $row['khoa_hoc'];
    }
    $stmt_khoa->close();

    // Lấy bài kiểm tra liên quan đến các khóa học này
    foreach ($khoa_map as $khoa_id => $ten_khoa) {
        $stmt_test = $conn->prepare("SELECT DISTINCT id_baitest FROM quiz WHERE ten_khoa = ?");
        $stmt_test->bind_param("s", $ten_khoa);
        $stmt_test->execute();
        $result_test = $stmt_test->get_result();

        while ($row = $result_test->fetch_assoc()) {
            $tests[] = [
                'id_test' => $row['id_baitest'],
                'khoa_id' => $khoa_id,
                'ten_khoa' => $ten_khoa
            ];
        }

        $stmt_test->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách bài kiểm tra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        .test-list {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .test-item {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .test-item:last-child {
            border-bottom: none;
        }
        a.start-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        a.start-link:hover {
            text-decoration: underline;
        }
        .logout {
            text-align: right;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="test-list">
    <div class="logout">
        Xin chào, <?= htmlspecialchars($student_name) ?> | <a href="logout.php">Đăng xuất</a>
    </div>
    <h2>Danh sách bài kiểm tra</h2>

    <?php if (empty($tests)): ?>
        <p>Không có bài kiểm tra nào có sẵn cho bạn.</p>
    <?php else: ?>
        <?php foreach ($tests as $test): ?>
            <div class="test-item">
                <strong>Khóa học:</strong> <?= htmlspecialchars($test['ten_khoa']) ?><br>
                <strong>ID Bài kiểm tra:</strong> <?= htmlspecialchars($test['id_test']) ?><br>
                <a class="start-link" href="quiz.php?id_test=<?= urlencode($test['id_test']) ?>&ma_khoa=<?= urlencode($test['khoa_id']) ?>">Làm bài</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
