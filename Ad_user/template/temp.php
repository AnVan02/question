<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống kiểm tra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Đăng nhập hệ thống kiểm tra</h2>
        <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        session_start();

        // Kiểm tra nếu đã đăng nhập, chuyển hướng sang quiz.php

        if (isset($_SESSION['khoa_hoc'])) {
            header("Location: quiz.php");
            exit;
        }

        // Kết nối cơ sở dữ liệu
        $conn = new mysqli("localhost", "root", "", "student");
        if ($conn->connect_error) {
            echo "<p class='error'>Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error . "</p>";
            exit;
        }

        // Xử lý đăng nhập
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $khoa_id = isset($_POST['khoa_id']) ? trim($_POST['khoa_id']) : '';
            // $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';

            // Kiểm tra tài khoản (khoa_id)
            $sql_check_khoa = "SELECT khoa_hoc FROM khoa_hoc WHERE id = ?";
            $stmt_khoa = $conn->prepare($sql_check_khoa);
            if (!$stmt_khoa) {
                echo "<p class='error'>Lỗi truy vấn khoa_hoc: " . $conn->error . "</p>";
                exit;
            }

            $stmt_khoa->bind_param("s", $khoa_id);
            $stmt_khoa->execute();
            $result_khoa = $stmt_khoa->get_result();

            // Kiểm tra mật khẩu (Student_ID)
            $sql_check_student = "SELECT Student_ID FROM students WHERE Student_ID = ?";
            $stmt_student = $conn->prepare($sql_check_student);
            if (!$stmt_student) {
                echo "<p class='error'>Lỗi truy vấn students: " . $conn->error . "</p>";
                exit;
            }
            
            $stmt_student->bind_param("s", $student_id);
            $stmt_student->execute();
            $result_student = $stmt_student->get_result();

            if ($result_khoa->num_rows == 0) {
                echo "<p class='error'>Tài khoản (mã khóa học) không tồn tại!</p>";
            } elseif ($result_student->num_rows == 0) {
                echo "<p class='error'>Mật khẩu (Student ID) không đúng!</p>";
            } else {
                $row_khoa = $result_khoa->fetch_assoc();
                $_SESSION['khoa_hoc'] = $row_khoa['khoa_hoc'];
                // $_SESSION['student_id'] = $student_id;
                $_SESSION['khoa_id'] = $khoa_id;
                header("Location: quiz.php");
                exit;
            }
            $stmt_khoa->close();
            $stmt_student->close();
        }
            
        $conn->close();
        ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="khoa_id">Mã khoá học:</label>
                <input type="text" name="khoa_id" id="khoa_id" placeholder="Nhập mã khóa học (VD: K004)" required>
            </div>

            <button type="submit">Đăng nhập</button>
        </form>
        
    </div>
</body>
</html>