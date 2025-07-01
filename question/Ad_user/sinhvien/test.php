<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Xử lý thêm sinh viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $imei = intval($_POST['imei']);
    $mb_id = intval($_POST['mb_id']);
    $os_id = intval($_POST['os_id']);
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $password = $conn->real_escape_string($_POST['password']);
    $ten = $conn->real_escape_string($_POST['ten']);
    $email = $conn->real_escape_string($_POST['email']);
    $sql = "INSERT INTO students (IMEI, MB_ID, OS_ID, Student_ID, Password, Ten, Email) VALUES ($imei, $mb_id, $os_id, '$student_id', '$password', '$ten', '$email')";
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
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; }
        .sidebar {
            width: 240px; background: #f4fafd; height: 100vh; position: fixed; left: 0; top: 0; padding-top: 30px;
            border-right: 1px solid #e0e0e0;
        }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { margin: 18px 0; }
        .sidebar a {
            display: flex; align-items: center; text-decoration: none; color: #333; padding: 10px 30px;
            border-radius: 8px; transition: background 0.2s;
        }
        .sidebar a.active, .sidebar a:hover { background: #e3f0fa; color: #1976d2; font-weight: bold; }
        .sidebar i { margin-right: 12px; }
        .main { margin-left: 260px; padding: 0 40px; }
        .header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 24px 0 10px 0; border-bottom: 1px solid #e0e0e0; background: #f7fafd;
        }
        .header img { height: 32px; }
        .header .profile { display: flex; align-items: center; }
        .header .profile img { height: 32px; border-radius: 50%; margin-left: 10px; }
        .form-box {
            background: #fff; border-radius: 16px; box-shadow: 0 4px 16px #0001; padding: 36px 32px; max-width: 700px; margin: 40px auto 30px auto;
        }
        .form-row { display: flex; gap: 24px; margin-bottom: 18px; }
        .form-col { flex: 1; }
        .form-col label { display: block; margin-bottom: 6px; font-weight: 600; }
        .form-col input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        .btn { background: #2196f3; color: #fff; border: none; padding: 12px 32px; border-radius: 8px; font-size: 16px; cursor: pointer; margin: 0 auto; display: block; }
        .btn:hover { background: #1976d2; }
        h2 { text-align: center; margin-top: 0; color: #2c3e50; }
        .table-box { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; padding: 24px; margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e0e0e0; text-align: center; }
        th { background: #e3f0fa; color: #1976d2; }
        tr:last-child td { border-bottom: none; }
        @media (max-width: 900px) {
            .main { margin-left: 0; padding: 10px; }
            .sidebar { position: static; width: 100%; height: auto; border-right: none; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <ul>
            <li><a href="add_khoahoc.php"><i>📚</i> Thêm khoá học</a></li>
            <li><a href="#" class="active"><i>👨‍🎓</i> Thêm sinh viên</a></li>
            <li><a href="#"><i>🏠</i> Tra cứu sinh viên</a></li>
            <li><a href="#"><i>❓</i> Thêm câu hỏi</a></li>
            <li><a href="#"><i>📝</i> Thêm bài test</a></li>
            <li><a href="#"><i>➕</i> Thêm tài khoản</a></li>
            <li><a href="#"><i>👤</i> Quản Lý Tài khoản</a></li>
            <li><a href="#"><i>⚙️</i> Cài đặt</a></li>
        </ul>
    </div>
    <div class="main">
        <div class="header">
            <div>
                <img src="logo.png" alt="logo" style="height:32px;vertical-align:middle;">
                <span style="font-size: 1.5em; margin-left: 18px;">Xin chào, <b>Admin</b></span>
            </div>
            <div class="profile">
                <span style="color: #e74c3c; font-size: 1.2em; margin-right: 8px;">●</span>
                <img src="profile.png" alt="Profile image">
            </div>
        </div>
        <h2>Nhập Dữ Liệu Sinh Viên</h2>
        <form class="form-box" method="post">
            <div class="form-row">
                <div class="form-col">
                    <label>IMEI</label>
                    <input type="number" name="imei" required>
                </div>
                <div class="form-col">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label>MB_ID</label>
                    <input type="number" name="mb_id" required>
                </div>
                <div class="form-col">
                    <label>Tên sinh viên</label>
                    <input type="text" name="ten" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label>OS_ID</label>
                    <input type="number" name="os_id" required>
                </div>
                <div class="form-col">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label>Student_ID</label>
                    <input type="text" name="student_id" required>
                </div>
                <div class="form-col"></div>
            </div>
            <button class="btn" type="submit" name="add_student">Thêm Sinh Viên</button>
        </form>
        <div class="table-box">
            <h2>Danh Sách Sinh Viên</h2>
            <table>
                <tr>
                    <th>IMEI</th>
                    <th>MB_ID</th>
                    <th>OS_ID</th>
                    <th>Student_ID</th>
                    <th>Mật khẩu</th>
                    <th>Tên sinh viên</th>
                    <th>Email</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['IMEI']) ?></td>
                    <td><?= htmlspecialchars($row['MB_ID']) ?></td>
                    <td><?= htmlspecialchars($row['OS_ID']) ?></td>
                    <td><?= htmlspecialchars($row['Student_ID']) ?></td>
                    <td><?= htmlspecialchars($row['Password']) ?></td>
                    <td><?= htmlspecialchars($row['Ten']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>