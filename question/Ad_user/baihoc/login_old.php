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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];
    $khoahoc = $_POST['khoahoc'];

    $sql = "SELECT * FROM login WHERE Student_ID = :student_id AND Password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['student_id' => $student_id, 'password' => $password]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['khoahoc, student_id'] = $user['Id'];
        
        // Chuyển hướng dựa trên student_id
        switch($user['Id']) {
            case 1:
                header("Location:Hoahoc.php");
                break;
            case 2:
                header("Location:Van.php");
                break;
            case 3:
                header("Location:Yolo.php");
                break;
            case 4:
                header("Location:Toan.php");
                break;
            case 5:
                header("Location:Python_nc.php");
                break;
             case 6:
                header("Location:Python_cb.php");
                break;
            case 7:
                header("Location:Tienganh.php");
                break;
            case 8:
                header("Location:sinhhoc.php");
                break;
            case 9:
                header("Location:hoahoc_old.php");
                break;
        
            default:
                $error = "Không có quyền truy cập!";
        }
        exit();
    } else {
        $error = "Id hoặc mật khẩu không đúng";
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
            flex-direction :column ;
            gap: 10px;

        }
        button {
            padding: 10px;
            background-color: #007bff;
            color : white  ;
            border : none ; 
            cursot: pointer ;
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
        <?php if(isset ($error)) :?>
            <p class ="error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="post" action="">
            <input type="text" name="student_id" placeholder="Mã sinh viên" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="submit" value="Đăng nhập">
         
        </form>
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>
    </div>

</body>
</html>