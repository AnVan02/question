<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$question_data = [];
$question_id = isset($_GET['question_id']) && is_numeric($_GET['question_id']) ? (int)$_GET['question_id'] : null;
$message = "";

if ($question_id) {
    $conn = dbconnect();
    $sql = "SELECT * FROM quiz WHERE Id_cauhoi = ?";
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
    $question_id = isset($_POST["question_id"]) && is_numeric($_POST["question_id"]) ? (int)$_POST["question_id"] : null;
    $image = $_POST["existing_image"] ?? null;

    // Xoá ảnh câu hỏi nếu có chọn
    if (isset($_POST["delete_image"]) && $_POST["delete_image"] == "1" && $image && file_exists($image)) {
        unlink($image);
        $image = null;
    }

    // Xử lý ảnh đáp án
    $image_answers = [];
    foreach (['a','b','c','d'] as $opt) {
        $image_answers[$opt] = $_POST["existing_image_{$opt}"] ?? null;
        if (isset($_POST["delete_image_{$opt}"]) && $_POST["delete_image_{$opt}"] == "1" && $image_answers[$opt] && file_exists($image_answers[$opt])) {
            unlink($image_answers[$opt]);
            $image_answers[$opt] = null;
        }
    }

    $upload_dir = "images/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    // Upload ảnh đáp án mới
    foreach(['a','b','c','d'] as $opt) {
        $file_key = "image_{$opt}";
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]["error"] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
                $message = "<div style='color:red;'>Chỉ cho phép JPG, PNG, GIF cho ảnh đáp án ".strtoupper($opt)."!</div>";
            } elseif ($_FILES[$file_key]["size"] > 5 * 1024 * 1024) {
                $message = "<div style='color:red;'>Ảnh đáp án ".strtoupper($opt)." không vượt quá 5MB!</div>";
            } else {
                $file_name = uniqid($opt.'_') . "." . $ext;
                $file_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $file_path)) {
                    $image_answers[$opt] = $file_path;
                    if ($question_id && !empty($question_data['hinhanh_' . $opt]) && file_exists($question_data['hinhanh_' . $opt])) {
                        unlink($question_data['hinhanh_' . $opt]);
                    }
                }
            }
        }
    }

    // Upload ảnh câu hỏi
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
            $message = "<div style='color:red;'>Chỉ cho phép JPG, PNG, GIF cho ảnh câu hỏi!</div>";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $message = "<div style='color:red;'>Ảnh câu hỏi không vượt quá 5MB!</div>";
        } else {
            $file_name = uniqid('q_') . "." . $ext;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image = $file_path;
                if ($question_id && !empty($question_data['hinhanh']) && file_exists($question_data['hinhanh'])) {
                    unlink($question_data['hinhanh']);
                }
            }
        }
    }

    // Validate dữ liệu
    if (empty($id_baitest) || empty($ten_khoa) || empty($question_text) ||
        empty($choices['A']) || empty($choices['B']) || empty($choices['C']) || empty($choices['D']) ||
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D']) ||
        empty($correct)) {
        $message = "<div style='color:red;'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (!in_array($correct, ['A','B','C','D'])) {
        $message = "<div style='color:red;'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } else {
        $conn = dbconnect();
        if ($question_id) {
            $sql = "UPDATE quiz SET id_baitest=?, ten_khoa=?, cauhoi=?, hinhanh=?,
                        cau_a=?, hinhanh_a=?, giaithich_a=?,
                        cau_b=?, hinhanh_b=?, giaithich_b=?,
                        cau_c=?, hinhanh_c=?, giaithich_c=?,
                        cau_d=?, hinhanh_d=?, giaithich_d=?,
                        dap_an=? WHERE Id_cauhoi=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssssssssis", $id_baitest, $ten_khoa, $question_text, $image,
                $choices['A'], $image_answers['a'], $explanations['A'],
                $choices['B'], $image_answers['b'], $explanations['B'],
                $choices['C'], $image_answers['c'], $explanations['C'],
                $choices['D'], $image_answers['d'], $explanations['D'],
                $correct, $question_id);
        } else {
            $sql = "INSERT INTO quiz (id_baitest, ten_khoa, cauhoi, hinhanh,
                        cau_a, hinhanh_a, giaithich_a,
                        cau_b, hinhanh_b, giaithich_b,
                        cau_c, hinhanh_c, giaithich_c,
                        cau_d, hinhanh_d, giaithich_d,
                        dap_an) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssssssss", $id_baitest, $ten_khoa, $question_text, $image,
                $choices['A'], $image_answers['a'], $explanations['A'],
                $choices['B'], $image_answers['b'], $explanations['B'],
                $choices['C'], $image_answers['c'], $explanations['C'],
                $choices['D'], $image_answers['d'], $explanations['D'],
                $correct);
        }

        if ($stmt->execute()) {
            $message = "<div style='color:green;'>Câu hỏi đã được lưu thành công!</div>";
        } else {
            $message = "<div style='color:red;'>Lỗi lưu dữ liệu: " . $stmt->error . "</div>";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($question_id) ? "Cập nhật câu hỏi" : "Thêm câu hỏi"; ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: auto; padding: 20px; }
        h2 { color: #2a7ae2; }
        input[type="text"], textarea, select {
            width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="file"] { margin-bottom: 10px; }
        label { font-weight: bold; }
        .image-preview { margin-bottom: 10px; }
        .image-preview img { max-width: 200px; display: block; margin-top: 5px; }
        .delete-checkbox { margin-bottom: 10px; }
        button { padding: 10px 20px; background-color: #2a7ae2; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #1d5dbb; }
        .message { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2><?php echo isset($question_id) ? "Cập nhật câu hỏi" : "Thêm câu hỏi mới"; ?></h2>

    <div class="message"><?php echo $message; ?></div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($question_id); ?>">

        <label>ID Bài Test:</label>
        <input type="text" name="id_baitest" value="<?php echo htmlspecialchars($question_data['id_baitest'] ?? ''); ?>">

        <label>Tên Khóa:</label>
        <input type="text" name="ten_khoa" value="<?php echo htmlspecialchars($question_data['ten_khoa'] ?? ''); ?>">

        <label>Nội dung câu hỏi:</label>
        <textarea name="question_text"><?php echo htmlspecialchars($question_data['cauhoi'] ?? ''); ?></textarea>

        <label>Ảnh câu hỏi:</label>
        <?php if (!empty($question_data['hinhanh'])): ?>
            <div class="image-preview">
                <img src="<?php echo htmlspecialchars($question_data['hinhanh']); ?>" alt="Ảnh câu hỏi">
                <label class="delete-checkbox"><input type="checkbox" name="delete_image" value="1"> Xoá ảnh</label>
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($question_data['hinhanh']); ?>">
            </div>
        <?php endif; ?>
        <input type="file" name="image">

        <?php foreach (['a', 'b', 'c', 'd'] as $opt): ?>
            <hr>
            <label>Đáp án <?php echo strtoupper($opt); ?>:</label>
            <input type="text" name="choice_<?php echo $opt; ?>" value="<?php echo htmlspecialchars($question_data['cau_' . $opt] ?? ''); ?>">

            <label>Ảnh đáp án <?php echo strtoupper($opt); ?>:</label>
            <?php if (!empty($question_data['hinhanh_' . $opt])): ?>
                <div class="image-preview">
                    <img src="<?php echo htmlspecialchars($question_data['hinhanh_' . $opt]); ?>" alt="Ảnh đáp án <?php echo strtoupper($opt); ?>">
                    <label class="delete-checkbox">
                        <input type="checkbox" name="delete_image_<?php echo $opt; ?>" value="1"> Xoá ảnh
                    </label>
                    <input type="hidden" name="existing_image_<?php echo $opt; ?>" value="<?php echo htmlspecialchars($question_data['hinhanh_' . $opt]); ?>">
                </div>
            <?php endif; ?>
            <input type="file" name="image_<?php echo $opt; ?>">

            <label>Giải thích đáp án <?php echo strtoupper($opt); ?>:</label>
            <textarea name="explain_<?php echo $opt; ?>"><?php echo htmlspecialchars($question_data['giaithich_' . $opt] ?? ''); ?></textarea>
        <?php endforeach; ?>

        <label>Đáp án đúng (A/B/C/D):</label>
        <input type="text" name="correct" value="<?php echo htmlspecialchars($question_data['dap_an'] ?? ''); ?>">

        <button type="submit" name="save_question">Lưu câu hỏi</button>
    </form>
</body>
</html>


