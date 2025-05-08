<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost"; // Thay bằng thông tin của bạn
$username = "root"; // Thay bằng username của bạn
$password = ""; // Thay bằng password của bạn
$dbname = "study"; // Tên database của bạn

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý khi form được gửi
$success_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_test = $_POST['ten_test'];
    $lan_thu = $_POST['lan_thu'];
    $id_khoa = 1; // Giá trị mặc định cho id_khoa, thay đổi nếu cần

    $sql = "INSERT INTO test (id_khoa, ten_test, lan_thu) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $id_khoa, $ten_test, $lan_thu);

    if ($stmt->execute()) {
        $stmt->close();
        // Chuyển hướng để tránh resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $success_message = "<p>Lỗi: " . $conn->error . "</p>";
    }

    $stmt->close();
}

// Lấy danh sách bản ghi từ bảng test
$sql = "SELECT id_test, id_khoa, ten_test, lan_thu FROM test";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập và hiển thị dữ liệu Test</title>
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 20px auto;
        max-width: 800px;
        background-color: #f4f7fa;
        color: #333;
        line-height: 1.6;
    }

    h2 {
        margin-bottom: 25px;
        color: #1a3c34;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
        padding: 10px 0;
        border: none;
    }

    form {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
        transition: transform 0.2s;
    }

    form:hover {
        transform: translateY(-2px);
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
    }

    input[type="text"],
    input[type="number"] {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d8e0;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    input:focus {
        border-color: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.2);
        outline: none;
    }

    button {
        padding: 12px 24px;
        background-color: #28a745;
        border: none;
        color: #fff;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    button:hover {
        background-color: #218838;
        transform: translateY(-1px);
    }

    button:active {
        transform: translateY(0);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    th, td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    th {
        background-color: #e9ecef;
        font-weight: 600;
        color: #2c3e50;
    }

    td {
        font-size: 15px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    p {
        color: #dc3545;
        margin-top: 12px;
        font-weight: 500;
    }

    .action-button {
        display: inline-block;
        padding: 10px 18px;
        background-color: #007bff;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background-color 0.3s, transform 0.2s;
    }

    .action-button:hover {
        background-color: #0056b3;
        transform: translateY(-1px);
    }

    .action-button:active {
        transform: translateY(0);
    }

    @media (max-width: 600px) {
        body {
            margin: 20px;
        }

        form, table {
            padding: 15px;
        }

        h2 {
            font-size: 20px;
        }

        input[type="text"],
        input[type="number"] {
            font-size: 14px;
            padding: 10px;
        }

        button, .action-button {
            font-size: 14px;
            padding: 10px 16px;
        }

        th, td {
            padding: 10px;
            font-size: 14px;
        }
    }
    </style>
</head>
<body>
    <h2>Nhập dữ liệu Test</h2>
    <?php echo $success_message; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="ten_test">Tên Test:</label>
            <input type="text" id="ten_test" name="ten_test" maxlength="255" required>
        </div>
        <div class="form-group">
            <label for="lan_thu">Lần thứ:</label>
            <input type="number" id="lan_thu" name="lan_thu" value="1" min="1" required>
        </div>
        <button type="submit">Thêm</button>
    </form>

    <h2>Danh sách Test</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID Test</th>
                <th>ID Khoa</th>
                <th>Tên Test</th>
                <th>Lần thứ</th>
                <th>Hành động</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_test']); ?></td>
                    <td><?php echo htmlspecialchars($row['id_khoa']); ?></td>
                    <td><?php echo htmlspecialchars($row['ten_test']); ?></td>
                    <td><?php echo htmlspecialchars($row['lan_thu']); ?></td>
                    <td>
                        <a href="question.php?id_test=<?php echo htmlspecialchars($row['id_test']); ?>" class="action-button">Xem câu hỏi</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Chưa có dữ liệu trong bảng Test.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>
<!-- có xoá sưa -->
<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost"; // Thay bằng thông tin của bạn
$username = "root"; // Thay bằng username của bạn
$password = ""; // Thay bằng password của bạn
$dbname = "study"; // Tên database của bạn

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý khi form được gửi (Thêm hoặc Sửa)
$success_message = '';
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_test = $_POST['ten_test'];
    $lan_thu = $_POST['lan_thu'];
    $id_khoa = 1; // Giá trị mặc định cho id_khoa, thay đổi nếu cần

    if (isset($_POST['id_test']) && !empty($_POST['id_test'])) {
        // Sửa bản ghi
        $id_test = $_POST['id_test'];
        $sql = "UPDATE test SET ten_test = ?, lan_thu = ?, id_khoa = ? WHERE id_test = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siii", $ten_test, $lan_thu, $id_khoa, $id_test);
    } else {
        // Thêm bản ghi mới
        $sql = "INSERT INTO test (id_khoa, ten_test, lan_thu) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $id_khoa, $ten_test, $lan_thu);
    }

    if ($stmt->execute()) {
        $stmt->close();
        // Chuyển hướng để tránh resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "<p>Lỗi: " . $conn->error . "</p>";
    }

    $stmt->close();
}

// Xử lý xóa bản ghi
if (isset($_GET['delete'])) {
    $id_test = $_GET['delete'];
    $sql = "DELETE FROM test WHERE id_test = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_test);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "<p>Lỗi khi xóa: " . $conn->error . "</p>";
    }

    $stmt->close();
}

// Lấy dữ liệu để sửa (nếu có)
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id_test = $_GET['edit'];
    $sql = "SELECT id_test, id_khoa, ten_test, lan_thu FROM test WHERE id_test = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_test);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Lấy danh sách bản ghi từ bảng test
$sql = "SELECT id_test, id_khoa, ten_test, lan_thu FROM test";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập, sửa, xóa dữ liệu Test</title>
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 20px auto;
        max-width: 800px;
        background-color: #f4f7fa;
        color: #333;
        line-height: 1.6;
    }

    h2 {
        margin-bottom: 25px;
        color: #1a3c34;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
        padding: 10px 0;
        border: none;
    }

    form {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
        transition: transform 0.2s;
    }

    form:hover {
        transform: translateY(-2px);
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
    }

    input[type="text"],
    input[type="number"] {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d8e0;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    input:focus {
        border-color: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.2);
        outline: none;
    }

    button {
        padding: 12px 24px;
        background-color: #28a745;
        border: none;
        color: #fff;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    button:hover {
        background-color: #218838;
        transform: translateY(-1px);
    }

    button:active {
        transform: translateY(0);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    th, td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    th {
        background-color: #e9ecef;
        font-weight: 600;
        color: #2c3e50;
    }

    td {
        font-size: 15px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    p {
        margin-top: 12px;
        font-weight: 500;
    }

    p.success {
        color: #28a745;
    }

    p.error {
        color: #dc3545;
    }

    .action-button {
        display: inline-block;
        padding: 10px 18px;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-right: 5px;
        transition: background-color 0.3s, transform 0.2s;
    }

    .action-button.edit {
        background-color: #ffc107;
    }

    .action-button.edit:hover {
        background-color: #e0a800;
        transform: translateY(-1px);
    }

    .action-button.delete {
        background-color: #dc3545;
    }

    .action-button.delete:hover {
        background-color: #c82333;
        transform: translateY(-1px);
    }

    .action-button.view {
        background-color: #007bff;
    }

    .action-button.view:hover {
        background-color: #0056b3;
        transform: translateY(-1px);
    }

    .action-button:active {
        transform: translateY(0);
    }

    @media (max-width: 600px) {
        body {
            margin: 20px;
        }

        form, table {
            padding: 15px;
        }

        h2 {
            font-size: 20px;
        }

        input[type="text"],
        input[type="number"] {
            font-size: 14px;
            padding: 10px;
        }

        button, .action-button {
            font-size: 14px;
            padding: 10px 16px;
        }

        th, td {
            padding: 10px;
            font-size: 14px;
        }
    }
    </style>
</head>
<body>
    <h2><?php echo $edit_mode ? 'Sửa dữ liệu Test' : 'Nhập dữ liệu Test'; ?></h2>
    <?php 
    if ($success_message) echo "<p class='success'>$success_message</p>";
    if ($error_message) echo "<p class='error'>$error_message</p>";
    ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="id_test" value="<?php echo htmlspecialchars($edit_data['id_test']); ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="ten_test">Tên Test:</label>
            <input type="text" id="ten_test" name="ten_test" maxlength="255" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['ten_test']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="lan_thu">Lần thứ:</label>
            <input type="number" id="lan_thu" name="lan_thu" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['lan_thu']) : '1'; ?>" min="1" required>
        </div>
        <button type="submit"><?php echo $edit_mode ? 'Cập nhật' : 'Thêm'; ?></button>
    </form>

    <h2>Danh sách Test</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID Test</th>
                <th>ID Khoa</th>
                <th>Tên Test</th>
                <th>Lần thứ</th>
                <th>Hành động</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_test']); ?></td>
                    <td><?php echo htmlspecialchars($row['id_khoa']); ?></td>
                    <td><?php echo htmlspecialchars($row['ten_test']); ?></td>
                    <td><?php echo htmlspecialchars($row['lan_thu']); ?></td>
                    <td>
                        <a href="?edit=<?php echo htmlspecialchars($row['id_test']); ?>" class="action-button edit">Sửa</a>
                        <a href="?delete=<?php echo htmlspecialchars($row['id_test']); ?>" class="action-button delete" onclick="return confirm('Bạn có chắc chắn muốn xóa bản ghi này?');">Xóa</a>
                        <a href="question.php?id_test=<?php echo htmlspecialchars($row['id_test']); ?>" class="action-button view">Xem câu hỏi</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Chưa có dữ liệu trong bảng Test.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>