<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Lấy id_test từ URL
$id_test = isset($_GET['id_test']) ? (int)$_GET['id_test'] : 0;

// Khởi tạo biến thông báo
$message = "";

// Xử lý xóa câu hỏi
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id']) && $id_test > 0) {
    $delete_id = (int)$_GET['delete_id'];
    $conn = dbconnect();
    
    // Lấy ten_test và khoa_hoc để lọc câu hỏi
    $sql = "SELECT t.ten_test, k.khoa_hoc 
            FROM test t 
            LEFT JOIN khoa_hoc k ON t.id_khoa = k.id 
            WHERE t.id_test = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_test);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ten_test = $row['ten_test'];
        $khoa_hoc = $row['khoa_hoc'];
        
        // Kiểm tra và xóa hình ảnh nếu có
        $sql = "SELECT hinhanh FROM quiz WHERE Id_cauhoi = ? AND id_baitest = ? AND ten_khoa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $delete_id, $ten_test, $khoa_hoc);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['hinhanh']) && file_exists($row['hinhanh'])) {
                unlink($row['hinhanh']);
            }
        }
        
        $stmt->close();
        // Xóa câu hỏi
        $sql = "DELETE FROM quiz WHERE Id_cauhoi = ? AND id_baitest = ? AND ten_khoa = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $delete_id, $ten_test, $khoa_hoc);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Xóa câu hỏi thành công!</div>";
        } else {
            $message = "<div class='message error'>Lỗi khi xóa câu hỏi: " . $stmt->error . "</div>";
        }
    } else {
        $message = "<div class='message error'>Bài kiểm tra không tồn tại.</div>";
    }
    $stmt->close();
    $conn->close();
}

// Lấy thông tin bài kiểm tra và khóa học
$test_info = null;
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
    } else {
        $message = "<div class='message error'>Bài kiểm tra không tồn tại.</div>";
    }
    $stmt->close();
}

// Lấy danh sách câu hỏi theo ten_test và ten_khoa
$questions = [];
if ($id_test > 0 && $test_info && $khoa_hoc) {
    $conn = dbconnect();
    $sql = "SELECT Id_cauhoi, id_baitest, ten_khoa, cauhoi, hinhanh, cau_a, hinhanh_a, cau_b, hinhanh_b, cau_c, hinhanh_c, cau_d, hinhanh_d, dap_an 
            FROM quiz 
            WHERE id_baitest = ? AND ten_khoa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $test_info['ten_test'], $khoa_hoc);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
    }
    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách câu hỏi - <?php echo htmlspecialchars($test_info['ten_test'] ??''); ?> - <?php echo htmlspecialchars($khoa_hoc ?? ''); ?></title>
   
</head>
<body>
    <div class="container">
        <h2>Danh sách câu hỏi <?php echo htmlspecialchars($test_info['ten_test'] ?? ''); ?> 
            - <?php echo htmlspecialchars($khoa_hoc ?? 'Không xác định'); ?></h2>
        
        <?php if (!empty($message)) echo $message; ?>
        <a href="index.php?action=add_khoahoc" class="btn-back">Danh sách khóa học</a>
        <?php if ($test_info): ?>
            <a href="index.php?action=khoahoc&id_khoa=<?php echo htmlspecialchars($test_info['id_khoa']); ?>" class="btn-back">Quay lại danh sách bài test</a>
        <?php endif; ?>

        <?php if ($id_test > 0 && $test_info && $khoa_hoc): ?>
            <a href="index.php?action=add_question&id_test<?php echo htmlspecialchars($id_test); ?>" class="btn-add">Thêm câu hỏi mới</a>

            <?php if (empty($questions)): ?>
                <p>Chưa có câu hỏi nào cho bài kiểm tra này của môn học.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Câu hỏi</th>
                            <th>Câu hỏi</th>
                            <th>Hình ảnh</th>
                            <th>Hành động</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($question['Id_cauhoi']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($question['cauhoi']); ?>
                                    <ul class="answer-list">
                                        <li<?php if ($question['dap_an'] == 'A') echo ' class="correct"'; ?>>
                                            <span class="answer-label">A.</span>
                                            <span class="answer-text"><?php echo htmlspecialchars($question['cau_a']); ?></span>
                                            <?php if (!empty($question['hinhanh_a'])): ?>
                                                <br><img src="<?php echo htmlspecialchars($question['hinhanh_a']); ?>" alt="Hình A">
                                            <?php endif; ?>
                                        </li>
                                        <li<?php if ($question['dap_an'] == 'B') echo ' class="correct"'; ?>>
                                            <span class="answer-label">B.</span>
                                            <span class="answer-text"><?php echo htmlspecialchars($question['cau_b']); ?></span>
                                            <?php if (!empty($question['hinhanh_b'])): ?>
                                                <br><img src="<?php echo htmlspecialchars($question['hinhanh_b']); ?>" alt="Hình B">
                                            <?php endif; ?>
                                        </li>
                                        <li<?php if ($question['dap_an'] == 'C') echo ' class="correct"'; ?>>
                                            <span class="answer-label">C.</span>
                                            <span class="answer-text"><?php echo htmlspecialchars($question['cau_c']); ?></span>
                                            <?php if (!empty($question['hinhanh_c'])): ?>
                                                <br><img src="<?php echo htmlspecialchars($question['hinhanh_c']); ?>" alt="Hình C">
                                            <?php endif; ?>
                                        </li>
                                        <li<?php if ($question['dap_an'] == 'D') echo ' class="correct"'; ?>>
                                            <span class="answer-label">D.</span>
                                            <span class="answer-text"><?php echo htmlspecialchars($question['cau_d']); ?></span>
                                            <?php if (!empty($question['hinhanh_d'])): ?>
                                                <br><img src="<?php echo htmlspecialchars($question['hinhanh_d']); ?>" alt="Hình D">
                                            <?php endif; ?>
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                   
                                </td>
                                <td>
                                    <a href="index.php?action=add_question&question_id=<?php echo htmlspecialchars($question['Id_cauhoi']); ?>&id_test=<?php echo htmlspecialchars($id_test); ?>" class="btn-edit">Sửa</a>
                                    <a href="<?php echo htmlspecialchars('index.php?action=question&id_test=' . $id_test . '&delete_id=' . $question['Id_cauhoi']); ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    
                </table>
            <?php endif; ?>
        <?php else: ?>
            <p>Vui lòng chọn một bài kiểm tra hợp lệ.</p>
        <?php endif; ?>
    </div>
    <style>
       
        body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f4f8;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1250px;
    margin: 30px auto;
    background: #fff;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

h2 {
    margin-bottom: 25px;
    color: #2d3748;
    font-size: 26px;
    font-weight: 700;
    text-align: center;
    background-color: #edf2f7;
    padding: 15px;
    border-radius: 10px;
}

.message.success {
    color: #2f855a;
    background-color: #e6fff3;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: bold;
}

.message.error {
    color: #c53030;
    background-color: #fff5f5;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
}

th, td {
    padding: 12px 16px;
    border-bottom: 1px solid #ddd;
    font-size: 17px;
    vertical-align: top;
}

th {
    background-color: #3182ce;
    color: white;
    font-weight: 600;
    text-align: left;
}

tr:hover {
    background-color: #f5f5f5;
}

.btn-add,
.btn-edit,
.btn-delete,
.btn-back {
    display: inline-block;
    padding: 10px 14px;
    margin: 10px 5px 15px 0;
    text-decoration: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    transition: background-color 0.3s;
    cursor: pointer;
}

.btn-add {
    background-color: #e53e3e;
    color: #fff;
}

.btn-edit {
    background-color: #e3f2fd;
    color: #0288d1;
}

.btn-delete {
    background-color: #ffebee;
    color: #c62828;
}

.btn-back {
    background-color: #3182ce;
    color: #fff;
}

.answer-list {
    list-style: none;
    padding-left: 0;
    margin: 10px 0;
}

.answer-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px dashed #cbd5e0;
    font-size: 16px;
    flex-wrap: wrap;
}

.answer-label {
    font-weight: bold;
    min-width: 24px;
    color: #2d3748;
}

.answer-text {
    flex: 1;
    word-break: break-word;
}

.answer-list img {
    max-width: 160px;
    max-height: 120px;
    border: 2px solid #3182ce;
    border-radius: 10px;
    padding: 4px;
    background: #f7fafc;
    transition: 0.3s ease;
}

.answer-list img:hover {
    transform: scale(1.05);
    box-shadow: 0 0 12px rgba(49, 130, 206, 0.5);
}

.answer-list li.correct {
    color: #2b6cb0;
    font-weight: bold;
    background: #ebf8ff;
    border-left: 4px solid #3182ce;
    padding-left: 12px;
    font-size: 18px;
}

    </style>
</body>
</html>