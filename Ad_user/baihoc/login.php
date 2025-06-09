
<?php
session_start();

// Bật báo lỗi PHP để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cài đặt encoding cho PHP
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    // Cài đặt charset cho kết nối database
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Xử lý đăng nhập
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);
    
    if (!empty($student_id) && !empty($password)) {
        try {
            $conn = dbconnect();
            
            // Debug thông tin đăng nhập
            error_log("Attempting login for student_id: " . $student_id);
            
            // Kiểm tra thông tin đăng nhập từ bảng students
            $sql = "SELECT Student_ID, Password, Ten, Khoahoc 
                    FROM students 
                    WHERE Student_ID = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Lỗi prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("s", $student_id);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi execute statement: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Debug thông tin từ database
                error_log("Found user: " . print_r($row, true));
                
                // So sánh mật khẩu
                if ($password === $row['Password']) {
                    // Debug thông tin khóa học từ database
                    error_log("Raw Khoahoc data from database: " . $row['Khoahoc']);
                    
                    // Lưu thông tin vào session
                    $_SESSION['student_id'] = $row['Student_ID'];
                    $_SESSION['student_name'] = $row['Ten'] ?? 'Unknown';
                    
                    // Xử lý khóa học
                    $courses = [];
                    if (!empty($row['Khoahoc'])) {
                        $courses = array_map('trim', explode(',', $row['Khoahoc']));
                        error_log("Courses after explode: " . print_r($courses, true));
                        
                        // Chuyển đổi tên khóa học thành ID
                        $course_mapping = [
                            'Python cơ bản' => 1,
                            'Python nâng cao' => 2,
                            'Yolo' => 3,
                            'Toán' => 4,
                            'Văn' => 5,
                            'Tiếng anh' => 6,
                            'Hoá học' => 10
                        ];
                        
                        $courses = array_map(function($course) use ($course_mapping) {
                            $mapped_id = $course_mapping[$course] ?? null;
                            error_log("Mapping course '$course' to ID: " . ($mapped_id ?? 'null'));
                            return $mapped_id;
                        }, $courses);
                        
                        $courses = array_filter($courses); // Loại bỏ các giá trị null
                        error_log("Final course IDs: " . print_r($courses, true));
                    } else {
                        error_log("Khoahoc is empty in database");
                    }
                    $_SESSION['courses'] = $courses;
                    
                    error_log("Login successful for student_id: " . $student_id);
                    error_log("Courses: " . print_r($courses, true));
                    
                    header("Location: tests.php");
                    exit();
                } else {
                    $message = "<div style='color:red;'>Mật khẩu không đúng!</div>";
                    error_log("Wrong password for student_id: " . $student_id);
                }
            } else {
                $message = "<div style='color:red;'>Mã sinh viên không tồn tại!</div>";
                error_log("Student ID not found: " . $student_id);
            }
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $message = "<div style='color:red;'>Lỗi hệ thống: " . $e->getMessage() . "</div>";
            error_log("Login error: " . $e->getMessage());
        }
    } else {
        $message = "<div style='color:red;'>Vui lòng nhập đầy đủ thông tin!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập sinh viên</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f4f4f4;
            margin: 0;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: rgb(247, 18, 18);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-login {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-login:hover {
            background-color: #218838;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #fff3f3;
            border: 1px solid #ffcdd2;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Đăng nhập sinh viên</h2>
        <?php if (!empty($message)) echo $message; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="student_id">Mã sinh viên:</label>
                <input type="text" id="student_id" name="student_id" required 
                       value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
