<?php
session_start();
error_log("Debug: Login.php started");

// DB config
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

// Connect DB
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    if (empty($student_id) || empty($password_input)) {
        $error = "Vui lòng nhập mã sinh viên và mật khẩu.";
    } else {
        $sql = "SELECT Student_ID, Password, Khoahoc FROM students WHERE Student_ID = :student_id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['student_id' => $student_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Dùng true nếu mật khẩu được mã hóa bằng password_hash()
            $use_hashed_passwords = false; // <<< Sửa tại đây tùy theo database

            $is_password_correct = $use_hashed_passwords
                ? password_verify($password_input, $user['Password'])
                : ($password_input === $user['Password']);

            if ($is_password_correct) {
                $_SESSION['user_id'] = $user['Student_ID'];
                $_SESSION['students']['Khoahoc'] = $user['Khoahoc'] ?? '';

                if (!empty($user['Khoahoc'])) {
                    $khoa_id = explode(',', $user['Khoahoc'])[0];

                    // Get course name
                    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = :khoa_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['khoa_id' => $khoa_id]);
                    $course = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['ten_khoa'] = $course['khoa_hoc'] ?? 'Default Course';

                    // Get first test
                    $sql = "SELECT ten_test FROM test WHERE id_khoa = :khoa_id LIMIT 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['khoa_id' => $khoa_id]);
                    $test = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['id_baitest'] = $test['ten_test'] ?? 'Default Test';
                } else {
                    $_SESSION['ten_khoa'] = 'Default Course';
                    $_SESSION['id_baitest'] = 'Default Test';
                }

                header("Location: FAQ.php");
                exit();
            } else {
                $error = "Mã sinh viên hoặc mật khẩu không đúng!";
            }
        } else {
            $error = "Mã sinh viên hoặc mật khẩu không đúng!";
        }
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input[type="text"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>

</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="student_id" placeholder="Mã sinh viên" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="submit" value="Đăng nhập">
            <a href="dangky.php">Đăng ký</a>
        </form>
    </div>
</body>
</html>
