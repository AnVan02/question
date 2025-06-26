<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
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
        'Tiếng anh' => 'Tienganh.php',
        'Hoá học' => 'Hoahoc.php'
    ];
    return $course_files[$course_name] ?? '';
}

// Kết nối CSDL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Danh sách khóa học
$allowed_courses = explode(',', $_SESSION['khoahoc']);
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
        .course-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
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
        if (!empty($allowed_courses)) {
            foreach ($allowed_courses as $course_id) {
                // Lấy thông tin khóa học
                $stmt = $conn->prepare("SELECT kh.khoa_hoc, t.ten_test, t.lan_thu, t.Pass, t.so_cau_hien_thi 
                                        FROM khoa_hoc kh 
                                        LEFT JOIN test t ON kh.id = t.id_khoa 
                                        WHERE kh.id = ?");
                $stmt->execute([$course_id]);
                $course = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($course) {
                    echo "<div class='course-card'>";
                    echo "<div class='course-title'>" . htmlspecialchars($course['khoa_hoc']) . "</div>";
                    echo "<div class='course-info'>";

                    if (!empty($course['ten_test'])) {
                        echo "<p><strong>Tên bài kiểm tra:</strong> " . htmlspecialchars($course['ten_test']) . "</p>";
                        echo "<p><strong>Số lần thử:</strong> " . (!is_null($course['lan_thu']) ? $course['lan_thu'] : "Không có") . "</p>";
                        echo "<p><strong>Điểm đạt:</strong> " . (!is_null($course['Pass']) ? $course['Pass'] . "%" : "Không có") . "</p>";
                        echo "<p><strong>Số câu hỏi:</strong> " . (!is_null($course['so_cau_hien_thi']) ? $course['so_cau_hien_thi'] : "Không có") . "</p>";

                        $course_file = getCourseFileName($course['khoa_hoc']);
                        if ($course_file) {
                            echo "<a href='../login/$course_file' class='start-btn'>Bắt đầu làm bài</a>";
                        } else {
                            echo "<p><em>Không tìm thấy file bài kiểm tra</em></p>";
                        }
                    } else {
                        echo "<p><em>Chưa có bài kiểm tra cho khóa học này.</em></p>";
                    }

                    echo "</div></div>";
                }
            }
        } else {
            echo "<div class='no-courses'>Bạn chưa được đăng ký khóa học nào</div>";
        }
        ?>
    </div>
</div>
</body>
</html>
