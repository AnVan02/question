
<?php
// Database connection
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}

// Initialize variables
$conn = dbconnect();
$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
$ten_khoa = isset($_GET['ten_khoa']) ? trim(urldecode($_GET['ten_khoa'])) : '';
$cau_hoi = [];
$edit_cauhoi = null;

// Validate ten_khoa
if (empty($ten_khoa)) {
    $message = "<div class='error-message'>Tên môn học không hợp lệ.</div>";
    $ten_khoa = '';
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM quiz WHERE Id_cauhoi = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = $stmt->affected_rows > 0
            ? "<div class='success-message'>Xóa câu hỏi thành công!</div>"
            : "<div class='error-message'>Câu hỏi không tồn tại!</div>";
    } else {
        $message = "<div class='error-message'>Lỗi khi xóa: " . $stmt->error . "</div>";
    }
    $stmt->close();
    header("Location: ?ten_khoa=" . urlencode($ten_khoa));
    exit;
}

// Handle AJAX update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $stmt = $conn->prepare("UPDATE quiz SET cauhoi=?, cau_a=?, cau_b=?, cau_c=?, cau_d=?, dap_an=? WHERE Id_cauhoi=?");
    $stmt->bind_param("ssssssi", $_POST['cauhoi'], $_POST['cau_a'], $_POST['cau_b'], $_POST['cau_c'], $_POST['cau_d'], $_POST['dap_an'], $_POST['update_id']);
    echo $stmt->execute() ? "success" : "error";
    $stmt->close();
    exit;
}

// Handle form-based update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $id = (int)$_POST['id'];
    $id_baitest = trim($_POST['id_baitest']);
    $ten_khoa = trim($_POST['ten_khoa']);
    $question_text = trim($_POST['question_text']);
    $choices = [
        'A' => trim($_POST['choice_a']),
        'B' => trim($_POST['choice_b']),
        'C' => trim($_POST['choice_c']),
        'D' => trim($_POST['choice_d'])
    ];
    $explanations = [
        'A' => trim($_POST['explain_a']),
        'B' => trim($_POST['explain_b']),
        'C' => trim($_POST['explain_c']),
        'D' => trim($_POST['explain_d'])
    ];
    $correct = strtoupper(trim($_POST['correct']));
    $image_path = $_POST['current_image'];

    // Validate inputs
    if (empty($id_baitest) || empty($ten_khoa) || empty($question_text) ||
        empty($choices['A']) || empty($choices['B']) || empty($choices['C']) || empty($choices['D']) ||
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D']) ||
        empty($correct)) {
        $message = "<div class='error-message'>Vui lòng điền đầy đủ thông tin!</div>";
    } elseif (!in_array($id_baitest, ['Giữa kỳ', 'Cuối kỳ'])) {
        $message = "<div class='error-message'>Loại bài test phải là Giữa kỳ hoặc Cuối kỳ!</div>";
    } elseif (!in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "<div class='error-message'>Đáp án đúng phải là A, B, C hoặc D!</div>";
    } else {
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                $message = "<div class='error-message'>Chỉ hỗ trợ định dạng JPG, PNG, GIF!</div>";
            } elseif ($_FILES['image']['size'] > $max_size) {
                $message = "<div class='error-message'>Hình ảnh không được vượt quá 5MB!</div>";
            } else {
                $image_path = 'uploads/' . time() . '_' . basename($_FILES['image']['name']);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    $message = "<div class='error-message'>Lỗi khi tải hình ảnh!</div>";
                    $image_path = $_POST['current_image'];
                }
            }
        }

        if (empty($message)) {
            $sql = "UPDATE quiz SET id_baitest=?, ten_khoa=?, cauhoi=?, hinhanh=?, 
                    cau_a=?, giaithich_a=?, cau_b=?, giaithich_b=?, 
                    cau_c=?, giaithich_c=?, cau_d=?, giaithich_d=?, dap_an=? 
                    WHERE Id_cauhoi=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssssi", $id_baitest, $ten_khoa, $question_text, $image_path,
                $choices['A'], $explanations['A'], $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'], $choices['D'], $explanations['D'], $correct, $id);

            if ($stmt->execute()) {
                $message = "<div class='success-message'>Cập nhật câu hỏi thành công!</div>";
            } else {
                $message = "<div class='error-message'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
            }
            $stmt->close();
            header("Location: ?ten_khoa=" . urlencode($ten_khoa));
            exit;
        }
    }
}

// Fetch questions for the course
if ($ten_khoa) {
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ?");
    $stmt->bind_param("s", $ten_khoa);
    $stmt->execute();
    $cau_hoi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch question for editing
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE Id_cauhoi = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_cauhoi = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách câu hỏi - <?= htmlspecialchars($ten_khoa) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #e6f3fa, #f4f4f9);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1450px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h2 {
            color: #1a3c34;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
        }

        h3 {
            color: #1a3c34;
            font-size: 1.5rem;
            text-align: center;
            margin: 30px 0 20px;
            font-weight: 600;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #00796b;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }

        .back-link i {
            margin-right: 8px;
        }

        .back-link:hover {
            color: #004d40;
        }

        .success-message, .error-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .success-message {
            background: #e6f8e6;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        .error-message {
            background: #ffeaea;
            color: #d9534f;
            border-left: 4px solid #c62828;
        }

        .add-question {
            display: inline-flex;
            align-items: center;
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease, transform 0.2s ease;
            margin: 10px 0;
        }

        .add-question i {
            margin-right: 8px;
        }

        .add-question:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }

        th {
            background: #e3f2fd;
            color: #1a3c34;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #f1f8ff;
        }

        .question-details {
            max-width: 600px;
        }

        .question-details p {
            margin: 5px 0;
        }

        .question-details img {
            max-width: 150px;
            height: auto;
            border-radius: 4px;
            margin-top: 10px;
        }

        .correct-answer {
            color: #2e7d32;
            font-weight: bold;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin-right: 8px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn.view {
            background: #007bff;
            color: white;
        }

        .btn.view:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn.edit {
            background: #ffc107;
            color: #333;
        }

        .btn.edit:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }

        .btn.delete {
            background: #dc3545;
            color: white;
        }

        .btn.delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .btn i {
            margin-right: 6px;
        }

        .empty {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 8px;
            margin-top: 20px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-content h3 {
            margin-top: 0;
            font-size: 1.5rem;
            color: #1a3c34;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
        }

        .modal-content form label {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
            display: block;
            font-weight: 600;
        }

        .modal-content form select,
        .modal-content form input[type="text"],
        .modal-content form textarea,
        .modal-content form input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 6px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal-content form textarea {
            height: 60px;
            resize: vertical;
        }

        .modal-content form button {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }

        .modal-content form button:hover {
            background: #45a049;
        }

        .modal-content img {
            max-width: 100px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 1.6rem;
            }

            h3 {
                font-size: 1.3rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            th, td {
                padding: 10px;
                font-size: 0.85rem;
            }

            .question-details img {
                max-width: 100px;
            }

            .btn {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .add-question {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .modal-content {
                width: 95%;
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.4rem;
            }

            h3 {
                font-size: 1.1rem;
            }

            .back-link, .add-question {
                font-size: 0.85rem;
            }

            .question-details img {
                max-width: 80px;
            }

            .modal-content form label,
            .modal-content form select,
            .modal-content form input,
            .modal-content form textarea,
            .modal-content form button {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h3>Test Câu Hỏi Môn: <?= htmlspecialchars($ten_khoa) ?></h3>
    <a class="btn view" href="add_khoahoc.php"><i class="fas fa-arrow-left"></i> Quay lại danh sách môn học</a><br><br>
    <?php if (!empty($message)): ?>
        <div class="<?= strpos($message, 'thành công') !== false ? 'success-message' : 'error-message' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <a href ="add_question.php "class="add-question"><i class="fas fa-plus"></i>Thêm câu hỏi mới </a>
        <?php iF(empty ($cau_hoi)):?>
            <p class ="empty">Chưa có câu hỏi nào cho môn học này </p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Bai Test</th>
                    <th>Câu hỏi</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cau_hoi as $ch): ?>
                    <tr>
                        <td><?= $ch ['Id_cauhoi']?></td>
                        <td><?= htmlspecialchars ($ch ['id_baitest']) ?></td>
                        <td><?= htmlspecialchars($ch['cauhoi']) ?></td>
                        <td>
                            <a class="btn edit" href="?ten_khoa=<?= urlencode($ten_khoa) ?>&id_baitest=<?= urlencode($id_baitest) ?>&edit=<?= $ch['Id_cauhoi'] ?>"><i class="fas fa-edit"></i> Sửa</a>
                            <a class="btn delete" href="?ten_khoa=<?= urlencode($ten_khoa) ?>&id_baitest=<?= urlencode($id_baitest) ?>&delete=<?= $ch['Id_cauhoi'] ?>" onclick="return confirm('Xác nhận xóa câu hỏi?')"><i class="fas fa-trash"></i> Xóa</a>
                            <a class="btn view" href="question.php?id=<?= $ch['Id_cauhoi'] ?>"><i class="fas fa-eye"></i> Xem</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Edit Modal -->
    <?php if ($edit_cauhoi): ?>
        <div class="modal" id="editModal-<?= $edit_cauhoi['Id_cauhoi'] ?>" style="display: flex;">
            <div class="modal-content">
                <span class="close" onclick="closeModal(<?= $edit_cauhoi['Id_cauhoi'] ?>)">×</span>
                <h3>Sửa câu hỏi</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit_cauhoi['Id_cauhoi'] ?>">
                    <input type="hidden" name="current_image" value="<?= htmlspecialchars($edit_cauhoi['hinhanh']) ?>">
                    <label>Bài test:</label>
                    <select name="id_baitest" required>
                        <option value="Giữa kỳ" <?= $edit_cauhoi['id_baitest'] === 'Giữa kỳ' ? 'selected' : '' ?>>Giữa kỳ</option>
                        <option value="Cuối kỳ" <?= $edit_cauhoi['id_baitest'] === 'Cuối kỳ' ? 'selected' : '' ?>>Cuối kỳ</option>
                    </select>
                    <label>Tên môn học:</label>
                    <input type="text" name="ten_khoa" value="<?= htmlspecialchars($edit_cauhoi['ten_khoa']) ?>" required>
                    <label>Câu hỏi:</label>
                    <textarea name="question_text" required><?= htmlspecialchars($edit_cauhoi['cauhoi']) ?></textarea>
                    <label>Đáp án A:</label>
                    <input type="text" name="choice_a" value="<?= htmlspecialchars($edit_cauhoi['cau_a']) ?>" required>
                    <label>Giải thích A:</label>
                    <textarea name="explain_a" required><?= htmlspecialchars($edit_cauhoi['giaithich_a']) ?></textarea>
                    <label>Đáp án B:</label>
                    <input type="text" name="choice_b" value="<?= htmlspecialchars($edit_cauhoi['cau_b']) ?>" required>
                    <label>Giải thích B:</label>
                    <textarea name="explain_b" required><?= htmlspecialchars($edit_cauhoi['giaithich_b']) ?></textarea>
                    <label>Đáp án C:</label>
                    <input type="text" name="choice_c" value="<?= htmlspecialchars($edit_cauhoi['cau_c']) ?>" required>
                    <label>Giải thích C:</label>
                    <textarea name="explain_c" required><?= htmlspecialchars($edit_cauhoi['giaithich_c']) ?></textarea>
                    <label>Đáp án D:</label>
                    <input type="text" name="choice_d" value="<?= htmlspecialchars($edit_cauhoi['cau_d']) ?>" required>
                    <label>Giải thích D:</label>
                    <textarea name="explain_d" required><?= htmlspecialchars($edit_cauhoi['giaithich_d']) ?></textarea>
                    <label>Đáp án đúng (A, B, C, D):</label>
                    <input type="text" name="correct" value="<?= htmlspecialchars($edit_cauhoi['dap_an']) ?>" required>
                    <label>Hình ảnh (nếu có):</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif">
                    <?php if ($edit_cauhoi['hinhanh']): ?>
                        <p>Hình ảnh hiện tại: <img src="<?= htmlspecialchars($edit_cauhoi['hinhanh']) ?>" alt="Hình ảnh câu hỏi"></p>
                    <?php endif; ?>
                    <button type="submit" name="update_question">Cập nhật câu hỏi</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function openModal(id) {
        document.getElementById('editModal-' + id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById('editModal-' + id).style.display = 'none';
    }
</script>
</body>
</html>