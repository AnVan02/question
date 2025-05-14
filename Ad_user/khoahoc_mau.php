<?php
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

// lấy id_khoa từ URL 

$id_khoa = isset ($_GET ['id_khoa']) ? (int)$_GET['id_khoa']:0;

// Xử lý khi form được gửi (Thêm hoặc Sửa)
$success_message = '';
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_test = $_POST['ten_test'];
    $lan_thu = isset($_POST['lan_thu']) ? (int)$_POST['lan_thu'] : 1; // Nếu không có lan_thu, mặc định là 1
    $id_khoa = 1; // Giá trị mặc định, thay đổi nếu cần

    if (isset($_POST['id_test']) && !empty($_POST['id_test'])) {
        // Sửa bản ghi
        $id_test = (int)$_POST['id_test'];
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
        $success_message = "<p>Thao tác thành công!</p>";
        $stmt->close();
        // Chuyển hướng để tránh resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "<p>Lỗi: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Xử lý xóa bản ghi
if (isset($_GET['delete'])) {
    $id_test = (int)$_GET['delete'];
    $sql = "DELETE FROM test WHERE id_test = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_test);

    if ($stmt->execute()) {
        $success_message = "<p>Xóa thành công!</p>";
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "<p>Lỗi khi xóa: " . $stmt->error . "</p>";
    }

    $stmt->close();
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

// Lấy dữ liệu để sửa (nếu có)
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id_test = (int)$_GET['edit'];
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
    <title>Nhập dữ liệu bài test</title>
    
</head>
<body>
    <h2><?php echo $edit_mode ? 'Sửa dữ liệu Test' : 'Nhập dữ liệu bài Test'; ?></h2>
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
            <label for="id_khoa">Tên khoá</label>
            <input type="number" id="id_khoa" name="id_khoa" value="<?php echo $edit_mode ? htmlspecialchars ($edit_data['id_khoa']) :'1' ?>"min="1" require>
        </div>
        
        <!-- <div class="form-group">
            <label for="lan_thu">Lần thứ:</label>
            <input type="number" id="lan_thu" name="lan_thu" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['lan_thu']) : '1'; ?>" min="1" required>
        </div> -->
        
        <button type="submit"><?php echo $edit_mode ? 'Cập nhật' : 'Thêm'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="cancel-button">Hủy</a>
        <?php endif; ?>
    </form>


    <h2>Danh sách bài Test</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID Test</th>
                <th>ID Khoa</th>
                <th>Tên Test</th>
                <!-- <th>Lần thứ</th> -->
                <th>Hành động</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_test']); ?></td>
                    <td><?php echo htmlspecialchars($row['id_khoa']); ?></td>
                    <td><?php echo htmlspecialchars($row['ten_test']); ?></td>
                    <!-- <td><?php echo htmlspecialchars($row['lan_thu']); ?></td> -->
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
            max-width: 900px;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            color: #333;
            line-height: 1.6;
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

        input[type="text"] {
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
            background-color: #ecc94b;
            color: #744210;
        }

        .edit-button:hover {
            background-color: #d69e2e;
            transform: translateY(-1px);
        }

        .delete-button {
            background-color: #e53e3e;
            color: #fff;
        }

        .delete-button:hover {
            background-color: #c53030;
            transform: translateY(-1px);
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
            background-color: #718096;
            color: #fff;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #4a5568;
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

            input[type="text"] {
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