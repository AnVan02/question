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

$conn = new mysqli("localhost", "root", "", "student");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

$sql = "SELECT * FROM quiz 
        WHERE ten_khoa = ? 
        AND id_baitest = ? 
        ORDER BY RAND() 
        LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $test['khoa_hoc'], $test['ten_test'], $test['so_cau_hien_thi']);
$stmt->execute();
$questions_result = $stmt->get_result();

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
        die("Lỗi: Bạn không có quyền truy cập vào '$ten_khoa' và '$id_test'.");
    }
    $_SESSION['questions'] = $questions;
    $_SESSION['ten_khoa'] = $ten_khoa;
    $_SESSION['id_baitest'] = $id_baitest;
    $_SESSION['current_index'] = 0;
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 1;
    }
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}



?>

<!DOCTYPE html>
<html>
<head>
    <title>Bai test</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
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
        
        <!-- <a href="logout.php" class="logout">Đăng xuất</a> -->
    </div>
        <div class="quiz-container">
            <form method="POST" action="submit_quiz.php">
                <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
                <div class="question">Câu hỏi: <?php echo $index +1; ?>: <?php  htmlspecialchars($question_data["cauhoi"]) ?></div>
                
                <?php if (!empty($question_data["image"])): ?>
                    <div class="question-image-container">
                        <img src="<?= htmlspecialchars($question_data["image"]) ?>" alt="Hình ảnh câu hỏi" class="question-image">
                    </div>
                <?php endif; ?>

                <?php foreach ($question_data["choices"] as $key => $value): ?>
                    <?php $label = $answer_labels[array_search($key, array_keys($question_data["choices"]))]; ?>
                    <div class="answer">
                        <input type="radio" name="answer" value="<?= $key ?>" id="<?= $key ?>"
                            <?php if (isset($_SESSION["answers"][$current]["selected"]) && $_SESSION["answers"][$current]["selected"] === $key): ?>
                                checked
                            <?php endif; ?>
                        >
                        <label for="<?= $key ?>"><?= $label ?>. <?= htmlspecialchars($value) ?></label>
                    </div>
                <?php endforeach; ?>

                <div class="content-area">
                    <div class="left-area">
                        <div class="progress">Câu <?= $current + 1 ?> / <?= $total ?></div>
                        <?php if ($_SESSION["feedback"]): ?>
                            <div class="result-box">
                                <?= $_SESSION["feedback"] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                

                    <div class="btn-area">
                        <button type="submit" name="goBack" class="btn-prev">⬅️ Quay lại</button>
                        <button type="submit" name="next" class="btn-next">Tiếp theo ➡️</button>
                    </div>
                </form>
            </div>
        </div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>