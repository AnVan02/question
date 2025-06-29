<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

$success_message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password_input = $_POST['password'];

    // Truy vấn từ bảng students
    $sql = "SELECT * FROM students WHERE Student_ID = :student_id AND Password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['student_id' => $student_id, 'password' => $password_input]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['student_id'] = $user['Student_ID'];
        $_SESSION['student_name'] = $user['Ten'];
        $_SESSION['khoahoc'] = $user['Khoahoc'];

        // Hiển thị thông báo đăng nhập thành công
        echo "<!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <title>Đăng nhập thành công</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: linear-gradient(135deg, #74ebd5, #ACB6E5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .success-message {
                    background-color: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                    text-align: center;
                    width: 400px;
                    animation: fadeIn 0.5s ease-in;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .success-text {
                    color: #28a745;
                    font-size: 24px;
                    margin-bottom: 20px;
                    padding: 15px;
                    background-color: #d4edda;
                    border-radius: 5px;
                    border: 1px solid #c3e6cb;
                }
                .loading {
                    color: #666;
                    font-size: 16px;
                }
            </style>
        </head>
        <body>
            <div class='success-message'>
                <div class='success-text'>
                    Đăng nhập thành công!  
                </div>
                <div class='loading'>
                    Đang chuyển hướng...
                </div>
            </div>
        </body>
        </html>";
        exit();
    } else {
        $error = "Mã sinh viên hoặc mật khẩu không đúng!";
    }
}


// Hàm chuyển đổi tên khóa học thành tên file
function getCourseFileName($course_name) {
    $course_files = [
        'Python cơ bản' => 'Python_cb.php',
        'Python nâng cao' => 'Python_nc.php',
        'YOLO' => 'Yolo.php',
        'Toán' => 'Toan.php',
        'Văn' => 'Van.php',
        'Tiếng anh' => 'Tienganh.php',
        'Hoá học' => 'Hoahoc.php'
    ];
    
    return $course_files[$course_name];
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập hệ thống</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="student_id">Mã sinh viên:</label>
                <input type="text" id="student_id" name="student_id" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <input type="submit" value="Đăng nhập">
        </form>
    </div>
</body>
</html>
