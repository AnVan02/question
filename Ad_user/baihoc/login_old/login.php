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
    die("Connection failed: " . $e->getMessage());
}


$success_message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM login WHERE Student_ID = :student_id AND Password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['student_id' => $student_id, 'password' => $password]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['student_id'] = $user['Id'];

        $success_message = "Đăng nhập thành công! Chào mừng, " . htmlspecialchars($user['Student_ID']) . "!";

        // Hiển thị thông báo rồi chuyển hướng
       switch($user['Id']) {
             case 1:
                $redirect_url = "content1.php";
                break;
            case 2:
                $redirect_url = "content2.php";
                break;
            case 3:
                $redirect_url = "content3.php";
                break;
          
            
        
            default:
                $error = "Không có quyền truy cập!";
        }

        if (empty($error)) {
            echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Đăng nhập thành công</title>
    <meta http-equiv='refresh' content='2;url=$redirect_url'>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success {
            color: green;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class='message-box'>
        <p class='success'>$success_message</p>
        <p>Đang chuyển hướng, vui lòng chờ...</p>
    </div>
</body>
</html>";
            exit();
        }
    } else {
        $error = "Sai mã sinh viên hoặc mật khẩu.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        body {
            font-family : Arial , sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            max-width: 400px;
            margin : 50px auto ;
        }
        form {
            display : flex ;
            flex-direction : column ;
            gap: 10px;
        }
        button {
            padding: 10px;
            background-color: #007bff;
            color : white;
            border : none;
            cursor: pointer;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
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
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="post" action="">
            <input type="text" name="student_id" placeholder="Mã sinh viên" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="submit" value="Đăng nhập">
        </form>
    </div>
</body>
</html>
