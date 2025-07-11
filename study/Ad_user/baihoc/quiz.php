<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Lấy giờ chuẩn 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['student_id'])) {
    echo "<script>
        alert('Vui lòng đăng nhập để truy cập!');
        window.location.href = 'login.php';
    </script>";
    exit();
}
 
$ma_khoa = '10'; // Thay đồi khoá học
$id_test = '12'; // Thay đổi test phù hơp

// Database connection
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}


// lấy khoá học từ bảng khoa_hoc
function getCoursesFromDB($conn) {
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    return $courses;
}

// Lấy tên bài test từ id_test
$stmt = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
$stmt->bind_param("i", $id_test);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('ID bài test ($id_test) không tồn tại trong hệ thống. Vui lòng kiểm tra lại!');</script>";
} else {
    $row = $result->fetch_assoc();
    $id_baitest = $row['ten_test'];
}
$stmt->close();

// Lấy thông tin kiểm tra (số lần thử tối đa)
function getTestInfo($conn, $ten_test, $ten_khoa) {
    $courses = getCoursesFromDB($conn);

    $id_khoa = array_search($ten_khoa, $courses);
    if ($id_khoa === false) {
        die("Lỗi: Không tìm thấy khóa học '$ten_khoa'");
    }
    $sql = "SELECT lan_thu FROM test WHERE ten_test = ? AND id_khoa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $ten_test, $id_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['lan_thu'];
    }
    $stmt->close();
    return 1; 
}
// Khởi tạo biến
$ten_khoa = '';
$current_index = isset($_POST['current_index']) ? intval($_POST['current_index']) : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$highest_score = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;
$attempts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 0;
$pass_score = 4; //số câu hỏi qua 


// Lấy tên khoá học và câu hỏi 
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $ten_khoa = $row['khoa_hoc'];
    $stmt2 = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
    $stmt2->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $questions = [];
    while ($row2 = $result2->fetch_assoc()) {
        $questions[] = [
            'id' => $row2['Id_cauhoi'],
            'question' => $row2['cauhoi'],
            'choices' => [
                'A' => $row2['cau_a'],
                'B' => $row2['cau_b'],
                'C' => $row2['cau_c'],
                'D' => $row2['cau_d']
            ],
            'explanations' => [
                'A' => $row2['giaithich_a'],
                'B' => $row2['giaithich_b'],
                'C' => $row2['giaithich_c'],
                'D' => $row2['giaithich_d']
            ],
            'correct' => $row2['dap_an'],
            'image' => $row2['hinhanh']
        ];
    }
    if (count($questions) < 1) {
        die("Lỗi: Bạn không có quyền truy cập vào ");
    }
    $_SESSION['questions'] = $questions;
    $_SESSION['ten_khoa'] = $ten_khoa;
    $_SESSION['id_baitest'] = $id_baitest;
    $_SESSION['current_index'] = 0;
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 1;
    }
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();
$stmt2->close();

// xử lý gửi câu trả lời 
if (isset($_POST['answer']) && isset($_SESSION['questions'])) {
    $user_answer = $_POST['answer'];
    $current_question = $_SESSION['questions'][$current_index];
    $is_correct = ($user_answer === $current_question['correct']);
    $answers[$current_index] = [
        'selected' => $user_answer,
        'is_correct' => $is_correct
    ];
    $_SESSION['answers'] = $answers;
    if ($is_correct) {
        $score++;
        $_SESSION['score'] = $score;
        if ($score > $highest_score) {
            $_SESSION['highest_score'] = $score;
        }
    }
    $current_index++;
    $_SESSION['current_index'] = $current_index;
}

// Xử lý thiết lập lại
if (isset($_POST['reset'])) {
    $attempts++;
    $_SESSION['attempts'] = $attempts;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
    $current_index = 0;
    $score = 0;
    $answers = [];
}

// sổ lần thử tối đa
$max_attempts = getTestInfo($conn, $id_baitest, $ten_khoa);
$conn->close();

?>

<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student');

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    die("<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>");
}

// Kiểm tra khóa học được gán
if (!isset($_SESSION['Khoahoc']) || empty($_SESSION['Khoahoc'])) {
    showNoCourseTemplate();
    exit();
}
// Lấy danh sách khóa học được phép
$allowed_courses = array_filter(explode(',', $_SESSION['Khoahoc']), function($value) {
    return is_numeric($value) && (int)$value > 0;
});

if (empty($allowed_courses)) {
    die("<script>alert('Danh sách khóa học không hợp lệ!'); window.location.href='logout.php';</script>");
}
if (isset($_GET['test_id'])) {
    $id_test = (int)$_GET['test_id'];
    
    // Kiểm tra bài test có thuộc khóa học được phép không
    $stmt_check = $conn->prepare("SELECT t.id_test FROM test t 
                                JOIN khoa_hoc kh ON t.id_khoa = kh.id 
                                WHERE t.id_test = ? AND t.id_khoa IN (".implode(',', array_fill(0, count($allowed_courses), '?')).")");
    
    $params = array_merge([$id_test], $allowed_courses);
    $types = str_repeat('i', count($params));
    $stmt_check->bind_param($types, ...$params);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if ($check_result->num_rows === 0) {
        die("<script>alert('Bạn không có quyền truy cập bài test này!'); window.location.href='select_test.php';</script>");
    }
    $stmt_check->close();
}



// Xác định khóa học hiện tại
$current_course = isset($_GET['course_id']) && in_array($_GET['course_id'], $allowed_courses) 
                ? $_GET['course_id'] 
                : $allowed_courses[0];

// Kết nối database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}


// Lấy thông tin khóa học hiện tại
$current_theme = $course_themes[$current_course] ?? [
    'name' => 'Khóa học ' . $current_course,
    'color' => '#607D8B',
    'class' => 'theme-default'
];


// Lấy danh sách bài test cho khóa học hiện tại
$stmt = $conn->prepare("SELECT t.id_test, t.ten_test, t.lan_thu, t.Pass, t.so_cau_hien_thi 
                       FROM test t WHERE t.id_khoa = ?");
$stmt->bind_param('i', $current_course);
$stmt->execute();
$tests_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_theme['name']; ?> - Hệ Thống Thi Trắc Nghiệm</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .course-banner {
            background: <?php echo $current_theme['color']; ?>;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .course-switcher {
            margin-bottom: 20px;
            text-align: center;
        }
        .course-switcher select {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        .test-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        .test-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid <?php echo $current_theme['color']; ?>;
        }
        .test-item h3 {
            margin-top: 0;
            color: <?php echo $current_theme['color']; ?>;
        }
        .btn-start {
            display: inline-block;
            padding: 8px 15px;
            background: <?php echo $current_theme['color']; ?>;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn-start:hover {
            opacity: 0.9;
        }
        .user-info {
            text-align: right;
            margin-bottom: 10px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .no-tests {
            text-align: center;
            color: #777;
            font-style: italic;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="user-info">
            Xin chào, <strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong> | 
            <button class="logout-btn" onclick="window.location.href='logout.php'">Đăng xuất</button>
        </div>
        
        <div class="course-banner">
            <?php echo $current_theme['name']; ?>
        </div>
        
        <?php if (count($allowed_courses) > 1): ?>
        <!-- <div class="course-switcher">
            <select onchange="window.location.href='?course_id='+this.value">
                <?php foreach ($allowed_courses as $course_id): ?>
                    <?php $course_name = $course_themes[$course_id]['name'] ?? "Khóa học $course_id"; ?>
                    <option value="<?php echo $course_id; ?>" <?php echo $course_id == $current_course ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div> -->
        <?php endif; ?>
        
        <div class="header">
            <h2>Danh sách bài kiểm tra</h2>
        </div>
        
        <div class="test-list">
            <?php if ($tests_result->num_rows > 0): ?>
                <?php while ($test = $tests_result->fetch_assoc()): ?>
                    <div class="test-item">
                        <h3><?php echo htmlspecialchars($test['ten_test']); ?></h3>
                        <p><strong>Số lần thi:</strong> <?php echo $test['lan_thu']; ?></p>
                        <p><strong>Điểm đạt:</strong> <?php echo $test['Pass']; ?>%</p>
                        <p><strong>Số câu hỏi:</strong> <?php echo $test['so_cau_hien_thi']; ?></p>
                        <a href="take_test.php?test_id=<?php echo $test['id_test']; ?>" class="btn-start">
                            Bắt đầu làm bài
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-tests">
                    Hiện không có bài kiểm tra nào trong khóa học này
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
// Đóng kết nối
$stmt->close();
$conn->close();

function showNoCourseTemplate() {
    echo '<!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Không có khóa học</title>
        <style>
            body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 100px auto; background: white; padding: 30px; 
                        border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
            .logout-btn { background: #dc3545; color: white; border: none; padding: 10px 20px; 
                         border-radius: 5px; text-decoration: none; margin-top: 20px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Không có khóa học nào được gán</h2>
            <p>Vui lòng liên hệ quản trị viên để được gán khóa học.</p>
            <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
    </body>
    </html>';
}
?>
