<?php
$sql_account_edit = "SELECT * FROM account WHERE account_id = '" . $_SESSION['account_id'] . "' LIMIT 1";
$query_account_edit = mysqli_query($mysqli, $sql_account_edit);
$row = mysqli_fetch_array($query_account_edit);
?>

<div class="row" style="margin-bottom: 10px;">
    <div class="col d-flex" style="justify-content: space-between; align-items: flex-end;">
        <h3>
            Thông tin tài khoản
        </h3>
        <a href="index.php?action=account&query=account_list" class="btn btn-outline-dark btn-fw">
            <i class="mdi mdi-reply"></i>
            Quay lại
        </a>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="card-content">
                    <?php if ($row): ?>
                        <!-- <form method="POST" action="modules/account/xuly.php?account_id=<?php echo $_SESSION['account_id_admin'] ?>"> -->
                            <div class="form-group">
                                <label for="account_name">Tên người dùng</label>
                                <input type="text" name="account_name" class="form-control" value="<?php echo htmlspecialchars($row['account_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="account_email">Email đăng nhập</label>
                                <input type="text" name="account_email" id="account_email" class="form-control" value="<?php echo htmlspecialchars($row['account_email']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="account_password">Mật khẩu</label>
                                <input type="password" name="account_password" id="account_password" class="form-control" value="<?php echo htmlspecialchars($row['account_password']); ?>" readonly>
                            </div>
                            <button type="submit" name="account_edit" class="btn btn-primary btn-icon-text" style="margin-right: 10px">
                                <i class="ti-file btn-icon-prepend"></i>
                                Lưu
                            </button>
                            <a href="index.php?action=account&query=password_change" class="btn btn-primary btn-icon-text">
                                <i class="ti-file btn-icon-prepend"></i>
                                Đổi mật khẩu
                            </a>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">Không tìm thấy thông tin tài khoản!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function showSuccessToast() {
        toast({
            title: "Success",
            message: "Cập nhật thành công",
            type: "success",
            duration: 3000,
        });
    }
</script>

<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo '<script>showSuccessToast(); window.history.pushState(null, "", "index.php?action=account&query=my_account");</script>';
}
?>