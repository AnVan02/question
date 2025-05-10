<?php
session_start();

// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_errno) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $email = trim($_POST['account_email']);
    $password = trim($_POST['account_password']);
    // $confirm_password = trim($_POST['add_account_password']);

    // Kiểm tra xác nhận mật khẩu
    if ($password !== $account_email){
        $error = "Mật khẩu xác nhận không khớp!";
    } else {

        $conn = dbconnect();
        
        // Truy vấn kiểm tra email
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                header("Location: add_khoahoc.php");
                exit();
            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Email không tồn tại!";
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login Admin</title>
</head>
<body>
    <section class="login">
        <div class="form-box">
            <div class="form-value">
                <form action="" autocomplete="on" method="POST">
                    <h2>Login</h2>
                    <?php if (isset($error)): ?>
                        <p style="color: red; text-align: center;"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <div class="inputbox">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="email" name="account_email" required>
                        <label for="">Email</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="account_password" required>
                        <label for="">Mật khẩu</label>
                    </div>

                    <div class ="inputbox">
                        <ion-icon name ="mail-closed-outline"></ion-icon>
                        <input type="password" name="add_account_password" required>
                        <label for="">Nhập tại mật khẩu</label>
                    </div>
                    
                    <div class="forget">
                        <label for=""><input type="checkbox">Remember Me <a href="#">Forget Password</a></label>
                    </div>
                    <button type="submit" name="login">Log in</button>

                    <div class="register">
                        <p>Don't have an account <a href="#">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
        }
        .login {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            width: 100%;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            background-position: center;
            background-size: cover;
        }
        .form-box {
            position: relative;
            width: 400px;
            height: 450px;
            background: transparent;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 20px;
            backdrop-filter: blur(15px);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        h2 {
            font-size: 2em;
            color: #fff;
            text-align: center;
        }
        .inputbox {
            position: relative;
            margin: 30px 0;
            width: 310px;
            border-bottom: 2px solid #fff;
        }
        .inputbox label {
            position: absolute;
            top: 50%;
            left: 5px;
            transform: translateY(-50%);
            color: #fff;
            font-size: 1em;
            pointer-events: none;
            transition: .5s;
        }
        input:focus ~ label,
        input:valid ~ label {
            top: -5px;
        }
        .inputbox input {
            width: 100%;
            height: 50px;
            background: transparent;
            border: none;
            outline: none;
            font-size: 1em;
            padding: 0 35px 0 5px;
            color: #fff;
        }
        .inputbox ion-icon {
            position: absolute;
            right: 8px;
            color: #fff;
            font-size: 1.2em;
            top: 20px;
        }
        .forget {
            margin: -15px 0 15px;
            font-size: .9em;
            color: #fff;
            display: flex;
            justify-content: space-between;
        }
        .forget label input {
            margin-right: 3px;
        }
        .forget label a {
            color: #fff;
            text-decoration: none;
        }
        button {
            width: 100%;
            height: 40px;
            border-radius: 40px;
            background: #fff;
            border: none;
            outline: none;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
        }
        .register {
            font-size: .9em;
            color: #fff;
            text-align: center;
            margin: 25px 0 10px;
        }
        .register p a {
            text-decoration: none;
            color: #fff;
            font-weight: 600;
        }
    </style>
     <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>