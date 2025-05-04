<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();

// Lấy danh sách khóa học
$khoa_hoc = [];
$khoa_res = $conn->query("SELECT * FROM khoa_hoc");
while ($row = $khoa_res->fetch_assoc()) {
    $khoa_hoc[] = $row;
}

// Xử lý chọn khóa học và test
$id_khoa = isset($_GET['id_khoa']) ? (int)$_GET['id_khoa'] : 0;
$id_test = isset($_GET['id_test']) ? (int)$_GET['id_test'] : 0;

$tests = [];
$cau_hoi = [];

if ($id_khoa > 0) {
    $res = $conn->query("SELECT * FROM test WHERE id_khoa = $id_khoa");
    while ($row = $res->fetch_assoc()) {
        $tests[] = $row;
    }
}

if ($id_test > 0) {
    $res = $conn->query("SELECT * FROM quiz WHERE id_baitest = '$id_test'");
    while ($row = $res->fetch_assoc()) {
        $cau_hoi[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách câu hỏi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 25px;
            max-width: 960px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        select, button {
            padding: 8px 12px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }
        th {
            background: #e3f2fd;
        }
        a.btn {
            padding: 5px 10px;
            background: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 5px;
        }
        a.btn:hover {
            background: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Quản lý câu hỏi theo khóa học</h2>

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

        <!-- Chọn bài test -->
        <?php if ($id_khoa > 0): ?>
        <form method="get">
            <input type="hidden" name="id_khoa" value="<?= $id_khoa ?>">
            <label>Chọn bài test:</label>
            <select name="id_test" onchange="this.form.submit()">
                <option value="0">-- Chọn --</option>
                <?php foreach ($tests as $t): ?>
                    <option value="<?= $t['id_test'] ?>" <?= $t['id_test'] == $id_test ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['ten_test']) ?> (Lần <?= $t['lan_thu'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>

        <!-- Danh sách câu hỏi -->
        <?php if ($id_test > 0): ?>
        <h3>Danh sách câu hỏi</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Câu hỏi</th>
                <th>Đáp án</th>
                <th>Thao tác</th>
            </tr>
            <?php foreach ($cau_hoi as $ch): ?>
            <tr>
                <td><?= $ch['Id_cauhoi'] ?></td>
                <td><?= htmlspecialchars($ch['cauhoi']) ?></td>
                <td><?= strtoupper($ch['dap_an']) ?></td>
                <td>
                    <a class="btn" href="xem_cauhoi.php?id=<?= $ch['Id_cauhoi'] ?>">Xem</a>
                    <a class="btn" href="sua_cauhoi.php?id=<?= $ch['Id_cauhoi'] ?>">Sửa</a>
                    <a class="btn" href="xoa_cauhoi.php?id=<?= $ch['Id_cauhoi'] ?>" onclick="return confirm('Xác nhận xóa câu hỏi?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>
