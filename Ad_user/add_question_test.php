<?php
// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy dữ liệu câu hỏi để chỉnh sửa (nếu có)
$question_data = [];
$question_id = isset($_GET['question_id']) && is_numeric($_GET['question_id']) ? (int)$_GET['question_id'] : null;
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

// Xử lý khi submit form
$message = "";
if(isset ($_GET ['suscuss']) && $_GET ['success'] === 1) {
    $massage ="<div style='color :green ;'> Câu hỏi đã đươc lưu vào cơ sơ dữ liệu !</div>";
}



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
    $correct = strtoupper(trim($_POST["correct"]));
    $question_id = isset($_POST["question_id"]) && is_numeric($_POST["question_id"]) ? (int)$_POST["question_id"] : null;
    $image = isset($_POST["existing_image"]) ? $_POST["existing_image"] : null;

    // Upload hình ảnh
    $upload_dir = "images/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_exts)) {
            $message = "<div style='color:red;'>Chỉ cho phép các định dạng hình ảnh JPG, JPEG, PNG, GIF!</div>";
        } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) { // 5MB
            $message = "<div style='color:red;'>Hình ảnh không được vượt quá 5MB!</div>";
        } else {
            $file_name = uniqid() . "." . $file_ext;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $file_path)) {
                $image = $file_path;
                // Xóa hình ảnh cũ nếu có
                if ($question_id && !empty($question_data['hinhanh']) && file_exists($question_data['hinhanh'])) {
                    unlink($question_data['hinhanh']);
                }
            } else {
                $message = "<div style='color:red;'>Lỗi khi tải lên hình ảnh!</div>";
            }
            
        }
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
            // Cập nhật câu hỏi
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
            // Thêm mới câu hỏi
            $sql = "INSERT INTO quiz (id_baitest, cauhoi, hinhanh, 
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

       if ($stmt -> execute ()) {
        $stmt -> close();
        $conn -> close ();
        header ("location : add_question.php?question_id".($question_id ?? ''). "&success=1");
        exit; 
       } else {
        $massage ="<div style= 'color:red;'>Lỗi khi lưu câu hỏi: ".$stmt->error."<div>";
        $stmt->close ();
        $conn -> clode ();
       }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm/Cập nhật Câu hỏi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2><?= $question_id ? 'Cập nhật câu hỏi' : 'Thêm câu hỏi mới' ?></h2>
        <?php if (!empty($message)) echo $message; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>ID câu hỏi</label><br>
            <input type="text" name="id_cauhoi" value="<?= htmlspecialchars ($question_data ['id_cauhoi'] ?? '') ?>"><br><br>
        
            <label>ID bài test:</label><br>
            <select name="correct" class="custom-select"></select>
            <input type="text" name="id_baitest" value="<?= htmlspecialchars($question_data['id_baitest'] ?? '') ?>"><br><br>


            <label>Nội dung câu hỏi:</label><br>
            <textarea name="question_text" rows="4" cols="50"><?= htmlspecialchars($question_data['cauhoi'] ?? '') ?></textarea><br><br>

            <label>Hình ảnh (nếu có):</label><br>
            <input type="file" name="image" accept="image/*">
            <?php if (!empty($question_data['hinhanh'])): ?>
                <div class="existing-image">
                    <p>Hình ảnh hiện tại:</p>
                    <img src="<?= htmlspecialchars($question_data['hinhanh']) ?>" alt="Hình ảnh câu hỏi">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($question_data['hinhanh']) ?>">
                </div>
            <?php endif; ?><br><br>

            <label>Đáp án A:</label><br>
            <input type="text" name="choice_a" value="<?= htmlspecialchars($question_data['cau_a'] ?? '') ?>"><br>
            <label>Giải thích A:</label><br>
            <input type="text" name="explain_a" value="<?= htmlspecialchars($question_data['giaithich_a'] ?? '') ?>"><br><br>

            <label>Đáp án B:</label><br>
            <input type="text" name="choice_b" value="<?= htmlspecialchars($question_data['cau_b'] ?? '') ?>"><br>
            <label>Giải thích B:</label><br>
            <input type="text" name="explain_b" value="<?= htmlspecialchars($question_data['giaithich_b'] ?? '') ?>"><br><br>

            <label>Đáp án C:</label><br>
            <input type="text" name="choice_c" value="<?= htmlspecialchars($question_data['cau_c'] ?? '') ?>"><br>
            <label>Giải thích C:</label><br>
            <input type="text" name="explain_c" value="<?= htmlspecialchars($question_data['giaithich_c'] ?? '') ?>"><br><br>

            <label>Đáp án D:</label><br>
            <input type="text" name="choice_d" value="<?= htmlspecialchars($question_data['cau_d'] ?? '') ?>"><br>
            <label>Giải thích D:</label><br>
            <input type="text" name="explain_d" value="<?= htmlspecialchars($question_data['giaithich_d'] ?? '') ?>"><br><br>

            <label>Đáp án đúng :</label><br>
            <select name="correct" class="custom-select"> 
                <option value="A" <?= ($question_data['dap_an'] ?? '') == 'A' ? 'selected' : '' ?>>A</option>
                <option value="B" <?= ($question_data['dap_an'] ?? '') == 'B' ? 'selected' : '' ?>>B</option>
                <option value="C" <?= ($question_data['dap_an'] ?? '') == 'C' ? 'selected' : '' ?>>C</option>
                <optipm value="D" <?= ($question_data['dap_an'] ?? '') == 'D' ? 'selected' : '' ?>>D</option>
            </select>

            <input type="text" name="correct" value="<?= htmlspecialchars($question_data['dap_an'] ?? '') ?>"><br><br>

            <!-- Ẩn ID câu hỏi nếu là cập nhật -->
            <input type="hidden" name="question_id" value="<?= $question_id ?? '' ?>">
            
            <div>
                <button type="submit" name="save_question" class="btn btn-primary">Lưu câu hỏi</button>
                <!-- <button type ="button" onclick="windown.location.href='upload.php'"name="all_question">Upload Excel</button> -->
            </div>

            </button>
        </form>
    </div>
</body>
</html>


<style>
/* style.css */
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to right, #f8f9fa, #e0f7fa);
    margin: 0;
    padding: 20px;
}

.container {
    background-color: #ffffff;
    max-width: 700px;
    margin: 0 auto;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color: #00796b;
    margin-bottom: 25px;
}

form label {
    font-weight: 600;
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
    color: #333;
}

form input[type="text"],
form textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}

form input[type="text"]:focus,
form textarea:focus {
    border-color: #009688;
    outline: none;
    background-color: #f1fefc;
}

form input[type="file"] {
    margin-top: 8px;
}

.existing-image {
    margin-top: 10px;
}

.existing-image img {
    max-width: 100%;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 10px;
    margin-top: 5px;
}

button {
    display: block;
    width: 100%;
    background-color: #009688;
    color: white;
    font-size: 16px;
    padding: 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 25px;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #00796b;
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