<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "study";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Khởi tạo các biến thông báo
$success_message = '';
$error_message = '';

// Lấy id_khoa từ URL
$id_khoa = isset($_GET['id_khoa']) ? (int)$_GET['id_khoa'] : 0;

// Xử lý xóa bài kiểm tra
if (isset($_GET['delete_test']) && $id_khoa > 0) {
    $id_test = (int)$_GET['delete_test'];
    $stmt = $conn->prepare("DELETE FROM test WHERE id_test = ? AND id_khoa = ?");
    if (!$stmt) {
        $error_message = "<p>Lỗi chuẩn bị truy vấn xóa: " . $conn->error . "</p>";
    } else {
        $stmt->bind_param("ii", $id_test, $id_khoa);
        if ($stmt->execute()) {
            $success_message = "<p>Xóa bài kiểm tra thành công!</p>";
            header("Location: " . $_SERVER['PHP_SELF'] . "?id_khoa=" . $id_khoa);
            exit();
        } else {
            $error_message = "<p>Lỗi khi xóa: " . $conn->error . "</p>";
        }
        $stmt->close();
    }
}


// Xử lý sửa bài kiểm tra
$editing = false;
$edit_test = null;
if (isset($_GET['edit_test']) && $id_khoa > 0) {
    $id_test = (int)$_GET['edit_test'];
    $stmt = $conn->prepare("SELECT id_test, ten_test, lan_thu FROM test WHERE id_test = ? AND id_khoa = ?");
    if (!$stmt) {
        $error_message = "<p>Lỗi chuẩn bị truy vấn sửa: " . $conn->error . "</p>";
    } else {
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

// Xử lý cập nhật bài kiểm tra
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_test']) && $id_khoa > 0) {
    $id_test = (int)$_POST['id_test'];
    $ten_test = trim($_POST['ten_test']);
    $lan_thu = isset($_POST['lan_thu']) ? (int)$_POST['lan_thu'] : 1;

    if (empty($ten_test)) {
        $error_message = "<p>Lỗi: Vui lòng nhập tên bài kiểm tra!</p>";
    } elseif ($lan_thu < 1) {
        $error_message = "<p>Lỗi: Lần thứ phải là số dương!</p>";
    } else {
        $stmt = $conn->prepare("UPDATE test SET ten_test = ?, lan_thu = ? WHERE id_test = ? AND id_khoa = ?");
        if (!$stmt) {
            $error_message = "<p>Lỗi chuẩn bị truy vấn cập nhật: " . $conn->error . "</p>";
        } else {
            $stmt->bind_param("siii", $ten_test, $lan_thu, $id_test, $id_khoa);
            if ($stmt->execute()) {
                $success_message = "<p>Cập nhật bài kiểm tra thành công!</p>";
                header("Location: " . $_SERVER['PHP_SELF'] . "?id_khoa=" . $id_khoa);
                exit();
            } else {
                $error_message = "<p>Lỗi khi cập nhật: " . $conn->error . "</p>";
            }
            $stmt->close();
        }
    }
}

// Lấy thông tin khóa học
$khoa_hoc = null;
if ($id_khoa > 0) {
    $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
    if (!$stmt) {
        $error_message = "<p>Lỗi chuẩn bị truy vấn: " . $conn->error . "</p>";
    } else {
        $stmt->bind_param("i", $id_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $khoa_hoc = $result->fetch_assoc()['khoa_hoc'];
        } else {
            $error_message = "<p>Khóa học không tồn tại với ID: $id_khoa.</p>";
        }
        $stmt->close();
    }
} else {
    $error_message = "<p>Lỗi: Không có ID khóa học được cung cấp. Vui lòng chọn khóa học từ danh sách.</p>";
}

// Xử lý thêm bài kiểm tra
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_test']) && $id_khoa > 0 && $khoa_hoc) {
    $ten_test = trim($_POST['ten_test']);
    $lan_thu = isset($_POST['lan_thu']) ? (int)$_POST['lan_thu'] : 1;

    if (empty($ten_test)) {
        $error_message = "<p>Lỗi: Vui lòng nhập tên bài kiểm tra!</p>";
    } elseif ($lan_thu < 1) {
        $error_message = "<p>Lỗi: Lần thứ phải là số dương!</p>";
    } else {
        $sql = "INSERT INTO test (id_khoa, ten_test, lan_thu) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_message = "<p>Lỗi chuẩn bị truy vấn: " . $conn->error . "</p>";
        } else {
            $stmt->bind_param("isi", $id_khoa, $ten_test, $lan_thu);
            if ($stmt->execute()) {
                $success_message = "<p>Thêm bài kiểm tra thành công!</p>";
                header("Location: " . $_SERVER['PHP_SELF'] . "?id_khoa=" . $id_khoa);
                exit();
            } else {
                $error_message = "<p>Lỗi khi thêm: " . $conn->error . "</p>";
            }
            $stmt->close();
        }
    }
}

// Lấy danh sách bản ghi từ bảng test, lọc theo id_khoa
$result = null;
if ($id_khoa > 0 && $khoa_hoc) {
    $sql = "SELECT t.id_test, t.id_khoa, t.ten_test, t.lan_thu, k.khoa_hoc 
            FROM test t 
            LEFT JOIN khoa_hoc k ON t.id_khoa = k.id 
            WHERE t.id_khoa = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error_message = "<p>Lỗi chuẩn bị truy vấn: " . $conn->error . "</p>";
    } else {
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
    <title>Nhập và hiển thị dữ liệu Test - <?php echo htmlspecialchars($khoa_hoc ?? 'Không xác định'); ?></title>
   
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
                    <!-- <th>Số câu </th> -->
                    <th>Hành động</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_test']); ?></td>
                        <td><?php echo htmlspecialchars($row['khoa_hoc'] ?? 'Không xác định'); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_test']); ?></td>
                        <td><?php echo htmlspecialchars($row['lan_thu']); ?></td>
                        <!-- <td><?php echo htmlspecialchars($row['so_câu'])?></td> -->

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
<style>
        /* Reset and Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 20px auto;
            max-width: 1100px;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            color: #333;
            line-height: 1.6;
            /* padding: 15px; */
        }

        /* Typography */
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

        /* Form Styles */
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

        /* Button Styles */
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
        }

        .back-button:hover {
            background-color:  #e3f2fd;
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

        /* Table Styles */
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

        /* Responsive Styles */
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

<?php
$conn->close();
?>