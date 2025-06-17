<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Lấy giờ chuẩn 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ten_khoa = '';
$current_index = isset($_POST['current_index']) ? intval($_POST['current_index']) : 0;


// Bắt đầu với nhập mã khoá học
if (isset($_POST['khoa_id'])) {
    $ma_khoa = $_POST['khoa_id'];
    $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
    $stmt->bind_param("s", $ma_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $ten_khoa = $row['khoa_hoc'];
        // Lấy câu hỏi từ bảng quiz theo tên khoá học
        $stmt2 = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ?");
        $stmt2->bind_param("s", $ten_khoa);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $questions = [];
        while ($row2 = $result2->fetch_assoc()) {
            $questions[] = $row2;
        }
        $_SESSION['questions'] = $questions;
        $_SESSION['ten_khoa'] = $ten_khoa;
        $_SESSION['current_index'] = 0;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Khi đã có session câu hỏi
if (isset($_SESSION['questions'])) {
    $questions = $_SESSION['questions'];
    $ten_khoa = $_SESSION['ten_khoa'];
    $current_index = isset($_POST['current_index']) ? intval($_POST['current_index']) : $_SESSION['current_index'];
    if (isset($_POST['answer'])) {
        $current_index++;
        $_SESSION['current_index'] = $current_index;
    }
} else {
    $questions = [];
}

// lấy số lần thử
function getTestInfo($ten_test, $ten_khoa) {
    $conn = dbconnect();
    $courses = getCoursesFromDB();
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
        $conn->close();
        return $row['lan_thu'];
    }
    $stmt->close();
    $conn->close();
    return 1;
}


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu câu hỏi theo mã khoá học</title>
</head>
<body>
  
    <?php if (!isset($_SESSION['questions'])): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="khoa_id">Mã khoá học:</label>
                <input type="text" name="khoa_id" id="khoa_id" placeholder="Nhập mã khoá học (VD: K001, K002...)" required>
                <button type="submit">Bắt đầu</button>
            </div>
        </form>
    <?php elseif ($current_index < count($questions)): ?>
        <?php $question = $questions[$current_index]; ?>
         <h2>
            <?php
                echo "Môn học: <span style='color:#1565c0; margin:5px'>" . htmlspecialchars($questions[0]['ten_khoa']) . "</span><br>";
                echo "Bài thi: <span style='color:#e67e22; margin:5px'>" . htmlspecialchars($questions[0]['id_baitest']) . "</span>";               
            ?>
        </h2>
         <form method="POST" action="">
            <div class="question-box">
                <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($question['cauhoi']); ?></h3>
                <?php if (!empty($question['hinhanh'])): ?>
                    <img src="<?php echo htmlspecialchars($question['hinhanh']); ?>" alt="Hình ảnh câu hỏi" style="max-width:300px;display:block;margin:10px 0;">
                <?php endif; ?>
                <ul style="list-style: none; padding: 0;">
                    <li><label><input type="radio" name="answer" value="A" required> A. <?php echo htmlspecialchars($question['cau_a']); ?></label></li>
                    <li><label><input type="radio" name="answer" value="B"> B. <?php echo htmlspecialchars($question['cau_b']); ?></label></li>
                    <li><label><input type="radio" name="answer" value="C"> C. <?php echo htmlspecialchars($question['cau_c']); ?></label></li>
                    <li><label><input type="radio" name="answer" value="D"> D. <?php echo htmlspecialchars($question['cau_d']); ?></label></li>
                </ul>
                <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
                <button type="submit">Trả lời &raquo;</button>
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

    
    

    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #f8f9fa, #e0f7fa);
            margin: 0;
            padding: 20px;
            
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);

        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 7px;
        }
        .question-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 24px 20px 18px 20px;
            margin-bottom: 30px;
            border-left: 6px solid #007bff;
            transition: box-shadow 0.2s;
        }
    
        .question-box h3 {
            color: #007bff;
            margin-top: 0;
        }
        .question-box ul li label {
            font-size: 17px;
            cursor: pointer;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #bbb;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
     
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
        }
        h3 {
            color: #333;
        }
        img {
            border-radius: 6px;
            margin-bottom: 10px;
            border: 1px solid #eee;
        }
        .error {
            color: #e74c3c;
            margin-top: 10px;
        }
        
    </style>
</body>
</html>