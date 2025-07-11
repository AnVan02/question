<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

$id_test = isset($_GET['id_test']) ? (int)$_GET['id_test'] : 0;
$message = "";

// Xử lý xóa
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id']) && $id_test > 0) {
    $delete_id = (int)$_GET['delete_id'];
    $conn = dbconnect();

    // Lấy thông tin test và khóa học
    $sql = "SELECT t.ten_test, t.id_test, k.khoa_hoc 
            FROM test t 
            LEFT JOIN khoa_hoc k ON t.id_khoa = k.id 
            WHERE t.id_test = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_test);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $khoa_hoc = $row['khoa_hoc'];

        // Xóa hình nếu có
        $sql = "SELECT hinhanh FROM quiz WHERE Id_cauhoi = ? AND id_baitest = ? AND ten_khoa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $delete_id, $id_test, $khoa_hoc);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $img = $result->fetch_assoc()['hinhanh'];
            if (!empty($img) && file_exists($img)) {
                unlink($img);
            }
        }

        // Xóa câu hỏi
        $sql = "DELETE FROM quiz WHERE Id_cauhoi = ? AND id_baitest = ? AND ten_khoa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $delete_id, $id_test, $khoa_hoc);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Xóa câu hỏi thành công!</div>";
        } else {
            $message = "<div class='message error'>Lỗi khi xóa: " . $stmt->error . "</div>";
        }
    } else {
        $message = "<div class='message error'>Không tìm thấy bài kiểm tra.</div>";
    }

    $stmt->close();
    $conn->close();
}

// Lấy thông tin bài test và câu hỏi
$test_info = null;
$questions = [];
$khoa_hoc = null;

if ($id_test > 0) {
    $conn = dbconnect();
    $sql = "SELECT t.id_test, t.ten_test, t.id_khoa, k.khoa_hoc 
            FROM test t 
            LEFT JOIN khoa_hoc k ON t.id_khoa = k.id 
            WHERE t.id_test = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_test);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $test_info = $result->fetch_assoc();
        $khoa_hoc = $test_info['khoa_hoc'];
    }
    $stmt->close();

    // Lấy câu hỏi
    if ($test_info && $khoa_hoc) {
        $sql = "SELECT * FROM quiz WHERE id_baitest = ? AND ten_khoa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $test_info['id_test'], $khoa_hoc);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách câu hỏi</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f4f8;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            background: #edf2f7;
            padding: 10px;
            border-radius: 10px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #3182ce;
            color: #fff;
        }
        .btn {
            padding: 8px 12px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-add { background: #e53e3e; color: #fff; }
        .btn-edit { background: #bee3f8; color: #2b6cb0; }
        .btn-delete { background: #fed7d7; color: #c53030; }
        .btn-back { background: #3182ce; color: #fff; }
        .message.success { background: #c6f6d5; padding: 10px; margin: 10px 0; border-left: 5px solid #38a169; }
        .message.error { background: #fed7d7; padding: 10px; margin: 10px 0; border-left: 5px solid #e53e3e; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Danh sách câu hỏi: <?php echo htmlspecialchars($test_info['ten_test'] ?? ''); ?> - <?php echo htmlspecialchars($khoa_hoc ?? ''); ?></h2>
        <?php echo $message; ?>
        <a href="index.php?action=add_question&id_test=<?php echo $id_test; ?>" class="btn btn-add">Thêm câu hỏi</a>
        <a href="index.php?action=khoahoc&id_khoa=<?php echo $test_info['id_khoa'] ?? 0; ?>" class="btn btn-back">Quay lại danh sách bài test</a>

        <?php if (empty($questions)): ?>
            <p>Chưa có câu hỏi nào.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Câu hỏi</th>
                        <th>Đáp án</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td><?php echo $q['Id_cauhoi']; ?></td>
                            <td><?php echo htmlspecialchars($q['cauhoi']); ?></td>
                            <td><?php echo $q['dap_an']; ?></td>
                            <td>
                                <a href="index.php?action=add_question&question_id=<?php echo $q['Id_cauhoi']; ?>&id_test=<?php echo $id_test; ?>" class="btn btn-edit">Sửa</a>
                                <a href="question_list.php?id_test=<?php echo $id_test; ?>&delete_id=<?php echo $q['Id_cauhoi']; ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
