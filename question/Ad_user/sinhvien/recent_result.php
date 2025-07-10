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

$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ma_khoa = '1';
$id_test = '19';
$student_id = $_SESSION['student_id'];
$link_quay_lai = "index.php"; // Thay bằng URL thực tế
$link_tiep_tuc = "dashboard.php"; // Thay bằng URL thực tế

// Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc']; // Ví dụ: "6,4"
    $khoahoc_list = array_map('intval', explode(',', $khoahoc));
    if (!in_array(intval($ma_khoa), $khoahoc_list)) {
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

// Lấy tên khóa học
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
$ten_khoa = $result->num_rows > 0 ? $result->fetch_assoc()['khoa_hoc'] : '';
$stmt->close();

// Lấy thông tin bài test (số lần thử tối đa)
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

// Lấy kết quả gần nhất
$stmt = $conn->prepare("SELECT kq_cao_nhat, test_gan_nhat, so_lan_thu FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
$stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
$stmt->execute();
$result = $stmt->get_result();
$recent_result = $result->num_rows > 0 ? $result->fetch_assoc() : null;
$stmt->close();

// Lấy lịch sử các lần làm bài
$stmt = $conn->prepare("SELECT so_lan_thu, kq_cao_nhat, test_cao_nhat, test_gan_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
$stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
$stmt->execute();
$result = $stmt->get_result();
$history = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả gần nhất - <?php echo htmlspecialchars($ten_khoa); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 0;
            min-height: 100vh;
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px);}
            to { opacity: 1; transform: translateY(0);}
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 28px;
            font-size: 2rem;
            letter-spacing: 0.5px;
        }
        h3 {
            color: #2d3436;
            margin-top: 28px;
            margin-bottom: 12px;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .result-table, .test-info-table {
            width: 100%;
            max-width: 520px;
            margin: 0 auto 28px auto;
            background: #f8fafc;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(44, 62, 80, 0.07);
            border: 1px solid #e0e6ed;
            font-size: 17px;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            transition: box-shadow 0.3s;
        }
        .result-table tr, .test-info-table tr {
            border-bottom: 1px solid #e0e6ed;
        }
        .result-table tr:last-child, .test-info-table tr:last-child {
            border-bottom: none;
        }
        .result-table td, .test-info-table td {
            padding: 14px 18px;
            text-align: left;
        }
        .result-table td:first-child, .test-info-table td:first-child {
            color: #1565c0;
            font-weight: 600;
            width: 44%;
            background: #f1f7fe;
            border-right: 1px solid #e0e6ed;
        }
        .result-table td:last-child, .test-info-table td:last-child {
            color: #222;
            font-weight: 500;
            background: #fff;
        }
        .result-table:hover, .test-info-table:hover {
            box-shadow: 0 6px 24px rgba(44, 62, 80, 0.13);
        }
        .detail-answer-block {
            background: #f9fbe7;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 14px;
            border-left: 5px solid #81c784;
            box-shadow: 0 1px 4px rgba(44, 62, 80, 0.04);
            text-align: left;
        }
        .detail-answer-block strong {
            color: #388e3c;
        }
        .detail-answer-block span[style*="font-weight:bold"] {
            background: #e3f2fd;
            border-radius: 4px;
            padding: 2px 6px;
        }
        .navigation-links {
            margin-top: 24px;
        }
        a.nav-link, a.start-quiz {
            padding: 14px 32px;
            margin-right: 10px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.07);
            display: inline-block;
        }
        a.nav-link {
            background-color: #3498db;
            color: white;
        }
        a.nav-link:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        a.start-quiz {
            background-color: #2ecc71;
            color: white;
        }
        a.start-quiz:hover:not(.disabled) {
            background-color: #27ae60;
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.13);
        }
        a.start-quiz.disabled {
            background-color: #bdc3c7;
            pointer-events: none;
            cursor: not-allowed;
            color: #fff;
            opacity: 0.7;
        }
        .no-result {
            color: #e74c3c;
            font-weight: 600;
            margin-top: 20px;
            text-align: center;
        }
        @media (max-width: 700px) {
            .container {
                padding: 12px 2vw;
            }
            .result-table, .test-info-table {
                font-size: 15px;
                max-width: 100%;
            }
            a.nav-link, a.start-quiz {
                font-size: 16px;
                padding: 10px 16px;
            }
        }
        .question-block {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(44, 62, 80, 0.04);
            padding: 10px 12px 6px 12px;
            margin-bottom: 10px;
            border-left: 3px solid #007bff;
            transition: box-shadow 0.2s;
        }
        .question-text {
            margin-bottom: 4px;
            font-size: 15px;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            margin: 8px 0;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: block;
        }
        ul {
            margin: 0 0 2px 0;
            padding: 0;
            list-style: none;
        }
        ul li {
            margin-bottom: 2px;
            padding: 4px 7px;
            border-radius: 3px;
            font-size: 14px;
            line-height: 1.3;
        }
        ul li.correct {
            background-color: #e6f4ea;
            color: #155724;
            font-weight: 600;
            position: relative;
            padding-left: 25px;
        }
        ul li.correct::before {
            content: "✓";
            position: absolute;
            left: 7px;
            color: #28a745;
            font-weight: bold;
            font-size: 16px;
        }
        ul li.incorrect {
            background-color: #fdeaea;
            color: #c0392b;
            font-weight: 600;
            position: relative;
            padding-left: 25px;
        }
        ul li.incorrect::before {
            content: "✗";
            position: absolute;
            left: 7px;
            color: #dc3545;
            font-weight: bold;
            font-size: 16px;
        }
        .explanation-block {
            margin-top: 2px;
            padding: 6px 10px;
            border-left: 3px solid;
            background-color: #fffbe7;
            border-radius: 3px;
            font-size: 13px;
        }
        hr {
            margin: 6px 0 0 0;
            border: none;
            border-top: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kết quả gần nhất - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>
        <div class="navigation-links">
            <a href="<?php echo htmlspecialchars($link_quay_lai); ?>" class="nav-link">← Quay lại</a>
        </div>
        <?php if ($recent_result): ?>
            <!-- ĐÃ LÀM BÀI: Hiển thị kết quả, số lần làm, trạng thái, chi tiết đáp án -->
            <table class="result-table">
                <tr><td>Khóa học:</td><td><?php echo htmlspecialchars($ten_khoa); ?></td></tr>
                <tr><td>Bài test:</td><td><?php echo htmlspecialchars($id_baitest); ?></td></tr>
                <tr><td>Thời gian hoàn thành:</td><td><?php echo date('H:i:s d/m/Y'); ?></td></tr>
                <tr><td>Điểm cao nhất:</td><td><?php echo $recent_result['kq_cao_nhat']; ?> / <?php echo count($_SESSION['questions'] ?? []); ?></td></tr>
                <tr><td>Câu trả lời gần nhất:</td><td><?php echo htmlspecialchars($recent_result['test_gan_nhat']); ?></td></tr>
                <tr><td>Số lần làm bài:</td><td><?php echo $recent_result['so_lan_thu']; ?> / <?php echo $max_attempts; ?></td></tr>
                <tr><td>Trạng thái:</td><td><?php echo $recent_result['kq_cao_nhat'] >= 4 ? 'Đạt' : 'Không đạt'; ?></td></tr>
            </table>
            <div class="navigation-links">
                <a href="quiz.php" class="start-quiz<?php echo ($recent_result && $recent_result['so_lan_thu'] >= $max_attempts) ? ' disabled' : ''; ?>">Bắt đầu làm bài</a>
            </div>
            <?php if (!empty($recent_result['test_gan_nhat'])): ?>
                <h3>Chi tiết lần làm bài gần nhất:</h3>
                <div style="text-align:left;">
                <?php
                // Parse đáp án
                $test_gan_nhat = $recent_result['test_gan_nhat'];
                $answers = [];
                if ($test_gan_nhat) {
                    $pairs = explode(';', $test_gan_nhat);
                    foreach ($pairs as $pair) {
                        if (strpos($pair, ':') !== false) {
                            list($qid, $ans) = explode(':', $pair);
                            $answers[$qid] = $ans;
                        }
                    }
                }
                // Lấy danh sách câu hỏi từ session
                $questions = $_SESSION['questions'] ?? [];
                $index = 0;
                foreach ($questions as $q) {
                    $qid = $q['id'];
                    $user_ans = $answers[$qid] ?? null;
                    $is_correct = $user_ans === $q['correct'];
                    echo "<div class='question-block'>";
                    echo "<p class='question-text'>Câu " . ($index + 1) . ": " . htmlspecialchars($q['question']) . "</p>";
                    
                    // Hiển thị hình ảnh nếu có (đã sửa lỗi)
                    if (!empty($q['image'])) {
                        echo "<img src='" . htmlspecialchars($q['image']) . "' alt='Hình ảnh câu hỏi' class='question-image' onerror='this.style.display=\"none\"'>";
                    }
                    
                    echo "<ul>";
                    foreach ($q['choices'] as $key => $val) {
                        $li_class = '';
                        if ($user_ans !== null && $key === $user_ans) {
                            $li_class = $is_correct ? 'correct' : 'incorrect';
                        } elseif ($key === $q['correct']) {
                            // $li_class = 'correct';
                        }
                        echo "<li class='$li_class'>";
                        echo "$key. " . htmlspecialchars($val);
                        echo "</li>";
                    }
                    echo "</ul>";
                    
                    // Giải thích nếu chọn sai
                    echo "<div class='explanation-block' style='border-color: " . ($is_correct ? "#28a745" : "#dc3545") . ";'>";
                    if ($user_ans !== null && !$is_correct) {
                        echo "<p><strong>Giải thích:</strong> " . htmlspecialchars($q['explanations'][$user_ans] ?? 'Không có giải thích') . "</p>";
                    }
                    echo "</div>";
                    echo "<hr>";
                    echo "</div>";
                    $index++;
                }
                ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- CHƯA LÀM BÀI: Hiển thị thông tin bài test -->
            <table class="test-info-table">
                <tr><td>Khóa học:</td><td><?php echo htmlspecialchars($ten_khoa); ?></td></tr>
                <tr><td>Bài test:</td><td><?php echo htmlspecialchars($id_baitest); ?></td></tr>
                <tr><td>Số câu hỏi:</td><td><?php echo count($_SESSION['questions'] ?? []); ?></td></tr>
                <tr><td>Số lần làm tối đa:</td><td><?php echo $max_attempts; ?></td></tr>
            </table>
            <p class="no-result">Bạn chưa làm bài test nào. Hãy bắt đầu làm bài!</p>
        <?php endif; ?>
      
    </div>
</body>
</html>
<?php ob_end_flush(); ?>