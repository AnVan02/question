<?php
/* ---------- KẾT NỐI CSDL ---------- */
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
$conn = dbconnect();

$errors  = [];
$success = "";

/* ---------- XOÁ ---------- */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM account WHERE account_id=?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute() && $stmt->affected_rows) {
        $success = "🗑️ Đã xoá tài khoản $id.";
    } else {
        $errors[] = "Không xoá được (có thể ID không tồn tại).";
    }
    $stmt->close();
}

/* ---------- CẬP NHẬT ---------- */
if (isset($_POST['btn_update'])) {
    $id    = $_POST['account_id'];          // hidden input
    $name  = trim($_POST['account_name']);
    $email = trim($_POST['account_email']);
    $type  = trim($_POST['account_type']);

    if (!$name || !$email || !$type) {
        $errors[] = "Vui lòng nhập đủ thông tin khi sửa.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE account SET account_name=?, account_email=?, account_type=? WHERE account_id=?"
        );
        $stmt->bind_param("ssss", $name, $email, $type, $id);
        if ($stmt->execute() && $stmt->affected_rows) {
            $success = "✏️ Đã cập nhật tài khoản $id.";
        } else {
            $errors[] = "Không có hàng nào được cập nhật (có thể bạn không đổi gì).";
        }
        $stmt->close();
    }
}

/* ---------- THÊM ---------- */
if (isset($_POST['btn_add'])) {
    $id       = trim($_POST['account_id']);
    $name     = trim($_POST['account_name']);
    $email    = trim($_POST['account_email']);
    $password = $_POST['account_password'];
    $type     = trim($_POST['account_type']);

    if (!$id || !$name || !$email || !$password || !$type) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin khi thêm.";
    } else {
        // kiểm tra trùng
        $stmt = $conn->prepare(
            "SELECT 1 FROM account WHERE account_id=? OR account_email=? LIMIT 1"
        );
        $stmt->bind_param("ss", $id, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows) {
            $errors[] = "ID hoặc Email đã tồn tại.";
        }
        $stmt->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO account (account_id, account_name, account_password, account_email, account_type)
             VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("sssss", $id, $name, $hash, $email, $type);
        if ($stmt->execute()) {
            $success = "✅ Đã thêm tài khoản $id.";
        } else {
            $errors[] = "Lỗi thêm tài khoản: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý tài khoản</title>
<style>
  

    /* Headings */
    h2 {
        color: #1e3a8a;
        margin: 1.5rem 0;
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Box container */
    .box {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        padding: 2rem;
        margin: 0 auto 2.5rem;
        max-width: 2800px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }


    /* Form grid */
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Labels */
    label {
        font-weight: 600;
        font-size: 1rem;
        color: #1e3a8a;
        margin-bottom: 0.5rem;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Inputs */
    input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        background: #f1f5f9;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    input:focus {
        outline: none;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
    }

    /* Buttons */
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
    }

    .btn:active {
        transform: scale(1);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .btn[name="btn_add"] {
        background: #3b82f6;
    }


    .btn-edit {
        background:rgb(96, 160, 255);
    }

    .btn-del {
        background: #ef4444;
        text-decoration: none;
        display: inline-flex;
    }


    /* Table */
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 1rem;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    th, td {
        padding: 1.25rem;
        text-align: left;
    }

    th {
        background:rgb(9, 5, 211);
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    td input[disabled] {
        background: #e2e8f0;
        color: #4b5563;
        border: none;
    }

    /* Alerts */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .alert.err {
        background: #fef2f2;
        color: #b91c1c;
        border-left: 4px solid #ef4444;
    }

    .alert.ok {
        background: #ecfdf5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert ul {
        list-style: disc;
        margin-left: 1.75rem;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }

        .grid {
            grid-template-columns: 1fr;
        }

        table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }

        th, td {
            min-width: 160px;
        }

        .btn, .btn-sm, .btn-del {
            width: 100%;
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 480px) {
        h2 {
            font-size: 1.5rem;
        }

        .box {
            padding: 1.5rem;
        }

        input, .btn, .btn-sm {
            font-size: 0.9rem;
        }
    }
    </style>
</head>
<body>

<h2>Thêm tài khoản mới</h2>

<?php if ($errors): ?>
    <div class="alert err"><ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
<?php elseif ($success): ?>
    <div class="alert ok"><?= $success ?></div>
<?php endif; ?>

<div class="box">
    <form method="POST">
        <div class="grid">
            <div><label>ID</label><input name="account_id" required></div>
            <div><label>Tên</label><input name="account_name" required></div>
            <div><label>Email</label><input type="email" name="account_email" required></div>
            <div><label>Mật khẩu</label><input type="password" name="account_password" required></div>
            <div><label>Loại</label><input name="account_type" required></div>
        </div><br>
        <button class="btn" name="btn_add">Thêm tài khoản</button>
    </form>
</div>

<h2>Danh sách tài khoản</h2>
<div class="box">
<table>
    <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Email</th>
        <th>Loại</th>
        <th>Thao tác</th>
    </tr>
<?php
$res = $conn->query("SELECT * FROM account");
if ($res && $res->num_rows):
    while ($row = $res->fetch_assoc()):
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

</body>
</html>
