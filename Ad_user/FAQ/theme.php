<?php
session_start();

$conn = new mysqli("localhost", "root", "", "study");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ten_khoa = '';
$current_index = isset($_POST['current_index']) ? intval($_POST['current_index']) : 0;

// Gán mã khoá học cố định
$ma_khoa = 'K007'; // Đổi thành mã bạn muốn

// Lấy tên khoá học từ bảng khoa_hoc
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $ten_khoa = $row['khoa_hoc'];
    // Lấy câu hỏi từ bảng quiz theo tên khoá học
    $stmt2 = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ?");
    $stmt2->bind_param("s", $ten_khoa);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $questions = [];
    while ($row2 = $result2->fetch_assoc()) {
        $questions[] = $row2;
    }
    $_SESSION['questions'] = $questions;
    $_SESSION['ten_khoa'] = $ten_khoa;
    $_SESSION['current_index'] = 0;
}
$stmt->close();

// lấy khoá học tù bảng khoa hoc 


// Khi đã có session câu hỏi
if (isset($_SESSION['questions'])) {
    $questions = $_SESSION['questions'];
    $ten_khoa = $_SESSION['ten_khoa'];
    $current_index = isset($_POST['current_index']) ? intval($_POST['current_index']) : $_SESSION['current_index'];
    if (isset($_POST['answer'])) {
        $current_index++;
        $_SESSION['current_index'] = $current_index;
    }
} else {
    $questions = [];
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu câu hỏi theo mã khoá học</title>
</head>
<body>

   </div>
    <div class="container">
    <?php if ($current_index < count($questions)): ?>
        <?php $question = $questions[$current_index]; ?>
         <h2>
            <?php
                echo "Môn học: <span style='color:#1565c0; margin:5px'>" . htmlspecialchars($questions[0]['ten_khoa']) . "</span><br>";
                echo "Bài thi: <span style='color:#e67e22; margin:5px'>" . htmlspecialchars($questions[0]['id_baitest']) . "</span>";               
            ?>
        </h2>
         <form method="POST" action="">
            <div class="question-box">
                <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($question['cauhoi']); ?></h3>
                <?php if (!empty($question['hinhanh'])): ?>
                    <img src="<?php echo htmlspecialchars($question['hinhanh']); ?>" alt="Hình ảnh câu hỏi" style="max-width:300px;display:block;margin:10px 0;">
                <?php endif; ?>
                <ul style="list-style: none; padding: 0;">
                    <li><label><input type="radio" name="answer" value="A" required> A. <?php echo htmlspecialchars($question['cau_a']); ?></label></li>
                    <li><label><input type="radio" name="answer" value="B"> B. <?php echo htmlspecialchars($question['cau_b']); ?></label></li>
                    <li><label><input type="radio" name="answer" value="C"> C. <?php echo htmlspecialchars($question['cau_c']); ?></label></li>
                    <li><label><input type="radio" name="answer" value="D"> D. <?php echo htmlspecialchars($question['cau_d']); ?></label></li>
                </ul>
                <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
                <button type="submit">Trả lời &raquo;</button>
            </div>
        </form>
        
    <?php else: ?>
        <h3>Bạn đã hoàn thành tất cả câu hỏi!</h3>
        <form method="post" action="">
            <button type="submit" name="reset" value="1">Làm lại</button>
        </form>
        <?php session_destroy(); ?>
    <?php endif; ?>
    </div>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #f8f9fa, #e0f7fa);
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: rgb(252, 251, 248);
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 7px;
        }
        .question-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 24px 20px 18px 20px;
            margin-bottom: 30px;
            border-left: 6px solid #007bff;
            transition: box-shadow 0.2s;
        }
        .question-box:hover {
            box-shadow: 0 4px 16px rgba(0,123,255,0.12);
        }
        .question-box h3 {
            color: #007bff;
            margin-top: 0;
        }
        .question-box ul li label {
            font-size: 17px;
            cursor: pointer;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #bbb;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
       
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
        }
        h3 {
            color: #333;
        }
        img {
            border-radius: 6px;
            margin-bottom: 10px;
            border: 1px solid #eee;
        }
        .error {
            color: #e74c3c;
            margin-top: 10px;
        }
    </style>
</body>
</html>