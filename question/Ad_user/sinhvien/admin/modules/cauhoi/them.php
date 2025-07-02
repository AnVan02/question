<?php
// Kết nối CSDL nếu chưa có
// include('../../../config/config.php');

$errors = [];
if (isset($_POST['btn_add'])) {
    $account_id= trim($_POST['account_id']);
    $account_name = trim($_POST['account_name']);
    $account_email = trim($_POST['account_email']);
    $account_password = $_POST['account_password'];
    $account_type = trim($_POST ['account_type']);

    // Kiểm tra dữ liệu hợp lệ
    if (empty($account_id) || empty( $account_name ) || empty( $account_email ) || empty( $account_password) || empty($account_type)) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin!";
    }
    if ($account_password !== $re_password) {
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
                        <label for="$account_id">ID</label>
                        <input class="form-control" type="text" name="$account_id" id="$account_id" value="<?php echo isset($_POST['$account_id']) ? htmlspecialchars($_POST['$account_id']) : '' ?>" placeholder="Nhập ID">
                    </div>
                    <div class="form-group">
                        <label for="$account_name">Username</label>
                        <input class="form-control" type="text" name="$account_name" id="$account_name" value="<?php echo isset($_POST['$account_name']) ? htmlspecialchars($_POST['$account_name']) : '' ?>" placeholder="tên tài khoản">
                    </div>
                    <div class="form-group">
                        <label for="$account_email">Email</label>
                        <input class="form-control" type="text" name="$account_email" id="$account_email" value="<?php echo isset($_POST['$account_email']) ? htmlspecialchars($_POST['$account_email']) : '' ?>" placeholder="email tài khoản">
                    </div>
                    <div class="form-group">
                        <label for="account_password">Mật khẩu</label>
                        <input class="form-control" type="account_password" name="account_password" id="account_password" placeholder="Mật khẩu">
                    </div>
                    <div class="form-group">
                        <label for="account_type">Tài khoản </label>
                        <input class="form-control" type="account_type" name="account_type" id="account_type" placeholder="admin">
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