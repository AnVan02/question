<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Xử lý nộp bài
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answers = $_POST['answer'] ?? [];
    $test_id = $_GET['test_id'] ?? '';
    $khoa_id = $_GET['khoa_id'] ?? '';
    $student_id = $_SESSION['student_id'];
    $enrolled_courses = $_SESSION['courses'] ?? [];
    
    if (!in_array($khoa_id, $enrolled_courses)) {
        $message = "<div style='color:red;'>Bạn không có quyền vào khóa học này!</div>";
    } else {
        $conn = dbconnect();
        
        // Kiểm tra số lượt làm bài
        $sql = "SELECT Tral, Max_tral FROM kiem_tra WHERE Student_ID = ? AND Test_ID = ? AND Khoa_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $student_id, $test_id, $khoa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['Tral'] >= $row['Max_tral']) {
            $message = "<div style='color:red;'>Bạn đã hết lượt làm bài kiểm tra này!</div>";
        } else {
            // Tính điểm
            $score = 0;
            $max_score = 0;
            $sql = "SELECT Id_cauhoi, dap_an FROM quiz WHERE id_baitest = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $test_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $max_score += 10; // Giả sử mỗi câu 10 điểm
                if (isset($answers[$row['Id_cauhoi']]) && $answers[$row['Id_cauhoi']] === $row['dap_an']) {
                    $score += 10;
                }
            }
            
            // Cập nhật điểm và số lượt làm bài
            $sql = "UPDATE kiem_tra SET Best_Scone = GREATEST(Best_Scone, ?), Max_Scone = ?, Tral = Tral + 1 
                    WHERE Student_ID = ? AND Test_ID = ? AND Khoa_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isisi", $score, $max_score, $student_id, $test_id, $khoa_id);
            $stmt->execute();
            
            $message = "<div style='color:green;'>Nộp bài thành công! Điểm của bạn: $score/$max_score</div>";
            $stmt->close();
        }
        $conn->close();
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả bài kiểm tra</title>
</head>
<body>
    <div class="container">
        <h2>Kết quả bài kiểm tra</h2>
        <p>Xin chào, <?= htmlspecialchars($_SESSION['student_name']) ?> | <a href="logout.php">Đăng xuất</a></p>
        <p><a href="tests.php">Quay lại danh sách bài kiểm tra</a></p>
        <?php if (!empty($message)) echo $message; ?>
    </div>
</body>
</html>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #f4f4f4;
    margin: 0;
}
h2 {
    text-align: center;
    color: rgb(247, 18, 18);
    margin-bottom: 25px;
}
.container {
    max-width: 1500px;
    margin: auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
</style>