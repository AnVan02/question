<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}
$id_test = isset ($_GET ['id_test'])?(int)$_GET ['id_test']:0;

// Khơi tạo biến thông báo
$message ="";

// Xử lý xóa câu hỏi
$message = "";
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $conn = dbconnect();
    
    // lấy tên_test và khoa_hoc để lọc câu hỏi 

    $sql = "SELECT t.ten_test, k.khoa_học
            FROM test t
            LEFT JOIN khoa_hoc k ON t.id_khoa = k.id
            WHERE t.id_test =?>";
    $stmt = $conn -> prepare ($sql);
    
    
    // Kiểm tra xem câu hỏi có hình ảnh không và xóa nếu có
    $sql = "SELECT hinhanh FROM quiz WHERE Id_cauhoi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['hinhanh']) && file_exists($row['hinhanh'])) {
            unlink($row['hinhanh']); // Xóa file hình ảnh
        }
    }
    $stmt->close();

    // Xóa câu hỏi khỏi cơ sở dữ liệu
    $sql = "DELETE FROM quiz WHERE Id_cauhoi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "<div style='color:green;'>Xóa câu hỏi thành công!</div>";
    } else {
        $message = "<div style='color:red;'>Lỗi khi xóa câu hỏi: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();
}

// Lấy danh sách câu hỏi
$conn = dbconnect();
$sql = "SELECT Id_cauhoi, id_baitest, cauhoi, dap_an FROM quiz";
$result = $conn->query($sql);
$questions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách câu hỏi</title>
    <!-- <link rel="stylesheet" hre="style.css"> -->
</head>
<body>
    <div class="container">
        <h2>Danh sách câu hỏi <?php echo htmlspecialchars($test_info['ten_test'] ?? '');?>
        - <?php echo htmlspecialchars ($khoa_hoc ?? 'không xác định');?></h2>
        <?php if (!empty ($message)) echo $message ;?>
        <a href="add_khoahoc.php" class="btn-black">Danh sách khoá học</a>
        <?php if ($test_info) :?>
            <a href="khoahoc.php?id_khoa=<?php echo htmlspecialchars ($test_info ['id_khoa']); ?>" class="btn-black">Quay lại danh sách bài test</a>
        <?php endif ;?>

        <?php if ($id_test > 0 && $test_info && $khoa_hoc ):?>
            <a href="add_khoahoc.php?id_khoa=<?php echo htmlspecialchars($test_info['id_khoa']); ?>" class="btn-back">Thêm bài test</a>
        <?php endif;?>

        <?php if (empty($questions)): ?>
            <p>Chưa có câu hỏi nào trong cơ sở dữ liệu.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <!-- <th>ID Câu hỏi</th> -->
                        <th>Bài test</th>
                        <!-- <th>Nội dung câu hỏi</th> -->
                        <th>Môn học</th>
                        <th>Đáp án đúng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <tr>
                            <!-- <td><?= htmlspecialchars($question['Id_cauhoi']) ?></td> -->
                            <td><?= htmlspecialchars($question['id_baitest']) ?></td>
                            <td><?= htmlspecialchars($question['cauhoi']) ?></td>
                            <td><?= htmlspecialchars($question['mon_hoc'])?></td>
                            <td><?= htmlspecialchars($question['dap_an']) ?></td>
                   
                            <td>
                                <a href="add_question.php?question_id=<?= $question['Id_cauhoi'] ?>" class="btn-edit">Sửa</a>
                                <a href="question.php?delete_id=<?= $question['Id_cauhoi'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">Xóa</a>
                            </td>
                            
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    

</body>
</html>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
    margin: 0;
}
h2 {
    text-align: center;
    color:rgb(247, 18, 18);
    margin-bottom: 25px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
}
.container {
    max-width: 1500px;
    margin: auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #009688;
    color: white;
    font-weight: 600;
}

tr:hover {
    background-color: #f5f5f5;
}

/* Kiểu cho nút hành động */
.btn-add, .btn-edit, .btn-delete {
    display: inline-block;
    padding: 8px 12px;
    margin-right: 5px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-add {
    background-color: #28a745;
    color: white;
}

.btn-add:hover {
    background-color: #218838;
}
/* button sửa */
.btn-edit {
    background-color:rgb(252, 14, 14);
    color: white;
}
/* button xoá  */
.btn-delete {
    background-color:rgb(255, 230, 7);
    color: blue;
}


</style>