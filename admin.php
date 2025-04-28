<?php
session_start();

// Nếu chưa có mảng câu hỏi, tạo mảng rỗng
if (!isset($_SESSION["questions"])) {
    $_SESSION["questions"] = [];
}

// Xử lý form khi submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $question = trim($_POST["question"]);
    $choice_a = trim($_POST["choice_a"]);
    $choice_b = trim($_POST["choice_b"]);
    $choice_c = trim($_POST["choice_c"]);
    $choice_d = trim($_POST["choice_d"]);
    $correct = $_POST["correct"];
    $explanation = trim($_POST["explanation"]);
    
    // Xử lý ảnh nếu có upload
    $image_path = "";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Bạn cần tạo thư mục này
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $image_path = $target_file;
    }

    // Tạo câu hỏi mới
    $new_question = [
        "question" => $question,
        "choices" => [
            "a" => $choice_a,
            "b" => $choice_b,
            "c" => $choice_c,
            "d" => $choice_d,
        ],
        "correct" => $correct,
        "reason" => $explanation,
        "image" => $image_path
    ];

    // Lưu vào session
    $_SESSION["questions"][] = $new_question;

    $success = "✅ Đã thêm câu hỏi thành công!";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Thêm câu hỏi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #6f42c1;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 8px;
            margin-top: 3px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        input[type="file"] {
            margin-top: 5px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #6f42c1;
            color: #fff;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }
        button:hover {
            background: #563d7c;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-left: 5px solid #28a745;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Thêm Câu Hỏi Mới</h2>

    <?php if (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Câu hỏi:</label>
        <textarea name="question" required></textarea>

        <label>Hình ảnh (không bắt buộc):</label>
        <input type="file" name="image" accept="image/*">

        <label>Đáp án A:</label>
        <input type="text" name="choice_a" required>

        <label>Đáp án B:</label>
        <input type="text" name="choice_b" required>

        <label>Đáp án C:</label>
        <input type="text" name="choice_c" required>

        <label>Đáp án D:</label>
        <input type="text" name="choice_d" required>

        <label>Đáp án đúng:</label>
        <select name="correct" required>
            <option value="">-- Chọn --</option>
            <option value="a">A</option>
            <option value="b">B</option>
            <option value="c">C</option>
            <option value="d">D</option>
        </select>

        <label>Giải thích (nếu có):</label>
        <textarea name="explanation"></textarea>

        <button type="submit">Thêm câu hỏi</button>
    </form>
</div>
</body>
</html>
