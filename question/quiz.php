<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Check if test ID is provided
if (!isset($_GET['id_test'])) {
    header("Location: dashboard.php");
    exit();
}

$test_id = $_GET['id_test'];

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

if (!$test) {
    die("Không tìm thấy bài test");
}

// Get questions for this test
$sql = "SELECT * FROM quiz WHERE id_baitest = ? AND ten_khoa = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $test['ten_test'], $test['khoa_hoc']);
$stmt->execute();
$questions = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bài test: <?php echo htmlspecialchars($test['ten_test']); ?></title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .test-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .question {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .options {
            margin-left: 20px;
        }
        .option {
            margin: 10px 0;
        }
        .submit-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2>Bài test: <?php echo htmlspecialchars($test['ten_test']); ?></h2>
        <p>Môn học: <?php echo htmlspecialchars($test['khoa_hoc']); ?></p>
        
        <form action="submit_test.php" method="POST">
            <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
            
            <?php
            $question_number = 1;
            while ($question = $questions->fetch_assoc()):
            ?>
            <div class="question">
                <p><strong>Câu <?php echo $question_number; ?>:</strong> <?php echo htmlspecialchars($question['cauhoi']); ?></p>
                
                <?php if ($question['hinhanh']): ?>
                <img src="<?php echo htmlspecialchars($question['hinhanh']); ?>" alt="Hình ảnh câu hỏi" style="max-width: 100%; margin: 10px 0;">
                <?php endif; ?>
                
                <div class="options">
                    <div class="option">
                        <input type="radio" name="answer[<?php echo $question['Id_cauhoi']; ?>]" value="A" required>
                        <label>A. <?php echo htmlspecialchars($question['cau_a']); ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answer[<?php echo $question['Id_cauhoi']; ?>]" value="B">
                        <label>B. <?php echo htmlspecialchars($question['cau_b']); ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answer[<?php echo $question['Id_cauhoi']; ?>]" value="C">
                        <label>C. <?php echo htmlspecialchars($question['cau_c']); ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answer[<?php echo $question['Id_cauhoi']; ?>]" value="D">
                        <label>D. <?php echo htmlspecialchars($question['cau_d']); ?></label>
                    </div>
                </div>
            </div>
            <?php
            $question_number++;
            endwhile;
            ?>
            
            <div>
                <a href="dashboard.php" class="back-btn">Quay lại</a>
                <button type="submit" class="submit-btn">Nộp bài</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?> 