<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ma_khoa = '1';
$id_test = '38';
$student_id = $_SESSION['student_id'];

// Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc'];
    $khoahoc_list = explode(',', $khoahoc);
    $khoahoc_list = array_map('intval', $khoahoc_list);
    if (!in_array(intval($ma_khoa), $khoahoc_list)) {
        die("Lỗi: Sinh viên không được đăng ký khóa học này (mã khóa: $ma_khoa).");
    }
} else {
    die("Lỗi: Không tìm thấy thông tin sinh viên với ID: $student_id.");
}
$stmt->close();

// Lấy tên bài test từ id_test
$stmt = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
$stmt->bind_param("i", $id_test);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>alert('ID bài test ($id_test) không tồn tại trong hệ thống. Vui lòng kiểm tra lại!');</script>";
    exit();
}
$row = $result->fetch_assoc();
$id_baitest = $row['ten_test'];
$stmt->close();

// Lấy thông tin khóa học và câu hỏi
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
        die("Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$id_baitest'.");
    }
    
    // Khởi tạo session nếu chưa có
    if (!isset($_SESSION['quiz_data'])) {
        $_SESSION['quiz_data'] = [
            'questions' => $questions,
            'ten_khoa' => $ten_khoa,
            'id_baitest' => $id_baitest,
            'current_index' => 0,
            'answers' => [],
            'score' => 0,
            'highest_score' => 0,
            'attempts' => isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 1,
            'submitted' => false // Thêm trạng thái nộp bài
        ];
    }
    
    // Đảm bảo key 'submitted' tồn tại trong mảng
    if (!isset($_SESSION['quiz_data']['submitted'])) {
        $_SESSION['quiz_data']['submitted'] = false;
    }
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();
$stmt2->close();

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_data = $_SESSION['quiz_data'];
    
    // Xử lý nút "Câu trước"
    if (isset($_POST['prev_question'])) {
        if ($quiz_data['current_index'] > 0) {
            $quiz_data['current_index']--;
            $_SESSION['quiz_data'] = $quiz_data;
        }
    }
    // Xử lý nút "Câu tiếp"
    elseif (isset($_POST['next_question'])) {
        if (isset($_POST['answer'])) {
            $user_answer = $_POST['answer'];
            $current_question = $quiz_data['questions'][$quiz_data['current_index']];
            $is_correct = ($user_answer === $current_question['correct']);
            
            $quiz_data['answers'][$quiz_data['current_index']] = [
                'selected' => $user_answer,
                'is_correct' => $is_correct
            ];
            
            if ($is_correct) {
                $quiz_data['score']++;
                if ($quiz_data['score'] > $quiz_data['highest_score']) {
                    $quiz_data['highest_score'] = $quiz_data['score'];
                }
            }
        }
        
        // Tăng chỉ số câu hỏi nếu không phải là câu cuối cùng
        if ($quiz_data['current_index'] < count($quiz_data['questions']) - 1) {
            $quiz_data['current_index']++;
        }
        
        $_SESSION['quiz_data'] = $quiz_data;
    }
    // Xử lý nút "Nộp bài"
    elseif (isset($_POST['submit_quiz'])) {
        if (isset($_POST['answer'])) {
            $user_answer = $_POST['answer'];
            $current_question = $quiz_data['questions'][$quiz_data['current_index']];
            $is_correct = ($user_answer === $current_question['correct']);
            
            $quiz_data['answers'][$quiz_data['current_index']] = [
                'selected' => $user_answer,
                'is_correct' => $is_correct
            ];
            
            if ($is_correct) {
                $quiz_data['score']++;
                if ($quiz_data['score'] > $quiz_data['highest_score']) {
                    $quiz_data['highest_score'] = $quiz_data['score'];
                }
            }
        }
        
        $quiz_data['submitted'] = true; // Đánh dấu đã nộp bài
        $_SESSION['quiz_data'] = $quiz_data;
    }
    // Xử lý reset bài quiz
    elseif (isset($_POST['reset'])) {
        $quiz_data['attempts']++;
        $quiz_data['score'] = 0;
        $quiz_data['answers'] = [];
        $quiz_data['current_index'] = 0;
        $quiz_data['submitted'] = false;
        $_SESSION['quiz_data'] = $quiz_data;
        $_SESSION['attempts'] = $quiz_data['attempts'];
    }
}

// Lấy số lần thử tối đa
function getTestInfo($conn, $ten_test, $ten_khoa) {
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ma_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $ten_khoa = $row['khoa_hoc'];
        $sql = "SELECT lan_thu FROM test WHERE ten_test = ? AND id_khoa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $ten_test, $ma_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['lan_thu'];
        }
    }
    return 1;
}

$max_attempts = getTestInfo($conn, $id_baitest, $ten_khoa);
$conn->close();

// Đảm bảo các biến session tồn tại trước khi sử dụng
$quiz_data = $_SESSION['quiz_data'];
$current_index = isset($quiz_data['current_index']) ? $quiz_data['current_index'] : 0;
$questions = isset($quiz_data['questions']) ? $quiz_data['questions'] : [];
$answers = isset($quiz_data['answers']) ? $quiz_data['answers'] : [];
$score = isset($quiz_data['score']) ? $quiz_data['score'] : 0;
$highest_score = isset($quiz_data['highest_score']) ? $quiz_data['highest_score'] : 0;
$attempts = isset($quiz_data['attempts']) ? $quiz_data['attempts'] : 1;
$submitted = isset($quiz_data['submitted']) ? $quiz_data['submitted'] : false;
$pass_score = 4;
$is_last_question = ($current_index === count($questions) - 1);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo htmlspecialchars($quiz_data['ten_khoa']); ?></title>
      <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
            font-size: 17px;
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
            transition: all 0.2s;
        }
        
        ul li:hover {
            background-color: #e0e0e0;
        }
        
        ul li label {
            cursor: pointer;
            display: block;
            padding: 5px;
        }
        
        li.correct {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        
        li.incorrect {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        button {
            padding: 10px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        button.submit-btn {
            background-color: #28a745;
        }
        
        button.submit-btn:hover {
            background-color: #218838;
        }
        
        button.reset-btn {
            background-color: #dc3545;
        }
        
        button.reset-btn:hover {
            background-color: #c82333;
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
        
        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .progress-bar {
            height: 20px;
            background-color: #4CAF50;
            border-radius: 5px;
            text-align: center;
            line-height: 20px;
            color: white;
        }
        
        .question-counter {
            text-align: right;
            font-style: italic;
            color: #666;
        }
        
        .result-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .result-summary p {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$submitted): ?>
            <?php 
            $question = $questions[$current_index]; 
            $is_answered = isset($answers[$current_index]);
            ?>
            
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($quiz_data['ten_khoa']); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($quiz_data['id_baitest']); ?></span>
            </h2>
            
            <div class="progress-container">
                <div class="progress-bar" style="width: <?php echo (($current_index + 1) / count($questions)) * 100; ?>%">
                    <?php echo $current_index + 1; ?>/<?php echo count($questions); ?>
                </div>
            </div>
            
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></h3>
                    
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li <?php echo $is_answered && $key === $answers[$current_index]['selected'] ? 
                                ($answers[$current_index]['is_correct'] ? 'class="correct"' : 'class="incorrect"') : ''; ?>>
                                <label>
                                    <input type="radio" name="answer" value="<?php echo $key; ?>" 
                                        <?php echo $is_answered && $key === $answers[$current_index]['selected'] ? 'checked' : ''; ?>
                                        <?php echo $is_answered ? 'disabled' : ''; ?>>
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="btn-group">
                        <button type="submit" name="prev_question" <?php echo $current_index === 0 ? 'disabled' : ''; ?>>
                            ← Câu trước
                        </button>
                        
                        <?php if ($is_last_question): ?>
                            <button type="submit" name="submit_quiz" class="submit-btn">
                                Nộp bài
                            </button>
                        <?php else: ?>
                            <button type="submit" name="next_question">
                                Câu tiếp →
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <div class="question-counter">
                Câu <?php echo $current_index + 1; ?> / <?php echo count($questions); ?>
            </div>
            
        <?php else: ?>
            <h1>Kết quả Quiz - <?php echo htmlspecialchars($quiz_data['ten_khoa']); ?> - <?php echo htmlspecialchars($quiz_data['id_baitest']); ?></h1>
            
            <div class="result-summary">
                <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($quiz_data['ten_khoa']); ?></p>
                <p><strong>Bài test:</strong> <?php echo htmlspecialchars($quiz_data['id_baitest']); ?></p>
                <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
                <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($questions); ?></p>
                <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?> / <?php echo count($questions); ?></p>
                <p><strong>Số lần làm bài:</strong> <?php echo $attempts; ?> / <?php echo $max_attempts; ?></p>
                <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? '<span style="color:green;">Đạt</span>' : '<span style="color:red;">Không đạt</span>'; ?></p>
            </div>
            <hr>
            
            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào!</p>
            <?php else: ?>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-block">
                        <p class="question-text">Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></p>
                        
                        <?php if (!empty($question['image'])): ?>
                            <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                        <?php endif; ?>
                        
                        <ul>
                            <?php foreach ($question['choices'] as $key => $value): ?>
                                <?php
                                $style = '';
                                if (isset($answers[$index])) {
                                    if ($key === $answers[$index]['selected']) {
                                        $style = $answers[$index]['is_correct'] ? 'correct' : 'incorrect';
                                    } elseif ($key === $question['correct']) {
                                        $style = 'correct';
                                    }
                                }
                                ?>
                                <li class="<?php echo $style; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if (isset($answers[$index])): ?>
                            <div class="explanation-block" style="border-color: <?php echo $answers[$index]['is_correct'] ? '#28a745' : '#dc3545'; ?>;">
                                <p><strong>Giải thích:</strong> <?php 
                                    $selected = $answers[$index]['selected'];
                                    echo htmlspecialchars($question['explanations'][$selected]); 
                                ?></p>
                            </div>
                        <?php endif; ?>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form method="POST" action="">
                <button type="submit" name="reset" value="1" class="reset-btn" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>
                    🔁 Làm lại (<?php echo $attempts; ?> / <?php echo $max_attempts; ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>