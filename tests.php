<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy danh sách bài kiểm tra của sinh viên
$conn = dbconnect();
$student_id = $_SESSION['student_id'];
$enrolled_courses = $_SESSION['courses'] ?? [];
$tests = [];
$message = "";

// Kiểm tra quyền truy cập khóa học
if (isset($_GET['khoa_id']) && !in_array($_GET['khoa_id'], $enrolled_courses)) {
    $message = "<div style='color:red;'>Bạn không có quyền vào khóa học này!</div>";
} else {
    // Lấy danh sách bài kiểm tra cho các khóa học sinh viên đã đăng ký
    $course_ids = implode(',', array_map('intval', $enrolled_courses));
    if (!empty($course_ids)) {
        $sql = "SELECT kt.Student_ID, kt.Khoa_ID, kt.Test_ID, kt.Best_Scone, kt.Max_Scone, kt.Pass, kt.Tral, kt.Max_tral, kh.khoa_hoc
                FROM kiem_tra kt
                JOIN khoa_hoc kh ON kt.Khoa_ID = kh.id
                WHERE kt.Student_ID = ? AND kt.Khoa_ID IN ($course_ids)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tests[] = $row;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách bài kiểm tra</title>
</head>
<body>
    <div class="container">
        <h2>Danh sách bài kiểm tra</h2>
        <p>Xin chào, <?= htmlspecialchars($_SESSION['student_name']) ?> | <a href="logout.php">Đăng xuất</a></p>
        <?php if (!empty($message)) echo $message; ?>

        <?php if (empty($tests)): ?>
            <p>Chưa có bài kiểm tra nào trong các khóa học bạn đã đăng ký.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tên khóa học</th>
                        <th>ID Bài kiểm tra</th>
                        <th>Điểm cao nhất</th>
                        <th>Điểm tối đa</th>
                        <th>Điểm đạt</th>
                        <th>Lượt làm</th>
                        <th>Lượt tối đa</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                        <tr>
                            <td><?= htmlspecialchars($test['khoa_hoc']) ?></td>
                            <td><?= htmlspecialchars($test['Test_ID']) ?></td>
                            <td><?= htmlspecialchars($test['Best_Scone']) ?></td>
                            <td><?= htmlspecialchars($test['Max_Scone']) ?></td>
                            <td><?= htmlspecialchars($test['Pass']) ?></td>
                            <td><?= htmlspecialchars($test['Tral']) ?></td>
                            <td><?= htmlspecialchars($test['Max_tral']) ?></td>
                            <td>
                                <a href="quiz.php?test_id=<?= urlencode($test['Test_ID']) ?>&khoa_id=<?= $test['Khoa_ID'] ?>" class="btn-take">Làm bài</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #f4f4f4;
    margin: 0;
}
h2 {
    text-align: center;
    color: rgb(247, 18, 18);
    margin-bottom: 25px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
}
.container {
    max-width: 1500px;
    margin: auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #009688;
    color: white;
    font-weight: 600;
}
tr:hover {
    background-color: #f5f5f5;
}
.btn-take {
    display: inline-block;
    padding: 8px 12px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
}
.btn-take:hover {
    background-color: #218838;
}
</style>