<?php
$sql_account_edit = "SELECT * FROM account WHERE account_id = '$_GET[account_id]' LIMIT 1";
$query_account_edit = mysqli_query($mysqli, $sql_account_edit);
?>
<div id="wp-content">
        <div id="content" class="container-fluid">
            <div class="card">
                <div class="card-header font-weight-bold">
                    Thêm tài khoản mới 
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <?php echo form_success('account'); ?>
                        <?php echo form_error('account'); ?>

                        <div class="form-group">
                            <label for="fullname">Tên tài khoản</label>
                            <input class="form-control" type="text" name="fullname" id="fullname" value="<?php echo set_value('fullname'); ?>" placeholder="Họ và Tên">
                            <?php echo form_error('fullname'); ?>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input class="form-control" type="text" name="username" id="username" value="<?php echo set_value('username'); ?>" placeholder="Tên tài khoản">
                            <?php echo form_error('username'); ?>
                        </div>

                        

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" type="text" name="email" id="email" value="<?php echo set_value('email'); ?>" placeholder="Email">
                            <?php echo form_error('email'); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mật khẩu</label>
                            <input class="form-control" type="password" name="password" id="password" placeholder="Mật khẩu">
                            <?php echo form_error('password'); ?>
                        </div>

                        <div class="form-group">
                            <label for="password">Nhập lại mật khẩu</label>
                            <input class="form-control"type="password" name="password" id="password" placeholder="Nhập lại mật khẩu">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" name="btn_add">Sửa tài khoản</button>

                        <button type="submit" class="btn btn-primary" name="btn_add">Thêm mới tài khoản</button>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
<div>
<div class="col-lg-4 gird-margin stretch - card">
    <div class="card">
        <div class ="card-body">
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
    $message = $_GET['message'];
    echo '<script>';
    echo '   showErrorToast();';
    echo 'window.history.pushState(null, "", "index.php?action=account&query=my_account");';
    echo '</script>';
}
?>