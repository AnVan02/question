<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Check if test_id is provided
if (!isset($_GET['test_id'])) {
    header("Location: quiz.php");
    exit();
}

$test_id = $_GET['test_id'];

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

if ($test_result->num_rows === 0) {
    header("Location: quiz.php");
    exit();
}

$test = $test_result->fetch_assoc();

// Get questions for this test
$sql = "SELECT * FROM quiz 
        WHERE ten_khoa = ? 
        AND id_baitest = ? 
        ORDER BY RAND() 
        LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $test['khoa_hoc'], $test['ten_test'], $test['so_cau_hien_thi']);
$stmt->execute();
$questions_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Làm bài test</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f2f5;
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
        .quiz-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .question {
            margin-bottom: 20px;
        }
        .options {
            margin-left: 20px;
        }
        .option {
            margin: 10px 0;
        }
        .option label {
            display: block;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .option label:hover {
            background-color: #f8f9fa;
        }
        .option input[type="radio"] {
            margin-right: 10px;
        }
        .submit-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['student_name']); ?></h2>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>

    <div class="quiz-container">
        <h2><?php echo htmlspecialchars($test['ten_test']); ?> - <?php echo htmlspecialchars($test['khoa_hoc']); ?></h2>
        <form method="POST" action="submit_test.php">
            <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
            <?php
            if ($questions_result->num_rows > 0) {
                $question_number = 1;
                while($row = $questions_result->fetch_assoc()) {
                    echo '<div class="question">';
                    echo '<h3>Câu ' . $question_number . ': ' . htmlspecialchars($row['cauhoi']) . '</h3>';
                    
                    if ($row['hinhanh']) {
                        echo '<img src="' . htmlspecialchars($row['hinhanh']) . '" alt="Question Image" style="max-width: 100%;">';
                    }
                    
                    echo '<div class="options">';
                    echo '<div class="option">';
                    echo '<label><input type="radio" name="answer[' . $row['Id_cauhoi'] . ']" value="A" required> A. ' . htmlspecialchars($row['cau_a']) . '</label>';
                    echo '</div>';
                    echo '<div class="option">';
                    echo '<label><input type="radio" name="answer[' . $row['Id_cauhoi'] . ']" value="B"> B. ' . htmlspecialchars($row['cau_b']) . '</label>';
                    echo '</div>';
                    echo '<div class="option">';
                    echo '<label><input type="radio" name="answer[' . $row['Id_cauhoi'] . ']" value="C"> C. ' . htmlspecialchars($row['cau_c']) . '</label>';
                    echo '</div>';
                    echo '<div class="option">';
                    echo '<label><input type="radio" name="answer[' . $row['Id_cauhoi'] . ']" value="D"> D. ' . htmlspecialchars($row['cau_d']) . '</label>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    $question_number++;
                }
                echo '<button type="submit" class="submit-btn">Nộp bài</button>';
            } else {
                echo "<p>Không có câu hỏi nào cho bài test này.</p>";
            }
            ?>
        </form>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?> 