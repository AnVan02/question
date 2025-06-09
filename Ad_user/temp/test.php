<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'study');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Khởi tạo biến
$student = null;
$questions = [];
$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Đăng nhập bằng Student_ID
    if (isset($_POST['student_id'])) {
        $student_id = trim($_POST['student_id']);
        $sql = "SELECT * FROM students WHERE Student_ID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed for select student: " . $conn->error);
            $login_error = "Lỗi truy vấn cơ sở dữ liệu";
        } else {
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            $stmt->close();

            if (!$student) {
                $login_error = "Sai tài khoản!";
            } else {
                // Lấy danh sách môn học từ cột Khoahoc
                $mon_hoc = !empty($student['Khoahoc']) ? array_map('trim', explode(',', $student['Khoahoc'])) : [];
                error_log("Mon hoc for student $student_id: " . print_r($mon_hoc, true));

                if (!empty($mon_hoc)) {
                    // Truy vấn câu hỏi từ bảng quiz
                    $placeholders = implode(',', array_fill(0, count($mon_hoc), '?'));
                    $sql = "SELECT q.*, k.khoa_hoc AS ten_mon_hoc 
                            FROM quiz q 
                            JOIN khoa_hoc k ON q.ten_khoa = k.id 
                            WHERE q.ten_khoa IN ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        error_log("Prepare failed for select quiz: " . $conn->error);
                        $login_error = "Lỗi truy vấn câu hỏi";
                    } else {
                        $stmt->bind_param(str_repeat('s', count($mon_hoc)), ...$mon_hoc);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $questions[] = $row;
                        }
                        $stmt->close();
                        error_log("Questions fetched for student $student_id: " . count($questions));
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập và Xem Câu Hỏi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 40px;
        }
        form {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: inline-block;
        }
        input[type="text"] {
            padding: 6px 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 180px;
        }
        button {
            padding: 7px 18px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #0056b3;
        }
        h3 {
            color: #007bff;
        }
        ul {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            list-style: decimal inside;
            max-width: 700px;
        }
        li {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .quiz-block {
            margin-bottom: 25px;
        }
        .quiz-title {
            font-weight: bold;
        }
        img {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php if ($student && !empty($questions)): ?>
        <h3>Xin chào, <?php echo htmlspecialchars($student['Ten']); ?>!</h3>
        <h3>Danh sách câu hỏi thuộc các môn học của bạn:</h3>
        <ul>
            <h4>Bắt đầu</h4>
            <?php foreach ($questions as $q): ?>
                <li class="quiz-block">
                    <span class="quiz-title"><?php echo htmlspecialchars($q['khoahoc']); ?>:</span>
                    <?php echo htmlspecialchars($q['cauhoi']); ?>
                    <?php if (!empty($q['hinhanh'])): ?>
                        <br><img src="<?php echo htmlspecialchars($q['hinhanh']); ?>" alt="Hình minh họa">
                    <?php endif; ?>
                    <ul>
                        <li>A. <?php echo htmlspecialchars($q['cau_a']); ?></li>
                        <li>B. <?php echo htmlspecialchars($q['cau_b']); ?></li>
                        <li>C. <?php echo htmlspecialchars($q['cau_c']); ?></li>
                        <li>D. <?php echo htmlspecialchars($q['cau_d']); ?></li>
                    </ul>
                </li>
            <?php endforeach; ?>
            <div class="question">Câu hoi: <?= htmlspecialchars ($questions_data["question"]) ?></div>
                <?php if (!empty($questions_data ["image"])) :?>
                    <div class="question-image-container">
                        <img src="<?php htmlspecialchars ($questions_data ["image"]) ?>" alt="hinh anh cau hỏi">
                    </div>
                <?php endif;?>

            <div class="btn-area">
                <button type="submit" name="goback" class="btn-prew">Quay lai</button>
                <button type="submit" name="next" class="btn-next">Tiep theo</button>
            </div>
            
        </ul>
    <?php elseif ($student): ?>
        <h3>Xin chào, <?php echo htmlspecialchars($student['Ten']); ?>!</h3>
        <p>Bạn chưa có câu hỏi nào thuộc các môn học đã đăng ký.</p>
        <form method="post">
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['Student_ID']); ?>">
            <input type="hidden" name="khoahoc" value ="<?php echo htmlspecialchars($khoahoc['khoahoc']);?>">
            <button type="submit">Tải lại câu hỏi</button>
        </form>
    <?php else: ?>

        <?php if ($login_error): ?>
            <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <label>Mã sinh viên (Student_ID):</label>
            <input type="text" name="student_id" required>

            <label>Khoa hoc (khoahoc):</label>
            <input type="text"name="khoahoc" required >

            <button type="submit">Đăng nhập</button>
        </form>

    <?php endif; ?>
</body>
</html>


<?php
// Đóng kết nối
$conn->close();
?>