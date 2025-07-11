<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = intval($_SESSION['student_id']);

// Kiểm tra quyền truy cập
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy thông tin sinh viên và khóa học
$stmt = $conn->prepare("SELECT s.Student_ID, s.Ten, s.Khoahoc, kh.khoa_hoc 
                       FROM students s 
                       LEFT JOIN khoa_hoc kh ON FIND_IN_SET(kh.id, s.Khoahoc)
                       WHERE s.Student_ID = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student_info = $result->fetch_assoc();
    $allowed_courses = explode(',', $student_info['Khoahoc']);
    
    // Kiểm tra xem khóa học 2 có trong danh sách khóa học được phép không
    if (in_array('2', $allowed_courses)) {
        // Cho phép truy cập
    } else {
        echo "Xin lỗi " . htmlspecialchars($student_info['Ten']) . ", bạn không có quyền truy cập khóa học này";
        exit();
    }
} else {
    echo "Không tìm thấy thông tin sinh viên";
    exit();
}
$stmt->close();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = 2"; // PYTHON NÂNG CAO
    $stmt = $conn->query($sql);
    $khoa_hoc = $stmt->fetchColumn();
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nội dung 2</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #e6f3fa, #f4f4f9);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .content-container {
            background-color: white;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            color: #007bff;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        .course-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h2>Khóa học <?php echo htmlspecialchars($khoa_hoc); ?></h2>
        <p>Xin chào <?php echo htmlspecialchars($student_info['Ten']); ?> - Bạn đang học khóa <?php echo htmlspecialchars($khoa_hoc); ?></p>
        <div class="course-info">
            <p>Danh sách khóa học của bạn:</p>
            <?php
            $course_list = explode(',', $student_info['Khoahoc']);
            foreach($course_list as $course_id) {
                $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
                $stmt->execute([$course_id]);
                $course_name = $stmt->fetchColumn();
                echo "<p>- " . htmlspecialchars($course_name) . "</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>