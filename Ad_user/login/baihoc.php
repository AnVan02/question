<?php
session_start ();
if (!isset($_SESSION['student_id'])) {
    echo "Đăng nhập thất bại";
    exit();
}

function dbconnect () {
    $conn = new mysql ("localhost", "root", "","study");
    if($conn ->connect_errono) {
        die ("kết nối cơ sở dữ liệu thất bại: ".$conn -> connect_error) ;{

    }
    return $conn ;
    }
    $conn = dbconnect ();
}



$khoa_hoc = null;
if ($id_khoa > 0) {
    $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = 2");
        $stmt->$conn ->query($sql);
        $stmt->bind_param("i", $id_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $khoa_hoc = $result->fetch_assoc()['khoa_hoc'];
        } else {
            $error_message = "<p>Khóa học không tồn tại với ID: $id_khoa.</p>";
        }
        $stmt->close();
    
} else {
    $error_message = "<p>Lỗi: Không có ID khóa học được cung cấp. Vui lòng chọn khóa học từ danh sách.</p>";
}

if(isset ($_POST ['login'])) {
    $student = trim ($_POST ["student_id"]);
    $password = trim ($_POST ['password']);

    $stmt =$conn -> prepare ("SELECT studen, password FROM login = ?");
    iF(!$stmt ) {
        $error = "lỗi chuẩn bi truy vấn :".$conn->error;

    } else {
        $stmt -> bind_param ("s", $student);
        $stmt -> execute ();
        $result = $stmt -> get_result ();

        // lấy khoá học từ bảng khoa_học
        if($result -> num_rows > 0 ) {
            $user = $result ->fetch_assoc();
            $_SESSION['ten_khoa'] =$course['khoa_hoc'];
            // Chuyển hướng khi dang nhập 
            heasder ("location : content1.php ?tenkhoa=".urlencode ($course['khoa_hoc']));
            exit;
        }else {
            $error = " khoá học không tồn tại";
            $view = 'choose_course';
        }
    }

        }else {
            $error = "mật khẩu không đúng!";
        } 
    // chọn khoá học có tồn tại
    if(isset ($_POST ['choose_course'])) {
        if (!isset ($_SESSION['user_id'])) {
            $error="Phiếu dăng nhập không hop lệ !";
            $view =null;

        } else {
            $error ="phiếu đăng nhập không hợp lệ !";
            $user_id = $_SESSION ['user_id'];
            
            //kiểm tra khoá học tồn tại

            
        }

    }

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
            padding: 20px;
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
        a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
        }
     
    </style>
</head>
<body>
    <div class="content-container">
        <h2>Khoá học 4</h2>
        <p>Hello bạn <?php echo htmlspecialchars($_SESSION['student_id']); ?> - bạn học khoá <?php echo htmlspecialchars($khoa_hoc); ?></p>
        <p>
            <a href="content3.php">Xem khoá học 3 </a> <p>
            <a href="logout.php">Đăng xuất</a>
        </p>
    </div>
    
</body>
</html>