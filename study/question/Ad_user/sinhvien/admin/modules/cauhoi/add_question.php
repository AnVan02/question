<?php

function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy danh sách khóa học
function getKhoaHocList() {
    $conn = dbconnect();
    $sql = "SELECT * FROM khoa_hoc ORDER BY khoa_hoc";
    $result = $conn->query($sql);
    $khoa_hoc_list = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $khoa_hoc_list[] = $row;
        }
    }
    $conn->close();
    return $khoa_hoc_list;
}

// Lấy danh sách bài test theo khóa học
function getBaiTestList($id_khoa = null) {
    $conn = dbconnect();
    if ($id_khoa) {
        $sql = "SELECT * FROM test WHERE id_khoa = ? ORDER BY ten_test";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $sql = "SELECT t.*, k.khoa_hoc FROM test t 
                LEFT JOIN khoa_hoc k ON t.id_khoa = k.id 
                ORDER BY k.khoa_hoc, t.ten_test";
        $result = $conn->query($sql);
    }
    
    $bai_test_list = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $bai_test_list[] = $row;
        }
    }
    $conn->close();
    return $bai_test_list;
}

// Lấy dữ liệu câu hỏi để chỉnh sửa (nếu có)
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

// Lấy danh sách khóa học và bài test
$khoa_hoc_list = getKhoaHocList();
$bai_test_list = getBaiTestList();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_question"])) {
    $id_khoa = trim($_POST["id_khoa"]);
    $id_baitest = trim($_POST["id_baitest"]);
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
    
    // Lấy tên khóa học từ id_khoa
    $ten_khoa = "";
    foreach($khoa_hoc_list as $khoa) {
        if($khoa['id'] == $id_khoa) {
            $ten_khoa = $khoa['khoa_hoc'];
            break;
        }
    }
    
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
    
    // Debug: Hiển thị thông tin để kiểm tra
    error_log("Debug - id_baitest: " . $id_baitest);
    error_log("Debug - ten_khoa: " . $ten_khoa);
    error_log("Debug - correct: " . $correct);
    error_log("Debug - image_answers: " . print_r($image_answers, true));
    
    // Xử lý câu hỏi 
    if (empty($id_khoa) || empty($id_baitest) || empty($question_text) ||
        empty($choices['A']) || empty($choices['B']) || empty($choices['C']) || empty($choices['D']) ||
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D']) ||
        empty($correct)) {
        $message = "<div style='color:red;'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (!in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "<div style='color:red;'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } else {
        $conn = dbconnect();
        
        // Đảm bảo id_baitest là string
        $id_baitest = (string)$id_baitest;
        
        if ($question_id) {
            // UPDATE query - Fixed bind_param
            $sql = "UPDATE quiz SET id_baitest=?, ten_khoa=?, cauhoi=?, hinhanh=?, 
                        cau_a=?, hinhanh_a=?, giaithich_a=?, 
                        cau_b=?, hinhanh_b=?, giaithich_b=?, 
                        cau_c=?, hinhanh_c=?, giaithich_c=?, 
                        cau_d=?, hinhanh_d=?, giaithich_d=?, 
                        dap_an=? 
                    WHERE Id_cauhoi=?";
            $stmt = $conn->prepare($sql);
            // Fixed: 17 parameters + 1 for WHERE clause = 18 parameters total
            // Parameter types: 17 strings + 1 integer = "sssssssssssssssssi"
            $stmt->bind_param("sssssssssssssssssi", 
                $id_baitest, $ten_khoa, $question_text, $image,
                $choices['A'], $image_answers['a'], $explanations['A'],
                $choices['B'], $image_answers['b'], $explanations['B'],
                $choices['C'], $image_answers['c'], $explanations['C'],
                $choices['D'], $image_answers['d'], $explanations['D'],
                $correct, $question_id
            );
        } else {
            // INSERT query - Fixed bind_param
            $sql = "INSERT INTO quiz (id_baitest, ten_khoa, cauhoi, hinhanh, 
                        cau_a, hinhanh_a, giaithich_a, 
                        cau_b, hinhanh_b, giaithich_b, 
                        cau_c, hinhanh_c, giaithich_c, 
                        cau_d, hinhanh_d, giaithich_d, 
                        dap_an) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            // Fixed: 17 parameters, all strings = "sssssssssssssssss"
            $stmt->bind_param("sssssssssssssssss", 
                $id_baitest, $ten_khoa, $question_text, $image,
                $choices['A'], $image_answers['a'], $explanations['A'],
                $choices['B'], $image_answers['b'], $explanations['B'],
                $choices['C'], $image_answers['c'], $explanations['C'],
                $choices['D'], $image_answers['d'], $explanations['D'],
                $correct
            );
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            $message = "<div style='color:green;'>Lưu câu hỏi thành công!</div>";
            
            // Debug: Hiển thị thông tin đã lưu
            $message .= "<div style='color:blue;'>Debug - Đã lưu: id_baitest=" . $id_baitest . ", dap_an=" . $correct . "</div>";
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
    <title>Cập nhật câu hỏi</title>
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
        form input[type="text"], form textarea, form select {
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
        
        /* Styling cho ảnh preview và existing image */
        .image-preview-container {
            margin-top: 10px;
            display: none;
            position: relative;
        }
        .image-preview-container img {
            max-width: 200px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .image-preview-container .remove-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .image-preview-container .remove-btn:hover {
            background: #ff0000;
        }
        
        .existing-image {
            margin-top: 10px;
            position: relative;
            display: inline-block;
        }
        .existing-image img {
            max-width: 200px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .existing-image .remove-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .existing-image .remove-btn:hover {
            background: #ff0000;
        }
        
        .removed-notice {
            color: #ff4444;
            font-weight: bold;
            margin-top: 10px;
            padding: 8px;
            background: #ffe6e6;
            border-radius: 5px;
            display: inline-block;
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
        .form-row {
            display: flex;
            flex-direction: column; /* Đảm bảo các phần tử con xếp dọc */
        }
        .form-row > div {
            flex: 1;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            margin: 8px 0;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: block;
        }
    </style>
    <script>
    function removeImage(btn, inputName, containerClass) {
        var container = btn.closest('.' + containerClass);
        if (container) {
            var img = container.querySelector('img');
            if (img) img.style.display = 'none';
            
            var notice = document.createElement('div');
            notice.className = 'removed-notice';
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
    
    function showPreviewAndRemoveBtn(inputId, containerId) {
        var input = document.getElementById(inputId);
        var container = document.getElementById(containerId);
        
        if (input.files && input.files.length > 0) {
            var file = input.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = container.querySelector('img');
                img.src = e.target.result;
                container.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            container.style.display = 'none';
        }
    }
    
    function removeNewImage(inputId, containerId) {
        var input = document.getElementById(inputId);
        var container = document.getElementById(containerId);
        input.value = '';
        container.style.display = 'none';
    }
    
    function updateBaiTestOptions() {
        var khoaSelect = document.getElementById('id_khoa');
        var baiTestSelect = document.getElementById('id_baitest');
        var selectedKhoa = khoaSelect.value;
        
        // Clear existing options
        baiTestSelect.innerHTML = '<option value="">-- Chọn bài test --</option>';
        
        <?php foreach($bai_test_list as $test): ?>
        if (selectedKhoa == '<?= $test['id_khoa'] ?>') {
            var option = document.createElement('option');
            option.value = '<?= $test['id_test'] ?>';
            option.textContent = '<?= htmlspecialchars($test['ten_test']) ?>';
            <?php if(isset($question_data['id_baitest']) && $question_data['id_baitest'] == $test['id_test']): ?>
            option.selected = true;
            <?php endif; ?>
            baiTestSelect.appendChild(option);
        }
        <?php endforeach; ?>
    }
    
    // Load bài test options when page loads
    window.onload = function() {
        updateBaiTestOptions();
    }
    </script>
</head>
<body>
    <div class="container">
        <h2><?= $question_id ? 'Cập nhật câu hỏi' : 'Thêm mới câu hỏi' ?></h2>
        <?php if (!empty($message)) echo $message; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div>
                    <label>Khóa học:</label>
                    <select name="id_khoa" id="id_khoa" required onchange="updateBaiTestOptions()">
                        <option value="">-- Chọn khóa học --</option>
                        <?php foreach($khoa_hoc_list as $khoa): ?>
                            <option value="<?= $khoa['id'] ?>" 
                                <?php 
                                // Tìm id_khoa từ question_data nếu đang edit
                                if($question_id && !empty($question_data['ten_khoa'])) {
                                    foreach($khoa_hoc_list as $k) {
                                        if($k['khoa_hoc'] == $question_data['ten_khoa'] && $k['id'] == $khoa['id']) {
                                            echo 'selected';
                                            break;
                                        }
                                    }
                                }
                                ?>>
                                <?= htmlspecialchars($khoa['khoa_hoc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label>Bài test:</label>
                    <select name="id_baitest" id="id_baitest" required>
                        <option value="">-- Chọn bài test --</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
            </div>

            <label>Nội dung câu hỏi:</label>
            <textarea name="question_text" rows="4" required><?= htmlspecialchars($question_data['cauhoi'] ?? '') ?></textarea>

            <label>Hình ảnh (nếu có):</label>
            <input type="file" name="image" id="image_input" accept="image/*" onchange="showPreviewAndRemoveBtn('image_input', 'image_preview_container')">
            <div id="image_preview_container" class="image-preview-container">
                <img src="" alt="Preview">
                <button type="button" class="remove-btn" onclick="removeNewImage('image_input','image_preview_container')">×</button>
            </div>
            
            <?php if (!empty($question_data['hinhanh'])): ?>
                <div class="existing-image question-image">
                    <img src="<?= htmlspecialchars($question_data['hinhanh']) ?>" alt="Hình ảnh câu hỏi">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($question_data['hinhanh']) ?>">
                    <button type="button" class="remove-btn" onclick="removeImage(this, 'delete_image', 'question-image')">×</button>
                </div>
            <?php endif; ?>

            <?php
            $options = ['A', 'B', 'C', 'D'];
            foreach ($options as $opt): $opt_lc = strtolower($opt); ?>
                <label>Đáp án <?= $opt ?>:</label>
                <textarea name="choice_<?= $opt_lc ?>" rows="2" required><?= htmlspecialchars($question_data["cau_".$opt_lc] ?? '') ?></textarea>
                
                <label>Ảnh đáp án <?= $opt ?> (nếu có):</label>
                <div>
                    <input type="file" name="image_<?= $opt_lc ?>" id="image_input_<?= $opt_lc ?>" accept="image/*" onchange="showPreviewAndRemoveBtn('image_input_<?= $opt_lc ?>', 'image_preview_container_<?= $opt_lc ?>')">
                </div>
                <div id="image_preview_container_<?= $opt_lc ?>" class="image-preview-container">
                    <img src="" alt="Preview">
                    <button type="button" class="remove-btn" onclick="removeNewImage('image_input_<?= $opt_lc ?>','image_preview_container_<?= $opt_lc ?>')">×</button>
                </div>
                
                <?php if (!empty($question_data["hinhanh_" . $opt_lc])): ?>
                    <div class="existing-image answer-image-<?= $opt_lc ?>">
                        <img src="<?= htmlspecialchars($question_data["hinhanh_" . $opt_lc]) ?>" alt="Ảnh đáp án <?= $opt ?>">
                        <input type="hidden" name="existing_image_<?= $opt_lc ?>" value="<?= htmlspecialchars($question_data["hinhanh_" . $opt_lc]) ?>">
                        <button type="button" class="remove-btn" onclick="removeImage(this, 'delete_image_<?= $opt_lc ?>', 'answer-image-<?= $opt_lc ?>')">×</button>
                    </div>
                <?php endif; ?>
                
                <label>Giải thích <?= $opt ?>:</label>
                <textarea name="explain_<?= $opt_lc ?>" rows="2" required><?= htmlspecialchars($question_data["giaithich_".$opt_lc] ?? '') ?></textarea>
            <?php endforeach; ?>

            <label>Đáp án đúng:</label>
            <select name="correct" class="custom-select" required>
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