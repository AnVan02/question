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
    
    // Kiểm tra xem có bài kiểm tra nào liên quan đến khóa học không
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM test WHERE id_khoa = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    if ($count > 0) {
        $message = "<div class='message error'>Không thể xóa khóa học vì có bài kiểm tra liên quan!</div>";
    } else {
        $stmt = $conn->prepare("DELETE FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Đã xóa khóa học thành công!</div>";
        } else {
            $message = "<div class='message error'>Lỗi khi xóa: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    $conn->close();
}

// Xử lý sửa khóa học
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_course"])) {
    $id = (int) $_POST["course_id"];
    $ten_khoahoc = trim($_POST["ten_khoahoc"]);
    if (empty($ten_khoahoc)) {
        $message = "<div class='message error'>Vui lòng nhập tên khóa học!</div>";
    } else {
        $conn = dbconnect();
        $stmt = $conn->prepare("UPDATE khoa_hoc SET khoa_hoc = ? WHERE id = ?");
        $stmt->bind_param("si", $ten_khoahoc, $id);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Đã cập nhật khóa học thành công!</div>";
        } else {
            $message = "<div class='message error'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
        }
        $stmt->close();
        $conn->close();
    }
}

// Xử lý thêm mới
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_course"])) {
    $ten_khoahoc = trim($_POST["ten_khoahoc"]);
    if (empty($ten_khoahoc)) {
        $message = "<div class='message error'>Vui lòng nhập tên khóa học!</div>";
    } else {
        $conn = dbconnect();
        $stmt = $conn->prepare("INSERT INTO khoa_hoc (khoa_hoc) VALUES (?)");
        $stmt->bind_param("s", $ten_khoahoc);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Đã thêm khóa học thành công!</div>";
        } else {
            $message = "<div class='message error'>Lỗi khi thêm: " . $stmt->error . "</div>";
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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #ffffff;
            max-width: 800px;
            width: 100%;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        h2, h3 {
            text-align: center;
            color: #01579b;
            margin-bottom: 25px;
            font-weight: 500;
        }

        form label {
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        form input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        form input[type="text"]:focus {
            border-color: #0288d1;
            box-shadow: 0 0 5px rgba(2, 136, 209, 0.2);
            outline: none;
        }

        button {
            width: 100%;
            background-color: #0288d1;
            color: white;
            font-size: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s, transform 0.1s;
        }

        button:hover {
            background-color: #0277bd;
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        a.cancel {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #0288d1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        a.cancel:hover {
            color: #01579b;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .message.error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin-top: 20px;
        }

        ul li {
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: 50px 1fr 60px 60px 100px;
            align-items: center;
            gap: 10px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        ul li:hover {
            background-color: #f5f5f5;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        ul li strong {
            text-align: left;
            color: #333;
            font-weight: 500;
        }

        ul li .course-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #333;
        }

        ul li a {
            text-align: center;
            padding: 6px 0;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            display: block;
            transition: background-color 0.3s, transform 0.1s;
        }

        ul li a.edit {
            background-color: #e3f2fd;
            color: #0288d1;
        }

        ul li a.edit:hover {
            background-color: #bbdefb;
            transform: translateY(-1px);
        }

        ul li a.delete {
            background-color: #ffebee;
            color: #c62828;
        }

        ul li a.delete:hover {
            background-color: #ffcdd2;
            transform: translateY(-1px);
        }

        ul li a.btn {
            background-color: #0288d1;
            color: white;
        }

        ul li a.btn:hover {
            background-color: #0277bd;
            transform: translateY(-1px);
        }
    </style>
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
                <a href="index.php" class="cancel">Huỷ</a>
            <?php else: ?>
                <button type="submit" name="add_course">Thêm khóa học</button>
            <?php endif; ?>
        </form>

        <h3>Danh sách khóa học</h3>
        <?php if (empty($khoa_hoc_list)): ?>
            <p style="text-align: center; color: #666;">Chưa có khóa học nào.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($khoa_hoc_list as $kh): ?>
                    <li>
                        <strong><?= htmlspecialchars($kh['id']) ?>:</strong>
                        <span class="course-name"><?= htmlspecialchars($kh['khoa_hoc']) ?></span>
                        <a href="?edit=<?= $kh['id'] ?>" class="edit">Sửa</a>
                        <a href="?delete=<?= $kh['id'] ?>" class="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                        <a href="khoahoc.php?id_khoa=<?= htmlspecialchars($kh['id']) ?>" class="btn">Xem test</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>