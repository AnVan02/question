<?php
// Giả sử bạn đã kết nối CSDL ở đây
include 'db_connection.php'; // nếu cần

if (isset($_POST['save_question'])) {
    // Lấy dữ liệu từ form
    $question = $_POST['question'] ?? '';
    $option_a = $_POST['option_a'] ?? '';
    $option_b = $_POST['option_b'] ?? '';
    $option_c = $_POST['option_c'] ?? '';
    $option_d = $_POST['option_d'] ?? '';
    $correct = $_POST['correct'] ?? '';

    // Lưu vào CSDL (ví dụ sử dụng MySQLi)
    $stmt = $conn->prepare("INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $question, $option_a, $option_b, $option_c, $option_d, $correct);
    $stmt->execute();
    $stmt->close();

    // Chuyển hướng sang trang danh sách câu hỏi
    header("Location: question.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thêm câu hỏi</title>
    <style>
        input, select, button {
            margin: 8px 0;
            padding: 8px;
            font-size: 16px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            color: white;
            cursor: pointer;
        }
        .btn-primary { background-color: #007bff; }
        .btn-secondary { background-color: #6c757d; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <h2>Thêm câu hỏi</h2>
    <form method="POST">
        Câu hỏi:<br>
        <input type="text" name="question" required><br>

        Đáp án A:<br>
        <input type="text" name="option_a" required><br>

        Đáp án B:<br>
        <input type="text" name="option_b" required><br>

        Đáp án C:<br>
        <input type="text" name="option_c" required><br>

        Đáp án D:<br>
        <input type="text" name="option_d" required><br>

        Đáp án đúng:<br>
        <select name="correct" required>
            <option value="">-- Chọn --</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select><br><br>

        <button type="submit" name="save_question" class="btn btn-primary">Lưu câu hỏi</button>
        <button type="button" onclick="window.location.href='question.php'" class="btn btn-secondary">Danh sách câu hỏi</button>
    </form>
</body>
</html>
