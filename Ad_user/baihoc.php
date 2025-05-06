<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();
$message = isset($_GET['message']) ? urldecode($_GET['message']) : "";

// Lấy ten_khoa từ GET
$ten_khoa = isset($_GET['ten_khoa']) ? trim(urldecode($_GET['ten_khoa'])) : '';

// Kiểm tra ten_khoa hợp lệ
if (empty($ten_khoa)) {
    echo "<p>Tên môn học không hợp lệ.</p>";
    exit;
}

// Xử lý xóa câu hỏi
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM quiz WHERE Id_cauhoi = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = $stmt->affected_rows > 0 ? "Xóa câu hỏi thành công!" : "Câu hỏi không tồn tại!";
    } else {
        $message = "Lỗi khi xóa: " . $stmt->error;
    }
    $stmt->close();
    header("Location: baitest.php?ten_khoa=" . urlencode($ten_khoa) . "&message=" . urlencode($message));
    exit;
}

// Xử lý sửa câu hỏi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_question"])) {
    $id = (int)$_POST["id"];
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

    // Kiểm tra dữ liệu
    if (empty($id_baitest) || empty($ten_khoa) || empty($question_text) || 
        empty($choices['A']) || empty($choices['B']) || empty($choices['C']) || empty($choices['D']) || 
        empty($explanations['A']) || empty($explanations['B']) || empty($explanations['C']) || empty($explanations['D']) || 
        empty($correct)) {
        $message = "Vui lòng điền đầy đủ thông tin!";
    } elseif (!in_array($id_baitest, ['Giữa kỳ', 'Cuối kỳ'])) {
        $message = "Loại bài test phải là Giữa kỳ hoặc Cuối kỳ!";
    } elseif (!in_array($correct, ['A', 'B', 'C', 'D'])) {
        $message = "Đáp án đúng phải là A, B, C hoặc D!";
    } else {
        // Xử lý hình ảnh
        $image_path = $_POST["current_image"];
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            if (!in_array($_FILES["image"]["type"], $allowed_types)) {
                $message = "Chỉ hỗ trợ định dạng JPG, PNG, GIF!";
            } elseif ($_FILES["image"]["size"] > $max_size) {
                $message = "Hình ảnh không được vượt quá 5MB!";
            } else {
                $image_path = 'uploads/' . time() . '_' . basename($_FILES["image"]["name"]);
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                    $message = "Lỗi khi tải hình ảnh!";
                    $image_path = $_POST["current_image"];
                }
            }
        }

        if (empty($message)) {
            // Cập nhật câu hỏi
            $sql = "UPDATE quiz SET id_baitest = ?, ten_khoa = ?, cauhoi = ?, hinhanh = ?, 
                    cau_a = ?, giaithich_a = ?, cau_b = ?, giaithich_b = ?, 
                    cau_c = ?, giaithich_c = ?, cau_d = ?, giaithich_d = ?, dap_an = ? 
                    WHERE Id_cauhoi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssssi", $id_baitest, $ten_khoa, $question_text, $image_path,
                $choices['A'], $explanations['A'], $choices['B'], $explanations['B'],
                $choices['C'], $explanations['C'], $choices['D'], $explanations['D'], $correct, $id);

            if ($stmt->execute()) {
                $message = "Cập nhật câu hỏi thành công!";
            } else {
                $message = "Lỗi khi cập nhật: " . $stmt->error;
            }
            $stmt->close();
            header("Location: baitest.php?ten_khoa=" . urlencode($ten_khoa) . "&message=" . urlencode($message));
            exit;
        }
    }
}

// Lấy tất cả câu hỏi thuộc ten_khoa
$cau_hoi = [];
$stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ?");
$stmt->bind_param("s", $ten_khoa);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cau_hoi[] = $row;
}
$stmt->close();

// Lấy thông tin câu hỏi để sửa (khi nhấn nút Sửa)
$edit_cauhoi = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE Id_cauhoi = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_cauhoi = $result->fetch_assoc();
    }
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

        td img {
            max-width: 80px;
            height: auto;
            border-radius: 4px;
            display: block;
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

            td img {
                max-width: 60px;
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
        <h2>Quản lý câu hỏi - Môn: <?= htmlspecialchars($ten_khoa) ?></h2>
        <a href="add_khoahoc.php" class="back-link"><i class="fas fa-arrow-left"></i>Quay lại danh sách môn học</a>
        <?php if (!empty($message)): ?>
            <div class="<?= strpos($message, 'thành công') !== false ? 'success-message' : 'error-message' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <a href="add_question.php" class="add-question"><i class="fas fa-plus"></i>Thêm câu hỏi mới</a>

        <h3>Danh sách câu hỏi</h3>
        <?php if (empty($cau_hoi)): ?>
            <p class="empty">Chưa có câu hỏi nào cho môn học này.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khoá học</th>
                    <th>Câu hỏi</th>
                    <th>Thao tác</th>
                    <th>Bài kiểm tra </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cau_hoi as $ch): ?>
                <tr>
                    <td><?= $ch['Id_cauhoi'] ?></td>
                    <td><?= htmlspecialchars($ch['id_baitest']) ?></td>
                    <td><?= htmlspecialchars($ch['cauhoi']) ?></td>
                    <td>
                        <a class="btn view" href="question.php"><i class="fas fa-eye"></i>Xem</a>
                        <a class="btn edit" href="?ten_khoa=<?= urlencode($ten_khoa) ?>&edit=<?= $ch['Id_cauhoi'] ?>" onclick="openModal(<?= $ch['Id_cauhoi'] ?>)"><i class="fas fa-edit"></i>Sửa</a>
                        <a class="btn delete" href="?ten_khoa=<?= urlencode($ten_khoa) ?>&delete=<?= $ch['Id_cauhoi'] ?>" onclick="return confirm('Xác nhận xóa câu hỏi?')"><i class="fas fa-trash"></i>Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Modal sửa câu hỏi -->
        <?php if ($edit_cauhoi): ?>
        <div class="modal" id="editModal-<?= $edit_cauhoi['Id_cauhoi'] ?>">
            <div class="modal-content">
                <span class="close" onclick="closeModal(<?= $edit_cauhoi['Id_cauhoi'] ?>)">&times;</span>
                <h3>Sửa câu hỏi</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit_cauhoi['Id_cauhoi'] ?>">
                    <input type="hidden" name="current_image" value="<?= htmlspecialchars($edit_cauhoi['hinhanh']) ?>">
                    <label for="id_baitest">Loại bài test:</label>
                    <select id="id_baitest" name="id_baitest" required>
                        <option value="Giữa kỳ" <?= $edit_cauhoi['id_baitest'] === 'Giữa kỳ' ? 'selected' : '' ?>>Giữa kỳ</option>
                        <option value="Cuối kỳ" <?= $edit_cauhoi['id_baitest'] === 'Cuối kỳ' ? 'selected' : '' ?>>Cuối kỳ</option>
                    </select>
                    <label for="ten_khoa">Tên môn học:</label>
                    <input type="text" id="ten_khoa" name="ten_khoa" value="<?= htmlspecialchars($edit_cauhoi['ten_khoa']) ?>" required>
                    <label for="question_text">Câu hỏi:</label>
                    <textarea id="question_text" name="question_text" required><?= htmlspecialchars($edit_cauhoi['cauhoi']) ?></textarea>
                    <label for="choice_a">Đáp án A:</label>
                    <input type="text" id="choice_a" name="choice_a" value="<?= htmlspecialchars($edit_cauhoi['cau_a']) ?>" required>
                    <label for="explain_a">Giải thích A:</label>
                    <textarea id="explain_a" name="explain_a" required><?= htmlspecialchars($edit_cauhoi['giaithich_a']) ?></textarea>
                    <label for="choice_b">Đáp án B:</label>
                    <input type="text" id="choice_b" name="choice_b" value="<?= htmlspecialchars($edit_cauhoi['cau_b']) ?>" required>
                    <label for="explain_b">Giải thích B:</label>
                    <textarea id="explain_b" name="explain_b" required><?= htmlspecialchars($edit_cauhoi['giaithich_b']) ?></textarea>
                    <label for="choice_c">Đáp án C:</label>
                    <input type="text" id="choice_c" name="choice_c" value="<?= htmlspecialchars($edit_cauhoi['cau_c']) ?>" required>
                    <label for="explain_c">Giải thích C:</label>
                    <textarea id="explain_c" name="explain_c" required><?= htmlspecialchars($edit_cauhoi['giaithich_c']) ?></textarea>
                    <label for="choice_d">Đáp án D:</label>
                    <input type="text" id="choice_d" name="choice_d" value="<?= htmlspecialchars($edit_cauhoi['cau_d']) ?>" required>
                    <label for="explain_d">Giải thích D:</label>
                    <textarea id="explain_d" name="explain_d" required><?= htmlspecialchars($edit_cauhoi['giaithich_d']) ?></textarea>
                    <label for="correct">Đáp án đúng (A, B, C, D):</label>
                    <input type="text" id="correct" name="correct" value="<?= htmlspecialchars($edit_cauhoi['dap_an']) ?>" required>
                    <label for="image">Hình ảnh (nếu có):</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
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

        // Tự động mở modal nếu có edit
        <?php if ($edit_cauhoi): ?>
            openModal(<?= $edit_cauhoi['Id_cauhoi'] ?>);
        <?php endif; ?>
    </script>
</body>
</html>