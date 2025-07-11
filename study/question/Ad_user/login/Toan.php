<?php
// Đặt múi giờ và bật báo cáo lỗi
date_default_timezone_set('Asia/Ho_Chi_Minh');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bắt đầu phiên làm việc
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['student_id'])) {
    echo "<script>
        alert('Vui lòng đăng nhập để truy cập!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Cấu hình khóa học và bài kiểm tra
$ma_khoa = '4'; // ID khóa học Hóa học
$id_test = '37'; // ID bài kiểm tra
$student_id = $_SESSION['student_id'];


// Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if ($student) {
    // Chuyển chuỗi Khoahoc thành mảng
    $allowed_courses = explode(',', $student['Khoahoc']);
    
    // Kiểm tra xem ma_khoa có nằm trong mảng allowed_courses không
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

// Lấy thông tin bài kiểm tra
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

// Lấy câu hỏi
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

// Lưu thông tin vào session
$_SESSION['questions'] = $questions;
$_SESSION['ten_khoa'] = $ten_khoa;
$_SESSION['id_baitest'] = $test_info['ten_test'];
$_SESSION['current_index'] = isset($_SESSION['current_index']) ? $_SESSION['current_index'] : 0;
$_SESSION['attempts'] = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 1;
$_SESSION['score'] = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$_SESSION['highest_score'] = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;

// Xử lý điều hướng câu hỏi
if (isset($_GET['question'])) {
    $requested_index = (int)$_GET['question'];
    if ($requested_index >= 0 && $requested_index < count($questions)) {
        $_SESSION['current_index'] = $requested_index;
    } else {
        header("Location: ?question=" . $_SESSION['current_index']);
        exit();
    }
}

// Xử lý nộp bài kiểm tra
if (isset($_GET['submit']) && $_GET['submit'] == 1) {
    $_SESSION['current_index'] = count($questions); // Đánh dấu bài kiểm tra đã hoàn thành
}

// Xử lý nộp câu trả lời hoặc điều hướng
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
    
    // Xử lý điều hướng sau khi lưu câu trả lời
    if (isset($_POST['navigate'])) {
        $direction = $_POST['navigate'];
        if ($direction === 'next' && $current_index < count($questions) - 1) {
            $_SESSION['current_index']++;
        } elseif ($direction === 'prev' && $current_index > 0) {
            $_SESSION['current_index']--;
        }
        header("Location: ?question=" . $_SESSION['current_index']);
        exit();
    } elseif (isset($_POST['save_answer'])) {
        // Chỉ lưu câu trả lời, không điều hướng
        header("Location: ?question=" . $_SESSION['current_index']);
        exit();
    } elseif (isset($_POST['submit_test'])) {
        // Xử lý nộp bài kiểm tra
        $_SESSION['current_index'] = count($questions);
        header("Location: ?submit=1");
        exit();
    }
}

// Xử lý đặt lại bài kiểm tra
if (isset($_POST['reset'])) {
    $_SESSION['attempts']++;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
}

// Kiểm tra giới hạn số lần thử
if ($_SESSION['attempts'] > $test_info['lan_thu']) {
    echo "<script>
        alert('Bạn đã hết số lần thử cho phép!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Kiểm tra xem bài kiểm tra đã hoàn thành chưa
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eef2f7;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.2em;
            margin-bottom: 15px;
        }

        .result {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            /* Đảm bảo text-align: center; ở đây không gây ảnh hưởng đến phần details */
            /* Nếu dòng này đang hoạt động, hãy loại bỏ nó */
        }

        .result h2 {
            color: #28a745;
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
        
        /* Các style mới cho phần câu hỏi trong trang kết quả */
        .question-block {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }

        .question-block:hover {
            transform: translateY(-2px);
        }

        .question-text {
            font-size: 1.2em;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .question-block img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .question-block ul {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .question-block li {
            padding: 12px 15px;
            margin: 8px 0;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .question-block li.correct {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .question-block li.incorrect {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .explanation-block {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 8px 8px 0;
        }

        .explanation-block p {
            margin: 0;
            color: #856404;
        }
        /* Kết thúc các style mới cho phần câu hỏi trong trang kết quả */


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
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .result {
            /* Điều này gây ra vấn đề căn giữa, đã chuyển các thiết lập cụ thể sang result-details */
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .result h2 {
            color: #28a745;
        }
        .navigation-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .question-list {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .question-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .question-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .question-number:hover {
            background-color: #dee2e6;
        }
        .question-number.answered {
            background-color: #007bff;
            color: white;
        }
        .question-number.current {
            border: 2px solid #28a745;
        }

        .no-answers {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }

        .back-to-quiz {
            color: #4e73df;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-to-quiz:hover {
            color: #2e59d9;
            text-decoration: underline;
        }

        hr {
            border: none;
            border-top: 2px solid #eef2f7;
            margin: 25px 0;
        }

        /* Các style cho thanh tiến độ */
        .progress {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .progress-bar {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
            transition: width 0.3s ease;
        }

        /* Thiết kế đáp ứng (Responsive design) */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .result h1 {
                font-size: 1.5em;
            }

            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
        
        /* Các style cụ thể cho .result-details */
        .result-details {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px; /* Thêm khoảng cách để phân tách */
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            text-align: left; /* Đảm bảo khối này căn trái */
        }

        .result-details h1 {
            color: #2c3e50;
            font-size: 1.8em;
            margin-bottom: 20px;
            text-align: center; /* Tiêu đề này vẫn căn giữa */
            border-bottom: 2px solid #eef2f7;
            padding-bottom: 15px;
        }

        .result-details p {
            text-align: left; /* Đảm bảo các đoạn văn trong result-details căn trái */
        }
    </style>
    <script>
        function confirmSubmit() {
            return confirm('Bạn có chắc chắn muốn nộp bài?');
        }

        function validateForm() {
            const radios = document.getElementsByName('answer');
            let isChecked = false;
            for (let radio of radios) {
                if (radio.checked) {
                    isChecked = true;
                    break;
                }
            }
            if (!isChecked) {
                alert('Vui lòng chọn một đáp án trước khi lưu!');
                return false;
            }
            return true;
        }

        // Cảnh báo người dùng nếu điều hướng mà chưa lưu câu trả lời
        let formModified = false;
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.getElementsByName('answer');
            for (let radio of radios) {
                radio.addEventListener('change', function() {
                    formModified = true; // Đặt true khi người dùng thay đổi lựa chọn
                });
            }

            const navButtons = document.querySelectorAll('.btn-secondary, .question-number');
            navButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Kiểm tra xem có bất kỳ đáp án nào đang được chọn không
                    let isAnyRadioChecked = Array.from(radios).some(radio => radio.checked);

                    // Nếu form đã được chỉnh sửa (người dùng đã tương tác) VÀ hiện tại có một đáp án đang được chọn
                    if (formModified && isAnyRadioChecked) {
                        if (!confirm('Bạn chưa lưu câu trả lời. Bạn có muốn tiếp tục?')) {
                            e.preventDefault(); // Ngăn chặn chuyển trang nếu người dùng chọn "Hủy"
                        }
                    }
                    // Nếu formModified là false (chưa tương tác) HOẶC không có đáp án nào được chọn,
                    // thì cứ để form submit. Hàm validateForm() sẽ xử lý thông báo nếu chưa chọn đáp án.
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <?php if (!$is_completed): ?>
            <div class="header">
                <h1>Bài kiểm tra: <?php echo htmlspecialchars($ten_khoa); ?></h1>
                <p>Lần thử: <?php echo $_SESSION['attempts']; ?>/<?php echo $test_info['lan_thu']; ?></p>
                <p>Thời gian: <?php echo date('h:i A d/m/Y'); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$is_completed): ?>
            <div class="progress">
                <p>Câu hỏi: <?php echo $current_index + 1; ?>/<?php echo count($questions); ?></p>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: <?php echo (($current_index + 1) / count($questions)) * 100; ?>%"></div>
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

                <form method="post" class="choices" onsubmit="return validateForm()" id="answer-form">
                    <?php 
                    $previous_answer = isset($_SESSION['answers'][$current_index]) ? $_SESSION['answers'][$current_index]['selected'] : null;
                    
                    foreach ($current_question['choices'] as $key => $choice): 
                        $is_selected = ($previous_answer === $key);
                    ?>
                        <label class="choice <?php echo $is_selected ? 'selected' : ''; ?>">
                            <input type="radio" name="answer" value="<?php echo $key; ?>" <?php echo $is_selected ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($choice); ?>
                        </label>
                    <?php endforeach; ?>

                    <div class="navigation-buttons">
                        <?php if ($current_index > 0): ?>
                            <button type="submit" name="navigate" value="prev" class="btn btn-secondary">
                                ← Câu trước
                            </button>
                        <?php endif; ?>

                        <button type="submit" name="save_answer" value="1" class="btn btn-primary">
                            Lưu câu trả lời
                        </button>

                        <?php if ($current_index < count($questions) - 1): ?>
                            <button type="submit" name="navigate" value="next" class="btn btn-secondary">
                                Câu sau →
                            </button>
                        <?php endif; ?>

                        <?php if ($current_index == count($questions) - 1): ?>
                            <button type="submit" name="submit_test" value="1" onclick="return confirmSubmit()" class="btn btn-primary">
                                Nộp bài
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="result">
                <div class="result-details">
                    <h1>Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($test_info['ten_test']); ?></h1>
                    <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
                    <p><strong>Bài test:</strong> <?php echo htmlspecialchars($test_info['ten_test']); ?></p>
                    <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
                    <p><strong>Tổng điểm:</strong> <?php echo $_SESSION['score']; ?> / <?php echo count($questions); ?></p>
                    <p><strong>Điểm cao nhất:</strong> <?php echo $_SESSION['highest_score']; ?> / <?php echo count($questions); ?></p>
                    <p><strong>Số lần làm bài:</strong> <?php echo $_SESSION['attempts']; ?> / <?php echo $test_info['lan_thu']; ?></p>
                    <p><strong>Trạng thái:</strong> <?php echo $is_passed ? 'Đạt' : 'Không đạt'; ?></p>
                </div>
                <hr>
                <?php if (empty($_SESSION['answers'])): ?>
                    <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
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
                                    $icon = '';
                                    if (isset($_SESSION['answers'][$index]['selected']) && $key === $_SESSION['answers'][$index]['selected']) {
                                        $style = $_SESSION['answers'][$index]['is_correct'] ? 'correct' : 'incorrect';
                                        $icon = $_SESSION['answers'][$index]['is_correct'] ? '✓' : '✗';
                                    }
                                    ?>
                                    <li class="<?php echo $style; ?>">
                                        <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?> <?php echo $icon; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (isset($_SESSION['answers'][$index]['selected'])): ?>
                                <div class="explanation-block" style="border-color: <?php echo $_SESSION['answers'][$index]['is_correct'] ? 'orange' : 'red'; ?>;">
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
                
                <?php if (!$is_passed && $_SESSION['attempts'] < $test_info['lan_thu']): ?>
                    <form method="post">
                        <input type="hidden" name="reset" value="1">
                        <button type="submit" class="btn btn-primary">🔁 Làm lại (<?php echo $_SESSION['attempts']; ?> / <?php echo $test_info['lan_thu']; ?>)</button>
                    </form>
                <?php endif; ?>
                
                <a href="login.php" class="btn btn-secondary">Quay lại</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>