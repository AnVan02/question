<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// Xử lý thêm khóa học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_khoa_hoc'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<div style='color:red;'>Lỗi bảo mật: CSRF token không hợp lệ!</div>";
    } else {
        $ten_khoa_hoc = trim($_POST['ten_khoa_hoc']);
        if (empty($ten_khoa_hoc)) {
            $message = "<div style='color:red;'>Vui lòng nhập tên khóa học!</div>";
        } else {
            $sql = "INSERT INTO khoa_hoc (khoa_hoc) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $ten_khoa_hoc);
            if ($stmt->execute()) {
                $message = "<div style='color:green;'>Thêm khóa học thành công!</div>";
            } else {
                $message = "<div style='color:red;'>Lỗi khi thêm khóa học: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Xử lý sửa khóa học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_khoa_id'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<div style='color:red;'>Lỗi bảo mật: CSRF token không hợp lệ!</div>";
    } else {
        $id_khoa = (int)$_POST['edit_khoa_id'];
        $ten_khoa_hoc = trim($_POST['ten_khoa_hoc']);
        if (empty($ten_khoa_hoc)) {
            $message = "<div style='color:red;'>Vui lòng nhập tên khóa học!</div>";
        } else {
            $sql = "UPDATE khoa_hoc SET khoa_hoc = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $ten_khoa_hoc, $id_khoa);
            if ($stmt->execute()) {
                if ($stmt->affected_rows === 0) {
                    $message = "<div style='color:red;'>Không tìm thấy khóa học để cập nhật!</div>";
                } else {
                    $message = "<div style='color:green;'>Cập nhật khóa học thành công!</div>";
                }
            } else {
                $message = "<div style='color:red;'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Xử lý xóa khóa học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_khoa_id'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<div style='color:red;'>Lỗi bảo mật: CSRF token không hợp lệ!</div>";
    } else {
        $id_khoa = (int)$_POST['delete_khoa_id'];
        $sql = "DELETE FROM khoa_hoc WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_khoa);
        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                $message = "<div style='color:red;'>Không tìm thấy khóa học để xóa!</div>";
            } else {
                $message = "<div style='color:green;'>Xóa khóa học thành công!</div>";
            }
        } else {
            $message = "<div style='color:red;'>Lỗi khi xóa: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Xử lý thêm bài test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_test'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<div style='color:red;'>Lỗi bảo mật: CSRF token không hợp lệ!</div>";
    } else {
        $id_khoa = (int)$_POST['id_khoa'];
        $ten_test = trim($_POST['ten_test']);

        if (empty($ten_test) || $id_khoa <= 0) {
            $message = "<div style='color:red;'>Vui lòng nhập đầy đủ và hợp lệ!</div>";
        } else {
            // Đếm số bài test hiện có để gán lan_thu
            $sql_count = "SELECT COUNT(*) AS count FROM test WHERE id_khoa = ?";
            $stmt_count = $conn->prepare($sql_count);
            $stmt_count->bind_param("i", $id_khoa);
            $stmt_count->execute();
            $count = $stmt_count->get_result()->fetch_assoc()['count'];
            $lan_thu = $count + 1;
            $stmt_count->close();

            $sql = "INSERT INTO test (id_khoa, ten_test, lan_thu) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $id_khoa, $ten_test, $lan_thu);

            if ($stmt->execute()) {
                $message = "<div style='color:green;'>Thêm bài test thành công!</div>";
            } else {
                $message = "<div style='color:red;'>Lỗi khi thêm bài test: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Xử lý cập nhật lần thử
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<div style='color:red;'>Lỗi bảo mật: CSRF token không hợp lệ!</div>";
    } else {
        $id_test = (int)$_POST['update_id'];
        $lan_thu = (int)$_POST['lan_thu'];

        if ($lan_thu <= 0) {
            $message = "<div style='color:red;'>Số thứ tự phải lớn hơn 0!</div>";
        } else {
            $sql = "UPDATE test SET lan_thu = ? WHERE id_test = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $lan_thu, $id_test);

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 0) {
                    $message = "<div style='color:red;'>Không tìm thấy bài test để cập nhật!</div>";
                } else {
                    // Cập nhật lại lan_thu cho các bài test khác trong cùng id_khoa
                    $sql_khoa = "SELECT id_khoa FROM test WHERE id_test = ?";
                    $stmt_khoa = $conn->prepare($sql_khoa);
                    $stmt_khoa->bind_param("i", $id_test);
                    $stmt_khoa->execute();
                    $id_khoa = $stmt_khoa->get_result()->fetch_assoc()['id_khoa'];
                    $stmt_khoa->close();

                    $sql_update = "
                        UPDATE test t
                        JOIN (
                            SELECT id_test, ROW_NUMBER() OVER (ORDER BY id_test) AS new_lan_thu
                            FROM test
                            WHERE id_khoa = ?
                        ) AS ranked
                        ON t.id_test = ranked.id_test
                        SET t.lan_thu = ranked.new_lan_thu
                        WHERE t.id_khoa = ?
                    ";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ii", $id_khoa, $id_khoa);
                    $stmt_update->execute();
                    $stmt_update->close();

                    $message = "<div style='color:green;'>Cập nhật số thứ tự thành công!</div>";
                }
            } else {
                $message = "<div style='color:red;'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Xử lý xóa bài test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<div style='color:red;'>Lỗi bảo mật: CSRF token không hợp lệ!</div>";
    } else {
        $id_test = (int)$_POST['delete_id'];

        // Lấy id_khoa trước khi xóa
        $sql_khoa = "SELECT id_khoa FROM test WHERE id_test = ?";
        $stmt_khoa = $conn->prepare($sql_khoa);
        $stmt_khoa->bind_param("i", $id_test);
        $stmt_khoa->execute();
        $id_khoa = $stmt_khoa->get_result()->fetch_assoc()['id_khoa'];
        $stmt_khoa->close();

        $sql = "DELETE FROM test WHERE id_test = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_test);

        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                $message = "<div style='color:red;'>Không tìm thấy bài test để xóa!</div>";
            } else {
                // Cập nhật lại lan_thu cho các bài test còn lại
                $sql_update = "
                    UPDATE test t
                    JOIN (
                        SELECT id_test, ROW_NUMBER() OVER (ORDER BY id_test) AS new_lan_thu
                        FROM test
                        WHERE id_khoa = ?
                    ) AS ranked
                    ON t.id_test = ranked.id_test
                    SET t.lan_thu = ranked.new_lan_thu
                    WHERE t.id_khoa = ?
                ";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ii", $id_khoa, $id_khoa);
                $stmt_update->execute();
                $stmt_update->close();

                $message = "<div style='color:green;'>Xóa bài test thành công!</div>";
            }
        } else {
            $message = "<div style='color:red;'>Lỗi khi xóa: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Lấy thông tin khóa học từ bảng khoa_hoc
function getCoursesFromDB() {
    $conn = dbconnect();
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    $conn->close();
    return $courses;
}

// Lấy danh sách khóa học
$sql_khoa = "
    SELECT k.id, k.khoa_hoc, IFNULL(SUM(t.lan_thu), 0) AS tong_lan_thu,
           COUNT(t.id_test) AS so_bai_test,
           (SELECT id_test FROM test WHERE id_khoa = k.id ORDER BY id_test LIMIT 1) AS id_test,
           (SELECT lan_thu FROM test WHERE id_khoa = k.id ORDER BY id_test LIMIT 1) AS lan_thu
    FROM khoa_hoc k
    LEFT JOIN test t ON k.id = t.id_khoa
    GROUP BY k.id, k.khoa_hoc
    ORDER BY k.id
";
$result_khoa = $conn->query($sql_khoa);
if (!$result_khoa) {
    die("Lỗi truy vấn khóa học: " . $conn->error);
}
$khoa_hoc = [];
while ($row = $result_khoa->fetch_assoc()) {
    $khoa_hoc[] = $row;
}

// Debug: In dữ liệu để kiểm tra
if (empty($khoa_hoc)) {
    $message .= "<div style='color:orange;'>Không tìm thấy dữ liệu trong bảng khoa_hoc. Vui lòng kiểm tra cơ sở dữ liệu!</div>";
}

// Lấy danh sách bài test
$sql_test = "
    SELECT t.id_test, t.id_khoa, t.ten_test, t.lan_thu, k.khoa_hoc
    FROM test t
    INNER JOIN khoa_hoc k ON t.id_khoa = k.id
    ORDER BY k.id, t.id_test
";
$result_test = $conn->query($sql_test);
if (!$result_test) {
    die("Lỗi truy vấn bài test: " . $conn->error);
}
$tests = [];
while ($row = $result_test->fetch_assoc()) {
    $tests[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khóa học và Bài test</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; cursor: pointer; }
        .btn-disabled { padding: 5px 10px; cursor: not-allowed; opacity: 0.5; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 50px auto; padding: 20px; width: 300px; }
        form { margin: 10px 0; }
        select, input[type="text"], input[type="number"] { width: 100%; padding: 5px; margin: 5px 0; }
    </style>
</head>
<body>

<h2>Quản lý Khóa học và Bài test</h2>

<?php echo $message; ?>

<!-- Biểu mẫu thêm khóa học -->
<h3>Thêm Khóa học mới</h3>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <label>Tên Khóa học:</label><br>
    <input type="text" name="ten_khoa_hoc" required><br>
    <button type="submit" name="add_khoa_hoc">Thêm</button>
</form>

<!-- Biểu mẫu thêm bài test -->
<h3>Thêm Bài test mới</h3>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <label>Khóa học:</label><br>
    <select name="id_khoa" required>
        <?php
        $courses = getCoursesFromDB();
        foreach ($courses as $id => $ten_khoa): ?>
            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($ten_khoa); ?></option>
        <?php endforeach; ?>
    </select><br>
    <label>Tên bài test:</label><br>
    <input type="text" name="ten_test" required><br>
    <button type="submit" name="add_test">Thêm</button>
</form>

<!-- Hiển thị danh sách khóa học -->
<h3>Danh sách Khóa học</h3>
<table>
    <tr>
        <th>ID Khóa học</th>
        <th>Tên Khóa học</th>
        <th>Số bài test</th>
        <th>Tổng số thứ tự</th>
        <th>Số thứ tự (bài test đầu tiên)</th>
        <th>Hành động</th>
    </tr>
    <?php if (empty($khoa_hoc)): ?>
        <tr>
            <td colspan="6">Không có khóa học nào để hiển thị.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($khoa_hoc as $khoa): ?>
        <tr>
            <td><?php echo $khoa['id']; ?></td>
            <td><?php echo htmlspecialchars($khoa['khoa_hoc']); ?></td>
            <td><?php echo $khoa['so_bai_test']; ?></td>
            <td><?php echo $khoa['tong_lan_thu']; ?></td>
            <td><?php echo $khoa['lan_thu'] !== null ? $khoa['lan_thu'] : 'Không có bài test'; ?></td>
            <td>
                <button class="btn" onclick="editKhoaHoc(<?php echo $khoa['id']; ?>, '<?php echo htmlspecialchars($khoa['khoa_hoc'], ENT_QUOTES); ?>')">Sửa</button>
                <button class="btn" onclick="deleteKhoaHoc(<?php echo $khoa['id']; ?>)">Xóa</button>
                <?php if ($khoa['so_bai_test'] > 0): ?>
                    <button class="btn" onclick="editLanThu(<?php echo $khoa['id_test']; ?>, <?php echo $khoa['lan_thu']; ?>)">Sửa số thứ tự</button>
                <?php else: ?>
                    <button class="btn-disabled" disabled>Không có bài test</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<!-- Hiển thị danh sách bài test -->
<h3>Danh sách Bài test</h3>
<table>
    <tr>
        <th>ID Test</th>
        <th>Khóa học</th>
        <th>Tên bài test</th>
        <th>Số thứ tự</th>
        <th>Hành động</th>
    </tr>
    <?php if (empty($tests)): ?>
        <tr>
            <td colspan="5">Không có bài test nào để hiển thị.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($tests as $test): ?>
        <tr>
            <td><?php echo $test['id_test']; ?></td>
            <td><?php echo htmlspecialchars($test['khoa_hoc']); ?></td>
            <td><?php echo htmlspecialchars($test['ten_test']); ?></td>
            <td><?php echo $test['lan_thu']; ?></td>
            <td>
                <button class="btn" onclick="editLanThu(<?php echo $test['id_test']; ?>, <?php echo $test['lan_thu']; ?>)">Sửa</button>
                <button class="btn" onclick="deleteLanThu(<?php echo $test['id_test']; ?>)">Xóa</button>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<!-- Modal sửa số thứ tự -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Sửa Số Thứ Tự</h3>
        <form id="editForm" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="update_id" id="update_id">
            <label>Số thứ tự:</label><br>
            <input type="number" name="lan_thu" id="edit_lan_thu" min="1" required><br><br>
            <button type="submit">Lưu</button>
            <button type="button" onclick="closeModal()">Hủy</button>
        </form>
    </div>
</div>

<!-- Modal sửa khóa học -->
<div id="editKhoaModal" class="modal">
    <div class="modal-content">
        <h3>Sửa Khóa học</h3>
        <form id="editKhoaForm" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="edit_khoa_id" id="edit_khoa_id">
            <label>Tên Khóa học:</label><br>
            <input type="text" name="ten_khoa_hoc" id="edit_ten_khoa_hoc" required><br><br>
            <button type="submit">Lưu</button>
            <button type="button" onclick="closeKhoaModal()">Hủy</button>
        </form>
    </div>
</div>

<script>
    function editLanThu(id_test, lan_thu) {
        document.getElementById('update_id').value = id_test;
        document.getElementById('edit_lan_thu').value = lan_thu;
        document.getElementById('editModal').style.display = 'block';
    }

    function deleteLanThu(id_test) {
        if (confirm('Bạn có chắc muốn xóa bài test này?')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = '';

            var inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'delete_id';
            inputId.value = id_test;
            form.appendChild(inputId);

            var inputCsrf = document.createElement('input');
            inputCsrf.type = 'hidden';
            inputCsrf.name = 'csrf_token';
            inputCsrf.value = '<?php echo $_SESSION['csrf_token']; ?>';
            form.appendChild(inputCsrf);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function editKhoaHoc(id_khoa, ten_khoa) {
        document.getElementById('edit_khoa_id').value = id_khoa;
        document.getElementById('edit_ten_khoa_hoc').value = ten_khoa;
        document.getElementById('editKhoaModal').style.display = 'block';
    }

    function deleteKhoaHoc(id_khoa) {
        if (confirm('Bạn có chắc muốn xóa khóa học này? Tất cả bài test liên kết sẽ bị xóa!')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = '';

            var inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'delete_khoa_id';
            inputId.value = id_khoa;
            form.appendChild(inputId);

            var inputCsrf = document.createElement('input');
            inputCsrf.type = 'hidden';
            inputCsrf.name = 'csrf_token';
            inputCsrf.value = '<?php echo $_SESSION['csrf_token']; ?>';
            form.appendChild(inputCsrf);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function closeKhoaModal() {
        document.getElementById('editKhoaModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) {
            closeModal();
        }
        if (event.target == document.getElementById('editKhoaModal')) {
            closeKhoaModal();
        }
    };
</script>

</body>
</html>