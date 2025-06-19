<?php
session_start();

// Hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

// Hàm kết nối CSDL
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Khởi tạo biến
$student_data = [];
$questions = [];
$answers = [];
$message = "";
$ten_khoa = "";
$ten_baitest = "";
$highest_score = 0;
$status = "";

// Lấy biến từ URL
$ma_khoa = $_GET['ma_khoa'] ?? '';
$id_baitest = $_GET['id_baitest'] ?? '';
$mode = $_GET['mode'] ?? '';
$student_id = $_GET['student_id'] ?? '';

if ($mode == 'edit' && !empty($ma_khoa) && !empty($id_baitest) && !empty($student_id)) {
    // Kết nối CSDL chỉ khi có đủ tham số
    $conn = dbconnect();

    // Lấy thông tin sinh viên từ DB
    $stmt = $conn->prepare("SELECT Student_ID, Khoahoc FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $student_data = $row;
        // Kiểm tra quyền truy cập khóa học
        $khoahoc_list = array_map('intval', explode(',', $row['Khoahoc']));
        if (!in_array(intval($ma_khoa), $khoahoc_list)) {
            $message = "Lỗi: Sinh viên không được đăng ký khóa học này (mã khóa: $ma_khoa).";
        }
    } else {
        $message = "Lỗi: Không tìm thấy thông tin sinh viên với ID: $student_id.";
    }
    $stmt->close();

    if (empty($message)) {
        // Lấy tên khóa học
        $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("s", $ma_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $ten_khoa = $row['khoa_hoc'];
        } else {
            $message = "Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'";
        }
        $stmt->close();
    }

    if (empty($message)) {
        // Lấy tên bài test
        $stmt = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
        $stmt->bind_param("s", $id_baitest);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $message = "ID bài test ($id_baitest) không tồn tại trong hệ thống.";
        } else {
            $row = $result->fetch_assoc();
            $ten_baitest = $row['ten_test'];
        }
        $stmt->close();
    }

    if (empty($message)) {
        // Lấy thông tin kết quả từ ket_qua
        $stmt = $conn->prepare("SELECT kq_cao_nhat, tt_bai_test FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $stmt->bind_param("sis", $student_id, $ma_khoa, $id_baitest);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $highest_score = $row['kq_cao_nhat'];
            $status = $row['tt_bai_test'];
        } else {
            $message = "Chưa có kết quả cho bài test này.";
        }
        $stmt->close();
    }

    if (empty($message)) {
        // Lấy câu hỏi từ quiz
        $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
        $stmt->bind_param("ss", $ten_khoa, $ten_baitest);
        $stmt->execute();
        $result = $stmt->get_result();
        
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
        
        if (count($questions) < 1) {
            $message = "Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$ten_baitest'.";
        }
        $stmt->close();

        // Lấy câu trả lời của sinh viên từ trường tt_bai_test
        if (!empty($status)) {
            $answer_parts = explode(', ', $status);
            foreach ($answer_parts as $part) {
                if (preg_match('/Câu (\d+):\s*([A-D])/', $part, $matches)) {
                    $question_num = (int)$matches[1];
                    $answers[$question_num] = $matches[2];
                }
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu kết quả bài test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
            font-size: 17px;
            color: #333;
            max-width: 1100px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .form-container {
            background-color: #e0f7fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-student {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 20px;
            margin-bottom: 15px;
            justify-content: center;
        }

        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
        }

        h3 {
            text-align: center;
            font-size: 1.5rem;
            color: #333;
        }

        input[type="text"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }

        button:hover {
            background-color: #45a049;
        }

        .message, .error {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
        }

        .message {
            color: green;
            background-color: #d4edda;
        }

        .error {
            color: red;
            background-color: #f8d7da;
        }

        .question-block {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 10px;
        }

        .correct {
            color: green;
            font-weight: bold;
        }

        .incorrect {
            color: red;
            font-weight: bold;
        }

        .score-info {
            background-color: #e7f3fe;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            max-width: 1100px;
            margin:  40px auto;
            box-sizing: border-box;
        }

        .question-block img {
            max-width: 300px;
            display: block;
            margin-top: 10px;
        }

        form {
            margin-bottom: 30px;
        }

        input[type="text"]:focus {
            border-color: #66afe9;
            outline: none;
        }

        input[type="text"]:hover {
            border-color: #888;
        }

        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        button:disabled:hover {
            background-color: #cccccc;
        }
        
    </style>
</head>
<body>
    <h1>Tra cứu kết quả bài test</h1>
    
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-container">
            <h3>Nhập thông tin tra cứu</h3>
            <div class="form-student">
                <label><strong>Student_ID:</strong></label>
                <input type="text" name="student_id" value="<?php echo htmlspecialchars($student_id ?? ''); ?>" required>
                
                <label><strong>Khoa_ID:</strong></label>
                <input type="text" name="ma_khoa" value="<?php echo htmlspecialchars($ma_khoa ?? ''); ?>" required>
                
                <label><strong>Test_ID:</strong></label>
                <input type="text" name="id_baitest" value="<?php echo htmlspecialchars($id_baitest ?? ''); ?>" required>
            </div>
            <input type="hidden" name="mode" value="edit">
            <button type="submit">Check</button>
        </div>
    </form>

    <?php if (!empty($message)): ?>
        <div class="<?php echo strpos($message, 'Lỗi') === false ? 'message' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
            <p><strong>Kết quả :</strong> <?php echo count($questions); ?>/<?php echo $highest_score; ?></p>
            
    <?php if ($mode == 'edit' && empty($message) && !empty($student_data)): ?>
        <!-- <div class="score-info">
            <h2>Kết quả bài test</h2>
            <p><strong>Sinh viên:</strong> <?php echo htmlspecialchars($student_data['Student_ID']); ?></p>
            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($ten_baitest); ?></p>
            <p><strong>Điểm số:</strong> <?php echo $highest_score; ?>/<?php echo count($questions); ?></p>
        </div> -->

        <h3 >Chi tiết bài làm</h3>
        <?php foreach ($questions as $index => $question): ?>
            <div class="question-block">
                <p><strong>Câu <?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question']); ?></p>
                <?php if (!empty($question['image'])): ?>
                    <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi" style="max-width: 300px;">
                <?php endif; ?>
                
                <ul>
                    <?php foreach ($question['choices'] as $key => $value): ?>
                        <?php
                        $question_num = $index + 1;
                        $is_selected = isset($answers[$question_num]) && $key === $answers[$question_num];
                        $is_correct = $key === $question['correct'];
                        $class = '';
                        
                        if ($is_selected) {
                            $class = $is_correct ? 'correct' : 'incorrect';
                        } elseif ($is_correct) {
                            $class = 'correct';
                        }
                        ?>
                        <li class="<?php echo $class; ?>">
                            <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                            <!-- <?php if ($is_correct): ?> (Đáp án đúng) <?php endif; ?> -->
                            <?php if ($is_selected && !$is_correct): ?> X && ? <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>