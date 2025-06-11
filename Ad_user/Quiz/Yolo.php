<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Lấy giờ chuẩn 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "study");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$student_id = $_SESSION ['student_id'];

// Kiểm tra quyền truy cập
if ($student_id == 1 ) {
    // Cho phép truy cập
} else {
    echo "Bạn không có quyền truy cập khoá học này";
    exit();
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
$ma_khoa = 'K003';// Thay đồi khoá học
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
    $id_baitest = 'Giữa kỳ'; // Thay đổi phù hợp với cau hỏi 
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
        die("Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$id_baitest'.");
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo htmlspecialchars($ten_khoa); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #2c3e50;
            text-align: center;
        }
        .question-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 24px;
            margin-bottom: 30px;
            border-left: 6px solid #007bff;
            transition: box-shadow 0.2s;
        }
        
        .question-box h3 {
            color: #007bff;
            margin-top: 0;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f1f1f1;
        }
        ul li label {
            font-size: 17px;
            cursor: pointer;
        }
        li.correct {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        li.incorrect {
            background-color: #d4edda;
            color:rgb(255, 5, 5);
            font-weight: bold;
        }
        button, a.try-again, a.back-to-quiz {
            padding: 10px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
       
        a.try-again.disabled {
            background-color: #ccc;
            pointer-events: none;
            cursor: not-allowed;
        }
        img {
            max-width: 300px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #eee;
            display: block;
        }
        .explanation-block {
            margin-top: 10px;
            padding: 15px;
            border-left: 6px solid;
            background-color: #fff3cd;
            border-radius: 6px;
        }
        .correct-answer {
            color: #2e7d32;
            font-weight: bold;
        }
        .no-answers {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($current_index < count($_SESSION['questions'])): ?>
            <?php $question = $_SESSION['questions'][$current_index]; ?>
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($ten_khoa); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($id_baitest); ?></span>
            </h2>
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li><label><input type="radio" name="answer" value="<?php echo $key; ?>" required> <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?></label></li>
                        <?php endforeach; ?>
                    </ul>
                    <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
                    <button type="submit">Trả lời »</button>
                </div>
            </form>
        <?php else: ?>
            <h1> Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?> </h1>
            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Số lần làm bài:</strong> <?php echo $attempts; ?> / <?php echo $max_attempts; ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>
            <hr>
            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                    <div class="question-block">
                        <p class="question-text">Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></p>
                        <?php if (!empty($question['image'])): ?>
                            <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                        <?php endif; ?>
                        <ul>
                            <?php foreach ($question['choices'] as $key => $value): ?>
                                <?php
                                $style = '';
                                $icon = '';
                                if (isset($answers[$index]['selected']) && $key === $answers[$index]['selected']) {
                                    $style = $answers[$index]['is_correct'] ? 'correct' : 'incorrect';
                                    $icon = $answers[$index]['is_correct'] ? 'grean' : 'red';
                                }
                                ?>
                                <li class="<?php echo $style; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?> <?php echo $icon; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (isset($answers[$index]['selected'])): ?>
                            
                            <div class="explanation-block" style="border-color: <?php echo $answers[$index]['is_correct'] ? 'orange' : 'red'; ?>;">
                                <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$question['correct']]); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="explanation-block" style="border-color: orange;">
                                <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$question['correct']]); ?></p>
                            </div>
                        <?php endif; ?>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <form method="POST" action="">
                <button type="submit" name="reset" value="1" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>🔁 Làm lại (<?php echo $attempts; ?> / <?php echo $max_attempts; ?>)</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>