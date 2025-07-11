<?php
    session_start();
    include('config/config.php');
    if (isset($_POST['login'])) {
        $account_email = $_POST['account_email'];
        $account_password = $_POST['account_password'];
        $account_email = mysqli_real_escape_string($mysqli, $account_email);
        $account_password = mysqli_real_escape_string($mysqli, $account_password);
        $sql_account = "SELECT * FROM account WHERE account_email='".$account_email."' AND account_password='".$account_password."' AND (account_type=1 OR account_type=2) ";
        $query_account = mysqli_query($mysqli, $sql_account);
        $row = mysqli_fetch_array($query_account);
        $count = mysqli_num_rows($query_account);
        if ($count>0) {
            $_SESSION['login'] = $row['account_email'];
            $_SESSION['account_id'] = $row['account_id'];
            $_SESSION['account_name'] = $row['account_name'];
            $_SESSION['account_type'] = $row['account_type'];
            header('Location:index.php');
        } else {
            echo '<script>alert("Tài khoản hoặc mật khẩu không chính xác, vui lòng nhập lại");</script>';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  
  <title>Login Admin</title>
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic">
                    <img src="team.jpg" alt="rosa">
                </div>
                <form action="" method="POST" class="login100-form validate-form">
                    <span class="login100-form-title">
                        <b> HỆ THỐNG ADMIN</b>
                    </span>

                    <!-- Email Input -->
                    <div class="wrap-input100 validate-input">
                        <input type="email" name="account_email" class="input100" placeholder="Tài khoản quản trị" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class='bx bx-user'></i>
                        </span>
                    </div>

                    <!-- Password Input -->
                    <div class="wrap-input100 validate-input">
                        <input type="password" name="account_password" class="input100" placeholder="Mật khẩu" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class='bx bx-key'></i>
                        </span>
                    </div>

                    <!-- Login Button -->
                    <div class="container-login100-form-btn">
                        <button type="submit" name="login" class="login100-form-btn">
                            Đăng nhập
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
<style>
    /* General Styles */
    body {
    font-family: 'Roboto', sans-serif; /* Sử dụng font Roboto */
    background: #001C40;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}


/* Title của form */
.login100-form-title {
    font-family: 'Arial', sans-serif; /* Font cho tiêu đề */
    font-size: 22px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
        /* Căn giữa theo cả chiều ngang và chiều dọc */
    display: flex;
    justify-content: center;  /* Căn giữa theo chiều ngang */
    align-items: center;      /* Căn giữa theo chiều dọc */
    height: 60px;             /* Đặt chiều cao cho phần chứa tiêu đề */
}
* {
    margin: 10px;
    padding: 0;
    font-family: ' "Arial", sans-serif;';
}

/* Login Container */
.limiter {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container-login100 {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.wrap-login100 {
    width: 400px;
    background-color: #fff;
    border-radius: 10px;
    padding: 100px;
    box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
}

.login100-pic {
    display: flex;
    justify-content: center; /* Căn giữa theo chiều ngang */
    align-items: center;     /* Căn giữa theo chiều dọc */
    margin-bottom: 30px;
}

.login100-pic img {
    width: 100%;
    max-width: 300px; /* Giới hạn kích thước ảnh nếu cần */
    border-radius: 10px;
    object-fit: cover; /* Đảm bảo ảnh không bị méo */
}


/* Form Title */
.login100-form-title {
    font-size: 22px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
    
    
}

/* Input Fields */
.wrap-input100 {
    position: relative;
    margin-bottom: 20px;
}

.input100 {
    width: 100%;
    padding: 15px;
    background: #f5f5f5;
    border-radius: 5px;
    border: none;
    font-size: 16px;
    color: #333;
}

.input100:focus {
    outline: none;
    background-color: #e6e6e6;
}

.symbol-input100 {
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    color: #333;
}



.login100-form-btn {
    width: 100%;
    padding: 15px;
    background-color:rgb(231, 228, 36);
    color: #000;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-family: 'Roboto', sans-serif; /* Sử dụng font Roboto */
}





</style>