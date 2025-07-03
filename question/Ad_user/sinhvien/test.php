<?php
// ------------------- KẾT NỐI CSDL -------------------
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        error_log("Kết nối thất bại: " . $conn->connect_error);
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

$conn = dbconnect();

$errors = [];
$success = false;

// ------------------- XỬ LÝ FORM -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_add'])) {
    $account_id = trim($_POST['account_id']);
    $account_name = trim($_POST['account_name']);
    $account_email = trim($_POST['account_email']);
    $account_password = $_POST['account_password'];
    $account_type = trim($_POST['account_type']);

    // Kiểm tra dữ liệu rỗng
    if (empty($account_id) || empty($account_name) || empty($account_email) || empty($account_password) || empty($account_type)) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin!";
    }

    // Kiểm tra trùng ID hoặc email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM account WHERE account_email = ? OR account_id = ?");
        $stmt->bind_param("ss", $account_email, $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email hoặc ID đã tồn tại!";
        }
        $stmt->close();
    }

    // Thêm tài khoản
    if (empty($errors)) {
        $hashed_password = password_hash($account_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO account (account_id, account_name, account_password, account_email, account_type, account_status) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssss", $account_id, $account_name, $hashed_password, $account_email, $account_type);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Lỗi khi thêm tài khoản: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #16a34a;
            --error-color: #dc2626;
            --border-color: #e5e7eb;
            --background-color: #f9fafb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: var(--background-color);
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: #1f2937;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            color: #374151;
            font-size: 0.9rem;
        }

        input {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            font-size: 0.9rem;
            transition: border-color 0.2s;
            width: 100%;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .submit-btn:hover {
            background-color: #1d4ed8;
        }

        .error {
            background-color: #fee2e2;
            padding: 1rem;
            border-radius: 0.375rem;
            color: var(--error-color);
            margin-bottom: 1rem;
        }

        .error ul {
            list-style: none;
            margin-top: 0.5rem;
        }

        .success {
            background-color: #dcfce7;
            padding: 1rem;
            border-radius: 0.375rem;
            color: var(--success-color);
            margin-bottom: 1rem;
        }

        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <h2>Thêm tài khoản mới</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($success): ?>
        <div class="success">✅ Tài khoản đã được thêm thành công!</div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>ID tài khoản</label>
                    <input type="text" name="account_id" required>
                </div>
                <div class="form-group">
                    <label>Tên tài khoản</label>
                    <input type="text" name="account_name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="account_email" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="account_password" required>
                </div>
                <div class="form-group">
                    <label>Loại tài khoản</label>
                    <input type="text" name="account_type" required>
                </div>
            </div>
            <input type="submit" name="btn_add" value="Thêm tài khoản" class="submit-btn">
        </form>
    </div>

    <h2>Danh sách tài khoản</h2>
    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Tên tài khoản</th>
                <th>Email</th>
                <th>Loại</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM account");
            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= htmlspecialchars($row['account_id']) ?></td>
                <td><?= htmlspecialchars($row['account_name']) ?></td>
                <td><?= htmlspecialchars($row['account_email']) ?></td>
                <td><?= htmlspecialchars($row['account_type']) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4">Chưa có tài khoản nào.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>