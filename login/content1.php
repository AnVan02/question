<?php
// Hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}


function dbconnect (){
   $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn; 
}
$student_id = intval($_SESSION['student_id']);


// Kiểm tra quyền truy cập
if ($student_id == 1 || $student_id == 2) {
    // Cho phép truy cập
} else {
    echo "Bạn không có quyền truy cập khoá học này";
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT khoa_hoc FROM khoa_hoc WHERE id = 1"; // PYTHON CƠ BẢN
    $stmt = $conn->query($sql);
    $khoa_hoc = $stmt->fetchColumn();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Content 1</title>

    <style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 20px auto;
        max-width: 1300px;
        background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
        color: #333;
        line-height: 1.6;
    }
    h2 {
        margin-bottom: 25px;
        color: #2d3748;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
        padding: 10px;
        background-color: #edf2f7;
        border-radius: 8px;
    }
   
    form {
        max-width: 800px;
        width: 100%;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin: 0 auto;
    }
    p {
        font-size: 1.1em;
    }
    label {
        display: block;
        margin: 10px 0;
        cursor: pointer;
    }
    input[type="radio"] {
        margin-right: 8px;
    }
    button {
        background: #007bff;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        margin: 10px 8px 0 0;
        font-size: 1em;
        cursor: pointer;
        transition: background 0.2s;
    }
   
    img {
        margin: 16px 0;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.10);
    }
    .feedback {
        color: #d9534f;
        font-weight: bold;
        margin: 12px 0;
    }

        
        
    </style>
</head>
<body>

<div class="content-container">
    <h2>Khoá học <?php echo htmlspecialchars($khoa_hoc); ?></h2>
    <p>Hello bạn user<?php echo htmlspecialchars($student_id); ?> - bạn học khoá <?php echo htmlspecialchars($khoa_hoc); ?></p>
    
    <tbody>
        <div class="question">Câu hỏi 
            
        </div>
        <?php foreach ($question as $question )?>
        <td>
            <?php echo htmlspecialchars($question['cauhoi']); ?>
            <?php echo htmlspecialchars($question['khoa_hoc']); ?>  


        </td>
    </tbody>
    </div>
</body>
</html>
