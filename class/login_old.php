<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Lỗi kết nối database: " . $conn->connect_error);
}

$error = ""; // Biến để hiển thị lỗi (nếu có)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sử dụng toán tử ?? để tránh lỗi undefined key
    $student_id = $_POST['student_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Kiểm tra không được để trống
    if ($student_id === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ mã sinh viên và mật khẩu.";
    } else {
        // Truy vấn kiểm tra thông tin đăng nhập
        $stmt = $conn->prepare("SELECT Student_ID, Ten, Khoahoc FROM students WHERE Student_ID = ? AND Password = ?");
        $stmt->bind_param("ss", $student_id, $password);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // $_SESSION['student_id'] = $user['Student_ID'];
                $_SESSION['student_name'] = $user['Ten'];
                $_SESSION['allowed_courses'] = explode(',', $user['Khoahoc']);

                header("Location: hoc.php");
                exit();
            } else {
                $error = "Mã sinh viên hoặc mật khẩu không đúng!";
            }
        } else {
            $error = "Lỗi truy vấn database!";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!-- Giao diện form đăng nhập -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập sinh viên</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .btn-login {
            padding: 6px 12px;
        }
    </style>
</head>
<body>
    <h2>Đăng nhập sinh viên</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="student_id">Mã sinh viên:</label>
            <input type="text" id="student_id" name="student_id" required>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn-login">Đăng nhập</button>
    </form>
</body>
</html>
