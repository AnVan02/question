<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi động session
session_start();

// Hàm kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_errno) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();

// lấy dữ liệu câu hỏi khoá để chỉnh sữa 

$question_data = [];
$question_id = isset($_GET['question_id']) && is_numeric($_GET['question_id']) ? (int)$_GET['question_id'] : null;
$message = "";

if ($question_id) {
    $conn = dbconnect();
    $sql = "SELECT * FROM kiemtra  WHERE  = id_cauhoi  ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $question_data = $result->fetch_assoc();
    } else {
        $message = "<div style='color:red;'>Câu hỏi không tồn tại!</div>";
    }
    $stmt->close();
    $conn->close();
}


if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "<div style='color:green;'>Câu hỏi đã được lưu vào cơ sở dữ liệu!</div>";
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_question"])) {
    $id_baitest = trim($_POST["id_khoahoc"]);
    $ten_khoa = trim($_POST["ten_khoa"]);
    $question_text = trim($_POST["question_text"]);
    $choices = [
        'IMEI' => trim($_POST["choice_IMEI"]),
        'MB_ID' => trim($_POST["choice_MB_ID"]),
        'Student' => trim($_POST["choice_studet"]),
        'Password' => trim($_POST["choice_password"]),
        'ten'=> trim ($_POST["choice_ten"]),
        'khoa_học'=> trim ($_POST ["choice_khoahoc"]),
        'email'=> trim ($_POST ["choice_email"])
    ];
    $explanations = [
        'IMEI' => trim($_POST["choice_IMEI"]),
        'MB_ID' => trim($_POST["choice_MB_ID"]),
        'Student' => trim($_POST["choice_studet"]),
        'Password' => trim($_POST["choice_password"]),
        'ten'=> trim ($_POST["choice_ten"]),
        'khoa_học'=> trim ($_POST ["choice_khoahoc"]),
        'email'=> trim ($_POST ["choice_email"])
    ];
    
    $correct = strtoupper(trim($_POST["correct"]));
    $question_id = isset($_POST["question_id"]) && is_numeric($_POST["question_id"]) ? (int)$_POST["question_id"] : null;
    $image = isset($_POST["existing_image"]) ? $_POST["existing_image"] : null;

}
// xử lý câp nhập thêm khoá 
// if($_SERVER ["REQUEST_METHOS"] == "POST" && isset ($_POST ['update_test']) && $id_khoa > 0) {
//     $IMEI = (int) $_POST ['id_IMEI'];
//     $MB_ID = trim ($_POST ['MB_ID']);
//     $OD_ID = trim ($_POST ['OD_ID']);
//     $student = trim ($_POST['student']);
//     $password = trim ($_POST ['passwork']);
//     $ten = trim ($_POST ['ten']);
//     $khoa_hoc = trim ($_POST['ten']); 
//     $emai = trim ($_POST ['email']);
 
//     if (empty ($te_test)) {
//         $error_message = "";

//     } else {

//     }
// }

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài test kiểm tra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            max-width: 600px;
            width: 150%;
            background: #fff;
            padding: 60px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-box h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .debug {
            color: blue;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id_khoa=" . $id_khoa); ?>">
            <div class="form-group">
                <label for="IMEI">IMEI:</label>
                <input type="IMEI" id="IMEI" name="IMEI" maxlength="255" value="">
            </div>
            <div class="form-group">
                <label for="MB_ID">MB_ID:</label>
                <input type="MB_ID" id="MB_ID" name="MB_ID" value="">
            </div>
            <div class ="form-group">
                <label for="OS_iD">OD_ID</label>
                <input type="OS_ID" id="OD_ID" name="OD_ID" value="">
            </div>
             <div class="form-group">
                <label for="student_id">Student:</label>
                <input type="student_id" id="student_id" name="student_id" value="">
            </div>
            <div class ="form-group">
                <label for="password">Password </label>
                <input type="password" id="password" name="password" value="">
            </div>
            <div class= "form-group">
                <label for="ten">Tên sinh viên</label>
                <input type="ten" id="ten "name="ten" value="">
            </div>
            <div class= "form-group">
                <label for="khoa_hoc">Khoá học</label>
                <input type="text" name="ten_khoa" value="<?= htmlspecialchars($question_data['ten_khoa'] ?? '') ?>">
            </div>
            <div class= "form-group">
                <label for="email">Email</label>
                <input type="email" id="email"name="email" value="">
            </div>
            <br><h2>Danh sách bài khoá </br></h2>
            <?php if (empty($khoa_hoc)): ?>
                <p style="text-align: center; color: #666;">Chưa có khóa học nào.</p>
            <?php else: ?>
        <ul>
            
            <?php foreach ($khoa_hoc as $kh ):?>
                <li>
                    <span class ="course-name"> <?= htmlspecialchars ($kh ['khoa_hoc']) ?></span>
                    <a href="?dit=<? $kh['id'] ?>" class="edit">Sữa</a>
                    <a href="?delete=<? $kh['id'] ?>" class ="delete" onclick="retuen confirm ('ban có chắc chắn xoá không')">Xoá</a>
                    <a href="<?= htmlspecialchars($kh['id']) ?>" class= "btn"> Xem khoá học</a>
                    <div class ="khoahoc-overlay" id="khoahocOverlay"><div>
                    <div id="khoahoc-popup">
                    <span id="khoahoc_overlay"></span>    
                </li>
            <?php endforeach ;?>
        </ul>    
            <?php endif;?>

</body>
</html>
