<?php
// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Xử lý khi submit form
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_question"])) {
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
    $correct = $_POST["correct"];
    $question_id = isset($_POST["question_id"]) && is_numeric($_POST["question_id"]) ? (int)$_POST["question_id"] : null;
    $image = "";

    // Upload hình ảnh
    $upload_dir = "images/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $file_name = uniqid() . "." . $file_ext;
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $file_path)) {
            $image = $file_path;
        }
    } elseif (isset($_POST["existing_image"])) {
        $image = $_POST["existing_image"];
    }

    // Validate
    if (empty($id_baitest) || empty($question_text) || empty($choices['A']) || empty($choices['B']) ||
        empty($choices['C']) || empty($choices['D']) || empty($correct) || 
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D'])) {
        $message = "<div style='color:red;'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (!in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "<div style='color:red;'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } else {
        $conn = dbconnect();

        if ($question_id) {
            // Cập nhật
            $sql = "UPDATE quiz SET id_baitest=?, cauhoi=?, hinhanh=?, 
                        cau_a=?, giaithich_a=?, 
                        cau_b=?, giaithich_b=?, 
                        cau_c=?, giaithich_c=?, 
                        cau_d=?, giaithich_d=?, 
                        dap_an=? 
                    WHERE Id_cauhoi=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssssi", $id_baitest, $question_text, $image,
                $choices['A'], $explanations['A'],
                $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'],
                $choices['D'], $explanations['D'],
                $correct, $question_id);
        } else {
            // Thêm mới
            $sql = "INSERT INTO quiz (id_baitest, id_cauhoi, hinhanh, 
                        cau_a, giaithich_a, 
                        cau_b, giaithich_b, 
                        cau_c, giaithich_c, 
                        cau_d, giaithich_d, 
                        dap_an) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssss", $id_baitest, $question_text, $image,
                $choices['A'], $explanations['A'],
                $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'],
                $choices['D'], $explanations['D'],
                $correct);
        }

        if ($stmt->execute()) {
            $message = "<div style='color:green;'>Câu hỏi đã được lưu vào cơ sở dữ liệu!</div>";
        } else {
            $message = "<div style='color:red;'>Lỗi: " . $stmt->error . "</div>";
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
    <title>Thêm/Cập nhật Câu hỏi</title>
</head>
<body>
    <h2>Cập nhật câu hỏi</h2>
    <?php if (!empty($message)) echo $message; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>ID bài test:</label><br>
        <input type="text" name="id_baitest"><br><br>

        <label>Nội dung câu hỏi:</label><br>
        <textarea name="question_text" rows="4" cols="50"></textarea><br><br>

        <label>Hình ảnh (nếu có):</label><br>
        <input type="file" name="image"><br><br>

        <label>Đáp án A:</label><br>
        <input type="text" name="choice_a"><br>
        <label>Giải thích A:</label><br>
        <input type="text" name="explain_a"><br><br>

        <label>Đáp án B:</label><br>
        <input type="text" name="choice_b"><br>
        <label>Giải thích B:</label><br>
        <input type="text" name="explain_b"><br><br>

        <label>Đáp án C:</label><br>
        <input type="text" name="choice_c"><br>
        <label>Giải thích C:</label><br>
        <input type="text" name="explain_c"><br><br>

        <label>Đáp án D:</label><br>
        <input type="text" name="choice_d"><br>
        <label>Giải thích D:</label><br>
        <input type="text" name="explain_d"><br><br>

        <label>Đáp án đúng (A/B/C/D):</label><br>
        <input type="text" name="correct"><br><br>

        <!-- Ẩn ID câu hỏi nếu là cập nhật -->
        <input type="hidden" name="question_id" value="">

        <button type="submit" name="save_question">Lưu câu hỏi</button>
    </form>
</body>
</html>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f2f5;
        padding: 20px;
    }

    h2 {
        color: #333;
        text-align: center;
            
    }

    form {
        background-color: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        margin: auto;
    }

    label {
        font-weight: bold;
        display: block;
        margin-top: 15px;
    }

    input[type="text"],
    textarea,
    input[type="file"] {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
    }

    textarea {
        resize: vertical;
    }

    button[type="submit"] {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 12px 20px;
        margin-top: 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button[type="submit"]:hover {
        background-color: #0056b3;
    }

    div {
        margin-bottom: 10px;
    }

    .success {
        color: green;
        font-weight: bold;
    }

    .error {
        color: red;
        font-weight: bold;
    }
</style>