<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối CSDL thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$message = "";

// Xử lý xóa khóa học va dữ liêu liên quan
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn = dbconnect();
    
    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->begin_transaction();
    
    try {
        //  Lấy tên khóa học để xóa các câu hỏi quiz liên quan
        $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $khoa_hoc = $result->fetch_assoc()['khoa_hoc'];
        $stmt->close();
        
        // Trước tiên lấy danh sách test_id để xóa các kết quả liên quan
        $test_ids = [];
        $stmt = $conn->prepare("SELECT id_test FROM test WHERE id_khoa = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $test_ids[] = $row['id_test'];
        }
        $stmt->close();
         
        if (!empty($test_ids)) {
            // Xóa các kết quả kiểm tra (ket_qua) liên quan đến các bài test
            $test_ids_str = implode("','", $test_ids);
            $conn->query("DELETE FROM ket_qua WHERE test_id IN ('$test_ids_str')");
            
            // Xóa các bản ghi kiểm tra (kiem_tra) liên quan
            $conn->query("DELETE FROM kiem_tra WHERE Khoa_ID = $id");
            
            // Xóa các bài test
            $conn->query("DELETE FROM test WHERE id_khoa = $id");
        }
        
        //  Xóa các câu hỏi quiz liên quan đến khóa học
        $conn->query("DELETE FROM quiz WHERE ten_khoa = '$khoa_hoc'");
        
        // Cập nhật các sinh viên đang tham gia khóa học này
        $students = [];
        $result = $conn->query("SELECT Student_ID, Khoahoc FROM students WHERE Khoahoc LIKE '%$id%'");
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        // Cập nhật từng sinh viên
        foreach ($students as $student) {
            $courses = explode(',', $student['Khoahoc']);
            $new_courses = array_filter($courses, function($course) use ($id) {
                return $course != $id;
            });
            $new_courses_str = implode(',', $new_courses);
            
            $stmt = $conn->prepare("UPDATE students SET Khoahoc = ? WHERE Student_ID = ?");
            $stmt->bind_param("ss", $new_courses_str, $student['Student_ID']);
            $stmt->execute();
            $stmt->close();
        }
        
        // 5. Cuối cùng xóa khóa học
        $stmt = $conn->prepare("DELETE FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction nếu mọi thứ thành công
        $conn->commit();
        $message = "<div class='message success'>Đã xóa khóa học và tất cả dữ liệu liên quan thành công!</div>";
    } catch (Exception $e) {
        // Rollback nếu có lỗi xảy ra
        $conn->rollback();
        $message = "<div class='message error'>Lỗi khi xóa: " . $e->getMessage() . "</div>";
    }
    
    $conn->close();
}

// Xử lý sửa khóa học
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_course"])) {
    $id = (int) $_POST["course_id"];
    $ten_khoahoc = trim($_POST["ten_khoahoc"]);
    if (empty($ten_khoahoc)) {
        $message = "<div class='message error'>Vui lòng nhập tên khóa học!</div>";
    } else {
        $conn = dbconnect();
        $stmt = $conn->prepare("UPDATE khoa_hoc SET khoa_hoc = ? WHERE id = ?");
        $stmt->bind_param("si", $ten_khoahoc, $id);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Đã cập nhật khóa học thành công!</div>";
        } else {
            $message = "<div class='message error'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
        }
        $stmt->close();
        $conn->close();
    }
}

// Xử lý thêm mới
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_course"])) {
    $ten_khoahoc = trim($_POST["ten_khoahoc"]);
    if (empty($ten_khoahoc)) {
        $message = "<div class='message error'>Vui lòng nhập tên khóa học!</div>";
    } else {
        $conn = dbconnect();
        $stmt = $conn->prepare("INSERT INTO khoa_hoc (khoa_hoc) VALUES (?)");
        $stmt->bind_param("s", $ten_khoahoc);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Đã thêm khóa học thành công!</div>";
        } else {
            $message = "<div class='message error'>Lỗi khi thêm: " . $stmt->error . "</div>";
        }
        $stmt->close();
        $conn->close();
    }
}

// Lấy danh sách khóa học
$conn = dbconnect();
$khoa_hoc_list = [];
$result = $conn->query("SELECT * FROM khoa_hoc ORDER BY id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $khoa_hoc_list[] = $row;
    }
}
$conn->close();

$editing = false;
$edit_khoa_hoc = '';

$edit_id = 0;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $conn = dbconnect();
    $stmt = $conn->prepare("SELECT * FROM khoa_hoc WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $editing = true;
        $edit_khoa_hoc = $row['khoa_hoc'];


    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khóa Học</title>
    <style>

        .container {
            background-color: #ffffff;
            max-width: 2500px;
            width: 100%;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        h2, h3 {
            margin-bottom: 25px;
            color: #2d3748;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            padding: 10px;
            background-color: #edf2f7;
            /* border-radius: 8px; */

        }

        form label {
            /* font-weight: 500; */
            display: block;
            margin-top: 15px;
            font-size:17px;
            margin-bottom: 5px;
            color: #333;
        }

        form input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        form input[type="text"]:focus {
            border-color: #0288d1;
            box-shadow: 0 0 5px rgba(2, 136, 209, 0.2);
            outline: none;
        }

        button {
            width: 100%;
            background-color: #0288d1;
            color: white;
            font-size: 16px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s, transform 0.1s;
        }

        button:hover {
            background-color: #0277bd;
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        a.cancel {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #0288d1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        a.cancel:hover {
            color: #01579b;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .message.error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin-top: 20px;
        }

        ul li {
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: 50px 1fr 60px 60px 100px;
            align-items: center;
            gap: 10px;
            transition: background-color 0.3s, box-shadow 0.3s;
            font-size:17px;
            
        }

        ul li:hover {
            background-color: #f5f5f5;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        ul li strong {
            text-align: left;
            color: #333;
            font-weight: 500;
        }

        ul li .course-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #333;
            font-size:15px;
        }

        ul li a {
            text-align: center;
            padding: 6px 0;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            display: block;
            transition: background-color 0.3s, transform 0.1s;
        }

        ul li a.edit {
            background-color: #e3f2fd;
            color: #0288d1;
        }

        ul li a.edit:hover {
            background-color: #bbdefb;
            transform: translateY(-1px);
        }

        ul li a.delete {
            background-color: #ffebee;
            color: #c62828;
        }

        ul li a.delete:hover {
            background-color: #ffcdd2;
            transform: translateY(-1px);
        }

        ul li a.btn {
            background-color: #0288d1;
            color: white;
        }

        ul li a.btn:hover {
            background-color: #0277bd;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= $editing ? "Cập nhật khóa học" : "Thêm khóa học" ?></h2>
        <?php if (!empty($message)) echo $message; ?>

        <form method="POST">
            <label>Tên khóa học:</label>
            <input type="text" name="ten_khoahoc" placeholder="Nhập tên khóa học" value="<?= htmlspecialchars($edit_khoa_hoc) ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="course_id" value="<?= $edit_id ?>">
                <button type="submit" name="update_course">Cập nhật</button>
                <a href="add_khoahoc.php" class="cancel">Huỷ</a>
            <?php else: ?>
                <button type="submit" name="add_course">Thêm khóa học</button>
            <?php endif; ?>
        </form>
        <br><h2>Danh sách khóa học</h2>
        <?php if (empty($khoa_hoc_list)): ?>
            <p style="text-align: center; color: #666;">Chưa có khóa học nào.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($khoa_hoc_list as $kh): ?>
                    <li>
                        <strong></strong>
                        <span class="course-name"><?= htmlspecialchars($kh['khoa_hoc']) ?></span>
                        <a href="index.php?action=add_khoahoc&edit=<?= $kh['id'] ?>" class="edit">Sửa</a>
                        <a href="index.php?action=add_khoahoc&delete=<?= $kh['id'] ?>" class="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                        <a href="index.php?action=khoahoc&id_khoa=<?= htmlspecialchars($kh['id']) ?>" class="btn">Xem test</a>                        
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    </div>
</body>
</html>