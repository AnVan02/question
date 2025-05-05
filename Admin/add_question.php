<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();
$message = "";

// Lấy danh sách môn học
$khoahoc = [];
$sql = "SELECT ten_khoa FROM khoahoc";
$result = $conn->query($sql);
if ($result === false) {
    $message = "<div class='error-message'>Lỗi: Không thể truy vấn bảng subjects. Vui lòng kiểm tra cơ sở dữ liệu.</div>";
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $khoahoc[] = $row['ten_khoa'];
        }
    } else {
        $message = "<div class='error-message'>Không có môn học nào trong cơ sở dữ liệu. Vui lòng thêm môn học trước.</div>";
    }
}

// Lấy ten_khoa từ POST (nếu form được gửi) hoặc để trống
$ten_khoa = isset($_POST['ten_khoa']) ? trim($_POST['ten_khoa']) : '';

// Xử lý form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_question"])) {
    $id_baitest = trim($_POST["id_baitest"]);
    $ten_khoa = trim($_POST["ten_khoa"]);
    $question_text = trim($_POST["question_text"]);
    $choices = [
        'A' => trim($_POST["choice_a"]),
        'B' => trim($_POST["choice_b"]),
        'C' => trim($_POST["choice_c"]),
        'D' => trim($_POST["choice_d"])
    ];
    $explanations = [
        'A' => trim($_POST["explain_a"]),
        'B' => trim($_POST["explain_b"]),
        'C' => trim($_POST["explain_c"]),
        'D' => trim($_POST["explain_d"])
    ];
    $correct = strtoupper(trim($_POST["correct"]));

    // Kiểm tra các trường không được để trống
    if (empty($id_baitest) || empty($ten_khoa) || empty($question_text) || empty($choices['A']) || 
        empty($choices['B']) || empty($choices['C']) || empty($choices['D']) || empty($correct) || 
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D'])) {
        $message = "<div class='error-message'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (!in_array($id_baitest, ['Giữa kỳ', 'Cuối kỳ'])) {
        $message = "<div class='error-message'>Loại bài test phải là Giữa kỳ hoặc Cuối kỳ!</div>";
    } elseif (!in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "<div class='error-message'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } elseif (!in_array($ten_khoa, $subjects) && !empty($subjects)) {
        $message = "<div class='error-message'>Môn học không hợp lệ!</div>";
    } else {
        // Xử lý hình ảnh nếu có
        $image_path = null;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            if (!in_array($_FILES["image"]["type"], $allowed_types)) {
                $message = "<div class='error-message'>Chỉ hỗ trợ định dạng JPG, PNG, GIF!</div>";
            } elseif ($_FILES["image"]["size"] > $max_size) {
                $message = "<div class='error-message'>Hình ảnh không được vượt quá 5MB!</div>";
            } else {
                $image_path = 'uploads/' . time() . '_' . basename($_FILES["image"]["name"]);
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                    $message = "<div class='error-message'>Lỗi khi tải hình ảnh!</div>";
                    $image_path = null;
                }
            }
        }

        if (empty($message)) {
            // Thực hiện câu lệnh SQL để lưu câu hỏi
            $sql = "INSERT INTO quiz (id_baitest, ten_khoa, cauhoi, hinhanh, 
                        cau_a, giaithich_a, cau_b, giaithich_b, 
                        cau_c, giaithich_c, cau_d, giaithich_d, dap_an) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssss", $id_baitest, $ten_khoa, $question_text, $image_path,
                $choices['A'], $explanations['A'], $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'], $choices['D'], $explanations['D'], $correct);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: baitest.php?ten_khoa=" . urlencode($ten_khoa) . "&message=" . urlencode("Câu hỏi đã được lưu thành công!"));
                exit;
            } else {
                $message = "<div class='error-message'>Lỗi khi lưu câu hỏi: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm câu hỏi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Giữ nguyên CSS từ mã gốc của bạn */
    </style>
</head>
<body>
    <div class="container">
        <h2>Thêm câu hỏi</h2>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Quay lại danh sách môn học</a>
        <?php if (!empty($message)) echo $message; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="ten_khoa">Môn học:</label>
            <select id="ten_khoa" name="ten_khoa" required>
                <option value="">Chọn môn học</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= htmlspecialchars($subject) ?>" <?= $ten_khoa === $subject ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subject) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_baitest">Loại bài test:</label>
            <select id="id_baitest" name="id_baitest" required>
                <option value="Giữa kỳ">Giữa kỳ</option>
                <option value="Cuối kỳ">Cuối kỳ</option>
            </select>

            <label for="question_text">Câu hỏi:</label>
            <textarea id="question_text" name="question_text" required></textarea>

            <label for="choice_a">Đáp án A:</label>
            <input type="text" id="choice_a" name="choice_a" required>
            <label for="explain_a">Giải thích A:</label>
            <textarea id="explain_a" name="explain_a" required></textarea>

            <label for="choice_b">Đáp án B:</label>
            <input type="text" id="choice_b" name="choice_b" required>
            <label for="explain_b">Giải thích B:</label>
            <textarea id="explain_b" name="explain_b" required></textarea>

            <label for="choice_c">Đáp án C:</label>
            <input type="text" id="choice_c" name="choice_c" required>
            <label for="explain_c">Giải thích C:</label>
            <textarea id="explain_c" name="explain_c" required></textarea>

            <label for="choice_d">Đáp án D:</label>
            <input type="text" id="choice_d" name="choice_d" required>
            <label for="explain_d">Giải thích D:</label>
            <textarea id="explain_d" name="explain_d" required></textarea>

            <label for="correct">Đáp án đúng (A, B, C, D):</label>
            <input type="text" id="correct" name="correct" required>

            <label for="image">Hình ảnh (nếu có):</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">

            <button type="submit" name="save_question"><i class="fas fa-save"></i> Lưu câu hỏi</button>
        </form>
    </div>
</body>
</html>