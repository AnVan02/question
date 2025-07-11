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
    // Xử lý xoá ảnh câu hỏi nếu có chọn
    if (isset($_POST["delete_image"]) && $_POST["delete_image"] == "1" && $image && file_exists($image)) {
        unlink($image);
        $image = null;
    }
    // Thêm biến cho ảnh đáp án
    $image_answers = [];
    foreach(['a','b','c','d'] as $opt) {
        $image_answers[$opt] = isset($_POST["existing_image_{$opt}"]) ? $_POST["existing_image_{$opt}"] : null;
        // Xử lý xoá ảnh đáp án nếu có chọn
        if (isset($_POST["delete_image_{$opt}"]) && $_POST["delete_image_{$opt}"] == "1" && $image_answers[$opt] && file_exists($image_answers[$opt])) {
            unlink($image_answers[$opt]);
            $image_answers[$opt] = null;
        }
    }
    $upload_dir = "images/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    // Xử lý hình ảnh đáp án
    foreach(['a','b','c','d'] as $opt) {
        $file_key = "image_{$opt}";
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]["error"] === UPLOAD_ERR_OK) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_exts)) {
                $message = "<div style='color:red;'>Chỉ cho phép các định dạng hình ảnh JPG, JPEG, PNG, GIF cho đáp án ".strtoupper($opt)."!</div>";
            } elseif ($_FILES[$file_key]["size"] > 5 * 1024 * 1024) {
                $message = "<div style='color:red;'>Hình ảnh đáp án ".strtoupper($opt)." không được vượt quá 5MB!</div>";
            } else {
                $file_name = uniqid($opt.'_') . "." . $file_ext;
                $file_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $file_path)) {
                    $image_answers[$opt] = $file_path;
                    if ($question_id && !empty($question_data['hinhanh_' . $opt]) && file_exists($question_data['hinhanh_' . $opt])) {
                        unlink($question_data['hinhanh_' . $opt]);
                    }
                } else {
                    $message = "<div style='color:red;'>Lỗi khi tải lên hình ảnh đáp án ".strtoupper($opt)."!</div>";
                }
            }
        }
    }
    // Xử lý hình ảnh câu hỏi (nếu có upload mới)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_exts)) {
            $message = "<div style='color:red;'>Chỉ cho phép các định dạng hình ảnh JPG, JPEG, PNG, GIF cho câu hỏi!</div>";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $message = "<div style='color:red;'>Hình ảnh câu hỏi không được vượt quá 5MB!</div>";
        } else {
            $file_name = uniqid('q_') . "." . $file_ext;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image = $file_path;
                if ($question_id && !empty($question_data['hinhanh']) && file_exists($question_data['hinhanh'])) {
                    unlink($question_data['hinhanh']);
                }
            } else {
                $message = "<div style='color:red;'>Lỗi khi tải lên hình ảnh câu hỏi!</div>";
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
                        cau_a=?, hinhanh_a=?, giaithich_a=?, 
                        cau_b=?, hinhanh_b=?, giaithich_b=?, 
                        cau_c=?, hinhanh_c=?, giaithich_c=?, 
                        cau_d=?, hinhanh_d=?, giaithich_d=?, 
                        dap_an=? 
                    WHERE Id_cauhoi=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssssssssi", $id_baitest, $ten_khoa, $question_text, $image,
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
                        dap_an) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssssssss", $id_baitest, $ten_khoa, $question_text, $image,
                $choices['A'], $image_answers['a'], $explanations['A'],
                $choices['B'], $image_answers['b'], $explanations['B'],
                $choices['C'], $image_answers['c'], $explanations['C'],
                $choices['D'], $image_answers['d'], $explanations['D'],
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
            min-height: 40px;
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
    <script>
    function removeImage(btn, inputName, containerClass) {
        var container = btn.closest('.' + containerClass);
        if (container) {
            var img = container.querySelector('img');
            if (img) img.style.display = 'none';
            var notice = document.createElement('div');
            notice.style.color = 'red';
            notice.style.fontWeight = 'bold';
            notice.textContent = 'Đã chọn xoá ảnh';
            container.appendChild(notice);
            btn.style.display = 'none';
        }
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = inputName;
        input.value = '1';
        btn.form.appendChild(input);
    }
    // Hiển thị preview và nút xoá bên cạnh input file khi chọn file
    function showPreviewAndRemoveBtn(inputId, previewId, removeBtnId) {
        var input = document.getElementById(inputId);
        var preview = document.getElementById(previewId);
        var removeBtn = document.getElementById(removeBtnId);
        if (input.files && input.files.length > 0) {
            var file = input.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'inline-block';
                removeBtn.style.display = 'inline-block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
            removeBtn.style.display = 'none';
        }
    }
    function removeNewImage(inputId, previewId, removeBtnId) {
        var input = document.getElementById(inputId);
        var preview = document.getElementById(previewId);
        var removeBtn = document.getElementById(removeBtnId);
        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        removeBtn.style.display = 'none';
    }
    </script>
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
            <input type="file" name="image" id="image_input" accept="image/*" onchange="showPreviewAndRemoveBtn('image_input', 'image_preview', 'remove_image_btn')">
            <img id="image_preview" src="" style="display:none;max-width:120px;margin-left:10px;vertical-align:middle;border-radius:10px;" alt="Preview">
            <button type="button" id="remove_image_btn" style="display:none;margin-left:10px;background:#f55;vertical-align:middle;" onclick="removeNewImage('image_input','image_preview','remove_image_btn')">Xoá ảnh</button>
            <?php if (!empty($question_data['hinhanh'])): ?>
                <div class="existing-image question-image">
                    <p>Hình ảnh hiện tại:</p>
                    <img src="<?= htmlspecialchars($question_data['hinhanh']) ?>" alt="Hình ảnh câu hỏi">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($question_data['hinhanh']) ?>">
                    <button type="button" onclick="removeImage(this, 'delete_image', 'question-image')">Xoá ảnh</button>
                </div>
            <?php endif; ?>

            <?php
            $options = ['A', 'B', 'C', 'D'];
            foreach ($options as $opt): $opt_lc = strtolower($opt); ?>
                <label>Đáp án <?= $opt ?>:</label>
                <textarea name="choice_<?= $opt_lc ?>" rows="2"><?= htmlspecialchars($question_data["cau_".$opt_lc] ?? '') ?></textarea>
                <label>Ảnh đáp án <?= $opt ?> (nếu có):</label>
                <input type="file" name="image_<?= $opt_lc ?>" id="image_input_<?= $opt_lc ?>" accept="image/*" onchange="showPreviewAndRemoveBtn('image_input_<?= $opt_lc ?>', 'image_preview_<?= $opt_lc ?>', 'remove_image_btn_<?= $opt_lc ?>')">
                <img id="image_preview_<?= $opt_lc ?>" src="" style="display:none;max-width:120px;margin-left:10px;vertical-align:middle;border-radius:10px;" alt="Preview">
                <button type="button" id="remove_image_btn_<?= $opt_lc ?>" style="display:none;margin-left:10px;background:#f55;vertical-align:middle;" onclick="removeNewImage('image_input_<?= $opt_lc ?>','image_preview_<?= $opt_lc ?>','remove_image_btn_<?= $opt_lc ?>')">Xoá ảnh</button>
                <?php if (!empty($question_data["hinhanh_" . $opt_lc])): ?>
                    <div class="existing-image answer-image-<?= $opt_lc ?>">
                        <img src="<?= htmlspecialchars($question_data["hinhanh_" . $opt_lc]) ?>" alt="Ảnh đáp án <?= $opt ?>">
                        <input type="hidden" name="existing_image_<?= $opt_lc ?>" value="<?= htmlspecialchars($question_data["hinhanh_" . $opt_lc]) ?>">
                        <button type="button" onclick="removeImage(this, 'delete_image_<?= $opt_lc ?>', 'answer-image-<?= $opt_lc ?>')">Xoá ảnh</button>
                    </div>
                <?php endif; ?>
                <label>Giải thích <?= $opt ?>:</label>
                <textarea name="explain_<?= $opt_lc ?>" rows="2"><?= htmlspecialchars($question_data["giaithich_".$opt_lc] ?? '') ?></textarea>
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
