<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id']) || !isset($_SESSION['khoahoc'])) {
    header("Location: login.php");
    exit();
}

// Hàm chuyển đổi tên khóa học thành tên file
function getCourseFileName($course_name) {
    $course_files = [
        'Python cơ bản' => 'Python_cb.php',
        'Python nâng cao' => 'Python_nc.php',
        'YOLO' => 'Yolo.php',
        'Toán' => 'Toan.php',
        'Văn' => 'Van.php',
        'Sinh học' => 'Sinhhoc.php',
        'Tiếng anh' => 'Tienganh.php',
        'Hoá học' => 'Hoahoc.php'
    ];
    $course_name = trim($course_name);
    return isset($course_files[$course_name]) && file_exists("../login/{$course_files[$course_name]}") 
        ? $course_files[$course_name] 
        : '';
}

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Kết nối thất bại: " . $e->getMessage());
    header("Location: error.php");
    exit();
}

// Dữ liệu đầu vào
$ma_khoa = '1';
$id_test = '19';
$student_id = $_SESSION['student_id'];
$allowed_khoa = [10, 2, 8, 5];

// Kiểm tra quyền truy cập khóa học
$allowed_khoa_string = implode(',', array_fill(0, count($allowed_khoa), '?'));
$sql = "SELECT Khoahoc FROM students WHERE Student_ID = ? AND Khoahoc IN ($allowed_khoa_string)";
$stmt = $conn->prepare($sql);
$stmt->execute(array_merge([$student_id], $allowed_khoa));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    // Lấy khóa học hiện tại của sinh viên
    $sql_khoa = "SELECT Khoahoc FROM students WHERE Student_ID = ?";
    $stmt_khoa = $conn->prepare($sql_khoa);
    $stmt_khoa->execute([$student_id]);
    $row_khoa = $stmt_khoa->fetch(PDO::FETCH_ASSOC);
    $khoa_hoc = $row_khoa['Khoahoc'] ?? 'không xác định';

    $message = "Sinh viên $student_id KHÔNG thuộc khóa học được phép! Khóa học hiện tại: $khoa_hoc.";
    echo "<!DOCTYPE html>
    <html lang='vi'>
    <head>
        <meta charset='UTF-8'>
        <title>Lỗi truy cập</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .error { color: #dc3545; font-size: 18px; }
        </style>
    </head>
    <body>
        <div class='error'>" . htmlspecialchars($message) . "</div>
        <p><a href='theme_list.php'>Quay lại danh sách khóa học</a></p>
    </body>
    </html>";
    exit();
}

// Lấy danh sách khóa học của sinh viên
$allowed_courses = array_filter(array_map('trim', explode(',', $_SESSION['khoahoc'])), 'is_numeric');
if (empty($allowed_courses)) {
    echo "<div class='no-courses'>Bạn chưa được đăng ký khóa học nào</div>";
    exit();
}

// Lọc khóa học theo $allowed_khoa
$allowed_courses = array_intersect($allowed_courses, $allowed_khoa);
if (empty($allowed_courses)) {
    echo "<div class='no-courses'>Bạn chưa được đăng ký khóa học nào trong danh sách cho phép</div>";
    exit();
}

// Truy vấn tất cả khóa học
$placeholders = implode(',', array_fill(0, count($allowed_courses), '?'));
$stmt = $conn->prepare("SELECT kh.khoa_hoc, t.ten_test, t.lan_thu, t.Pass, t.so_cau_hien_thi 
                        FROM khoa_hoc kh 
                        LEFT JOIN test t ON kh.id = t.id_khoa 
                        WHERE kh.id IN ($placeholders)");
$stmt->execute($allowed_courses);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách khóa học</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .welcome {
            font-size: 24px;
            color: #333;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .course-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .course-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .course-info {
            margin-bottom: 15px;
        }
        .course-info p {
            margin: 8px 0;
            color: #666;
        }
        .course-info strong {
            color: #333;
        }
        .start-btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .start-btn:hover {
            background: #218838;
        }
        .no-courses {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome">
                Xin chào, <?php echo htmlspecialchars($_SESSION['student_name']); ?>
            </div>
            <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
        
        <div class="course-grid">
            <?php
            if (!empty($courses)) {
                foreach ($courses as $course) {
                    // Kiểm tra nếu $ma_khoa và $id_test được sử dụng để lọc
                    if (isset($ma_khoa) && isset($id_test)) {
                        $stmt_test = $conn->prepare("SELECT ten_test FROM test WHERE id_khoa = ? AND id = ?");
                        $stmt_test->execute([$ma_khoa, $id_test]);
                        $test = $stmt_test->fetch(PDO::FETCH_ASSOC);
                        if (!$test || $course['khoa_hoc'] !== ($course['khoa_hoc'] ?? '')) {
                            continue; // Bỏ qua nếu không khớp
                        }
                    }

                    echo "<div class='course-card'>";
                    echo "<div class='course-title'>" . htmlspecialchars($course['khoa_hoc']) . "</div>";
                    echo "<div class='course-info'>";
                    if ($course['ten_test']) {
                        echo "<p><strong>Tên bài kiểm tra:</strong> " . htmlspecialchars($course['ten_test']) . "</p>";
                        echo "<p><strong>Số lần thử:</strong> " . htmlspecialchars($course['lan_thu']) . "</p>";
                        echo "<p><strong>Điểm đạt:</strong> " . htmlspecialchars($course['Pass']) . "%</p>";
                        echo "<p><strong>Số câu hỏi:</strong> " . htmlspecialchars($course['so_cau_hien_thi']) . "</p>";
                        $course_file = getCourseFileName($course['khoa_hoc']);
                        if ($course_file) {
                            echo "<a href='../login/" . htmlspecialchars($course_file) . "' class='start-btn'>Bắt đầu làm bài</a>";
                        } else {
                            echo "<p>Lỗi: Không tìm thấy file bài kiểm tra.</p>";
                        }
                    } else {
                        echo "<p>Chưa có bài kiểm tra cho khóa học này</p>";
                    }
                    echo "</div></div>";
                }
            } else {
                echo "<div class='no-courses'>Bạn chưa được đăng ký khóa học nào</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>