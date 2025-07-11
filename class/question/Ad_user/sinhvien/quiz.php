<?php
ob_start();
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
$id_test = '19';
$student_id = $_SESSION['student_id'];
$link_quay_lai = "khoahoc.php";

// Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc'];
    $khoahoc_list = array_map('intval', explode(',', $khoahoc));
    if (!in_array(intval($ma_khoa), $khoahoc_list)) {
        echo "<script>alert('Bạn không có quyền truy cập khóa học này!'); window.location.href = 'login.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Không tìm thấy thông tin sinh viên!'); window.location.href = 'login.php';</script>";
    exit();
}
$stmt->close();

// Kiểm tra ID bài test
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

// Lấy tên khóa học và câu hỏi
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
            'images' => [
                'A' => $row2['hinhanh_a'],
                'B' => $row2['hinhanh_b'],
                'C' => $row2['hinhanh_c'],
                'D' => $row2['hinhanh_d']
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
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();
$stmt2->close();

// Lấy số lần thử tối đa
function getTestInfo($conn, $ten_test, $ten_khoa) {
    $sql = "SELECT lan_thu FROM test WHERE ten_test = ? AND id_khoa = (SELECT id FROM khoa_hoc WHERE khoa_hoc = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ten_test, $ten_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    $lan_thu = $result->num_rows > 0 ? $result->fetch_assoc()['lan_thu'] : 1;
    $stmt->close();
    return $lan_thu;
}
$max_attempts = getTestInfo($conn, $id_baitest, $ten_khoa);

// Khởi tạo biến
$current_index = isset($_SESSION['current_index']) ? intval($_SESSION['current_index']) : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;

// Kiểm tra số lần thử
$stmt = $conn->prepare("SELECT so_lan_thu FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
$stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
$stmt->execute();
$result = $stmt->get_result();
$attempts = $result->num_rows > 0 ? $result->fetch_assoc()['so_lan_thu'] : 0;
$stmt->close();

// Xử lý gửi câu trả lời
if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST['next']) || isset($_POST['submit']) || isset($_POST['previous']))) {
    if (isset($_POST['answer']) && isset($_SESSION['questions'][$current_index])) {
        $user_answer = $_POST['answer'];
        $current_question = $_SESSION['questions'][$current_index];
        $is_correct = ($user_answer === $current_question['correct']);
        $answers[$current_index] = [
            'selected' => $user_answer,
            'is_correct' => $is_correct
        ];
        $_SESSION['answers'] = $answers;
        if ($is_correct && !isset($_SESSION['score_saved'][$current_index])) {
            $score++;
            $_SESSION['score'] = $score;
            $_SESSION['score_saved'][$current_index] = true;
        }
    }

    if (isset($_POST['next']) && $current_index < count($_SESSION['questions']) - 1) {
        $current_index++;
        $_SESSION['current_index'] = $current_index;
    } elseif (isset($_POST['previous']) && $current_index > 0) {
        $current_index--;
        $_SESSION['current_index'] = $current_index;
    } elseif (isset($_POST['submit'])) {
        $conn->close();
        header("Location: result.php");
        exit();
    }
    header("Location: quiz.php");
    exit();
}

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
        h2 {
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
        button {
            padding: 10px 11px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        button:hover:not(:disabled) {
            background-color: #0056b3;
        }
        img {
            max-width: 650px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #eee;
            display: block;
        }
        .btn-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navigation-links {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        a.nav-link {
            padding: 10px 11px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        a.nav-link:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <?php if ($attempts >= $max_attempts): ?>
            <p class="no-answers">Bạn đã sử dụng hết số lần làm bài! <a class="nav-link" href="recent_result.php">Xem kết quả</a></p>
        <?php elseif ($current_index < count($_SESSION['questions'])): ?>
            <?php $question = $_SESSION['questions'][$current_index]; ?>
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?> / <?php echo count($_SESSION['questions']); ?>: </h3>
                    <h3><?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo 'admin/' . htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li>
                                <label>
                                    <input type="radio" name="answer" value="<?php echo $key; ?>" 
                                        <?php echo isset($answers[$current_index]) && $answers[$current_index]['selected'] === $key ? 'checked' : ''; ?> 
                                        required> 
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </label>
                                <?php if (!empty($question['images'][$key])): ?>
                                    <img src="<?php echo 'admin/' . htmlspecialchars($question['images'][$key]); ?>" alt="Hình ảnh đáp án <?php echo $key; ?>">
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="btn-area">
                        <button type="submit" name="previous" <?php echo $current_index == 0 ? 'disabled' : ''; ?>>Câu trước</button>
                        <?php if ($current_index == count($_SESSION['questions']) - 1): ?>
                            <button type="submit" name="submit">Nộp bài</button>
                        <?php else: ?>
                            <button type="submit" name="next">Câu sau</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p>Đã hoàn thành bài test. Chuyển hướng đến trang kết quả...</p>
            <script>window.location.href = 'result.php';</script>
        <?php endif; ?>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>