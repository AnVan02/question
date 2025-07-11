<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();
$message = "";

// Xử lý cập nhật lần thử
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "<div style='color:red;'>Lỗi bảo mật: CSRF token không hợp lệ!</div>";
    } else {
        $id_khoa = (int)$_POST['update_id'];
        $lan_thu = (int)$_POST['lan_thu'];

        if ($lan_thu < 0) {
            $message = "<div style='color:red;'>Lần thử không được nhỏ hơn 0!</div>";
        } else {
            $sql = "UPDATE test SET lan_thu = ? WHERE id_khoa = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $lan_thu, $id_khoa);

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 0) {
                    $message = "<div style='color:red;'>Không có bài test nào để cập nhật!</div>";
                } else {
                    $message = "<div style='color:green;'>Cập nhật lần thử thành công!</div>";
                }
            } else {
                $message = "<div style='color:red;'>Lỗi khi cập nhật: " . $stmt->error . "</div>";
            }
        }
    }
}

// lấy thông tin khoá học từ bằng khoa_hoc
function getCoursesFromDB() {
    $conn = dbconnect();
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    $conn->close();
    return $courses;
}


// lấy tất cả khoa học từ bảng khoa_hoc
function getTestInfo($id, $khoa_hoc) {
    $conn = dbconnect ();
    $courses = getCoursesFromDB();
    $id_khoa = array_search ($khoa_hoc, $courses);
    if($id_khoa === false ) {
        die ("Lỗi: không tim thấy khoá hoc '$ten_khoa'");

    }
    $sql ="SELECT khoa_hoc FROM khoa_hoc WHERE id =? AND khoa_hoc =?";
    $stmt= $conn -> prepare ($sql);
    $stmt -> bind_param ("si",$id, $khoa_hoc );
    $stmt -> execute ();
    $result = $stmt -> get_result();
    if($result -> num_rows > 0) {
        $row = $result -> fetch_assoc ();
        $stmt -> close ();
        $conn -> close ();
        return $row ['khoa_hoc'];
    }
    $stmt-> close ();
    $conn -> close ();
    die("Lôi: Không tim thấy bài test '$id' cho khoá hoc '$khoa_hoc'");
}

$sql_khoa = "
    SELECT k.id, k.khoa_hoc, IFNULL(SUM(t.lan_thu), 0) AS tong_lan_thu,
           (SELECT lan_thu FROM test WHERE id_khoa = k.id LIMIT 1) AS lan_thu
    FROM khoa_hoc k
    LEFT JOIN test t ON k.id = t.id_khoa
    GROUP BY k.id, k.khoa_hoc
";

$result_khoa = $conn->query($sql_khoa);
$khoa_hoc = [];
while ($row = $result_khoa->fetch_assoc()) {
    $khoa_hoc[] = $row;
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Khóa học và Lần thử</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; cursor: pointer; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 50px auto; padding: 20px; width: 200px; }
    </style>
</head>
<body>

<h2>Danh sách Khóa học</h2>

<?php echo $message; ?>

<table>
    <tr>
        <th>ID Khóa học</th>
        <th>Tên Khóa học</th>
        <th>Bài test</th>
        <th>Tổng Lần Thử</th>

    </tr>
    <?php foreach ($khoa_hoc as $khoa): ?>
    <tr>
        <td><?php echo $khoa['id']; ?></td>
        <td><?php echo htmlspecialchars($khoa['khoa_hoc']); ?></td>
        <td id="lan_thu_<?php echo $khoa['id']; ?>"><?php echo $khoa['tong_lan_thu']; ?></td>
    
        <td>
            <button class="btn" onclick="editLanThu(<?php echo $khoa['id']; ?>, <?php echo $khoa['lan_thu'] ?? 0; ?>)">Sửa</button>
   
            <button class="btn" onclick="deletelanthu(<?php echo $khoa ['id'];?>,<?php echo $khoa['lan_thu'] ?? 0;?>)">Xoá</button>
        </td>
    </tr>
    <?php endforeach; ?>
</table>




<!-- Modal sửa lần thử -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Sửa Lần Thử</h3>
        <form id="editForm" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="update_id" id="update_id">
            <label>Lần thử:</label><br>
            <input type="number" name="lan_thu" id="edit_lan_thu" min="0" required><br><br>
            <button type="submit">Lưu</button>
            <button type="button" onclick="closeModal()">Hủy</button>
        </form>
    </div>
</div>

<script>
    function editLanThu(id, lan_thu) {
        document.getElementById('update_id').value = id;
        document.getElementById('edit_lan_thu').value = lan_thu;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) {
            closeModal();
        }
    };
</script>

</body>
</html>