<?php
// Hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
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
        die("Lỗi: Bạn không có quyền truy cập vào '$ten_khoa' và '$id_test'.");
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


// Kiểm tra quyền truy cập
if ($student_id == 1 || $student_id == 2) {
    // Cho phép truy cập
} else {
    echo "Bạn không có quyền truy cập khoá học này";
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = 1"; // PYTHON CƠ BẢN
    $stmt = $conn->query($sql);
    $khoa_hoc = $stmt->fetchColumn();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Content 1</title>

    <style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 20px auto;
        max-width: 1300px;
        background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
        color: #333;
        line-height: 1.6;
    }
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
   
    form {
        max-width: 800px;
        width: 100%;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin: 0 auto;
    }
    p {
        font-size: 1.1em;
    }
    label {
        display: block;
        margin: 10px 0;
        cursor: pointer;
    }
    input[type="radio"] {
        margin-right: 8px;
    }
    button {
        background: #007bff;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        margin: 10px 8px 0 0;
        font-size: 1em;
        cursor: pointer;
        transition: background 0.2s;
    }
   
    img {
        margin: 16px 0;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.10);
    }
    .feedback {
        color: #d9534f;
        font-weight: bold;
        margin: 12px 0;
    }

        
        
    </style>
</head>
<body>

<div class="content-container">
    <h2>Khoá học <?php echo htmlspecialchars($khoa_hoc); ?></h2>
    <p>Hello bạn user<?php echo htmlspecialchars($student_id); ?> - bạn học khoá <?php echo htmlspecialchars($khoa_hoc); ?></p>
    
    
    </div>
</body>
</html>
