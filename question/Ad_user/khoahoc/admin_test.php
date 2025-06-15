<?php
session_start();
require_once "cauhoi.php";

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Initialize message
$message = "";

// Create images directory if it doesn't exist
$upload_dir = "images/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// lấy dữ liệu câu hỏi đế chỉnh sửa 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_question"])) {
    $question_text = trim($_POST["question_text"]);
    $choices = [
        'A' => trim($_POST["choice_a"]),
        'B' => trim($_POST["choice_b"]),
        'C' => trim($_POST["choice_c"]),
        'D' => trim($_POST["choice_d"])
    ];
    $correct = $_POST["correct"];
    $explanation = trim($_POST["explanation"]);
    $question_id = isset($_POST["question_id"]) ? $_POST["question_id"] : null;
    $image = "";

    // Upload hình ảnh
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/png', 'image/jpeg', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_type = mime_content_type($_FILES["image"]["tmp_name"]);
        $file_size = $_FILES["image"]["size"];
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $file_name = uniqid() . "." . $file_ext; // Unique filename to avoid conflicts
        $file_path = $upload_dir . $file_name;

        if (!in_array($file_type, $allowed_types)) {
            $message = "<div style='color: red;'>Chỉ cho phép tải lên file PNG, JPG hoặc GIF!</div>";
        } elseif ($file_size > $max_size) {
            $message = "<div style='color: red;'>Kích thước file không được vượt quá 2MB!</div>";
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $file_path)) {
            $image = $file_path; // Store relative path
        } else {
            $message = "<div style='color: red;'>Lỗi khi tải lên hình ảnh!</div>";
        }
    } elseif (isset($_POST["existing_image"])) {
        $image = $_POST["existing_image"]; // Keep existing image if no new upload
    }

    // Validate input
    if (empty($message) && (empty($question_text) || empty($choices['A']) || empty($choices['B']) || empty($choices['C']) || empty($choices['D']) || empty($correct) || empty($explanation))) {
        $message = "<div style='color: red;'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (empty($message) && !in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "<div style='color: red;'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } else {
        // Prepare question data
        $question_data = [
            "question" => $question_text,
            "image" => $image,
            "choices" => $choices,
            "correct" => $correct,
            "explanation" => $explanation
        ];

        // Update or add question
        if ($question_id && isset($questions[$question_id])) {
            $questions[$question_id] = $question_data;
            $message = "<div style='color: green;'>Câu hỏi đã được cập nhật!</div>";
        } else {
            $new_id = (count($questions) > 0 ? max(array_keys($questions)) + 1 : 1);
            $questions[$new_id] = $question_data;
            $message = "<div style='color: green;'>Câu hỏi đã được thêm!</div>";
        }

        // Save questions to cauhoi.php
        $questions_array_string = "<?php\n\$questions = " . var_export($questions, true) . ";\n?>";
        file_put_contents("cauhoi.php", $questions_array_string);
    }
}

// Handle delete request
if (isset($_GET["delete"])) {
    $question_id = $_GET["delete"];
    if (isset($questions[$question_id])) {
        // Delete associated image if exists
        if (!empty($questions[$question_id]["image"]) && file_exists($questions[$question_id]["image"])) {
            unlink($questions[$question_id]["image"]);
        }
        unset($questions[$question_id]);
        $questions_array_string = "<?php\n\$questions = " . var_export($questions, true) . ";\n?>";
        file_put_contents("cauhoi.php", $questions_array_string);
        $message = "<div style='color: green;'>Câu hỏi đã được xóa!</div>";
    }
}

// Load question for editing
$edit_question = null;
if (isset($_GET["edit"])) {
    $question_id = $_GET["edit"];
    if (isset($questions[$question_id])) {
        $edit_question = $questions[$question_id];
        $edit_question['id'] = $question_id;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý câu hỏi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="logout.php" class="btn btn-cancel">Đăng xuất</a>

    <div class="container">
        <h1>Quản lý câu hỏi</h1>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <!-- Form for adding/editing questions -->
        <div class="form-section">
            <h2><?= $edit_question ? "Chỉnh sửa câu hỏi" : "Thêm câu hỏi mới" ?></h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="question_id" value="<?= $edit_question ? htmlspecialchars($edit_question['id']) : '' ?>">
                <input type="hidden" name="existing_image" value="<?= $edit_question ? htmlspecialchars($edit_question['image']) : '' ?>">
                
                <div class="form-group">
                    <label for="question_text">Câu hỏi</label>
                    <textarea name="question_text" id="question_text" required><?= $edit_question ? htmlspecialchars($edit_question['question']) : '' ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Hình ảnh (tùy chọn)</label>
                    <?php if ($edit_question && !empty($edit_question['image'])): ?>
                        <p>Hình ảnh hiện tại: <img src="<?= htmlspecialchars($edit_question['image']) ?>" width="100" alt="Hình ảnh câu hỏi"></p>
                    <?php endif; ?>
                    <input type="file" name="image" id="image" accept="image/png,image/jpeg,image/gif">
                </div>

                <div class="form-group">
                    <label for="choice_a">Đáp án A</label>
                    <input type="text" name="choice_a" id="choice_a" required value="<?= $edit_question ? htmlspecialchars($edit_question['choices']['A']) : '' ?>">
                    <label for="choice_a">Giải thích</label>
                    <textarea name="explanation_a" id="explanation_a" required><?= $edit_question ? htmlspecialchars($edit_question['explanation_a']) : '' ?></textarea>
                </div>

                <div class="form-group">
                    <label for="choice_b">Đáp án B</label>
                    <input type="text" name="choice_b" id="choice_b" required value="<?= $edit_question ? htmlspecialchars($edit_question['choices']['B']) : '' ?>">
                    <label for="choice_a">Giải thích</label>
                    <textarea name="explanation_b" id="explanation_b" required><?= $edit_question ? htmlspecialchars($edit_question['explanation_b']) : '' ?></textarea>
                </div>

                <div class="form-group">
                    <label for="choice_c">Đáp án C</label>
                    <input type="text" name="choice_c" id="choice_c" required value="<?= $edit_question ? htmlspecialchars($edit_question['choices']['C']) : '' ?>">
                    <label for="choice_a">Giải thích</label>
                    <textarea name="explanation_c" id="explanation_c" required><?= $edit_question ? htmlspecialchars($edit_question['explanation_c']) : '' ?></textarea>
                </div>

                <div class="form-group">
                    <label for="choice_d">Đáp án D</label>
                    <input type="text" name="choice_d" id="choice_d" required value="<?= $edit_question ? htmlspecialchars($edit_question['choices']['D']) : '' ?>">
                    <label for="choice_a">Giải thích</label>
                    <textarea name="explanation_d" id="explanation_d" required><?= $edit_question ? htmlspecialchars($edit_question['explanation_d']) : '' ?></textarea>
                </div>

                <div class="form-group">
                    <label for="correct">Đáp án đúng</label>
                    <select name="correct" id="correct" required>
                        <option value="A" <?= $edit_question && $edit_question['correct'] === 'A' ? 'selected' : '' ?>>A</option>
                        <option value="B" <?= $edit_question && $edit_question['correct'] === 'B' ? 'selected' : '' ?>>B</option>
                        <option value="C" <?= $edit_question && $edit_question['correct'] === 'C' ? 'selected' : '' ?>>C</option>
                        <option value="D" <?= $edit_question && $edit_question['correct'] === 'D' ? 'selected' : '' ?>>D</option>
                    </select>
                </div>

                <!-- <div class="form-group">
                    <label for="explanation">Giải thích</label>
                    <textarea name="explanation" id="explanation" required><?= $edit_question ? htmlspecialchars($edit_question['explanation']) : '' ?></textarea>
                </div> -->

                <div class="form-group">
                    <button type="submit" name="save_question" class="btn btn-save">Lưu</button>
                    <?php if ($edit_question): ?>
                        <a href="admin.php" class="btn btn-cancel">Hủy</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- List of existing questions -->
        <div class="questions-section">
            <h2>Danh sách câu hỏi</h2>
            <?php if (empty($questions)): ?>
                <p>Không có câu hỏi nào.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Câu hỏi</th>
                            <th>Hình ảnh</th>
                            <th>Đáp án</th>
                            <th>Đáp án đúng</th>
                            <th>Giải thích</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $id => $q): ?>
                            <tr>
                                <td><?= $id ?></td>
                                <td><?= htmlspecialchars($q['question']) ?></td>
                                <td><?= $q['image'] ? '<img src="' . htmlspecialchars($q['image']) . '" width="50">' : 'Không' ?></td>
                                <td>
                                    A: <?= htmlspecialchars($q['choices']['A']) ?><br>
                                    B: <?= htmlspecialchars($q['choices']['B']) ?><br>
                                    C: <?= htmlspecialchars($q['choices']['C']) ?><br>
                                    D: <?= htmlspecialchars($q['choices']['D']) ?>
                                </td>
                                <td><?= $q['correct'] ?></td>
                                <td><?= htmlspecialchars($q['explanation']) ?></td>
                                <td class="action-links">
                                    <a href="admin.php?edit=<?= $id ?>">Sửa</a>
                                    <a href="admin.php?delete=<?= $id ?>" onclick="return confirm('Bạn có chắc muốn xóa câu hỏi này?')">Xóa</a>
                                </td>
                                    A: <?= htmlspecialchars($q['explanation_a'][A]) ?><br>
                                    B: <?= htmlspecialchars($q['explanation_b'][B]) ?><br>
                                    C: <?= htmlspecialchars($q['explanation_c'][C]) ?><br>
                                    D: <?= htmlspecialchars($q['explanation_d'][D]) ?><br>
                                <td>
                                    A: <?= htmlspecialchars($q['choice']['A']) ?><br>
                                    B: <?= htmlspecialchars($q['choice']['B']) ?><br>
                                    C: <?= htmlspecialchars($q['choice']['C']) ?><br>
                                    D: <?= htmlspecialchars($q['choice']['D']) ?><br>
                                </td>
                                
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<Style>
    /* Basic Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fa;
    color: #333;
}

/* Container */
.container {
    width: 80%;
    margin: 20px auto;
    padding: 20px;
    background-color: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Header */
h1 {
    font-size: 24px;
    color: #4CAF50;
    margin-bottom: 20px;
}

h2 {
    font-size: 20px;
    color: #333;
    margin-bottom: 15px;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s ease;
}

.btn-cancel {
    background-color: #f44336;
    color: white;
}

.btn-save {
    background-color: #4CAF50;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}

/* Form styling */
.form-group {
    margin-bottom: 20px;
}

label {
    font-size: 14px;
    color: #333;
    display: block;
    margin-bottom: 5px;
}

input[type="text"],
textarea,
select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background-color: #f9f9f9;
    color: #333;
}

textarea {
    resize: vertical;
    height: 120px;
}

input[type="file"] {
    background-color: #f9f9f9;
}

/* Messages */
.message {
    padding: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    border-radius: 4px;
    display: inline-block;
}

.message div {
    margin: 0;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th,
table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #f4f4f4;
    color: #333;
}

table td img {
    border-radius: 4px;
}

.action-links a {
    margin-right: 10px;
    text-decoration: none;
    color: #4CAF50;
}

.action-links a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        width: 95%;
    }

    table th,
    table td {
        font-size: 12px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label,
    .form-group input,
    .form-group select {
        font-size: 12px;
    }
}

</Style>