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

// Xóa câu hỏi
if (isset($_GET['delete_question'])) {
    $id_cauhoi = (int)$_GET['delete_question'];
    $stmt = $conn->prepare("DELETE FROM quiz WHERE Id_cauhoi = ?");
    $stmt->bind_param("i", $id_cauhoi);
    if ($stmt->execute()) {
        $message = "<div style='color:green;'>Đã xóa câu hỏi thành công!</div>";
    } else {
        $message = "<div style='color:red;'>Lỗi khi xóa câu hỏi: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Sửa câu hỏi
if (isset($_POST['edit_question'])) {
    $id_cauhoi_edit = (int)$_POST['id_cauhoi_edit'];
    $cauhoi_edit = trim($_POST['cauhoi_edit']);
    $cau_a_edit = trim($_POST['cau_a_edit']);
    $giaithich_a_edit = trim($_POST['giaithich_a_edit']);
    $cau_b_edit = trim($_POST['cau_b_edit']);
    $giaithich_b_edit = trim($_POST['giaithich_b_edit']);
    $cau_c_edit = trim($_POST['cau_c_edit']);
    $giaithich_c_edit = trim($_POST['giaithich_c_edit']);
    $cau_d_edit = trim($_POST['cau_d_edit']);
    $giaithich_d_edit = trim($_POST['giaithich_d_edit']);
    $dap_an_edit = trim($_POST['dap_an_edit']);

    if (!empty($cauhoi_edit) && !empty($cau_a_edit) && !empty($cau_b_edit) && !empty($cau_c_edit) && !empty($cau_d_edit) && !empty($dap_an_edit)) {
        $stmt = $conn->prepare("
            UPDATE quiz 
            SET cauhoi = ?, cau_a = ?, giaithich_a = ?, cau_b = ?, giaithich_b = ?, 
                cau_c = ?, giaithich_c = ?, cau_d = ?, giaithich_d = ?, dap_an = ?
            WHERE Id_cauhoi = ?
        ");
        $stmt->bind_param("ssssssssssi", $cauhoi_edit, $cau_a_edit, $giaithich_a_edit, $cau_b_edit, $giaithich_b_edit, 
            $cau_c_edit, $giaithich_c_edit, $cau_d_edit, $giaithich_d_edit, $dap_an_edit, $id_cauhoi_edit);
        if ($stmt->execute()) {
            $message = "<div style='color:green;'>Sửa câu hỏi thành công!</div>";
        } else {
            $message = "<div style='color:red;'>Lỗi khi sửa câu hỏi: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div style='color:red;'>Vui lòng nhập đầy đủ thông tin câu hỏi!</div>";
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
}

// Lấy danh sách câu hỏi trực tiếp từ bảng quiz theo id_khoa
$cau_hoi_theo_khoa = [];
if ($id_khoa > 0) {
    $stmt = $conn->prepare("
        SELECT q.*, k.khoa_hoc 
        FROM quiz q
        JOIN khoa_hoc k ON q.id_khoa = k.id
        WHERE q.id_khoa = ?
        ORDER BY q.Id_cauhoi ASC
    ");
    $stmt->bind_param("i", $id_khoa);
    if (!$stmt->execute()) {
        die("Lỗi truy vấn: " . $stmt->error);
    }
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cau_hoi_theo_khoa[] = $row;
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
    <div>
        <button type="button" onclick="window.location.href='question.php'" class="btn btn-secondary">Danh sách câu hỏi</button>
    </div>

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

    <!-- Danh sách câu hỏi thuộc khóa học -->
    <h3>Danh sách câu hỏi thuộc khóa học</h3>
    <?php if (empty($cau_hoi_theo_khoa)): ?>
        <p>Chưa có câu hỏi nào trong khóa học này.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Câu hỏi</th>
                <th>Khóa học</th>
                <th>Thao tác</th>
            </tr>
            <?php foreach ($cau_hoi_theo_khoa as $c): ?>
                <tr>
                    <td><?= $c['Id_cauhoi'] ?></td>
                    <td>
                        <!-- Form sửa câu hỏi -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id_cauhoi_edit" value="<?= $c['Id_cauhoi'] ?>">
                            <input type="text" name="cauhoi_edit" value="<?= htmlspecialchars($c['cauhoi']) ?>" required>
                    </td>
                    <td><?= htmlspecialchars($c['khoa_hoc']) ?></td>
                    <td>
                            <button type="submit" name="edit_question" class="edit">Sửa</button>
                        </form>
                        <!-- Xóa câu hỏi -->
                        <a href="?id_khoa=<?= $id_khoa ?>&delete_question=<?= $c['Id_cauhoi'] ?>"><button class="delete">Xóa</button></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>