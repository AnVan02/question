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

// Xử lý yêu cầu AJAX để lấy khóa học của sinh viên
if (isset($_GET['action']) && $_GET['action'] == 'get_courses' && isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
    $stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $khoa_hoc_ids = [];
    if ($row = $result->fetch_assoc()) {
        $khoa_hoc_ids = !empty($row['Khoahoc']) ? array_map('intval', explode(',', $row['Khoahoc'])) : [];
    }
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode($khoa_hoc_ids);
    exit;
}
// Xử lý khi submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $student_id = $_POST['student_id'];
        $stmt = $conn->prepare("DELETE FROM students WHERE Student_ID = ?");
        $stmt->bind_param("s", $student_id);
        if ($stmt->execute()) {
            $message = "Xóa sinh viên thành công!";
        } else {
            $message = "Lỗi khi xóa: " . $conn->error;
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update') {
        $student_id = $_POST['student_id'];
        $imei = (int)$_POST['imei'];
        $mb_id = (int)$_POST['mb_id'];
        $os_id = (int)$_POST['os_id'];
        $password = $_POST['password'];
        $ten = $_POST['ten'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("UPDATE students SET IMEI = ?, MB_ID = ?, OS_ID = ?, Password = ?, Ten = ?, Email = ? WHERE Student_ID = ?");
        $stmt->bind_param("iiissss", $imei, $mb_id, $os_id, $password, $ten, $email, $student_id);
        if ($stmt->execute()) {
            $message = "Cập nhật thành công!";
        } else {
            $message = "Lỗi khi cập nhật: " . $conn->error;
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
        $imei = (int)$_POST['imei'];
        $mb_id = (int)$_POST['mb_id'];
        $os_id = (int)$_POST['os_id'];
        $student_id = $_POST['student_id'];
        $password = $_POST['password'];
        $ten = $_POST['ten'];
        $email = $_POST['email'];

        // Kiểm tra Student_ID đã tồn tại
        $stmt = $conn->prepare("SELECT Student_ID FROM students WHERE Student_ID = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $message = "Lỗi: Student_ID đã tồn tại!";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (IMEI, MB_ID, OS_ID, Student_ID, Password, Ten, Email) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiissss", $imei, $mb_id, $os_id, $student_id, $password, $ten, $email);
            if ($stmt->execute()) {
                $message = "Thêm sinh viên thành công!";
            } else {
                $message = "Lỗi khi thêm: " . $conn->error;
            }
        }
        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'save_courses') {
        $student_id = $_POST['student_id'];
        $khoa_hoc_ids = isset($_POST['khoa_hoc']) ? $_POST['khoa_hoc'] : [];

        // Chuyển danh sách khóa học thành chuỗi
        $khoa_hoc_string = !empty($khoa_hoc_ids) ? implode(',', $khoa_hoc_ids) : '';

        // Cập nhật cột Khoahoc trong bảng students
        $stmt = $conn->prepare("UPDATE students SET Khoahoc = ? WHERE Student_ID = ?");
        $stmt->bind_param("ss", $khoa_hoc_string, $student_id);
        if ($stmt->execute()) {
            $message = "Lưu khóa học thành công!";
        } else {
            $message = "Lỗi khi lưu khóa học: " . $conn->error;
        }
        $stmt->close();
    }
}

// Kiểm tra chế độ chỉnh sửa
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
$student_data = [];
if ($mode == 'edit' && $student_id) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student_data = $result->fetch_assoc();
    } else {
        $message = "Không tìm thấy sinh viên với Student_ID: " . htmlspecialchars($student_id);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sinh Viên</title>
</head>
<body>
    <?php if ($mode == 'edit' && !empty($student_data)): ?>
        <!-- Form chỉnh sửa sinh viên -->
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
                    <label>IMEI</label>
                    <input type="number" name="imei" value="<?php echo htmlspecialchars($student_data['IMEI'] ?? ''); ?>" required>
                    <label>MB_ID</label>
                    <input type="number" name="mb_id" value="<?php echo htmlspecialchars($student_data['MB_ID'] ?? ''); ?>">
                    <label>OS_ID</label>
                    <input type="number" name="os_id" value="<?php echo htmlspecialchars($student_data['OS_ID'] ?? ''); ?>">
                    <label>Student_ID</label>
                    <input type="text" name="student_id" value="<?php echo htmlspecialchars($student_data['Student_ID'] ?? ''); ?>" readonly>
                </div>
                <div class="form-right">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($student_data['Password'] ?? ''); ?>" required>
                    <label>Tên</label>
                    <input type="text" name="ten" value="<?php echo htmlspecialchars($student_data['Ten'] ?? ''); ?>" required>
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($student_data['Email'] ?? ''); ?>" required>
                </div>
            </div>
            <input type="submit" value="Cập Nhật">
        </form>

    <?php else: ?>
        <!-- Form thêm sinh viên -->
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
                    <label>IMEI</label>
                    <input type="number" name="imei" required>
                    <label>MB_ID</label>
                    <input type="number" name="mb_id">
                    <label>OS_ID</label>
                    <input type="number" name="os_id">
                    <label>Student_ID</label>
                    <input type="text" name="student_id" required>
                </div>
                <div class="form-right">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required>
                    <label>Tên sinh viên</label>
                    <input type="text" name="ten" required>
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
            </div>
            <input type="submit" value="Thêm Sinh Viên">
        </form>

        <!-- Hiển thị danh sách sinh viên -->
         <?php
        $stmt = $conn->prepare("SELECT * FROM students");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<h2>Danh Sách Sinh Viên</h2>";
            echo "<table>";
            echo "<tr>
                <th>IMEI</th>
                <th>MB_ID</th>
                <th>OS_ID</th>
                <th>Student_ID</th>
                <th>Password</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Khoá học</th>
                <th>Hành Động</th>
            </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['IMEI'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['MB_ID'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['OS_ID'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['Student_ID'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['Password'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['Ten'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['Email'] ?? '') . "</td>";

                // Lấy danh sách khóa học từ cột Khoahoc
                $khoa_hoc_ids = !empty($row['Khoahoc']) ? explode(',', $row['Khoahoc']) : [];
                $khoa_hoc_names = [];
                if (!empty($khoa_hoc_ids)) {
                    $placeholders = implode(',', array_fill(0, count($khoa_hoc_ids), '?'));
                    $stmt_khoa_hoc = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id IN ($placeholders)");
                    $stmt_khoa_hoc->bind_param(str_repeat('i', count($khoa_hoc_ids)), ...$khoa_hoc_ids);
                    $stmt_khoa_hoc->execute();
                    $khoa_hoc_result = $stmt_khoa_hoc->get_result();
                    while ($khoa_hoc_row = $khoa_hoc_result->fetch_assoc()) {
                        $khoa_hoc_names[] = htmlspecialchars($khoa_hoc_row['khoa_hoc']);
                    }
                    $stmt_khoa_hoc->close();
                }
                echo "<td>";
                if (!empty($khoa_hoc_names)) {
                    echo "<ul>";
                    foreach ($khoa_hoc_names as $name) {
                        echo "<li>" . $name . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo 'Chưa có khóa học';
                }
                echo "</td>";

                echo "<td class='actions'>";
                echo "<form method='POST' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                echo "<input type='hidden' name='action' value='delete'>";
                echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['Student_ID']) . "'>";
                echo "<input type='submit' value='Xóa' onclick='return confirm(\"Bạn có chắc muốn xóa?\");'>";
                echo "</form>";
                echo "<form method='GET' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                echo "<input type='hidden' name='mode' value='edit'>";
                echo "<input type='hidden' name='student_id' value='" . htmlspecialchars($row['Student_ID']) . "'>";
                echo "<input type='submit' value='Sửa'>";
                echo "</form>";
                echo "<button onclick=\"openModal('" . htmlspecialchars($row['Student_ID']) . "')\">Xem Khóa Học</button>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='text-align:center;'>Chưa có dữ liệu sinh viên.</p>";
        }
        $stmt->close();
        ?>
    <?php endif; ?>
    <!-- Modal hiển thị và lưu khóa học -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h2 id="modalTitle">Khóa Học Của Sinh Viên</h2>
            <form id="courseForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="action" value="save_courses">
                <input type="hidden" name="student_id" id="modalStudentId">
                <div class="course-list">
                   <?php
                    $stmt = $conn->prepare("SELECT * FROM khoa_hoc");
                    $stmt->execute();
                    $khoaHocResult = $stmt->get_result();
                    if ($khoaHocResult->num_rows > 0) {
                        while ($khoaHocRow = $khoaHocResult->fetch_assoc()) {
                            echo "<label>";
                            echo "<input type='checkbox' name='khoa_hoc[]' value='" . htmlspecialchars($khoaHocRow['id']) . "' onchange='updateSelectedCourses()'>";
                            echo htmlspecialchars($khoaHocRow['khoa_hoc']);
                            echo "</label>";
                        }
                    } else {
                        echo "<p>Không có khóa học nào.</p>";
                    }
                    $stmt->close();
                    ?>
                </div>
                
                <div id="selected-courses">
                    <p><strong>Khóa học đã chọn:</strong> <span id="selectedCoursesText">Chưa chọn khóa học nào.</span></p>
                </div>

                <input type="submit" value="Lưu" style="background-color: #28a745; margin-top: 10px;">
            </form>
        </div>
    </div>

    <?php
    // Đóng kết nối
    $conn->close();
    ?>

    <script>
        function openModal(studentId) {
            document.getElementById('modalTitle').innerText = `Khóa Học Của Sinh Viên: ${studentId}`;
            document.getElementById('modalStudentId').value = studentId;
            document.getElementById('courseModal').style.display = 'block';

            // Lấy danh sách khóa học đã chọn
            fetch(`<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?action=get_courses&student_id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    const checkboxes = document.querySelectorAll('input[name="khoa_hoc[]"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = data.includes(parseInt(checkbox.value));
                    });
                    updateSelectedCourses();
                });
        }

        function closeModal() {
            document.getElementById('courseModal').style.display = 'none';
            const checkboxes = document.querySelectorAll('input[name="khoa_hoc[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = false);
            updateSelectedCourses();
        }

        function updateSelectedCourses() {
            const selectedCourses = [];
            const checkboxes = document.querySelectorAll('input[name="khoa_hoc[]"]:checked');
            checkboxes.forEach(checkbox => {
                const label = checkbox.parentElement.textContent.trim();
                selectedCourses.push(label);
            });
            const selectedCoursesText = selectedCourses.length > 0 ? selectedCourses.join(', ') : 'Chưa chọn khóa học nào.';
            document.getElementById('selectedCoursesText').innerText = selectedCoursesText;
        }

        window.onclick = function(event) {
            const modal = document.getElementById('courseModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 20px auto;
            /* max-width: 1200px; */
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            color: #333;
            line-height: 1.6;
            padding: 15px;
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
        input[type="number"], input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="number"]:focus, input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus {
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
        .actions input[type="submit"], .actions button {
            padding: 8px 12px;
            font-size: 14px;
            margin: 0;
            cursor: pointer;
        }
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
            max-width: 700px;
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
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
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
        td ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        td ul li {
            margin-bottom: 5px;
        }
        .test-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .test-table th, .test-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .test-table th {
            background-color: #3498db;
            color: white;
        }
        .test-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .test-table-container {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 10px;
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
</body>
</html>