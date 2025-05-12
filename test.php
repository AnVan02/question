<?php
session_start();

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_errno) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();

if (isset($_POST['login'])) {
    $email = trim($_POST['account_email']);
    $password = trim($_POST['account_password']);

    $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['bai_hoc'] = $user['bai_hoc'];

            if ($user['bai_hoc'] == 0) {
                $view = 'choose_course';
            } else {
                $view = 'study_course';
            }
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Email không tồn tại!";
    }
}

if (isset($_POST['choose_course'])) {
    $course_id = $_POST['course_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE accounts SET bai_hoc = ? WHERE id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    
    $_SESSION['bai_hoc'] = $course_id;
    $view = 'study_course';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Học Trực Tuyến</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php if (!isset($view)): ?>
            <!-- ========== FORM ĐĂNG NHẬP ========== -->
            <div class="form-box">
                <h2>Đăng Nhập</h2>
                <?php if (isset($error)): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="email" name="account_email" placeholder="Email" required>
                    <input type="password" name="account_password" placeholder="Mật khẩu" required>
                    <button type="submit" name="login">Đăng nhập</button>
                </form>
            </div>

        <?php elseif ($view === 'choose_course'): ?>
            <!-- ========== CHỌN KHÓA HỌC ========== -->
            <div class="form-box">
                <h2>Chọn Khóa Học</h2>
                <form method="POST">
                    <select name="course_id">
                        <?php
                        $result = $conn->query("SELECT * FROM khoa_hoc");
                        while ($course = $result->fetch_assoc()): ?>
                            <option value="<?= $course['id'] ?>"><?= $course['khoa_hoc'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" name="choose_course">Xác nhận</button>
                </form>
            </div>

        <?php elseif ($view === 'study_course'): ?>
            <!-- ========== HỌC VÀ LÀM TRẮC NGHIỆM ========== -->
            <div class="form-box">
                <?php
                $course_id = $_SESSION['bai_hoc'];
                $stmt = $conn->prepare("SELECT * FROM khoa_hoc WHERE id = ?");
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                $course = $stmt->get_result()->fetch_assoc();
                ?>

                <h2>Khóa học: <?= $course['khoa_hoc'] ?></h2>
                <form method="POST">
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ?");
                    $stmt->bind_param("s", $course['khoa_hoc']);
                    $stmt->execute();
                    $quiz = $stmt->get_result();

                    while ($row = $quiz->fetch_assoc()) {
                        echo "<div class='question'>";
                        echo "<p><strong>" . $row['cauhoi'] . "</strong></p>";
                        echo "<label><input type='radio' name='answer_" . $row['Id_cauhoi'] . "' value='A'> " . $row['cau_a'] . "</label><br>";
                        echo "<label><input type='radio' name='answer_" . $row['Id_cauhoi'] . "' value='B'> " . $row['cau_b'] . "</label><br>";
                        echo "<label><input type='radio' name='answer_" . $row['Id_cauhoi'] . "' value='C'> " . $row['cau_c'] . "</label><br>";
                        echo "<label><input type='radio' name='answer_" . $row['Id_cauhoi'] . "' value='D'> " . $row['cau_d'] . "</label><br>";
                        echo "</div>";
                    }
                    ?>
                    <button type="submit" name="submit_quiz">Nộp bài</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
session_start();

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_errno) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $email = trim($_POST['account_email']);
    $password = trim($_POST['account_password']);

    // Truy vấn thông tin tài khoản
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ Nếu mật khẩu là dạng text bình thường
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['bai_hoc'] = $user['bai_hoc'];

            // 🚀 Nếu chưa chọn bài học (0 hoặc NULL), chuyển đến giao diện chọn khóa học
            if ($user['bai_hoc'] == 0 || $user['bai_hoc'] === null) {
                $view = 'choose_course';
            } else {
                $view = 'study_course';
            }
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Email không tồn tại!";
    }
}

// Xử lý chọn khóa học
if (isset($_POST['choose_course'])) {
    $course_id = $_POST['course_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE accounts SET bai_hoc = ? WHERE id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    
    $_SESSION['bai_hoc'] = $course_id;
    $view = 'study_course';
}
?>

