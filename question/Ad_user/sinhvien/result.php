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
$link_tiep_tuc = "add_khoahoc.php";
$ten_khoa = $_SESSION['ten_khoa'] ?? '';
$id_baitest = $_SESSION['id_baitest'] ?? '';
$pass_score = 4;

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

// Lưu câu trả lời vào cơ sở dữ liệu
function saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score) {
    $tt_bai_test = '';
    if (!empty($answers)) {
        $answer_pairs = [];
        foreach ($answers as $index => $answer) {
            if (isset($_SESSION['questions'][$index]['id'])) {
                $question_id = $_SESSION['questions'][$index]['id'];
                $answer_pairs[] = $question_id . ":" . $answer['selected'];
            }
        }
        $tt_bai_test = implode(";", $answer_pairs);
        if (strlen($tt_bai_test) > 1000) {
            $tt_bai_test = substr($tt_bai_test, 0, 997) . '...';
        }
    } else {
        $tt_bai_test = 'Không có câu trả lời';
    }

    $stmt = $conn->prepare("SELECT so_lan_thu, kq_cao_nhat, test_cao_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
    $stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $so_lan_thu = $row['so_lan_thu'] + 1;
        $highest_score = max($score, $row['kq_cao_nhat']);
        $test_cao_nhat = ($score >= $row['kq_cao_nhat']) ? $tt_bai_test : $row['test_cao_nhat'];
        $stmt = $conn->prepare("UPDATE ket_qua SET so_lan_thu = ?, kq_cao_nhat = ?, test_cao_nhat = ?, test_gan_nhat = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $stmt->bind_param("iisssis", $so_lan_thu, $highest_score, $test_cao_nhat, $tt_bai_test, $student_id, $ma_khoa, $id_test);
        $stmt->execute();
    } else {
        $so_lan_thu = 1;
        $highest_score = $score;
        $test_cao_nhat = $tt_bai_test;
        $stmt = $conn->prepare("INSERT INTO ket_qua (student_id, khoa_id, test_id, so_lan_thu, kq_cao_nhat, test_cao_nhat, test_gan_nhat) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiiss", $student_id, $ma_khoa, $id_test, $so_lan_thu, $highest_score, $test_cao_nhat, $tt_bai_test);
        $stmt->execute();
    }
    $stmt->close();
    return $highest_score;
}

// Lưu kết quả
$answers = $_SESSION['answers'] ?? [];
$score = $_SESSION['score'] ?? 0;
$highest_score = saveAnswerToDatabase($conn, $student_id, $ma_khoa, $id_test, $answers, $score);

// Lấy số lần thử
$stmt = $conn->prepare("SELECT so_lan_thu FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
$stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
$stmt->execute();
$result = $stmt->get_result();
$attempts = $result->num_rows > 0 ? $result->fetch_assoc()['so_lan_thu'] : 1;
$stmt->close();

// Xử lý làm lại bài test
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reset'])) {
    if ($attempts < $max_attempts) {
        $_SESSION['score'] = 0;
        $_SESSION['answers'] = [];
        $_SESSION['current_index'] = 0;
        $_SESSION['score_saved'] = [];
        header("Location: quiz.php");
        exit();
    }
}

// Lấy danh sách câu hỏi từ database
$stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
$stmt->bind_param("ss", $ten_khoa, $id_baitest);
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?></title>
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
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .question-block {
            margin-bottom: 20px;
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
        .no-answers {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
        .navigation-actions {
            display: flex;
            align-items: center;
        }
        button, a.nav-link {
            padding: 10px 13px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
        }
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        button:hover:not(:disabled), a.nav-link:hover {
            background-color: #0056b3;
        }
        a.nav-link {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>
        <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
        <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
        <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION['questions']); ?></p>
        <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?> / <?php echo count($_SESSION['questions']); ?></p>
        <p><strong>Số lần làm bài:</strong> <?php echo $attempts; ?> / <?php echo $max_attempts; ?></p>
        <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>
        <hr>
        <?php if (empty($answers)): ?>
            <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="nav-link" href="quiz.php">Quay lại làm bài</a></p>
        <?php else: ?>
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-block">
                    <p class="question-text">Câu <?php echo $index + 1; ?> (ID:<?php echo $question['id']; ?>): <?php echo htmlspecialchars($question['question']); ?></p>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <?php
                            $style = '';
                            $is_selected = isset($answers[$index]) && $key === $answers[$index]['selected'];
                            $is_correct = $key === $question['correct'];
                            if ($is_selected) {
                                $style = $answers[$index]['is_correct'] ? 'correct' : 'incorrect';
                            }
                            ?>
                            <li class="<?php echo $style; ?>">
                                <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="explanation-block" style="border-color: <?php echo isset($answers[$index]) && $answers[$index]['is_correct'] ? '#28a745' : '#dc3545'; ?>;">
                        <?php if (isset($answers[$index]['selected']) && !$answers[$index]['is_correct']): ?>
                            <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($question['explanations'][$answers[$index]['selected']]); ?></p>
                        <?php endif; ?>
                    </div>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="navigation-actions">
            <form method="POST" action="">
                <button type="submit" name="reset" value="1" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>
                    🔁 Làm lại (<?php echo $attempts; ?> / <?php echo $max_attempts; ?>)
                </button>
            </form>
            <a href="<?php echo htmlspecialchars($link_tiep_tuc); ?>" class="nav-link" style="margin-left: 72%;">→ Tiếp tục</a>
        </div>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>