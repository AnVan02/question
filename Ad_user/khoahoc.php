<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối CSDL thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$message = "";

// Xử lý xóa khóa học
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn = dbconnect();
    $stmt = $conn->prepare("DELETE FROM khoa_hoc WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "<div style='color:green;'>Đã xóa khóa học thành công!</div>";
    } else {
        $message = "<div style='color:red;'>Lỗi khi xóa: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();
}

// Xử lý sửa khóa học
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_course"])) {
    $id = (int) $_POST["course_id"];
    $ten_khoahoc = trim($_POST["ten_khoahoc"]);
    if (empty($ten_khoahoc)) {
        $message = "<div style='color:red;'>Vui lòng nhập tên khóa học!</div>";
    } else {
        $conn = dbconnect();
        $stmt = $conn->prepare("UPDATE khoa_hoc SET khoa_hoc = ? WHERE id = ?");
        $stmt->bind_param("si", $ten_khoahoc, $id);
        if ($stmt->execute()) {
            $message = "<div style='color:green;'>Đã cập nhật khóa học thành công!</div>";
        } else {
            $message = "<div style='color:red;'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
        }
        $stmt->close();
        $conn->close();
    }
}

// Xử lý thêm mới
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_course"])) {
    $ten_khoahoc = trim($_POST["ten_khoahoc"]);
    if (empty($ten_khoahoc)) {
        $message = "<div style='color:red;'>Vui lòng nhập tên khóa học!</div>";
    } else {
        $conn = dbconnect();
        $stmt = $conn->prepare("INSERT INTO khoa_hoc (khoa_hoc) VALUES (?)");
        $stmt->bind_param("s", $ten_khoahoc);
        if ($stmt->execute()) {
            $message = "<div style='color:green;'>Đã thêm khóa học thành công!</div>";
        } else {
            $message = "<div style='color:red;'>Lỗi khi thêm: " . $stmt->error . "</div>";
        }
        $stmt->close();
        $conn->close();
    }
}

// Lấy danh sách khóa học
$conn = dbconnect();
$khoa_hoc_list = [];
$result = $conn->query("SELECT * FROM khoa_hoc ORDER BY id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $khoa_hoc_list[] = $row;
    }
}
$conn->close();

// Nếu đang sửa
$editing = false;
$edit_khoa_hoc = '';
$edit_id = 0;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $conn = dbconnect();
    $stmt = $conn->prepare("SELECT * FROM khoa_hoc WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $editing = true;
        $edit_khoa_hoc = $row['khoa_hoc'];
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khóa Học</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2><?= $editing ? "Cập nhật khóa học" : "Thêm khóa học" ?></h2>
        <?php if (!empty($message)) echo $message; ?>

        <form method="POST">
            <label>Tên khóa học:</label>
            <input type="text" name="ten_khoahoc" placeholder="Nhập tên khóa học" value="<?= htmlspecialchars($edit_khoa_hoc) ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="course_id" value="<?= $edit_id ?>">
                <button type="submit" name="update_course">Cập nhật</button>
                <a href="index.php" style="display:block;margin-top:10px;text-align:center;">Huỷ</a>
            <?php else: ?>
                <button type="submit" name="add_course">Thêm khóa học</button>
            <?php endif; ?>
        </form>

        <h3 style="margin-top:40px;">Danh sách khóa học</h3>
        <?php if (empty($khoa_hoc_list)): ?>
            <p>Chưa có khóa học nào.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($khoa_hoc_list as $kh): ?>
                    <li>
                        <strong><?= htmlspecialchars($kh['id']) ?>:</strong>
                        <?= htmlspecialchars($kh['khoa_hoc']) ?>
                        <br>
                        <a href="?edit=<?= $kh['id'] ?>"> Sửa</a> |
                        <a href="?delete=<?= $kh['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')"> Xóa</a> |
                    </li>
                   
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div style="with:100px ">
            <button type="button" onclick="window.location.href='baihoc.php'" class="btn btn-secondary">Xem test </button>
        </div>
        
    </div>

    
</body>
</html>

<style>
     /* style.css */

body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to right, #f8f9fa, #e0f7fa);
    margin: 0;
    padding: 20px;
}

.container {
    background-color: #ffffff;
    max-width: 1000px;
    margin: 0 auto;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

h2, h3 {
    text-align: center;
    color: #00796b;
    margin-bottom: 25px;
}

form label {
    font-weight: 600;
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
    color: #333;
}

form input[type="text"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
    transition: border-color 0.3s, background-color 0.3s;
}

form input[type="text"]:focus {
    border-color: #009688;
    outline: none;
    background-color: #f1fefc;
}

button {
    display: block;
    width: 100%;
    background-color: #009688;
    color: white;
    font-size: 16px;
    padding: 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 25px;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #00796b;
}

a {
    text-decoration: none;
    color: #00796b;
    font-weight: 500;
    transition: color 0.3s;
}

a:hover {
    color: #004d40;
}

ul {
    list-style-type: none;
    padding: 0;
}

ul li {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

ul li strong {
    margin-right: 10px;
    color: #333;
}

ul li a {
    margin-left: 10px;
    font-size: 14px;
}

div[style^="color:red"] {
    background-color: #ffeaea;
    color: #c62828;
    padding: 12px;
    border-left: 5px solid red;
    margin-bottom: 20px;
    border-radius: 6px;
}

div[style^="color:green"] {
    background-color: #e0fbe7;
    color: #2e7d32;
    padding: 12px;
    border-left: 5px solid green;
    margin-bottom: 20px;
    border-radius: 6px;
}

a[href*="edit"], a[href*="delete"] {
    background-color: #e0f2f1;
    padding: 5px 10px;
    border-radius: 6px;
    margin-top: 8px;
    display: inline-block;
}

a[href*="edit"]:hover {
    background-color: #b2dfdb;
}

a[href*="delete"]:hover {
    background-color: #ffcdd2;
    color: #c62828;
}

a[href*="xem_cauhoi"] {
    background-color: #1976d2;
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
}

a[href*="xem_cauhoi"]:hover {
    background-color: #1565c0;
}
</style>