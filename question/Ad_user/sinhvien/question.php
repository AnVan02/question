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
    $sql = "SELECT Id_cauhoi, id_baitest, ten_khoa, cauhoi, cau_a, cau_b, cau_c, cau_d, dap_an 
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

// lây danh sách test dựa vao ten_test và ten_khoa
$id_baitest = [];
if($id_test > 0 && $test_info && $khoa_hoc) {
    $conn = dbconnect ();
    $sql = "SELECT id_cauhoi, id_khoa, id_baitest, ten_test, lanthu, pass, so_cau_hien_thi FROM test WHERE lanthu and so_cau_hien_thi ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param ("ss", $test_info ['id_baitest'], $lanthu);
    $stmt -> execute();
    $result = $stmt -> get_result ();
    if ($result -> num_rows > 0) {
        while ($row = $result -> fetch_assoc()){
            $question []=$row;
        }
    }
    $stmt -> close();
    $conn -> close ();
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
        <a href="add_khoahoc.php" class="btn-back">Danh sách khóa học</a>
        <?php if ($test_info): ?>
            <a href="khoahoc.php?id_khoa=<?php echo htmlspecialchars($test_info['id_khoa']); ?>" class="btn-back">Quay lại danh sách bài test</a>
        <?php endif; ?>

        <?php if ($id_test > 0 && $test_info && $khoa_hoc): ?>
            <a href="add_question.php?id_test=<?php echo htmlspecialchars($id_test); ?>" class="btn-add">Thêm câu hỏi mới</a>

            <?php if (empty($questions)): ?>
                <p>Chưa có câu hỏi nào cho bài kiểm tra này của môn học.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <!-- <th>ID Câu hỏi</th> -->
                            <th>Bài test</th>
                            <th>Môn học</th>
                            <th>Câu hỏi</th>
                            <th>Hành động</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <!-- <td><?php echo htmlspecialchars($question['Id_cauhoi']); ?></td> -->
                                <td><?php echo htmlspecialchars($question['id_baitest']); ?></td>
                                <td><?php echo htmlspecialchars($question['ten_khoa']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($question['cauhoi']); ?>
                                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                                        <p<?php if ($question['dap_an'] == 'A') echo ' style="color:blue;font-weight:bold;font-size:18px"'; ?>>
                                            A. <?php echo htmlspecialchars($question['cau_a']); ?>
                                        </p>
                                        <p<?php if ($question['dap_an'] == 'B') echo ' style="color:blue;font-weight:bold;font-size:18px"'; ?>>
                                            B. <?php echo htmlspecialchars($question['cau_a']); ?>
                                        </p>
                                        <p<?php if($question['dap_an'] == 'C') echo ' style="color:blue;font-weight:bold;font-size:18px"'; ?>>
                                            C. <?php echo htmlspecialchars($question['cau_c']);?>
                                        </p>
                                        <p<?php if($question['dap_an'] == 'D') echo ' style="color:blue;font-weight:bold;font-size:18px"'; ?>>
                                            D. <?php echo htmlspecialchars($question ['cau_d']);?>
                                        </p>

                                    </ul>
                                </td>

                                
                                <td>
                                    <a href="add_question.php?question_id=<?php echo htmlspecialchars($question['Id_cauhoi']); ?>&id_test=<?php echo htmlspecialchars($id_test); ?>" class="btn-edit">Sửa</a>
                                    <a href="<?php echo htmlspecialchars('question.php?id_test=' . $id_test . '&delete_id=' . $question['Id_cauhoi']); ?>" 
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
            font-family: Arial, sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            margin: 0;
        }
        .container {
            max-width: 1250px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 25px;
            color: #2d3748;
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            padding: 10px;
            background-color: #edf2f7;
            border-radius: 8px;
        }

        .message.success {
            color: #2f855a;
            background-color: #e6fff3;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .message.error {
            color: #c53030;
            background-color: #fff5f5;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            
        }
        th {
            background-color:  #3182ce;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn-add, .btn-edit, .btn-delete, .btn-back {
            display: inline-block;
            padding: 8px 12px;
            margin-right: 5px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .btn-add {
            background-color:rgb(245, 17, 17);
            color: white;
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
            background-color:  #3182ce;
            color: white;
            margin-bottom: 20px;
        }
      
    </style>
</body>
</html>