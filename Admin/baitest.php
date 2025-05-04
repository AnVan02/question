<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Lỗi kết nối: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();
$message = "";
$id_khoa = isset($_GET['id_khoa']) ? (int)$_GET['id_khoa'] : 0;

// Thêm bài test
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_test"])) {
     $id_khoa_post = (int)$_POST["id_khoa"];
     $ten_test = trim($_POST["ten_test"]);
     $lan_thu = (int)$_POST["lan_thu"];
 
     if ($id_khoa_post > 0 && !empty($ten_test)) {
         $stmt = $conn->prepare("INSERT INTO test (id_khoa, ten_test, lan_thu) VALUES (?, ?, ?)");
         $stmt->bind_param("isi", $id_khoa_post, $ten_test, $lan_thu);
         if ($stmt->execute()) {
             $message = "<div style='color:green;'>Thêm bài test thành công!</div>";
             // Sau khi thêm thành công, chuyển hướng để không gửi lại form khi bấm F5
             header("Location: " . $_SERVER['PHP_SELF'] . "?id_khoa=" . $id_khoa_post);
             exit();
         } else {
             $message = "<div style='color:red;'>Lỗi khi thêm test: " . $stmt->error . "</div>";
         }
         $stmt->close();
     } else {
         $message = "<div style='color:red;'>Vui lòng chọn khóa học và nhập tên test!</div>";
     }
 }
 

// Xóa bài test
if (isset($_GET['delete_test'])) {
    $id_test = (int)$_GET['delete_test'];
    $stmt = $conn->prepare("DELETE FROM test WHERE id_test = ?");
    $stmt->bind_param("i", $id_test);
    if ($stmt->execute()) {
        $message = "<div style='color:green;'>Đã xóa bài test thành công!</div>";
    } else {
        $message = "<div style='color:red;'>Lỗi khi xóa bài test: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Sửa bài test
if (isset($_POST['edit_test'])) {
    $id_test_edit = (int)$_POST['id_test_edit'];
    $ten_test_edit = trim($_POST['ten_test_edit']);
    $lan_thu_edit = (int)$_POST['lan_thu_edit'];

    if (!empty($ten_test_edit)) {
        $stmt = $conn->prepare("UPDATE test SET ten_test = ?, lan_thu = ? WHERE id_test = ?");
        $stmt->bind_param("sii", $ten_test_edit, $lan_thu_edit, $id_test_edit);
        if ($stmt->execute()) {
            $message = "<div style='color:green;'>Sửa bài test thành công!</div>";
        } else {
            $message = "<div style='color:red;'>Lỗi khi sửa bài test: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div style='color:red;'>Vui lòng nhập tên bài test!</div>";
    }
}

// Lấy danh sách khóa học
$khoa_hoc = [];
$res = $conn->query("SELECT * FROM khoa_hoc");
while ($row = $res->fetch_assoc()) {
    $khoa_hoc[] = $row;
}

// Lấy danh sách test theo khóa học
$ds_test = [];
if ($id_khoa > 0) {
    $res = $conn->query("SELECT * FROM test WHERE id_khoa = $id_khoa ORDER BY id_test DESC");
    while ($row = $res->fetch_assoc()) {
        $ds_test[] = $row;
    }

    // Lấy câu hỏi theo từng bài test thuộc khóa học
    $stmt = $conn->prepare("
        SELECT q.*, t.ten_test 
        FROM quiz q
        JOIN test t ON q.id_baitest = t.id_test
        WHERE t.id_khoa = ?
        ORDER BY t.id_test DESC, q.Id_cauhoi ASC
    ");
    $stmt->bind_param("i", $id_khoa);
    $stmt->execute();
    $result = $stmt->get_result();

    $cau_hoi_theo_test = [];
    while ($row = $result->fetch_assoc()) {
        $cau_hoi_theo_test[$row['ten_test']][] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý bài test theo khóa học</title>
    <style>
        body {
          font-family: Arial, sans-serif;
          background-color: #f4f4f9;
          margin: 0;
          padding: 0;
        }
        .container {
            background: #fff;
            padding: 25px;
            max-width: 1000px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #00796b;
        }
        form {
            margin-top: 20px;
        }
        select, input[type="text"], input[type="number"], button {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            box-sizing: border-box;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        .message {
            margin-top: 15px;
            padding: 10px;
            border-left: 5px solid;
            border-radius: 4px;
        }
        .message[style*="green"] {
            background: #e8f5e9;
            border-color: green;
        }
        .message[style*="red"] {
            background: #ffebee;
            border-color: red;
        }
        button.edit, button.delete {
            width: auto;
            background: #4caf50;
            color: white;
            margin-right: 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
        }
        button.delete {
            background: #f44336;
        }
        button.edit:hover, button.delete:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Quản lý Bài Test theo Khóa học</h2>

    <?= $message ?>

    <!-- Chọn khóa học -->
    <form method="get">
        <label>Chọn khóa học:</label>
        <select name="id_khoa" onchange="this.form.submit()">
            <option value="0">-- Chọn --</option>
            <?php foreach ($khoa_hoc as $k): ?>
                <option value="<?= $k['id'] ?>" <?= $k['id'] == $id_khoa ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['khoa_hoc']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Thêm bài test -->
    <?php if ($id_khoa > 0): ?>
    <form method="post">
        <input type="hidden" name="id_khoa" value="<?= $id_khoa ?>">
        <label>Tên bài test:</label>
        <input type="text" name="ten_test" placeholder="Nhập tên bài test">
        <label>Lần thử:</label>
        <input type="number" name="lan_thu" value="1" min="1">
        <button type="submit" name="add_test">Thêm bài test</button>
        
    </form>

    <!-- Danh sách bài test -->
    <h3>Danh sách bài test đã thêm</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Tên test</th>
            <th>Lần thử</th>
            <th>Thao tác</th>
        </tr>
        <?php if (empty($ds_test)): ?>
            <tr><td colspan="4">Chưa có bài test nào.</td></tr>
        <?php else: ?>
            <?php foreach ($ds_test as $t): ?>
            <tr>
                <td><?= $t['id_test'] ?></td>
                <td><?= htmlspecialchars($t['ten_test']) ?></td>
                <td><?= $t['lan_thu'] ?></td>
                <td>
                    <!-- Sửa bài test -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id_test_edit" value="<?= $t['id_test'] ?>">
                        <input type="text" name="ten_test_edit" value="<?= htmlspecialchars($t['ten_test']) ?>" required>
                        <input type="number" name="lan_thu_edit" value="<?= $t['lan_thu'] ?>" min="1" required>
                        <button type="submit" name="edit_test" class="edit">Sửa</button>
                    </form>
                    <!-- Xóa bài test -->
                    <a href="?id_khoa=<?= $id_khoa ?>&delete_test=<?= $t['id_test'] ?>"><button class="delete">Xóa</button></a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <!-- Hiển thị câu hỏi theo từng bài test -->
    <h3>Danh sách câu hỏi theo từng bài test</h3>
    <?php if (empty($cau_hoi_theo_test)): ?>
        <p>Chưa có câu hỏi nào trong khóa học này.</p>
    <?php else: ?>
        <?php foreach ($cau_hoi_theo_test as $ten_test => $ds_cauhoi): ?>
            <h4><?= htmlspecialchars($ten_test) ?></h4>
            <!-- Hiển thị luôn câu hỏi mà không cần bấm vào nút -->
            <div id="questions-<?= $ten_test ?>" style="display: block;">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Câu hỏi</th>
                    </tr>
                    <?php foreach ($ds_cauhoi as $c): ?>
                        <tr>
                            <td><?= $c['Id_cauhoi'] ?></td>
                            <td><?= htmlspecialchars($c['cauhoi']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>


