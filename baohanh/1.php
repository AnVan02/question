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

// Database connection
$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "123"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST["import"])) {
    // Kiểm tra nếu có file được upload
    if ($_FILES["file"]["error"] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES["file"]["tmp_name"];
        
        // Kiểm tra xem file đã được tải lên chưa
        var_dump($_FILES["file"]); // Debug thông tin file

        try {
            // Load file Excel
            $spreadsheet = IOFactory::load($fileTmpPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            // Bắt đầu transaction
            $conn->begin_transaction();

            // Prepare data
            $mahang = "Cột MAHANG";
            $tenhang = "Cột TENHANG";
            $so_serial = "Cột SoSerial";
            $ngayxuat = "Cột NGAYXUAT";
            $thoihanbh = "Cột THOIHANBH";

            // bind seri extraction
            $checkStmt = $conn->prepare("SELECT SoSerial FROM sanpham WHERE SoSerial = ?"); 
            $checkStmt->bind_param("s", $so_serial);

            // bind inserting seri
            $insertStmt = $conn->prepare("INSERT INTO sanpham (SoSerial, MAHANG, TENHANG, NGAYXUAT, THOIHANBH) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("ssssi", $so_serial, $mahang, $tenhang, $ngayxuat, $thoihanbh);

            // bind updating seri
            $updateStmt = $conn->prepare("UPDATE sanpham SET MAHANG = ?, TENHANG = ?, NGAYXUAT = ?, THOIHANBH = ? WHERE SoSerial = ?");
            $updateStmt->bind_param("sssis", $mahang, $tenhang, $ngayxuat, $thoihanbh, $so_serial);

            
            
            // Lặp qua từng hàng trong dữ liệu để kiểm tra mã serial
            foreach ($data as $key => $row) {
                if ($key === 0) { // Bỏ qua hàng tiêu đề
                    continue;
                }

                // sthrow new Exception("Lỗi");
                // Lấy dữ liệu từ mảng theo thứ tự mà người dùng cung cấp
                $mahang = trim($row[0]); // Cột MAHANG
                $tenhang = trim($row[1]); // Cột TENHANG
                $so_serial = trim($row[2]); // Cột SoSerial
                $ngayxuat = trim($row[3]); // Cột NGAYXUAT
                $thoihanbh = trim($row[4]); // Cột THOIHANBH
            
                // Kiểm tra mã serial đã tồn tại trong cơ sở dữ liệu chưa
                // $checkStmt = $conn->prepare("SELECT SoSerial FROM sanpham WHERE SoSerial = ?"); 
                // $checkStmt->bind_param("s", $so_serial);
                $checkStmt->execute();
                $checkStmt->store_result();

                echo "Tổng số   $checkStmt->num_rows$so_serial.<br>";
                echo "----------------------<br>";

                if ($checkStmt->num_rows > 0) {
                    // Nếu mã serial đã tồn tại, thực hiện cập nhật thông tin
                    // $updateStmt = $conn->prepare("UPDATE sanpham SET MAHANG = ?, TENHANG = ?, NGAYXUAT = ?, THOIHANBH = ? WHERE SoSerial = ?");
                    // $updateStmt->bind_param("sssis", $mahang, $tenhang, $ngayxuat, $thoihanbh, $so_serial);

                    // Thực hiện cập nhật
                    $updateStmt->execute();
                    // $updateStmt->close();
                    // echo "Cập nhật thành công cho serial $so_serial.<br>";
                } else {
                    // Nếu mã serial không tồn tại, thực hiện thêm dữ liệu mới
                    // $insertStmt = $conn->prepare("INSERT INTO sanpham (SoSerial, MAHANG, TENHANG, NGAYXUAT, THOIHANBH) VALUES (?, ?, ?, ?, ?)");
                    // $insertStmt->bind_param("ssssi", $so_serial, $mahang, $tenhang, $ngayxuat, $thoihanbh);

                    // // Thực hiện thêm sản phẩm mới
                    $insertStmt->execute();
                    // $insertStmt->close();
                    // echo "Thêm mới thành công sản phẩm với serial $so_serial.<br>";
                }

                // $checkStmt->close();
            }

            // Commit transaction
            $conn->commit();
            echo "<script>alert('Cập nhật dữ liệu Excel thành công!'); window.location.href='index.php';</script>";

            // close all prepares
            $checkStmt->close();
            $updateStmt->close();
            $insertStmt->close();

        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $conn->rollback();
            echo "Lỗi: " . $e->getMessage();
            error_log("Error: " . $e->getMessage(), 3, 'errors.log'); // Ghi log lỗi vào file
        }

        // Đóng kết nối sau khi hoàn tất
        $conn->close();
    } else {
        echo "Lỗi khi upload file: " . $_FILES["file"]["error"];
        error_log("File upload error: " . $_FILES["file"]["error"], 3, 'errors.log'); // Ghi log lỗi vào file
    }
}
?>
