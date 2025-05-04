<?php
// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_question"])) {
    // Lấy dữ liệu từ form
    $id_baitest = trim($_POST["id_baitest"]);
    $id_khoa = trim($_POST["id_khoa"]);
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
    if (empty($id_baitest) || empty($id_khoa) || empty($question_text) || empty($choices['A']) || empty($choices['B']) ||
        empty($choices['C']) || empty($choices['D']) || empty($correct) || 
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D'])) {
        echo "Vui lòng điền đầy đủ thông tin!";
    } elseif (!in_array($correct, ['A', 'B', 'C', 'D'])) {
        echo "Đáp án đúng phải là A, B, C hoặc D!";
    } else {
        // Xử lý hình ảnh nếu có
        $image_path = null;
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            // Đảm bảo thư mục uploads đã tồn tại và có quyền ghi
            $image_path = 'uploads/' . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                echo "Hình ảnh đã được tải lên thành công!";
            } else {
                echo "Lỗi khi tải hình ảnh!";
                $image_path = null;
            }
        }

        // Kết nối cơ sở dữ liệu
        $conn = dbconnect();

        // Thực hiện câu lệnh SQL để lưu câu hỏi
        $sql = "INSERT INTO quiz (id_baitest, id_khoa, cauhoi, hinhanh, 
                    cau_a, giaithich_a, 
                    cau_b, giaithich_b, 
                    cau_c, giaithich_c, 
                    cau_d, giaithich_d, 
                    dap_an) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Gán giá trị vào câu lệnh
            $stmt->bind_param("ssssssssssss", $id_baitest, $id_khoa, $question_text, $image_path,
                $choices['A'], $explanations['A'],
                $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'],
                $choices['D'], $explanations['D'],
                $correct);

            // Thực thi câu lệnh SQL
            if ($stmt->execute()) {
                echo "Câu hỏi đã được lưu thành công!";
            } else {
                echo "Lỗi khi lưu câu hỏi: " . $stmt->error;
            }

            // Đóng kết nối
            $stmt->close();
        } else {
            echo "Lỗi khi chuẩn bị câu lệnh SQL: " . $conn->error;
        }

        // Đóng kết nối cơ sở dữ liệu
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lưu Câu Hỏi</title>
</head>
<body>
    <h1>Thêm Câu Hỏi Trắc Nghiệm</h1>
    <form method="POST" action="save_question.php" enctype="multipart/form-data">
        <label for="id_baitest">ID Bài Test:</label><br>
        <input type="text" id="id_baitest" name="id_baitest" required><br><br>

        <label for="id_khoa">ID Khóa Học:</label><br>
        <input type="text" id="id_khoa" name="id_khoa" required><br><br>

        <label for="question_text">Câu Hỏi:</label><br>
        <textarea id="question_text" name="question_text" required></textarea><br><br>

        <label for="choice_a">Câu A:</label><br>
        <input type="text" id="choice_a" name="choice_a" required><br><br>

        <label for="explain_a">Giải Thích Câu A:</label><br>
        <textarea id="explain_a" name="explain_a" required></textarea><br><br>

        <label for="choice_b">Câu B:</label><br>
        <input type="text" id="choice_b" name="choice_b" required><br><br>

        <label for="explain_b">Giải Thích Câu B:</label><br>
        <textarea id="explain_b" name="explain_b" required></textarea><br><br>

        <label for="choice_c">Câu C:</label><br>
        <input type="text" id="choice_c" name="choice_c" required><br><br>

        <label for="explain_c">Giải Thích Câu C:</label><br>
        <textarea id="explain_c" name="explain_c" required></textarea><br><br>

        <label for="choice_d">Câu D:</label><br>
        <input type="text" id="choice_d" name="choice_d" required><br><br>

        <label for="explain_d">Giải Thích Câu D:</label><br>
        <textarea id="explain_d" name="explain_d" required></textarea><br><br>

        <label for="correct">Đáp Án Đúng:</label><br>
        <input type="text" id="correct" name="correct" required><br><br>

        <label for="image">Hình Ảnh (nếu có):</label><br>
        <input type="file" id="image" name="image"><br><br>

        <button type="submit" name="save_question">Lưu Câu Hỏi</button>
    </form>
</body>
</html>
<style>
}/* Đặt kiểu chữ cho toàn bộ trang */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
}

/* Thiết lập phần tiêu đề */
h1 {
    text-align: center;
    color: #333;
    margin-top: 30px;
}

/* Thiết lập cho form */
form {
    width: 60%;
    margin: 20px auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Thẻ label cho các trường trong form */
label {
    font-size: 16px;
    color: #333;
    margin-bottom: 5px;
    display: block;
}

/* Định dạng các trường nhập liệu */
input[type="text"],
textarea {
    width: 100%;
    padding: 10px;
    margin: 8px 0 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

input[type="file"] {
    margin-bottom: 20px;
}

/* Nút submit */
button[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

/* Thông báo lỗi hoặc thành công */
.error-message,
.success-message {
    color: #d9534f;
    font-size: 16px;
    text-align: center;
}

.success-message {
    color: #5bc0de;
}

/* Đảm bảo các phần tử trong form được căn chỉnh tốt trên các màn hình nhỏ */
@media screen and (max-width: 768px) {
    form {
        width: 90%;
    }
}

@media screen and (max-width: 480px) {
    h1 {
        font-size: 20px;
    }

    form {
        width: 100%;
        padding: 15px;
    }

    input[type="text"],
    textarea {
        font-size: 14px;
    }

    button[type="submit"] {
        width: 100%;
        padding: 10px;
    }
}

</style>