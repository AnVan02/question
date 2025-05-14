<?php
// Bật hiển thị lỗi để gỡ lỗi (xóa dòng này trong môi trường sản xuất)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Hàm kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Kết nối cơ sở dữ liệu
$conn = dbconnect();
$message = isset($_GET['message']) ? urldecode($_GET['message']) : "";
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : "";

// Xử lý khi submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $student_id = $_POST['student_id'];
        $khoa_id = $_POST['khoa_id'];
        $test_id = $_POST['test_id'];
        
        $stmt = $conn->prepare("DELETE FROM kiem_tra WHERE Studen_ID = ? AND Khoa_ID = ? AND Test_ID = ?");
        $stmt->bind_param("iii", $student_id, $khoa_id, $test_id);
        if ($stmt->execute()) {
            $message = "Xóa kết quả kiểm tra thành công!";
        } else {
            $message = "Lỗi khi xóa: " . $conn->error;
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update') {
        $student_id = $_POST['student_id'];
        $khoa_id = $_POST['khoa_id'];
        $test_id = $_POST['test_id'];
        $best_score = $_POST['best_score'];
        $max_score = $_POST['max_score'];
        $pass = $_POST['pass'];
        $tral = $_POST['tral'];
        $max_tral = $_POST['max_tral'];

        $stmt = $conn->prepare("UPDATE kiem_tra SET Best_Scone = ?, Max_Scone = ?, Pass = ?, Tral = ?, Max_tral = ? WHERE Studen_ID = ? AND Khoa_ID = ? AND Test_ID = ?");
        $stmt->bind_param("sssssiii", $best_score, $max_score, $pass, $tral, $max_tral, $student_id, $khoa_id, $test_id);
        if ($stmt->execute()) {
            $message = "Cập nhật thành công!";
        } else {
            $message = "Lỗi khi cập nhật: " . $conn->error;
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
        $student_id = $_POST['student_id'];
        $khoa_id = $_POST['khoa_id'];
        $test_id = $_POST['test_id'];
        $best_score = $_POST['best_score'];
        $max_score = $_POST['max_score'];
        $pass = $_POST['pass'];
        $tral = $_POST['tral'];
        $max_tral = $_POST['max_tral'];

        // Kiểm tra bản ghi đã tồn tại
        $stmt = $conn->prepare("SELECT * FROM kiem_tra WHERE Studen_ID = ? AND Khoa_ID = ? AND Test_ID = ?");
        $stmt->bind_param("iii", $student_id, $khoa_id, $test_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $message = "Lỗi: Bản ghi này đã tồn tại!";
        } else {
            $stmt = $conn->prepare("INSERT INTO kiem_tra (Studen_ID, Khoa_ID, Test_ID, Best_Scone, Max_Scone, Pass, Tral, Max_tral) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiisssss", $student_id, $khoa_id, $test_id, $best_score, $max_score, $pass, $tral, $max_tral);
            if ($stmt->execute()) {
                $message = "Thêm kết quả kiểm tra thành công!";
            } else {
                $message = "Lỗi khi thêm: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Kiểm tra chế độ chỉnh sửa
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$khoa_id = isset($_GET['khoa_id']) ? $_GET['khoa_id'] : '';
$test_id = isset($_GET['test_id']) ? $_GET['test_id'] : '';
$kiem_tra_data = [];

if ($mode == 'edit' && $student_id && $khoa_id && $test_id) {
    $stmt = $conn->prepare("SELECT * FROM kiem_tra WHERE Studen_ID = ? AND Khoa_ID = ? AND Test_ID = ?");
    $stmt->bind_param("iii", $student_id, $khoa_id, $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $kiem_tra_data = $result->fetch_assoc();
    } else {
        $message = "Không tìm thấy kết quả kiểm tra!";
    }
    $stmt->close();
}

// Lấy thông tin sinh viên
$student_info = null;
if ($student_id) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student_info = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Kết Quả Kiểm Tra</title>
</head>
<body>
    <div class="header">
        <h2>Quản Lý Kết Quả Kiểm Tra</h2>
        <?php if ($student_info): ?>
            <p class="student-info">
                Sinh viên: <?php echo htmlspecialchars($student_info['Ten']); ?> 
                (ID: <?php echo htmlspecialchars($student_info['Student_ID']); ?>)
            </p>
        <?php endif; ?>
        <a href="student.php" class="back-button">← Quay lại danh sách sinh viên</a>
    </div>

    <?php if ($mode == 'edit' && !empty($kiem_tra_data)): ?>
        <!-- Form chỉnh sửa kết quả kiểm tra -->
        <h3>Sửa Kết Quả Kiểm Tra</h3>

        <?php if (!empty($message)): ?>
            <p class="<?php echo strpos($message, 'Lỗi') === false ? 'message' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
            <input type="hidden" name="khoa_id" value="<?php echo htmlspecialchars($khoa_id); ?>">
            <input type="hidden" name="test_id" value="<?php echo htmlspecialchars($test_id); ?>">
            
            <div class="form-container">
                <div class="form-left">
                    <label>Student ID</label>
                    <input type="text" value="<?php echo htmlspecialchars($kiem_tra_data['Studen_ID']); ?>" readonly>
                    <label>Khóa ID</label>
                    <input type="number" value="<?php echo htmlspecialchars($kiem_tra_data['Khoa_ID']); ?>" readonly>
                    <label>Test ID</label>
                    <input type="number" value="<?php echo htmlspecialchars($kiem_tra_data['Test_ID']); ?>" readonly>
                </div>
                <div class="form-right">
                    <label>Điểm tốt nhất</label>
                    <input type="text" name="best_score" value="<?php echo htmlspecialchars($kiem_tra_data['Best_Scone']); ?>" required>
                    <label>Điểm tối đa</label>
                    <input type="text" name="max_score" value="<?php echo htmlspecialchars($kiem_tra_data['Max_Scone']); ?>" required>
                    <label>Đạt</label>
                    <input type="text" name="pass" value="<?php echo htmlspecialchars($kiem_tra_data['Pass']); ?>" required>
                    <label>Số câu trả lời</label>
                    <input type="text" name="tral" value="<?php echo htmlspecialchars($kiem_tra_data['Tral']); ?>" required>
                    <label>Số câu tối đa</label>
                    <input type="text" name="max_tral" value="<?php echo htmlspecialchars($kiem_tra_data['Max_tral']); ?>" required>
                </div>
            </div>
            <input type="submit" value="Cập Nhật">
        </form>

    <?php else: ?>
        <!-- Form thêm kết quả kiểm tra -->
        <h3>Nhập Kết Quả Kiểm Tra</h3>

        <?php if (!empty($message)): ?>
            <p class="<?php echo strpos($message, 'Lỗi') === false ? 'message' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
            <div class="form-container">
                <div class="form-left">
                    <label>Student ID</label>
                    <input type="text" value="<?php echo htmlspecialchars($student_id); ?>" readonly>
                    <label>Khóa ID</label>
                    <input type="number" name="khoa_id" required>
                    <label>Test ID</label>
                    <input type="number" name="test_id" required>
                </div>
                <div class="form-right">
                    <label>Điểm tốt nhất</label>
                    <input type="text" name="best_score" required>
                    <label>Điểm tối đa</label>
                    <input type="text" name="max_score" required>
                    <label>Đạt</label>
                    <input type="text" name="pass" required>
                    <label>Số câu trả lời</label>
                    <input type="text" name="tral" required>
                    <label>Số câu tối đa</label>
                    <input type="text" name="max_tral" required>
                </div>
            </div>
            <input type="submit" value="Thêm Kết Quả">
        </form>

        <!-- Hiển thị danh sách kết quả kiểm tra -->
        <?php
        if ($student_id) {
            $stmt = $conn->prepare("SELECT * FROM kiem_tra WHERE Studen_ID = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo "<h3>Danh Sách Kết Quả Kiểm Tra</h3>";
                echo "<table>";
                echo "<tr>
                    <th>Khóa ID</th>
                    <th>Test ID</th>
                    <th>Điểm tốt nhất</th>
                    <th>Điểm tối đa</th>
                    <th>Đạt</th>
                    <th>Số câu trả lời</th>
                    <th>Số câu tối đa</th>
                    <th>Hành Động</th>
                </tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Khoa_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Test_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Best_Scone']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Max_Scone']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Pass']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Tral']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Max_tral']) . "</td>";
                    echo "<td class='actions'>";
                    echo "<form method='POST' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                    echo "<input type='hidden' name='action' value='delete'>";
                    echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['Studen_ID']) . "'>";
                    echo "<input type='hidden' name='khoa_id' value='" . htmlspecialchars($row['Khoa_ID']) . "'>";
                    echo "<input type='hidden' name='test_id' value='" . htmlspecialchars($row['Test_ID']) . "'>";
                    echo "<input type='submit' value='Xóa' onclick='return confirm(\"Bạn có chắc muốn xóa?\");'>";
                    echo "</form>";
                    echo "<form method='GET' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                    echo "<input type='hidden' name='mode' value='edit'>";
                    echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['Studen_ID']) . "'>";
                    echo "<input type='hidden' name='khoa_id' value='" . htmlspecialchars($row['Khoa_ID']) . "'>";
                    echo "<input type='hidden' name='test_id' value='" . htmlspecialchars($row['Test_ID']) . "'>";
                    echo "<input type='submit' value='Sửa'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='text-align:center;'>Chưa có dữ liệu kết quả kiểm tra cho sinh viên này.</p>";
            }
            $stmt->close();
        }
        ?>
    <?php endif; ?>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 20px auto;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            color: #333;
            line-height: 1.6;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        h3 {
            color: #2c3e50;
            margin: 20px 0;
            text-align: center;
        }

        .student-info {
            font-size: 1.1em;
            color: #34495e;
            margin-bottom: 10px;
        }

        .back-button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 10px;
        }

        .back-button:hover {
            background-color: #2980b9;
        }

        .form-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin: 0 auto 20px;
            max-width: 1000px;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .form-left, .form-right {
            flex: 1;
            min-width: 280px;
        }

        .form-left label, .form-right label {
            display: block;
            margin: 10px 0 6px;
            font-weight: 600;
            color: #34495e;
        }

        input[type="number"], input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input[type="number"]:focus, input[type="text"]:focus {
            border-color: #3498db;
            outline: none;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 14px 22px;
            margin: 20px auto;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: block;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .message {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 1500px;
            margin: 10px auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid #e6e6e6;
        }

        th {
            background-color: #3498db;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:hover {
            background-color: #f4f6f8;
        }

        .actions form {
            display: inline-block;
            margin-right: 5px;
        }

        .actions input[type="submit"] {
            padding: 8px 12px;
            font-size: 14px;
            margin: 0;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .form-container {
                flex-direction: column;
            }
        }
    </style>
</body>
</html> 