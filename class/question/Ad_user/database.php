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

// Kết nối database
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập các tham số
$ma_khoa = '1';
$id_test = '19';
$student_id = $_SESSION['student_id'];
$link_quay_lai = "index.php";
$link_tiep_tuc = "dashboard.php";
$required_pass_percent = 70; // Giá trị mặc định nếu không có trong database

// 1. Kiểm tra quyền truy cập khóa học
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc'];
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

// 2. Lấy thông tin bài test
$stmt = $conn->prepare("SELECT id_test, ten_test, lan_thu FROM test WHERE id_test = ?");
$stmt->bind_param("i", $id_test);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('ID bài test ($id_test) không tồn tại trong hệ thống. Vui lòng kiểm tra lại!');</script>";
    exit();
}

$test_info = $result->fetch_assoc();
$id_baitest = $test_info['id_test'];
$ten_test = $test_info['ten_test'] ?? 'Bài test ' . $id_test;
$max_attempts = $test_info['lan_thu'] ?? 3;
$stmt->close();

// 3. Lấy tên khóa học
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
$ten_khoa = $result->num_rows > 0 ? $result->fetch_assoc()['khoa_hoc'] : 'Không xác định';
$stmt->close();

// 4. Lấy kết quả gần nhất
$stmt = $conn->prepare("SELECT kq_cao_nhat, test_gan_nhat, so_lan_thu FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
$stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
$stmt->execute();
$result = $stmt->get_result();
$recent_result = $result->num_rows > 0 ? $result->fetch_assoc() : null;
$stmt->close();

// 5. Tính toán điểm đạt
$total_questions = count($_SESSION['questions'] ?? []);
$passing_score = ceil($total_questions * $required_pass_percent / 100);

// Đóng kết nối database
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả gần nhất - <?php echo htmlspecialchars($ten_khoa); ?></title>
    <style>
        /* Giữ nguyên phần CSS như code gốc */
    </style>
</head>
<body>
    <div class="container">
        <div class="navigation-links">
            <a href="<?php echo htmlspecialchars($link_quay_lai); ?>" class="nav-link">← Quay lại</a>
        </div>
        
        <h1>Kết quả bài test gần nhất</h1>
        
        <?php if ($recent_result): ?>
            <!-- ĐÃ LÀM BÀI: Hiển thị kết quả -->
            <table class="result-table">
                <tr><td>Khóa học:</td><td><?php echo htmlspecialchars($ten_khoa); ?></td></tr>
                <tr><td>Bài test:</td><td><?php echo htmlspecialchars($ten_test); ?> (ID: <?php echo $id_baitest; ?>)</td></tr>
                <tr><td>Số câu hỏi:</td><td><?php echo $total_questions; ?></td></tr>
                <tr><td>Điểm cao nhất:</td><td><?php echo $recent_result['kq_cao_nhat']; ?>/<?php echo $total_questions; ?></td></tr>
                <tr><td>Số lần làm bài:</td><td><?php echo $recent_result['so_lan_thu']; ?>/<?php echo $max_attempts; ?></td></tr>
                <tr><td>Yêu cầu đậu:</td><td><?php echo $required_pass_percent; ?>% (<?php echo $passing_score; ?> câu)</td></tr>
                <tr><td>Trạng thái:</td><td>
                    <?php if ($recent_result['kq_cao_nhat'] >= $passing_score): ?>
                        <span style="color:green;font-weight:bold">ĐẠT</span>
                    <?php else: ?>
                        <span style="color:red;font-weight:bold">KHÔNG ĐẠT</span>
                    <?php endif; ?>
                </td></tr>
            </table>

            <div class="navigation-links">
                <a href="quiz.php" class="start-quiz<?php echo ($recent_result && $recent_result['so_lan_thu'] >= $max_attempts) ? ' disabled' : ''; ?>">
                    <?php echo ($recent_result && $recent_result['so_lan_thu'] >= $max_attempts) ? 'Đã hết lượt làm bài' : 'Bắt đầu làm bài'; ?>
                </a>
            </div>

            <?php if (!empty($recent_result['test_gan_nhat'])): ?>
                <h3>Chi tiết lần làm bài gần nhất:</h3>
                <div style="text-align:left;">
                <?php
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
                
                $questions = $_SESSION['questions'] ?? [];
                $index = 0;
                
                foreach ($questions as $q) {
                    $qid = $q['id'];
                    $user_ans = $answers[$qid] ?? null;
                    $is_correct = $user_ans === $q['correct'];
                    
                    echo "<div class='question-block'>";
                    echo "<p class='question-text'>Câu " . ($index + 1) . ": " . htmlspecialchars($q['question']) . "</p>";

                    if (!empty($q['image'])) {
                        echo "<img src='admin/" . htmlspecialchars($q['image']) . "' alt='Hình ảnh câu hỏi' class='question-image'>";
                    }
                    
                    echo "<ul>";
                    foreach ($q['choices'] as $key => $val) {
                        $li_class = '';
                        if ($user_ans !== null && $key === $user_ans) {
                            $li_class = $is_correct ? 'correct' : 'incorrect';
                        } elseif ($key === $q['correct']) {
                            // Highlight đáp án đúng nếu muốn
                        }
                        echo "<li class='$li_class'>$key. " . htmlspecialchars($val) . "</li>";
                    }
                    echo "</ul>";
                    
                    if ($user_ans !== null && !$is_correct) {
                        echo "<div class='explanation-block' style='border-color:#dc3545;'>";
                        echo "<p><strong>Giải thích:</strong> " . htmlspecialchars($q['explanations'][$user_ans] ?? 'Không có giải thích') . "</p>";
                        echo "</div>";
                    }
                    
                    echo "<hr></div>";
                    $index++;
                }
                ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- CHƯA LÀM BÀI: Hiển thị thông tin -->
            <table class="test-info-table">
                <tr><td>Khóa học:</td><td><?php echo htmlspecialchars($ten_khoa); ?></td></tr>
                <tr><td>Bài test:</td><td><?php echo htmlspecialchars($ten_test); ?> (ID: <?php echo $id_baitest; ?>)</td></tr>
                <tr><td>Số câu hỏi:</td><td><?php echo $total_questions; ?></td></tr>
                <tr><td>Số lần làm tối đa:</td><td><?php echo $max_attempts; ?></td></tr>
                <tr><td>Yêu cầu đậu:</td><td><?php echo $required_pass_percent; ?>% (<?php echo $passing_score; ?> câu)</td></tr>
            </table>
            
            <div class="navigation-links">
                <a href="quiz.php" class="start-quiz">Bắt đầu làm bài lần đầu</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>