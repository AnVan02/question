<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    return $conn;
}

$conn = dbconnect();
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Xử lý xóa
$messege = "";
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $ten_khoa = $_GET['ten_khoa'] ?? '';
    $id_baitest = $_GET['id_baitest'] ?? '';
    
    $sql = "DELETE FROM quiz WHERE Id_cauhoi = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $messege = "<div style='color:green;'>Xoá câu hỏi thành công</div>";
        // Chuyển hướng để giữ tham số
        header("Location: ?ten_khoa=" . urlencode($ten_khoa) . "&id_baitest=" . urlencode($id_baitest));
        exit;
    } else {
        $messege = "<div style='color:red;'>Lỗi khi xoá câu hỏi: " . $stmt->error . "</div>";
    }
}

// Xử lý sửa (AJAX POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $stmt = $conn->prepare("UPDATE quiz SET cauhoi=?, cau_a=?, cau_b=?, cau_c=?, cau_d=?, dap_an=? WHERE Id_cauhoi=?");
    $stmt->bind_param("ssssssi", $_POST['cauhoi'], $_POST['cau_a'], $_POST['cau_b'], $_POST['cau_c'], $_POST['cau_d'], $_POST['dap_an'], $_POST['update_id']);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// Dữ liệu lọc
$ten_khoa = $_GET['ten_khoa'] ?? '';
$id_baitest = $_GET['id_baitest'] ?? '';
$khoa_hoc = [];
$bai_kiem_tra = [];
$cau_hoi = [];

// Lấy danh sách môn học
$stmt = $conn->prepare("SELECT DISTINCT ten_khoa FROM quiz");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $khoa_hoc[] = $row['ten_khoa'];
}

// Lấy danh sách bài kiểm tra
if ($ten_khoa !== '') {
    $stmt = $conn->prepare("SELECT DISTINCT id_baitest FROM quiz WHERE ten_khoa = ?");
    $stmt->bind_param("s", $ten_khoa);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $bai_kiem_tra[] = $row['id_baitest'];
    }
}

// Lấy danh sách câu hỏi
if ($ten_khoa !== '' && $id_baitest !== '') {
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
    $stmt->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $cau_hoi[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý câu hỏi</title>
</head>
<body>
<div class="container">
    <h3>Những Câu Hỏi Môn: <?= htmlspecialchars($ten_khoa) ?></h3>
    <a class="btn view" href="add_khoahoc.php"><i class="fas fa-eye"></i> Quay lại danh sách môn học</a><br><br>

    <?= $messege ?>

    <?php if ($ten_khoa !== ''): ?>
    <form method="get">
        <input type="hidden" name="ten_khoa" value="<?= htmlspecialchars($ten_khoa) ?>">
        <label>Chọn bài kiểm tra:</label>
        <select name="id_baitest" onchange="this.form.submit()">
            <option value="">-- Chọn bài kiểm tra --</option>
            <?php foreach ($bai_kiem_tra as $bt): ?>
                <option value="<?= htmlspecialchars($bt) ?>" <?= $bt == $id_baitest ? 'selected' : '' ?>>
                    <?= htmlspecialchars($bt) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>
    <a class="btn view" href="question.php"><i class="fas fa-eye"></i> Xem câu hỏi</a>


    <?php if (!empty($cau_hoi)): ?>
    <!-- <h3>Danh sách câu hỏi</h3> -->
    <table>
        <tr>
            <th>Câu</th>
            <th>Câu hỏi</th>
            <th>Thao tác</th>
        </tr>
        <?php foreach ($cau_hoi as $ch): ?>
        <tr>
            <td><?= $ch['Id_cauhoi'] ?></td>
            <td><?= htmlspecialchars($ch['cauhoi']) ?></td>
            <td>
                <button class="btn editBtn"
                        data-id="<?= $ch['Id_cauhoi'] ?>"
                        data-cauhoi="<?= htmlspecialchars($ch['cauhoi'], ENT_QUOTES) ?>"
                        data-a="<?= htmlspecialchars($ch['cau_a'], ENT_QUOTES) ?>"
                        data-b="<?= htmlspecialchars($ch['cau_b'], ENT_QUOTES) ?>"
                        data-c="<?= htmlspecialchars($ch['cau_c'], ENT_QUOTES) ?>"
                        data-d="<?= htmlspecialchars($ch['cau_d'], ENT_QUOTES) ?>"
                        data-dapan="<?= htmlspecialchars($ch['dap_an'], ENT_QUOTES) ?>">Sửa</button>
                <button class="btn" onclick="window.location.href='?delete_id=<?= $ch['Id_cauhoi'] ?>&ten_khoa=<?= urlencode($ten_khoa) ?>&id_baitest=<?= urlencode($id_baitest) ?>'; return confirm('Bạn chắc chắn muốn xóa?')">Xóa</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <!-- <h3>Không có câu hỏi nào cho bài kiểm tra này.</h3> -->
    <?php endif; ?>
</div>

<!-- Modal popup -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h3>Sửa câu hỏi</h3>
        <form id="editForm">
            <input type="hidden" name="update_id" id="update_id">
            <label>Câu hỏi:</label>
            <textarea name="cauhoi" id="cauhoi"></textarea>
            <label>Câu A:</label>
            <input type="text" name="cau_a" id="cau_a">
            <label>Câu B:</label>
            <input type="text" name="cau_b" id="cau_b">
            <label>Câu C:</label>
            <input type="text" name="cau_c" id="cau_c">
            <label>Câu D:</label>
            <input type="text" name="cau_d" id="cau_d">
            <label>Đáp án đúng (a, b, c hoặc d):</label>
            <input type="text" name="dap_an" id="dap_an">
            <button type="submit">Lưu thay đổi</button>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById("editModal");
const form = document.getElementById("editForm");

document.querySelectorAll(".editBtn").forEach(btn => {
    btn.onclick = () => {
        document.getElementById("update_id").value = btn.dataset.id;
        document.getElementById("cauhoi").value = btn.dataset.cauhoi;
        document.getElementById("cau_a").value = btn.dataset.a;
        document.getElementById("cau_b").value = btn.dataset.b;
        document.getElementById("cau_c").value = btn.dataset.c;
        document.getElementById("cau_d").value = btn.dataset.d;
        document.getElementById("dap_an").value = btn.dataset.dapan;
        modal.style.display = "block";
    }
});

form.onsubmit = async e => {
    e.preventDefault();
    const data = new FormData(form);
    const res = await fetch("<?= $_SERVER['PHP_SELF'] ?>", {
        method: "POST",
        body: data
    });
    const text = await res.text();
    if (text.trim() === "success") {
        alert("Cập nhật thành công!");
        window.location.reload();
    } else {
        alert("Lỗi cập nhật!");
    }
};

function closeModal() {
    modal.style.display = "none";
}
window.onclick = e => {
    if (e.target === modal) closeModal();
};
</script>

<style>
/* General Styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
    min-height: 100vh;
    padding: 20px;
    color: #2d3748;
    line-height: 1.6;
}

/* Container Styling */
.container {
    max-width: 1400px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    padding: 40px;
    transition: transform 0.3s ease;
}

/* Headings */
h2, h3 {
    color: #1a202c;
    margin-bottom: 20px;
    font-weight: 700;
    text-align: center;
}

h2 {
    font-size: 2rem;
}

h3 {
    font-size: 1.5rem;
}

/* Form Styling */
form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
}

label {
    font-weight: 600;
    color: #4a5568;
    margin-right: 10px;
}

select, input[type="text"], textarea {
    padding: 10px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #2d3748;
    background: #f7fafc;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

select:focus, input:focus, textarea:focus {
    outline: none;
    border-color: #3182ce;
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

select {
    min-width: 200px;
    cursor: pointer;
}

textarea {
    width: 100%;
    resize: vertical;
    min-height: 100px;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

th, td {
    padding: 16px 20px;
    text-align: left;
    border-bottom: 1px solid #edf2f7;
}

th {
    background: #f1f5f9;
    color: #2d3748;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
}

td {
    font-size: 0.95rem;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover {
    background: #f8fafc;
}

/* Button Styling */
.btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn.editBtn {
    background: #3182ce;
    color: #ffffff;
}

.btn.editBtn:hover {
    background: #2b6cb0;
}

.btn.view {
    background: #38a169;
    color: #ffffff;
}



button.btn[onclick*="delete"] {
    background: #e53e3e;
    color: #ffffff;
    border: none;
}

button.btn[onclick*="delete"]:hover {
    background: #c53030;
}

/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    overflow: auto;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background: #ffffff;
    margin: 5% auto;
    padding: 30px;
    width: 90%;
    max-width: 700px;
    border-radius: 12px;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease;
}

.close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 28px;
    cursor: pointer;
    color: #718096;
    transition: color 0.3s ease;
}

.close:hover {
    color: #2d3748;
}

/* Modal Form Styling */
#editForm {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

#editForm label {
    font-weight: 600;
    color: #2d3748;
}

#editForm button {
    background: #3182ce;
    color: #ffffff;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    align-self: flex-start;
}

#editForm button:hover {
    background: #2b6cb0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 20px;
    }

    h2 {
        font-size: 1.5rem;
    }

    h3 {
        font-size: 1.2rem;
    }

    table {
        font-size: 0.85rem;
    }

    th, td {
        padding: 12px;
    }

    .btn {
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .modal-content {
        width: 95%;
        padding: 20px;
    }

    form {
        flex-direction: column;
    }

    select {
        width: 100%;
    }
}
</style>
</body>
</html>