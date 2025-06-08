<?php
    session_start();
    include('config/config.php');
    if (isset($_POST['login'])) {
        $student_id = $_POST['student_id'];
        $password = $_POST['password'];
        $student_id = mysqli_real_escape_string($mysqli, $studet_id);
        $password = mysqli_real_escape_string($mysqli, $password);
        $sql_login = "SELECT * FROM login WHERE student='".$student_id."' AND password='".$password ."' AND (account_type=1 OR account_type=2) ";
        $query_login= mysqli_query($mysqli, $sql_account);
        $row = mysqli_fetch_array($query_login);
        $count = mysqli_num_rows($query_login);
        if ($count>0) {
            $_SESSION['login'] = $row['account_email'];
            $_SESSION['student_id_admin'] = $row['student_id'];
            $_SESSION['password'] = $row['password'];
            header('Location:index.php');
        } else {
            echo '<script>alert("Tài khoản hoặc mật khẩu không chính xác, vui lòng nhập lại");</script>';
        }
    }
?>