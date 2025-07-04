<?php
$sql_accound_edit ="SELECT * FROM account WHERE account_id ='$_GET[account_id]'LIMIT 1";
$query_account_edit = mysql_query($mysql, $sql_accound_edit);

?>
<h2>Danh sách tài khoản </h2>
<div class ="box">
    <table>
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Loại</th>
            <th>Thao tác</th>
        </tr>
        <?php 
        $res = $conn -> query ("SELECT * FROM account");
        if ($res && $res ->num_rows) :
            while ($row = $res -> fetch_assoc()) :
        ?>
          <tr>
            <form method="POST">
                <td>
                    <input type="hidden" name="account_id" value="<?=htmlspecialchars($row['account_id'])?>">
                    <input value="<?=htmlspecialchars($row['account_id'])?>" disabled>
                </td>

                <td><input name="account_name" value="<?=htmlspecialchars($row['account_name'])?>" required></td>
                <td><input type="email" name="account_email" value="<?=htmlspecialchars($row['account_email'])?>" required></td>
                <td><input name="account_type" value="<?=htmlspecialchars($row['account_type'])?>" required></td>
                <td>
                    <button class="btn btn-sm btn-edit" name="btn_update">✏️ Sửa</button>
                    <a class="btn btn-sm btn-del" href="index.php?action=them&delete=<?=urlencode($row['account_id'])?>" 
                    onclick="return confirm('Xoá tài khoản này?')"> 🗑️ Xoá</a>
                </td>
            </form>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="5">Chưa có tài khoản.</td></tr>
        <?php endif; ?>
        </table>
        </div>
    </table>
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
    $message = $_GET['message'];
    echo '<script>';
    echo '   showErrorToast();';
    echo 'window.history.pushState(null, "", "index.php?action=account&query=my_account");';
    echo '</script>';
}
?>