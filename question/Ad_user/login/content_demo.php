<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = 2"; // PYTHON NÂNG CAO
    $stmt = $conn->query($sql);
    $khoa_hoc = $stmt->fetchColumn();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// lấy danh sách bài kiêm tra cua sinh viên


$conn = dbconnect ();
$student_id =$_SESSION['khoa_hoc'];
$student_id = $_SESSION ['courses'] ?? [];
$tests = [];
$message ="";


$student_id = intval($_SESSION['student_id']);
$enrolled_courses = $_SESSION ['courses'] ?? [];
$tests = [];
$message = "";



// Kiểm tra quyền truy cập
if ($student_id == 1 || $student_id == 3) {
    // Cho phép truy cập
} else {
    echo "Bạn không có quyền truy cập khoá học này";
    exit();
}

if(isset ($_GET ['khoahoc']) && !in_array($_GET['khoahoc'], $enrolled_courses)){
    $message = "<div style='color:red;'>Bạn không có quyền truy cập vào khoá hoc này!</div>";
} else {
       // Lấy danh sách bài kiểm tra cho các khóa học sinh viên đã đăng ký
    $course_ids = implode(',', array_map('intval', $enrolled_courses));
    if (!empty($course_ids)) {

    $sql = "SELECT kt.Student_ID, kt.Khoa_ID, kt.Test_ID, kt.Best_Scone, kt.Max_Scone, kt.Pass, kt.Tral, kt.Max_tral, kh.khoa_hoc
                FROM kiem_tra kt
                JOIN khoa_hoc kh ON kt.Khoa_ID = kh.id
                WHERE kt.Student_ID = ? AND kt.Khoa_ID IN ($course_ids)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tests[] = $row;
            }
        }
        $stmt->close();
    }
}
$conn->close();




?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Content 2</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #e6f3fa, #f4f4f9);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .content-container {
            background-color: white;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            color: #007bff;
        }
        p {
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h2>Khoá học <?php echo htmlspecialchars($khoa_hoc); ?></h2>
        <p>Hello bạn user<?php echo htmlspecialchars($student_id); ?> - bạn học khoá <?php echo htmlspecialchars($khoa_hoc); ?></p>
    </div>
    <tbody> 
        <?php if (empty($questions) && empty($message)): ?>
            <p>Không có câu hỏi nào cho bài kiểm tra này.</p>
            <?php elseif (empty($message)): ?>
            <form method="POST" action="submit_quiz.php?test_id=<?= urlencode($test_id) ?>&khoa_id=<?= urlencode($khoa_id) ?>">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question">
                        <h3>Câu hỏi <?= $index + 1 ?>: <?= htmlspecialchars($question['cauhoi']) ?></h3>
                        <?php if (!empty($question['hinhanh'])): ?>
                            <img src="<?= htmlspecialchars($question['hinhanh']) ?>" alt="Hình ảnh câu hỏi" style="max-width: 300px;">
                        <?php endif; ?>
                        <div class="options">
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="A" required> A. <?= htmlspecialchars($question['cau_a']) ?></label><br>
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="B"> B. <?= htmlspecialchars($question['cau_b']) ?></label><br>
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="C"> C. <?= htmlspecialchars($question['cau_c']) ?></label><br>
                            <label><input type="radio" name="answer[<?= $question['Id_cauhoi'] ?>]" value="D"> D. <?= htmlspecialchars($question['cau_d']) ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn-submit">Nộp bài</button>
            </form>
        <?php endif; ?>
    </tbody>    
</body>
</html>