<?php

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}
// lấy dữ liệu câu hỏi để chỉnh sữa (nếu có )
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

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "<div style='color:green;'>Câu hỏi đã được lưu vào cơ sở dữ liệu!</div>";
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
    $image = isset($_POST["existing_image"]) ? $_POST["existing_image"] : null;

    $upload_dir = "images/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    // Xử lý hình ảnh
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_exts)) {
            $message = "<div style='color:red;'>Chỉ cho phép các định dạng hình ảnh JPG, JPEG, PNG, GIF!</div>";
        } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
            $message = "<div style='color:red;'>Hình ảnh không được vượt quá 5MB!</div>";
        } else {
            $file_name = uniqid() . "." . $file_ext;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $file_path)) {
                $image = $file_path;
                if ($question_id && !empty($question_data['hinhanh']) && file_exists($question_data['hinhanh'])) {
                    unlink($question_data['hinhanh']);
                }
            } else {
                $message = "<div style='color:red;'>Lỗi khi tải lên hình ảnh!</div>";
            }
        }
    }
    // Xử lý câu hỏi 
    if (empty($id_baitest) || empty($ten_khoa) || empty($question_text) ||
        empty($choices['A']) || empty($choices['B']) || empty($choices['C']) || empty($choices['D']) ||
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D']) ||
        empty($correct)) {
        $message = "<div style='color:red;'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (!in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "<div style='color:red;'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } else {
        $conn = dbconnect();
        if ($question_id) {
            $sql = "UPDATE quiz SET id_baitest=?, ten_khoa=?, cauhoi=?, hinhanh=?, 
                        cau_a=?, giaithich_a=?, 
                        cau_b=?, giaithich_b=?, 
                        cau_c=?, giaithich_c=?, 
                        cau_d=?, giaithich_d=?, 
                        dap_an=? 
                    WHERE Id_cauhoi=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssssi", $id_baitest, $ten_khoa, $question_text, $image,
                $choices['A'], $explanations['A'],
                $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'],
                $choices['D'], $explanations['D'],
                $correct, $question_id);
        } else {
            $sql = "INSERT INTO quiz (id_baitest, ten_khoa, cauhoi, hinhanh, 
                        cau_a, giaithich_a, 
                        cau_b, giaithich_b, 
                        cau_c, giaithich_c, 
                        cau_d, giaithich_d, 
                        dap_an) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssss", $id_baitest, $ten_khoa, $question_text, $image,
                $choices['A'], $explanations['A'],
                $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'],
                $choices['D'], $explanations['D'],
                $correct);
        }
        // hiển thị thêm thành công
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                $message = "<div style='color:green;'>Thêm câu hỏi thành công!</div>";
                // Không redirect nữa
                // header("Location: question.php?success=1");
                // exit;
            } else {
                $message = "<div style='color:red;'>Lỗi khi lưu câu hỏi: " . $stmt->error . "</div>";
                $stmt->close();
                $conn->close();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhập câu hỏi </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: rgb(252, 251, 248);
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: rgb(247, 18, 18);
            margin-bottom: 25px;
        }
        form label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #333;
        }
        form input[type="text"], form textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }
        form input[type="file"] {
            margin-top: 8px;
        }
        form textarea {
            resize: vertical;
        }
        .custom-select {
            padding: 8px 12px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 150px;
        }
        .existing-image img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        button {
            background-color: rgba(71, 151, 255, 0.81);
            color: white;
            font-size: 18px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
        }
        div[style^="color:red"] {
            background-color: #ffeaea;
            padding: 10px;
            border-left: 5px solid red;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        div[style^="color:green"] {
            background-color: #e0fbe7;
            padding: 10px;
            border-left: 5px solid green;
            margin-bottom: 20px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= $question_id ? 'Cập nhật câu hỏi' : 'Thêm mới câu hỏi' ?></h2>
        <?php if (!empty($message)) echo $message; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>ID bài test:</label>
            <input type="text" name="id_baitest" value="<?= htmlspecialchars($question_data['id_baitest'] ?? '') ?>">

            <label>Nội dung câu hỏi:</label>
            <textarea name="question_text" rows="4"><?= htmlspecialchars($question_data['cauhoi'] ?? '') ?></textarea>

            <label>Tên khoá:</label>
            <input type="text" name="ten_khoa" value="<?= htmlspecialchars($question_data['ten_khoa'] ?? '') ?>">

            <label>Hình ảnh (nếu có):</label>
            <input type="file" name="image" accept="image/*">
            <?php if (!empty($question_data['hinhanh'])): ?>
                <div class="existing-image">
                    <p>Hình ảnh hiện tại:</p>
                    <img src="<?= htmlspecialchars($question_data['hinhanh']) ?>" alt="Hình ảnh câu hỏi">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($question_data['hinhanh']) ?>">
                </div>
            <?php endif; ?>

            <?php
            $options = ['A', 'B', 'C', 'D'];
            foreach ($options as $opt): ?>
                <label>Đáp án <?= $opt ?>:</label>
                <input type="text" name="choice_<?= strtolower($opt) ?>" value="<?= htmlspecialchars($question_data["cau_".strtolower($opt)] ?? '') ?>">
                <label>Giải thích <?= $opt ?>:</label>
                <input type="text" name="explain_<?= strtolower($opt) ?>" value="<?= htmlspecialchars($question_data["giaithich_".strtolower($opt)] ?? '') ?>">
            <?php endforeach; ?>

            <label>Đáp án đúng:</label>
            <select name="correct" class="custom-select">
                <?php foreach ($options as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($question_data['dap_an'] ?? '') == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>

            <?php if ($question_id): ?>
                <input type="hidden" name="question_id" value="<?= $question_id ?>">
            <?php endif; ?>

            <div>
                <button type="submit" name="save_question">Lưu câu hỏi</button>
            </div>
        </form>
    </div>
</body>
</html>
