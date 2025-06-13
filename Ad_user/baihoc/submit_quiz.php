<?php
session_start();
require_once("dbconnect.php");

if (!isset($_SESSION['student_id']) || !isset($_SESSION['Khoahoc'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: dashboard.php");
    exit();
}

$answers = $_POST['answers'] ?? [];
$id_cauhoi = $_POST['id_cauhoi'] ?? [];
$score = 0;
$total_questions = count($id_cauhoi);

$conn = dbconnect();
foreach ($id_cauhoi as $index => $cauhoi_id) {
    $stmt = $conn->prepare("SELECT dapan FROM quiz WHERE Id_cauhoi = ?");
    $stmt->bind_param("i", $cauhoi_id);
    $stmt->execute();
    $stmt->bind_result($correct_answer);
    $stmt->fetch();
    $stmt->close();
    
    if (isset($answers[$cauhoi_id]) && $answers[$cauhoi_id] === $correct_answer) {
        $score++;
    }
}

$conn->close();
$percentage = ($total_questions > 0) ? ($score / $total_questions) * 100 : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kết quả</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .result-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logout {
            padding: 8px 16px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Người dùng'); ?></h2>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
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
                                    $icon = $answers[$index]['is_correct'] ? '' : '';
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
    </div>
</body>
</html>