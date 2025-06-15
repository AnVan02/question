<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    echo "<script>
        alert('Vui lòng đăng nhập để truy cập!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Kết nối database
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Cấu hình khóa học
$ma_khoa = '1'; // Mã khóa học Hóa học
$id_test = '19'; // ID bài test
$student_id = $_SESSION['student_id'];

// Kiểm tra quyền truy cập
$stmt = $conn->prepare("SELECT s.Student_ID, s.Ten, s.Khoahoc, kh.khoa_hoc 
                       FROM students s 
                       LEFT JOIN khoa_hoc kh ON FIND_IN_SET(kh.id, s.Khoahoc)
                       WHERE s.Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student_info = $result->fetch_assoc();
    $allowed_courses = explode(',', $student_info['Khoahoc']);
    
    if (!in_array($ma_khoa, $allowed_courses)) {
        echo "<script>
            alert('Bạn không có quyền truy cập khóa học này!');
            window.location.href = 'login.php';
        </script>";
        exit();
    }
} else {
    echo "<script>
        alert('Không tìm thấy thông tin sinh viên!');
        window.location.href = 'login.php';
    </script>";
    exit();
}
$stmt->close();

// Lấy thông tin khóa học
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
$course_info = $result->fetch_assoc();
$ten_khoa = $course_info['khoa_hoc'];
$stmt->close();

// Lấy thông tin bài test
$stmt = $conn->prepare("SELECT * FROM test WHERE id_test = ? AND id_khoa = ?");
$stmt->bind_param("is", $id_test, $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
$test_info = $result->fetch_assoc();

if (!$test_info) {
    echo "<script>
        alert('Không tìm thấy bài test này!');
        window.location.href = 'login.php';
    </script>";
    exit();
}
$stmt->close();

// Lấy danh sách câu hỏi
$stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
$stmt->bind_param("ss", $ten_khoa, $test_info['ten_test']);
$stmt->execute();
$result = $stmt->get_result();
$questions = [];

while ($row = $result->fetch_assoc()) {
    $questions[] = [
        'id' => $row['Id_cauhoi'],
        'question' => $row['cauhoi'],
        'choices' => [
            'A' => $row['cau_a'],
            'B' => $row['cau_b'],
            'C' => $row['cau_c'],
            'D' => $row['cau_d']
        ],
        'explanations' => [
            'A' => $row['giaithich_a'],
            'B' => $row['giaithich_b'],
            'C' => $row['giaithich_c'],
            'D' => $row['giaithich_d']
        ],
        'correct' => $row['dap_an'],
        'image' => $row['hinhanh']
    ];
}
$stmt->close();

// Lấy danh sách khóa học của học sinh
$stmt = $conn->prepare("SELECT s.Student_ID, s.Ten, s.Khoahoc, kh.id, kh.khoa_hoc 
                       FROM students s 
                       LEFT JOIN khoa_hoc kh ON FIND_IN_SET(kh.id, s.Khoahoc)
                       WHERE s.Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$enrolled_courses = [];

while ($row = $result->fetch_assoc()) {
    $enrolled_courses[] = [
        'id' => $row['id'],
        'name' => $row['khoa_hoc']
    ];
}
$stmt->close();

// Xử lý chọn khóa học
if (isset($_POST['select_course'])) {
    $ma_khoa = $_POST['select_course'];
    $_SESSION['selected_course'] = $ma_khoa;
    $_SESSION['current_index'] = 0;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
}

// Lấy khóa học đã chọn
$ma_khoa = isset($_SESSION['selected_course']) ? $_SESSION['selected_course'] : $enrolled_courses[0]['id'];

// Lưu thông tin vào session
$_SESSION['questions'] = $questions;
$_SESSION['ten_khoa'] = $ten_khoa;
$_SESSION['id_baitest'] = $test_info['ten_test'];
$_SESSION['current_index'] = 0;
$_SESSION['attempts'] = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 1;
$_SESSION['score'] = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$_SESSION['highest_score'] = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;

// Xử lý gửi câu trả lời
if (isset($_POST['answer']) && isset($_SESSION['questions'])) {
    $current_index = $_SESSION['current_index'];
    $user_answer = $_POST['answer'];
    $current_question = $_SESSION['questions'][$current_index];
    
    if (!isset($_SESSION['answers'])) {
        $_SESSION['answers'] = [];
    }
    
    $_SESSION['answers'][$current_index] = [
        'selected' => $user_answer,
        'is_correct' => ($user_answer === $current_question['correct'])
    ];
    
    if ($_SESSION['answers'][$current_index]['is_correct']) {
        $_SESSION['score']++;
        if ($_SESSION['score'] > $_SESSION['highest_score']) {
            $_SESSION['highest_score'] = $_SESSION['score'];
        }
    }
    
    $_SESSION['current_index']++;
}

// Xử lý reset bài test
if (isset($_POST['reset'])) {
    $_SESSION['attempts']++;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
}

// Kiểm tra số lần thử
if ($_SESSION['attempts'] > $test_info['lan_thu']) {
    echo "<script>
        alert('Bạn đã hết số lần thử cho phép!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Kiểm tra đã hoàn thành bài test
$current_index = $_SESSION['current_index'];
$is_completed = $current_index >= count($questions);
$pass_score = $test_info['Pass'];
$is_passed = $_SESSION['score'] >= $pass_score;

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài kiểm tra <?php echo htmlspecialchars($ten_khoa); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .question {
            margin-bottom: 30px;
        }
        .question img {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
        }
        .choices {
            display: grid;
            gap: 10px;
            margin-top: 15px;
        }
        .choice {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .choice:hover {
            background-color: #f8f9fa;
        }
        .choice.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .choice.correct {
            background-color: #c8e6c9;
            border-color: #4caf50;
        }
        .choice.incorrect {
            background-color: #ffcdd2;
            border-color: #f44336;
        }
        .explanation {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: none;
        }
        .explanation.show {
            display: block;
        }
        .progress {
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .progress-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #007bff;
            transition: width 0.3s;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .result {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .result h2 {
            color: #28a745;
        }
        .course-selection {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .course-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 10px;
        }
        .course-form select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hệ thống học tập</h1>
            <p>Xin chào, <?php echo htmlspecialchars($student_info['Ten']); ?>!</p>
        </div>

        <?php if (!$is_completed): ?>
            <div class="progress">
                <p>Câu hỏi: <?php echo $current_index + 1; ?>/<?php echo count($questions); ?></p>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: <?php echo ($current_index / count($questions)) * 100; ?>%"></div>
                </div>
            </div>

            <div class="question">
                <?php
                $current_question = $questions[$current_index];
                ?>
                <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($current_question['question']); ?></h3>
                
                <?php if (!empty($current_question['image'])): ?>
                    <img src="<?php echo htmlspecialchars($current_question['image']); ?>" alt="Hình ảnh câu hỏi">
                <?php endif; ?>

                <form method="post" class="choices">
                    <?php foreach ($current_question['choices'] as $key => $choice): ?>
                        <label class="choice">
                            <input type="radio" name="answer" value="<?php echo $key; ?>" required>
                            <?php echo htmlspecialchars($choice); ?>
                        </label>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary">Trả lời</button>
                </form>
            </div>
        <?php else: ?>
            <div class="result">
                <h2>Kết quả bài kiểm tra</h2>
                <p>Điểm số: <?php echo $_SESSION['score']; ?>/<?php echo count($questions); ?></p>
                <p>Trạng thái: <?php echo $is_passed ? 'Đạt' : 'Không đạt'; ?></p>
                
                <?php if (!$is_passed && $_SESSION['attempts'] < $test_info['lan_thu']): ?>
                    <form method="post">
                        <input type="hidden" name="reset" value="1">
                        <button type="submit" class="btn btn-primary">Thử lại</button>
                    </form>
                <?php endif; ?>
                
                <a href="login.php" class="btn btn-danger">Quay lại</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
