<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi động session
session_start();

// Hàm kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_errno) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $IMEI= trim($_POST['IMEI']);
    $MB_ID = trim($_POST['MB_ID']);
    $OS_ID = trim($_POST['OS_ID']);
    $student= trim($_POST['student']);
    $password = trim($_POST['password']);
    $Ten = trim($_POST['ten']);
    $email = trim($_POST ['email']);
    $stmt = $conn->prepare("SELECT IMEI, MB_ID, OS_ID, password , ten  FROM accounts WHERE email = ?");
    if (!$stmt) {
        $error = "Lỗi chuẩn bị truy vấn: " . $conn->error;
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) {
                $_SESSION['IMEI'] = $user['IMEI'];
                $_SESSION['MB_ID'] = $user['MB_ID'];
                $_SESSION['OS_ID'] = $user['OS_ID'];
                $_SESSION['STUDENT'] = $user['STUDENT'];
                $_SESSION['PASSWORD'] = $user['PASSWORD'];
                $_SESSION['email'] = $user['email'];


                if ($user['bai_hoc'] == 0 || $user['bai_hoc'] === null) {
                    $view = 'choose_course';
                } else {
                    // Lấy tên khóa học từ bảng khoa_hoc
                    $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
                    $stmt->bind_param("i", $user['bai_hoc']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $course = $result->fetch_assoc();
                        $_SESSION['ten_khoa'] = $course['khoa_hoc'];
                        // Chuyển hướng đến FAQ.php với tham số ten_khoa và id_baitest mặc định
                        header("Location: FAQ.php?ten_khoa=" . urlencode($course['khoa_hoc']) . "&id_baitest=Giữa kỳ");
                        exit;
                    } else {
                        $error = "Khóa học không tồn tại!";
                        $view = 'choose_course';
                    }
                }
            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Email không tồn tại!";
        }
        $stmt->close();
    }
}

// Xử lý chọn khóa học
if (isset($_POST['choose_course'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = "Phiên đăng nhập không hợp lệ!";
        $view = null;
    } else {
        $course_id = (int)$_POST['course_id'];
        $user_id = $_SESSION['user_id'];

        // Kiểm tra khóa học có tồn tại
        $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $error = "Khóa học không tồn tại!";
            $view = 'choose_course';
        } else {
            $course = $result->fetch_assoc();
            // Cập nhật bai_hoc cho tài khoản
            $stmt = $conn->prepare("UPDATE accounts SET bai_hoc = ? WHERE id = ?");
            $stmt->bind_param("ii", $course_id, $user_id);
            if ($stmt->execute()) {
                $_SESSION['bai_hoc'] = $course_id;
                $_SESSION['ten_khoa'] = $course['khoa_hoc'];
                // Chuyển hướng đến FAQ.php
                header("Location: FAQ.php?ten_khoa=" . urlencode($course['khoa_hoc']) . "&id_baitest=Giữa kỳ");
                exit;
            } else {
                $error = "Lỗi khi cập nhật khóa học: " . $stmt->error;
                $view = 'choose_course';
            }
        }
        $stmt->close();
    }
}


// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
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
        <!-- Debug thông tin -->
        <!-- <?php if (isset($_SESSION['user_id'])): ?>
            <p class="debug">User ID: <?= $_SESSION['user_id'] ?>, Email: <?= $_SESSION['user_email'] ?>, Bài học: <?= $_SESSION['bai_hoc'] ?? 'Chưa chọn' ?></p>
        <?php endif; ?>
        <p class="debug">View: <?= isset($view) ? $view : 'không được gán' ?></p> -->

        <!-- Hiển thị lỗi -->
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <!-- Form đăng nhập -->
        <?php if (!isset($view)): ?>
            <div class="form-box">
                <h2>Đăng Nhập</h2>
                <form method="POST">
                    <input type="studen" name="account_student" placeholder="student" required>
                    <input type="password" name="account_password" placeholder="Mật khẩu" required>
                    <button type="submit" name="login">Đăng nhập</button>
                </form>
            </div>

        <!-- Form chọn khóa học -->
        <?php elseif ($view === 'choose_course'): ?>
            <div class="form-box">
                <h2>Chọn Khóa Học</h2>
                <form method="POST">
                    <select name="course_id" required>
                        <option value="" disabled selected>Chọn khóa học</option>
                        <?php
                        $result = $conn->query("SELECT id, khoa_hoc FROM khoa_hoc");
                        if ($result->num_rows > 0) {
                            while ($course = $result->fetch_assoc()): ?>
                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['khoa_hoc']) ?></option>
                            <?php endwhile; ?>
                        <?php } else { ?>
                            <option disabled>Không có khóa học nào!</option>
                        <?php } ?>
                    </select>
                    <button type="submit" name="choose_course">Xác nhận</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<!-- <?php $conn->close(); ?> -->