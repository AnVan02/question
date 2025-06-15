<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['test_id'])) {
    header("Location: quiz.php");
    exit();
}

$test_id = $_POST['test_id'];
$answers = $_POST['answer'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "student");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get test information
$sql = "SELECT t.*, kh.khoa_hoc 
        FROM test t 
        JOIN khoa_hoc kh ON t.id_khoa = kh.id 
        WHERE t.id_test = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $test_id);
$stmt->execute();
$test_result = $stmt->get_result();
$test = $test_result->fetch_assoc();

// Get correct answers
$sql = "SELECT Id_cauhoi, dap_an FROM quiz WHERE Id_cauhoi IN (" . implode(',', array_keys($answers)) . ")";
$result = $conn->query($sql);
$correct_answers = [];
while($row = $result->fetch_assoc()) {
    $correct_answers[$row['Id_cauhoi']] = $row['dap_an'];
}

// Calculate score
$total_questions = count($answers);
$correct_count = 0;
$wrong_answers = [];

foreach($answers as $question_id => $answer) {
    if($answer === $correct_answers[$question_id]) {
        $correct_count++;
    } else {
        $wrong_answers[] = $question_id;
    }
}

$score = ($correct_count / $total_questions) * 100;

// Update test results in database
$sql = "INSERT INTO kiem_tra (Student_ID, Khoa_ID, Test_ID, Best_Scone, Max_Scone, Pass, Tral, Max_tral) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        Best_Scone = GREATEST(Best_Scone, ?),
        Tral = Tral + 1";
$stmt = $conn->prepare($sql);
$max_score = 100;
$trial = 1;
$max_trial = $test['lan_thu'];
$stmt->bind_param("iisiiiiii", 
    $_SESSION['student_id'], 
    $test['id_khoa'], 
    $test_id,
    $score,
    $max_score,
    $test['Pass'],
    $trial,
    $max_trial,
    $score
);
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kết quả bài test</title>
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
            margin-bottom: 20px;
        }
        .score {
            font-size: 24px;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border-radius: 4px;
            background-color: <?php echo $score >= $test['Pass'] ? '#d4edda' : '#f8d7da'; ?>;
            color: <?php echo $score >= $test['Pass'] ? '#155724' : '#721c24'; ?>;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
        .wrong-answers {
            margin-top: 20px;
        }
        .wrong-answer {
            background-color: #f8d7da;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <h2>Kết quả bài test: <?php echo htmlspecialchars($test['ten_test']); ?></h2>
        <div class="score">
            <h3>Điểm số: <?php echo number_format($score, 2); ?>%</h3>
            <p><?php echo $score >= $test['Pass'] ? 'Chúc mừng! Bạn đã đạt yêu cầu.' : 'Bạn chưa đạt yêu cầu. Hãy cố gắng hơn!'; ?></p>
        </div>
        
        <p>Số câu đúng: <?php echo $correct_count; ?>/<?php echo $total_questions; ?></p>
        
        <?php if (!empty($wrong_answers)): ?>
        <div class="wrong-answers">
            <h3>Các câu trả lời sai:</h3>
            <?php
            $sql = "SELECT * FROM quiz WHERE Id_cauhoi IN (" . implode(',', $wrong_answers) . ")";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()):
            ?>
            <div class="wrong-answer">
                <p><strong>Câu hỏi:</strong> <?php echo htmlspecialchars($row['cauhoi']); ?></p>
                <p><strong>Đáp án đúng:</strong> <?php echo htmlspecialchars($row['dap_an']); ?></p>
                <p><strong>Giải thích:</strong> <?php echo htmlspecialchars($row['giaithich_' . strtolower($row['dap_an'])]); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        
        <a href="quiz.php" class="back-btn">Quay lại danh sách bài test</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?> 