<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
        }
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #1877f2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #166fe5;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        <?php
        session_start();
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $conn = new mysqli("localhost", "root", "", "student");
            
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            $student_id = $_POST['student_id'];
            $password = $_POST['password'];
            
            $sql = "SELECT * FROM students WHERE Student_ID = ? AND Password = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $student_id, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Debug information
                error_log("User data: " . print_r($row, true));
                
                $_SESSION['student_id'] = $row['Student_ID'];
                $_SESSION['student_name'] = $row['Ten'];
                $_SESSION['Khoahoc'] = $row['Khoahoc'];
                
                // Debug information
                error_log("Session data after login: " . print_r($_SESSION, true));
                
                header("Location: quiz.php");
                exit();
            } else {
                echo "<div class='error'>Sai tên đăng nhập hoặc mật khẩu!</div>";
            }
            
            $stmt->close();
            $conn->close();
        }
        ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="student_id">Mã sinh viên:</label>
                <input type="text" id="student_id" name="student_id" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html> 