<?php
// Hàm kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Kết nối
$conn = dbconnect();
$message = isset($_GET['message']) ? urldecode($_GET['message']) : "";

// Xử lý khi submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $deleteSQL = "DELETE FROM Students WHERE STUDENT_ID = '$student_id'";
        if ($conn->query($deleteSQL) === TRUE) {
            $message = "Xóa sinh viên thành công!";
        } else {
            $message = "Lỗi: " . $conn->error;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update') {
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $imei = $conn->real_escape_string($_POST['imei']);
        $mbid = $conn->real_escape_string($_POST['mbid']);
        $osid = $conn->real_escape_string($_POST['osid']);
        $pass = $conn->real_escape_string($_POST['pass']);
        $ten = $conn->real_escape_string($_POST['ten']);
        $email = $conn->real_escape_string($_POST['email']);

        $updateSQL = "UPDATE Students SET IMEI='$imei', MBID='$mbid', OSID='$osid', PASS='$pass', TEN='$ten', EMAIL='$email' WHERE STUDENT_ID='$student_id'";
        if ($conn->query($updateSQL) === TRUE) {
            $message = "Cập nhật thành công!";
        } else {
            $message = "Lỗi: " . $conn->error;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
        $imei = $conn->real_escape_string($_POST['imei']);
        $mbid = $conn->real_escape_string($_POST['mbid']);
        $osid = $conn->real_escape_string($_POST['osid']);
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $pass = $conn->real_escape_string($_POST['pass']);
        $ten = $conn->real_escape_string($_POST['ten']);
        $email = $conn->real_escape_string($_POST['email']);

        $insertSQL = "INSERT INTO Students (IMEI, MBID, OSID, STUDENT_ID, PASS, TEN, EMAIL) 
                      VALUES ('$imei', '$mbid', '$osid', '$student_id', '$pass', '$ten', '$email')";

        if ($conn->query($insertSQL) === TRUE) {
            $message = "Dữ liệu đã được thêm thành công!";
        } else {
            $message = "Lỗi: " . $conn->error;
        }
    }
    if (!empty($message)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
        exit();
    }
}

// Kiểm tra nếu đang ở chế độ "Sửa"
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$student_id = isset($_GET['student_id']) ? $conn->real_escape_string($_GET['student_id']) : '';
$student_data = [];
if ($mode == 'edit' && $student_id) {
    $selectSQL = "SELECT * FROM Students WHERE STUDENT_ID = '$student_id'";
    $result = $conn->query($selectSQL);
    $student_data = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhập Dữ Liệu Sinh Viên</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(to right, #fdfbfb, #ebedee);
            padding: 40px;
            margin: 0;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .form-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin: 0 auto 20px;
            max-width: 900px;
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

        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus {
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
            max-width: 1000px;
            margin: 20px auto;
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
        }

        /* CSS cho popup */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-width: 500px;
            border-radius: 10px;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .course-list {
            margin-top: 20px;
        }

        .course-list label {
            display: block;
            margin: 10px 0;
        }

        #selected-courses {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        @media (max-width: 768px) {
            .form-container {
                flex-direction: column;
            }
            .modal-content {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <?php if ($mode == 'edit' && !empty($student_data)): ?>
        <h2>Sửa Thông Tin Sinh Viên</h2>

        <?php if (!empty($message)): ?>
            <p class="<?php echo strpos($message, 'Lỗi') === false ? 'message' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
            <div class="form-container">
                <div class="form-left">
                    <label>IMEI (15 ký tự)</label>
                    <input type="text" name="imei" value="<?php echo htmlspecialchars($student_data['IMEI'] ?? ''); ?>" required>

                    <label>MBID</label>
                    <input type="text" name="mbid" value="<?php echo htmlspecialchars($student_data['MBID'] ?? ''); ?>">

                    <label>OSID</label>
                    <input type="text" name="osid" value="<?php echo htmlspecialchars($student_data['OSID'] ?? ''); ?>">

                    <label>STUDENT_ID</label>
                    <input type="text" name="student_id" value="<?php echo htmlspecialchars($student_data['STUDENT_ID'] ?? ''); ?>" readonly>
                </div>

                <div class="form-right">
                    <label>PASS (Mật khẩu)</label>
                    <input type="password" name="pass" value="<?php echo htmlspecialchars($student_data['PASS'] ?? ''); ?>" required>

                    <label>TEN (Tên)</label>
                    <input type="text" name="ten" value="<?php echo htmlspecialchars($student_data['TEN'] ?? ''); ?>" required>

                    <label>EMAIL</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($student_data['EMAIL'] ?? ''); ?>" required>
                </div>
            </div>
            <input type="submit" value="Cập Nhật">
        </form>

    <?php else: ?>
        <h2>Nhập Dữ Liệu Sinh Viên</h2>

        <?php if (!empty($message)): ?>
            <p class="<?php echo strpos($message, 'Lỗi') === false ? 'message' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="action" value="add">
            <div class="form-container">
                <div class="form-left">
                    <label>IMEI (15 ký tự)</label>
                    <input type="text" name="imei" required>

                    <label>MBID</label>
                    <input type="text" name="mbid">

                    <label>OSID</label>
                    <input type="text" name="osid">

                    <label>STUDENT_ID</label>
                    <input type="text" name="student_id" required>
                </div>

                <div class="form-right">
                    <label>PASS (Mật khẩu)</label>
                    <input type="password" name="pass" required>

                    <label>TEN (Tên)</label>
                    <input type="text" name="ten" required>

                    <label>EMAIL</label>
                    <input type="email" name="email" required>
                </div>
            </div>
            <input type="submit" value="Thêm Dữ Liệu">
        </form>

        <?php
        // Hiển thị danh sách sinh viên với cột "Khóa Học" (ngẫu nhiên từ khoa_hoc)
        $selectSQL = "SELECT s.*, kh.khoa_hoc 
                      FROM Students s 
                      LEFT JOIN khoa_hoc kh ON FLOOR(RAND() * (SELECT COUNT(*) FROM khoa_hoc)) + 1 = kh.id";
        $result = $conn->query($selectSQL);
        if ($result->num_rows > 0) {
            echo "<h2>Danh Sách Sinh Viên:</h2>";
            echo "<table>";
            echo "<tr><th>IMEI</th><th>MBID</th><th>OSID</th><th>STUDENT_ID</th><th>PASS</th><th>TEN</th><th>EMAIL</th><th>Khóa Học</th><th>Hành Động</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['IMEI']) . "</td>";
                echo "<td>" . htmlspecialchars($row['MBID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['OSID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['STUDENT_ID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PASS']) . "</td>";
                echo "<td>" . htmlspecialchars($row['TEN']) . "</td>";
                echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                echo "<td>" . htmlspecialchars($row['khoa_hoc'] ?: 'Chưa chọn') . "</td>";
                echo "<td class='actions'>";
                echo "<form method='POST' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                echo "<input type='hidden' name='action' value='delete'>";
                echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['STUDENT_ID']) . "'>";
                echo "<input type='submit' value='Xóa' onclick='return confirm(\"Bạn có chắc muốn xóa?\");'>";
                echo "</form>";
                echo "<form method='GET' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                echo "<input type='hidden' name='mode' value='edit'>";
                echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['STUDENT_ID']) . "'>";
                echo "<input type='submit' value='Sửa'>";
                echo "</form>";
                echo "<button onclick=\"openModal('" . htmlspecialchars($row['STUDENT_ID']) . "')\">Xem Bài</button>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='text-align:center;'>Chưa có dữ liệu sinh viên.</p>";
        }
        ?>
    <?php endif; ?>

    <!-- Popup (Modal) -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Khóa Học Của Sinh Viên</h2>
            <div class="course-list">
                <?php
                $khoaHocSQL = "SELECT * FROM khoa_hoc";
                $khoaHocResult = $conn->query($khoaHocSQL);
                if ($khoaHocResult->num_rows > 0) {
                    while ($khoaHocRow = $khoaHocResult->fetch_assoc()) {
                        echo "<label>";
                        echo "<input type='checkbox' name='khoa_hoc[]' value='" . htmlspecialchars($khoaHocRow['khoa_hoc']) . "' onchange='updateSelectedCourses()'>";
                        echo htmlspecialchars($khoaHocRow['khoa_hoc']);
                        echo "</label>";
                    }
                } else {
                    echo "<p>Không có khóa học nào.</p>";
                }
                ?>
            </div>
            <div id="selected-courses">
                <p><strong>Khóa học đã chọn:</strong> <span id="selectedCoursesText">Chưa chọn khóa học nào.</span></p>
            </div>
        </div>
    </div>

    <?php
    $conn->close();
    ?>

    <script>
        function openModal(studentId) {
            document.getElementById('modalTitle').innerText = `Khóa Học Của Sinh Viên: ${studentId}`;
            document.getElementById('courseModal').style.display = 'block';
            updateSelectedCourses();
        }

        function closeModal() {
            document.getElementById('courseModal').style.display = 'none';
            // Reset checkbox khi đóng modal
            const checkboxes = document.querySelectorAll('input[name="khoa_hoc[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = false);
            updateSelectedCourses();
        }

        function updateSelectedCourses() {
            const selectedCourses = [];
            const checkboxes = document.querySelectorAll('input[name="khoa_hoc[]"]:checked');
            checkboxes.forEach(checkbox => selectedCourses.push(checkbox.value));
            const selectedCoursesText = selectedCourses.length > 0 ? selectedCourses.join(', ') : 'Chưa chọn khóa học nào.';
            document.getElementById('selectedCoursesText').innerText = selectedCoursesText;
        }

        // Đóng modal khi nhấp ra ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('courseModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>