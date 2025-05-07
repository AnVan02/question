<?php
// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();
$message = "";

// Lấy danh sách khóa học
$khoa_hoc = [];
$sql_khoa = "SELECT id, khoa_hoc FROM khoa_hoc";
$result_khoa = $conn->query($sql_khoa);
while ($row = $result_khoa->fetch_assoc()) {
    $khoa_hoc[$row['id']] = $row['khoa_hoc'];
}

// Lấy danh sách bài test
$tests = [];
$sql_test = "SELECT id_test, id_khoa, ten_test, lan_thu FROM test";
$result_test = $conn->query($sql_test);
while ($row = $result_test->fetch_assoc()) {
    $tests[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý lần thử bài test & Khóa học</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { margin: 10px 0; }
        .btn { padding: 5px 10px; cursor: pointer; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 50px auto; padding: 20px; width: 300px; }
    </style>
</head>
<body>
    <h2>Quản lý lần thử bài test</h2>

    <!-- Hiển thị thông báo -->
    <?php echo $message; ?>

    <!-- Hiển thị danh sách bài test -->
    <table>
        <tr>
            <th>ID Test</th>
            <th>Khóa học</th>
            <th>Tên Test</th>
            <th>Lần thử</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($tests as $test): ?>
        <tr>
            <td><?php echo $test['id_test']; ?></td>
            <td><?php echo htmlspecialchars($khoa_hoc[$test['id_khoa']]); ?></td>
            <td><?php echo htmlspecialchars($test['ten_test']); ?></td>
            <td><?php echo $test['lan_thu']; ?></td>
            <td>
                <button class="btn" onclick="editTest(<?php echo $test['id_test']; ?>, <?php echo $test['lan_thu']; ?>)">Sửa</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Modal sửa lần thử -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Sửa lần thử</h3>
            <form id="editForm" method="post">
                <input type="hidden" name="update_id" id="update_id">
                <label>Lần thử:</label><br>
                <input type="number" name="lan_thu" id="edit_lan_thu" min="1" required><br>
                <button type="submit">Lưu</button>
                <button type="button" onclick="closeModal()">Hủy</button>
            </form>
        </div>
    </div>

    <script>
        function editTest(id, lan_thu) {
            document.getElementById('update_id').value = id;
            document.getElementById('edit_lan_thu').value = lan_thu;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        };
    </script>

    <!-- Hiển thị danh sách Khóa học -->
    <h2>Danh sách Khóa học</h2>
    <table>
        <tr>
            <th>ID Khóa học</th>
            <th>Tên Khóa học</th>
        </tr>
        <?php foreach ($khoa_hoc as $id => $tenKhoa): ?>
        <tr>
            <td><?php echo $id; ?></td>
            <td><?php echo htmlspecialchars($tenKhoa); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
