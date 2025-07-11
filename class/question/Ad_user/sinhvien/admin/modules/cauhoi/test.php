<?php
// admin.php - Quản lý sinh viên (Admin)
session_start();

// Kiểm tra quyền admin (bạn có thể sửa lại điều kiện này)
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Xử lý xóa sinh viên
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $student_id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM students WHERE Student_ID = '$student_id'");
    header("Location: admin.php");
    exit;
}

// Xử lý thêm sinh viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $password = $conn->real_escape_string($_POST['password']);
    $ten = $conn->real_escape_string($_POST['ten']);
    $email = $conn->real_escape_string($_POST['email']);
    $sql = "INSERT INTO students (Student_ID, Password, Ten, Email) VALUES ('$student_id', '$password', '$ten', '$email')";
    $conn->query($sql);
    header("Location: admin.php");
    exit;
}

// Lấy danh sách sinh viên
$result = $conn->query("SELECT * FROM students ORDER BY Student_ID");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Quản lý sinh viên</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px #0001; }
        h1 { text-align: center; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #3498db; color: #fff; }
        tr:nth-child(even) { background: #f2f2f2; }
        .form-add { margin: 30px 0; }
        .form-add input { padding: 8px; margin: 0 8px 0 0; }
        .btn { padding: 6px 16px; border: none; border-radius: 4px; background: #3498db; color: #fff; cursor: pointer; }
        .btn-danger { background: #e74c3c; }
        .btn:hover { opacity: 0.8; }
    </style>
</head>
<body>
<div class="container">
    <h1>Quản lý sinh viên (Admin)</h1>
    <form class="form-add" method="post">
        <input type="text" name="student_id" placeholder="Student ID" required>
        <input type="text" name="ten" placeholder="Tên sinh viên" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button class="btn" type="submit" name="add_student">Thêm sinh viên</button>
    </form>
    <table>
        <tr>
            <th>Student_ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Mật khẩu</th>
            <th>Hành động</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['Student_ID']) ?></td>
            <td><?= htmlspecialchars($row['Ten']) ?></td>
            <td><?= htmlspecialchars($row['Email']) ?></td>
            <td><?= htmlspecialchars($row['Password']) ?></td>
            <td>
                <a class="btn btn-danger" href="admin.php?delete=<?= urlencode($row['Student_ID']) ?>" onclick="return confirm('Xóa sinh viên này?')">Xóa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>