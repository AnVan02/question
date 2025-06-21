<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
     .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .col-md-4 {
        display: flex;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .btn-primary {
        width: 100%;
    }

    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Import file bảo hành</h2>
    <img src="modules/baohanh/upload.png" alt="upload bảo hành" class="img-fluid mb-4"style="max-width: 80%; height: auto; margin-bottom: 20px;">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="col-md-4 ">
            <!-- Bạn có thể thêm nội dung vào đây nếu cần -->
        </div>
        <div class="form-group">
            <label for="fileInput">Nhập file Excel</label>
            <input type="file" class="form-control" name="file" id="fileInput" required>
        </div>
        <button type="submit" class="btn btn-primary" name="import">Import bảo hành</button>
    </form>
</div>

    

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
require_once('vendor/autoload.php'); // Nếu import.php nằm ở thư mục gốc project/

use PhpOffice\PhpSpreadsheet\IOFactory;

// Kết nối tới cơ sở dữ liệu
$servername = "localhost";
$username = "nvpbgqcv_rosacomputer";
$password = "LhAZBR8iN6";
$dbname = "nvpbgqcv_rosacomputer"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname); // Tạo một đối tượng kết nối mới

// Kiểm tra kết nối cơ sở dữ liệu
if ($conn->connect_error) { // Nếu có lỗi trong kết nối
    die("Connection failed: " . $conn->connect_error); // Dừng chương trình và thông báo lỗi
}

// Kiểm tra xem người dùng đã nhấn nút "import" hay chưa
if (isset($_POST["import"])) {
    // Kiểm tra xem file upload có lỗi hay không
    if ($_FILES["file"]["error"] == UPLOAD_ERR_OK) { // Kiểm tra mã lỗi upload
        $fileTmpPath = $_FILES["file"]["tmp_name"]; // Lấy đường dẫn tạm thời của file được upload

        try {
            // Đọc file Excel đã tải lên
            $spreadsheet = IOFactory::load($fileTmpPath); // Sử dụng IOFactory để tải file Excel
            $worksheet = $spreadsheet->getActiveSheet(); // Lấy worksheet hiện tại
            $data = $worksheet->toArray(); // Chuyển nội dung của worksheet thành mảng

            // doc file 
            

            // Bắt đầu một giao dịch cơ sở dữ liệu
            $conn->begin_transaction(); // Bắt đầu giao dịch

            // Lặp qua từng dòng dữ liệu trong file Excel
            foreach ($data as $key => $row) {
                // Bỏ qua hàng tiêu đề (thường là hàng đầu tiên)
                if ($key === 0) {
                    continue; // Nếu là hàng tiêu đề thì bỏ qua
                }

                // Lấy giá trị từ từng cột của dòng
                $mahang = trim($row[0]); // Mã hàng
                $tenhang = trim($row[1]); // Tên hàng
                $so_serial = trim($row[2]); // Số serial
                $ngayxuat = trim($row[3]); // Ngày xuất (dạng chuỗi)
                $thoihanbh = trim($row[4]); // Thời hạn bảo hành (số tháng)

                // Kiểm tra xem số serial đã tồn tại trong cơ sở dữ liệu hay chưa
                $checkStmt = $conn->prepare("SELECT SoSerial, NGAYXUAT, THOIHANBH FROM sanpham WHERE SoSerial = ?"); // Chuẩn bị câu truy vấn
                $checkStmt->bind_param("s", $so_serial); // Liên kết tham số với câu truy vấn
                $checkStmt->execute(); // Thực thi câu truy vấn
                $checkStmt->store_result(); // Lưu kết quả truy vấn

                // Nếu serial tồn tại, thực hiện kiểm tra ngày hết hạn bảo hành
                if ($checkStmt->num_rows > 0) { // Kiểm tra xem có kết quả hay không
                    // Lấy thông tin về ngày xuất và thời hạn bảo hành
                    $checkStmt->bind_result($db_serial, $db_ngayxuat, $db_thoihanbh); // Liên kết kết quả với biến
                    $checkStmt->fetch(); // Lấy dữ liệu

                    // Tính toán ngày hết hạn bảo hành bằng cách cộng số tháng bảo hành vào ngày xuất
                    $expirationDate = new DateTime($db_ngayxuat); // Khởi tạo đối tượng DateTime với ngày xuất
                    $expirationDate->modify("+$db_thoihanbh months"); // Cộng thêm số tháng bảo hành

                    // Lấy ngày hiện tại
                    $currentDate = new DateTime(); // Khởi tạo đối tượng DateTime với ngày hiện tại
                    $daysLeft = $expirationDate->diff($currentDate)->days; // Tính số ngày còn lại

                    // Nếu sản phẩm sắp hết hạn bảo hành trong vòng 30 ngày, xóa nó
                    if ($daysLeft <= 30 && $currentDate <= $expirationDate) { // Kiểm tra ngày còn lại
                        $deleteStmt = $conn->prepare("DELETE FROM sanpham WHERE SoSerial = ?"); // Chuẩn bị câu truy vấn xóa
                        $deleteStmt->bind_param("s", $db_serial); // Liên kết tham số với câu truy vấn
                        $deleteStmt->execute(); // Thực thi câu truy vấn xóa
                        $deleteStmt->close(); // Đóng câu lệnh
                      
                    } else {
                        // Cập nhật thông tin bảo hành nếu serial tồn tại nhưng chưa hết hạn
                        $updateStmt = $conn->prepare("UPDATE sanpham SET MAHANG = ?, TENHANG = ?, NGAYXUAT = ?, THOIHANBH = ? WHERE SoSerial = ?"); // Chuẩn bị câu truy vấn cập nhật
                        $updateStmt->bind_param("sssis", $mahang, $tenhang, $ngayxuat, $thoihanbh, $so_serial); // Liên kết tham số với câu truy vấn
                        $updateStmt->execute(); // Thực thi câu truy vấn cập nhật
                        $updateStmt->close(); // Đóng câu lệnh
                        echo "Cập nhật thành công cho serial $so_serial.<br>"; // Thông báo cập nhật thành công
                    }
                } else {
                    // Nếu serial không tồn tại, thêm sản phẩm mới vào cơ sở dữ liệu
                    $insertStmt = $conn->prepare("INSERT INTO sanpham (SoSerial, MAHANG, TENHANG, NGAYXUAT, THOIHANBH) VALUES (?, ?, ?, ?, ?)"); // Chuẩn bị câu truy vấn thêm mới
                    $insertStmt->bind_param("ssssi", $so_serial, $mahang, $tenhang, $ngayxuat, $thoihanbh); // Liên kết tham số với câu truy vấn
                    $insertStmt->execute(); // Thực thi câu truy vấn thêm mới
                    $insertStmt->close(); // Đóng câu lệnh
                    echo "Thêm mới thành công sản phẩm với serial $so_serial.<br>"; // Thông báo thêm mới thành công
                }

                // Đóng câu lệnh kiểm tra
                $checkStmt->close(); // Đóng câu lệnh kiểm tra serial
            }

            // Nếu không có lỗi, commit giao dịch
            $conn->commit(); // Xác nhận tất cả thay đổi trong giao dịch
            echo "<script>alert('Cập nhật dữ liệu Excel thành công!'); window.location.href='index.php';</script>"; // Thông báo thành công và chuyển hướng
        } catch (Exception $e) {
            // Nếu có lỗi, rollback giao dịch
            $conn->rollback(); // Hoàn tác tất cả thay đổi trong giao dịch
            echo "Lỗi: " . $e->getMessage(); // Thông báo lỗi
            error_log("Error: " . $e->getMessage(), 3, 'errors.log'); // Ghi lỗi vào file log
        }

        // Đóng kết nối cơ sở dữ liệu
        $conn->close(); // Đóng kết nối
    } else {
        // Nếu có lỗi trong quá trình upload file
        echo "Lỗi khi upload file: " . $_FILES["file"]["error"]; // Thông báo lỗi upload
        error_log("File upload error: " . $_FILES["file"]["error"], 3, 'errors.log'); // Ghi lỗi upload vào file log
    }
}


?>
