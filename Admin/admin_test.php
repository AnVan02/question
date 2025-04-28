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

// Handle form submission for adding or editing questions
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
    $difficulty = $_POST["difficulty"];
    $question_id = isset($_POST["question_id"]) ? $_POST["question_id"] : null;
    $image = "";

    // Handle image upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/png', 'image/jpeg', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_type = mime_content_type($_FILES["image"]["tmp_name"]);
        $file_size = $_FILES["image"]["size"];
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $file_name = uniqid() . "." . $file_ext;
        $file_path = $upload_dir . $file_name;

        if (!in_array($file_type, $allowed_types)) {
            $message = "<div style='color: red;'>Chỉ cho phép tải lên file PNG, JPG hoặc GIF!</div>";
        } elseif ($file_size > $max_size) {
            $message = "<div style='color: red;'>Kích thước file không được vượt quá 2MB!</div>";
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $file_path)) {
            $image = $file_path;
        } else {
            $message = "<div style='color: red;'>Lỗi khi tải lên hình ảnh!</div>";
        }
    } elseif (isset($_POST["existing_image"])) {
        $image = $_POST["existing_image"];
    }

    // Validate input
    if (empty($message) && (empty($question_text) || empty($choices['A']) || empty($choices['B']) || empty($choices['C']) || empty($choices['D']) || empty($correct) || empty($explanation) || empty($difficulty))) {
        $message = "<div style='color: red;'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (empty($message) && !in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "<div style='color: red;'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } elseif (empty($message) && !in_array($difficulty, ['easy', 'medium', 'hard'])) {
        $message = "<div style='color: red;'>Mức độ phải là Dễ, Trung bình hoặc Khó!</div>";
    } else {
        // Prepare question data
        $question_data = [
            "question" => $question_text,
            "image" => $image,
            "choices" => $choices,
            "correct" => $correct,
            "explanation" => $explanation,
            "difficulty" => $difficulty
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
    <div class="container">
        <h1>Quản lý câu hỏi</h1>
        <a href="logout.php" class="btn btn-cancel">Đăng xuất</a>

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
                </div>

                <div class="form-group">
                    <label for="choice_b">Đáp án B</label>
                    <input type="text" name="choice_b" id="choice_b" required value="<?= $edit_question ? htmlspecialchars($edit_question['choices']['B']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="choice_c">Đáp án C</label>
                    <input type="text" name="choice_c" id="choice_c" required value="<?= $edit_question ? htmlspecialchars($edit_question['choices']['C']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="choice_d">Đáp án D</label>
                    <input type="text" name="choice_d" id="choice_d" required value="<?= $edit_question ? htmlspecialchars($edit_question['choices']['D']) : '' ?>">
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

                <div class="form-group">
                    <label for="difficulty">Mức độ</label>
                    <select name="difficulty" id="difficulty" required>
                        <option value="easy" <?= $edit_question && $edit_question['difficulty'] === 'easy' ? 'selected' : '' ?>>Dễ</option>
                        <option value="medium" <?= $edit_question && $edit_question['difficulty'] === 'medium' ? 'selected' : '' ?>>Trung bình</option>
                        <option value="hard" <?= $edit_question && $edit_question['difficulty'] === 'hard' ? 'selected' : '' ?>>Khó</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="explanation">Giải thích</label>
                    <textarea name="explanation" id="explanation" required><?= $edit_question ? htmlspecialchars($edit_question['explanation']) : '' ?></textarea>
                </div>

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
                            <th>Mức độ</th>
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
                                <td><?= $q['difficulty'] === 'easy' ? 'Dễ' : ($q['difficulty'] === 'medium' ? 'Trung bình' : 'Khó') ?></td>
                                <td><?= htmlspecialchars($q['explanation']) ?></td>
                                <td class="action-links">
                                    <a href="admin.php?edit=<?= $id ?>">Sửa</a>
                                    <a href="admin.php?delete=<?= $id ?>" onclick="return confirm('Bạn có chắc muốn xóa câu hỏi này?')">Xóa</a>
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