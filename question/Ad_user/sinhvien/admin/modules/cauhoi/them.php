<?php
// Kết nối CSDL nếu chưa có
// include('../../../config/config.php');

$errors = [];
if (isset($_POST['btn_add'])) {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];

    // Kiểm tra dữ liệu hợp lệ
    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($re_password)) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin!";
    }
    if ($password !== $re_password) {
        $errors[] = "Mật khẩu nhập lại không khớp!";
    }
    // Kiểm tra trùng email/username
    $sql_check = "SELECT * FROM account WHERE account_email = '" . mysqli_real_escape_string($mysqli, $email) . "' OR account_username = '" . mysqli_real_escape_string($mysqli, $username) . "'";
    $result_check = mysqli_query($mysqli, $sql_check);
    if (mysqli_num_rows($result_check) > 0) {
        $errors[] = "Email hoặc Username đã tồn tại!";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql_insert = "INSERT INTO account (account_name, account_username, account_email, account_password, account_type, account_status)
                       VALUES ('" . mysqli_real_escape_string($mysqli, $fullname) . "', '" . mysqli_real_escape_string($mysqli, $username) . "', '" . mysqli_real_escape_string($mysqli, $email) . "', '$password_hash', 0, 0)";
        if (mysqli_query($mysqli, $sql_insert)) {
            header("Location: index.php?action=account&query=account_list&message=success");
            exit;
        } else {
            $errors[] = "Lỗi khi thêm tài khoản: " . mysqli_error($mysqli);
        }
    }
}
?>
<div id="wp-content">
    <div id="content" class="container-fluid">
        <div class="card">
            <div class="card-header font-weight-bold">
                Thêm tài khoản mới 
            </div>
            <div class="card-body">
                <?php
                if (!empty($errors)) {
                    echo '<div class="alert alert-danger">';
                    foreach ($errors as $err) echo $err . '<br>';
                    echo '</div>';
                }
                ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="fullname">Tên tài khoản</label>
                        <input class="form-control" type="text" name="fullname" id="fullname" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" placeholder="Họ và Tên">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input class="form-control" type="text" name="username" id="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" placeholder="Tên tài khoản">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="form-control" type="text" name="email" id="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" placeholder="Email">
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input class="form-control" type="password" name="password" id="password" placeholder="Mật khẩu">
                    </div>
                    <div class="form-group">
                        <label for="re_password">Nhập lại mật khẩu</label>
                        <input class="form-control" type="password" name="re_password" id="re_password" placeholder="Nhập lại mật khẩu">
                    </div>
                    <button type="submit" class="btn btn-primary" name="btn_add">Thêm mới tài khoản</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function showErrorToast() {
        toast({
            title: "Success",
            message: "Cập nhật thành công",
            type: "success",
            duration: 0,
        });
    }
</script>
<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>';
    echo '   showErrorToast();';
    echo 'window.history.pushState(null, "", "index.php?action=account&query=account_list");';
    echo '</script>';
}
?>