<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$success_message = '';
$error_message = '';
$id_khoa = isset($_GET['id_khoa']) ? (int)$_GET['id_khoa'] : 0;

// Xử lý xóa bài kiểm tra
if (isset($_GET['delete_test']) && $id_khoa > 0) {
    $id_test = (int)$_GET['delete_test'];
    $stmt = $conn->prepare("DELETE FROM test WHERE id_test = ? AND id_khoa = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $id_test, $id_khoa);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?id_khoa=" . $id_khoa);
            exit();
        } else {
            $error_message = "<p>Lỗi khi xóa: " . $conn->error . "</p>";
        }
        $stmt->close();
    }
}

$editing = false;
$edit_test = null;
if (isset($_GET['edit_test']) && $id_khoa > 0) {
    $id_test = (int)$_GET['edit_test'];
    $stmt = $conn->prepare("SELECT id_test, ten_test, lan_thu, pass, so_cau_hien_thi FROM test WHERE id_test = ? AND id_khoa = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $id_test, $id_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $edit_test = $result->fetch_assoc();
            $editing = true;
        } else {
            $error_message = "<p>Bài kiểm tra không tồn tại hoặc không thuộc khóa học này.</p>";
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_test']) && $id_khoa > 0) {
    $id_test = (int)$_POST['id_test'];
    $ten_test = trim($_POST['ten_test']);
    $pass = trim($_POST['pass']);
    $lan_thu = (int)$_POST['lan_thu'];
    $so_cau_hien_thi = (int)$_POST['so_cau_hien_thi'];

    // Validate dữ liệu
    if (empty($ten_test)) {
        $error_message = "<p>Lỗi: Tên bài test không được để trống!</p>";
    } elseif (!preg_match('/^[0-9]+%?$/', $pass) || (int)$pass < 0 || (int)$pass > 100) {
        $error_message = "<p>Lỗi: Pass phải là số từ 0 đến 100 (có thể kèm %).</p>";
    } elseif ($lan_thu < 1) {
        $error_message = "<p>Lỗi: Lần thứ phải là số nguyên dương.</p>";
    } elseif ($so_cau_hien_thi < 0) {
        $error_message = "<p>Lỗi: Số câu hiển thị phải là số không âm.</p>";
    } else {
        $test_type = (stripos($ten_test, 'Giữa kỳ') !== false) ? 'Giữa kỳ' : 'Cuối kỳ';
        $sql_count = "SELECT COUNT(q.Id_cauhoi) as so_cau FROM quiz q INNER JOIN khoa_hoc k ON LOWER(TRIM(q.ten_khoa)) = LOWER(TRIM(k.khoa_hoc)) WHERE k.id = ? AND q.id_baitest = ?";
        $stmt_count = $conn->prepare($sql_count);
        $so_cau = 0;
        if ($stmt_count) {
            $stmt_count->bind_param("is", $id_khoa, $test_type);
            $stmt_count->execute();
            $result_count = $stmt_count->get_result();
            if ($row_count = $result_count->fetch_assoc()) {
                $so_cau = (int)$row_count['so_cau'];
            }
            $stmt_count->close();
        }

        if ($so_cau == 0 && $so_cau_hien_thi > 0) {
            $error_message = "<p>Lỗi: Chưa có câu hỏi nào cho loại bài test này. Vui lòng đặt số câu hiển thị là 0 hoặc thêm câu hỏi trước.</p>";
        } elseif ($so_cau_hien_thi > $so_cau) {
            $error_message = "<p>Lỗi: Số câu hiển thị ($so_cau_hien_thi) vượt quá số câu hỏi có sẵn ($so_cau)!</p>";
        } else {
            $stmt = $conn->prepare("UPDATE test SET ten_test = ?, lan_thu = ?, pass = ?, so_cau_hien_thi = ? WHERE id_test = ? AND id_khoa = ?");
            if ($stmt) {
                $stmt->bind_param("sisiii", $ten_test, $lan_thu, $pass, $so_cau_hien_thi, $id_test, $id_khoa);
                if ($stmt->execute()) {
                    $success_message = "<p>Cập nhật bài test thành công!</p>";
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id_khoa=" . $id_khoa);
                    exit();
                } else {
                    $error_message = "<p>Lỗi khi cập nhật: " . $conn->error . "</p>";
                }
                $stmt->close();
            }
        }
    }
}

$khoa_hoc = null;
if ($id_khoa > 0) {
    $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $khoa_hoc = $result->fetch_assoc()['khoa_hoc'];
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_test']) && $id_khoa > 0 && $khoa_hoc) {
    $ten_test = trim($_POST['ten_test']);
    $pass = trim($_POST['pass']);
    $lan_thu = (int)$_POST['lan_thu'];
    $so_cau_hien_thi = (int)$_POST['so_cau_hien_thi'];

    // Validate dữ liệu
    if (empty($ten_test)) {
        $error_message = "<p>Lỗi: Tên bài test không được để trống!</p>";
    } elseif (!preg_match('/^[0-9]+%?$/', $pass) || (int)$pass < 0 || (int)$pass > 100) {
        $error_message = "<p>Lỗi: Pass phải là số từ 0 đến 100 (có thể kèm %).</p>";
    } elseif ($lan_thu < 1) {
        $error_message = "<p>Lỗi: Lần thứ phải là số nguyên dương.</p>";
    } elseif ($so_cau_hien_thi < 0) {
        $error_message = "<p>Lỗi: Số câu hiển thị phải là số không âm.</p>";
    } else {
        $test_type = (stripos($ten_test, 'Giữa kỳ') !== false) ? 'Giữa kỳ' : 'Cuối kỳ';
        $sql_count = "SELECT COUNT(q.Id_cauhoi) as so_cau FROM quiz q INNER JOIN khoa_hoc k ON LOWER(TRIM(q.ten_khoa)) = LOWER(TRIM(k.khoa_hoc)) WHERE k.id = ? AND q.id_baitest = ?";
        $stmt_count = $conn->prepare($sql_count);
        $so_cau = 0;
        if ($stmt_count) {
            $stmt_count->bind_param("is", $id_khoa, $test_type);
            $stmt_count->execute();
            $result_count = $stmt_count->get_result();
            if ($row_count = $result_count->fetch_assoc()) {
                $so_cau = (int)$row_count['so_cau'];
            }
            $stmt_count->close();
        }

        if ($so_cau == 0 && $so_cau_hien_thi > 0) {
            $error_message = "<p>Lỗi: Chưa có câu hỏi nào cho loại bài test này. Vui lòng đặt số câu hiển thị là 0 hoặc thêm câu hỏi trước.</p>";
        } elseif ($so_cau_hien_thi > $so_cau) {
            $error_message = "<p>Lỗi: Số câu hiển thị ($so_cau_hien_thi) vượt quá số câu hỏi có sẵn ($so_cau)!</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO test (id_khoa, ten_test, lan_thu, pass, so_cau_hien_thi) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isisi", $id_khoa, $ten_test, $lan_thu, $pass, $so_cau_hien_thi);
                if ($stmt->execute()) {
                    $success_message = "<p>Bài test đã được thêm thành công!</p>";
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id_khoa=" . $id_khoa);
                    exit();
                } else {
                    $error_message = "<p>Lỗi khi thêm: " . $conn->error . "</p>";
                }
                $stmt->close();
            }
        }
    }
}

$result = null;
if ($id_khoa > 0 && $khoa_hoc) {
    $sql = "SELECT t.id_test, t.id_khoa, t.ten_test, t.lan_thu, t.pass, t.so_cau_hien_thi, k.khoa_hoc FROM test t LEFT JOIN khoa_hoc k ON t.id_khoa = k.id WHERE t.id_khoa = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập vàDiggs và hiển thị dữ liệu Test - <?php echo htmlspecialchars($khoa_hoc ?? 'Không xác định'); ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 20px auto;
            max-width: 1300px;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            color: #333;
            line-height: 1.6;
        }

        h2 {
            margin-bottom: 25px;
            color: #2d3748;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            padding: 10px;
            background-color: #edf2f7;
            border-radius: 8px;
        }

        p {
            margin: 15px 0;
            font-size: 15px;
            text-align: center;
        }

        p.success {
            color: #2f855a;
            background-color: #e6fff3;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #2f855a;
        }

        p.error {
            color: #c53030;
            background-color: #fff5f5;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #c53030;
        }

        form {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: transform 0.2s ease;
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
            color: #4a5568;
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background-color: #fff;
        }

        input:focus {
            border-color: #3182ce;
            box-shadow: 0 0 5px rgba(49, 130, 206, 0.2);
            outline: none;
        }

        button {
            padding: 10px 20px;
            background-color: #3182ce;
            border: none;
            color: #fff;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #2b6cb0;
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        .action-button, .edit-button, .delete-button {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            margin-right: 5px;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .action-button {
            background-color: #3182ce;
            color: #fff;
        }

        .action-button:hover {
            background-color: #2b6cb0;
            transform: translateY(-1px);
        }

        .edit-button {
            background-color: #e3f2fd;
            color: #0288d1;
        }

        .edit-button:hover {
            background-color: #e3f2fd;
            transform: translateY(-1px);
        }

        .delete-button {
            background-color: #ffebee;
            color: #c62828;
        }

        .action-button:active, .edit-button:active, .delete-button:active {
            transform: translateY(0);
        }

        .back-button, .cancel-button {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .back-button {
            background-color: #e3f2fd;
            color: #0288d1;
            font-size: 14px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #e3f2fd;
            transform: translateY(-1px);
        }

        .back-button:active {
            transform: translateY(0);
        }

        .cancel-button {
            background-color: #718096;
            color: #fff;
            font-size: 14px;
            margin-left: 10px;
        }

        .cancel-button:hover {
            background-color: #4a5568;
            transform: translateY(-1px);
        }

        .cancel-button:active {
            transform: translateY(0);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
        }

        th {
            background-color: #3182ce;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            font-size: 14px;
            color: #4a5568;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f7fafc;
        }

        @media (max-width: 600px) {
            body {
                margin: 10px;
                padding: 5px;
            }

            h2 {
                font-size: 20px;
            }

            form, table {
                padding: 15px;
            }

            input[type="text"],
            input[type="number"] {
                font-size: 13px;
                padding: 8px;
            }

            button, .action-button, .edit-button, .delete-button, .back-button, .cancel-button {
                font-size: 12px;
                padding: 6px 10px;
            }

            th, td {
                padding: 8px 10px;
                font-size: 12px;
            }

            .cancel-button {
                margin-left: 0;
                margin-top: 10px;
                display: block;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <h2><?php echo $editing ? 'Sửa bài kiểm tra' : 'Nhập dữ liệu bài test'; ?> - <?php echo htmlspecialchars($khoa_hoc ?? 'Không xác định'); ?></h2>
    <a href="add_khoahoc.php" class="back-button">Quay lại danh sách khóa học</a>

    <?php 
    if ($success_message) echo "<p class='success'>$success_message</p>";
    if ($error_message) echo "<p class='error'>$error_message</p>";
    ?>
    <?php if ($khoa_hoc && $id_khoa > 0): ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id_khoa=" . $id_khoa); ?>">
            <div class="form-group">
                <label for="ten_test">Tên Test:</label>
                <input type="text" id="ten_test" name="ten_test" maxlength="255" value="<?php echo $editing ? htmlspecialchars($edit_test['ten_test']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="lan_thu">Lần thứ:</label>
                <input type="number" id="lan_thu" name="lan_thu" value="<?php echo $editing ? htmlspecialchars($edit_test['lan_thu']) : '1'; ?>" min="1" required>
            </div>

            <div class="form-group">
                <label for="pass">Pass %:</label>
                <input type="text" id="pass" name="pass" value="<?php echo $editing ? htmlspecialchars($edit_test['pass']) : ''; ?>" required>
                <small style="color: #4a5568;">Nhập số từ 0 đến 100 (có thể kèm %)</small>
            </div>

            <div class="form-group">
                <label for="so_cau_hien_thi">Số câu hiển thị:</label>
                <input type="number" id="so_cau_hien_thi" name="so_cau_hien_thi" value="<?php echo $editing ? htmlspecialchars($edit_test['so_cau_hien_thi']) : '0'; ?>" min="0" required>
                <small style="color: #4a5568;">Đặt 0 nếu chưa có câu hỏi</small>
            </div>
            
            <?php if ($editing): ?>
                <input type="hidden" name="id_test" value="<?php echo htmlspecialchars($edit_test['id_test']); ?>">
                <button type="submit" name="update_test">Cập nhật</button>
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id_khoa=' . $id_khoa); ?>" class="cancel-button">Hủy</a>
            <?php else: ?>
                <button type="submit" name="add_test">Thêm</button>
            <?php endif; ?>
        </form>
        
        <h2>Danh sách bài test</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID Test</th>
                    <th>Khóa học</th>
                    <th>Tên Test</th>
                    <th>Lần thứ</th>
                    <th>Pass</th>
                    <th>Câu hỏi</th>
                    <th>Tổng số câu hỏi</th>
                    <th>Hành động</th>
                </tr>
                
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Xác định loại bài test (Giữa kỳ hoặc Cuối kỳ) dựa trên ten_test
                    $test_type = (stripos($row['ten_test'], 'Giữa kỳ') !== false) ? 'Giữa kỳ' : 'Cuối kỳ';
                    
                    // Đếm số câu hỏi có sẵn trong quiz
                    $so_cau = 0;
                    $sql_count = "SELECT COUNT(q.Id_cauhoi) as so_cau FROM quiz q INNER JOIN khoa_hoc k ON LOWER(TRIM(q.ten_khoa)) = LOWER(TRIM(k.khoa_hoc)) WHERE k.id = ? AND q.id_baitest = ?";
                    $stmt_count = $conn->prepare($sql_count);
                    if ($stmt_count) {
                        $stmt_count->bind_param("is", $id_khoa, $test_type);
                        $stmt_count->execute();
                        $result_count = $stmt_count->get_result();
                        if ($row_count = $result_count->fetch_assoc()) {
                            $so_cau = (int)$row_count['so_cau'];
                        }
                        $stmt_count->close();
                    } else {
                        $error_message = "<p>Lỗi chuẩn bị truy vấn đếm câu hỏi: " . $conn->error . "</p>";
                    }
                    ?>
                    
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_test']); ?></td>
                        <td><?php echo htmlspecialchars($row['khoa_hoc'] ?? 'Không xác định'); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_test']); ?></td>
                        <td><?php echo htmlspecialchars($row['lan_thu']); ?></td>
                        <td><?php echo htmlspecialchars($row['pass']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['so_cau_hien_thi'] . '/' . $so_cau); ?>
                            <?php if ($so_cau < $row['so_cau_hien_thi']) echo '<span style="color: red;">(Lỗi: Số câu hiển thị vượt quá số câu hỏi)</span>'; ?>
                            <?php if ($so_cau == 0) echo '<span style="color: red;">(Chưa có câu hỏi)</span>'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($so_cau); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id_khoa=' . $id_khoa . '&edit_test=' . $row['id_test']); ?>" class="edit-button">Sửa</a>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id_khoa=' . $id_khoa . '&delete_test=' . $row['id_test']); ?>" class="delete-button" onclick="return confirm('Bạn có chắc chắn muốn xóa bài kiểm tra này?')">Xóa</a>
                            <a href="question.php?id_test=<?php echo htmlspecialchars($row['id_test']); ?>" class="action-button">Xem câu hỏi</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Chưa có dữ liệu trong bảng Test cho khóa học này.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Vui lòng chọn một khóa học hợp lệ từ trang danh sách khóa học.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>