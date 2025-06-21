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

function isDateFormat($date) {
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    
    // Kiểm tra xem định dạng có khớp và ngày tháng có hợp lệ không
    return $d && $d->format($format) === $date;
}

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
            $errorMessages = ""; // Danh sách lỗi
            $countInserted = 0; // Đếm số lượng mã đã thêm
            $countUpdated = 0; // Đếm số lượng mã đã cập nhật

            // Prepare data
            $mahang = "Cột MAHANG";
            $tenhang = "Cột TENHANG";
            $so_serial = "Cột SoSerial";
            // $ngayxuat = "Cột NGAYXUAT";
            $thoihanbh = "Cột THOIHANBH";


            // bind seri extraction
            $checkStmt = $conn->prepare("SELECT SoSerial FROM sanpham WHERE SoSerial = ?"); 
            $checkStmt->bind_param("s", $so_serial);

            // bind inserting seri
            $insertStmt = $conn->prepare("INSERT INTO sanpham (MAHANG, TENHANG,THOIHANBH) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("sssi", $mahang, $tenhang, $thoihanbh);

            // bind updating seri
            $updateStmt = $conn->prepare("UPDATE sanpham SET MAHANG = ?, TENHANG = ?, THOIHANBH = ? WHERE SoSerial = ?");
            $updateStmt->bind_param("sssis", $mahang, $tenhang, $thoihanbh, $so_serial);              
            

            // Lặp qua từng hàng trong dữ liệu để kiểm tra mã serial
            foreach ($data as $key => $row) {
                $key += 1;
                if ($key === 1) { // Bỏ qua hàng tiêu đề
                    continue;
                }

                // sthrow new Exception("Lỗi");
                // Lấy dữ liệu từ mảng theo thứ tự mà người dùng cung cấp
                $mahang = trim($row[0]); // Cột MAHANG
                $tenhang = trim($row[1]); // Cột TENHANG
                $so_serial = trim($row[2]); // Cột SoSerial
                // $ngayxuat = trim($row[3]); // Cột NGAYXUAT
                $thoihanbh = intval(trim($row[4])); // Cột THOIHANBH
               
                

                 // Kiểm tra các trường hợp sai 
                if (empty($mahang) || empty($tenhang) || empty($so_serial)) {
                    $errorMessages .= "Cập nhật lỗi ở hàng {$key}: Mã Hàng/Tên Hàng/Số Seri đang trống" ;
                    break;
                }
                // elseif (!isDateFormat($ngayxuat)){
                //     $errorMessages .= "Cập nhật lỗi ở hàng {$key}: Ngày sai format" ;
                //     break;
                // }
                elseif ($thoihanbh === 0){
                    $errorMessages .= "Cập nhật lỗi ở hàng {$key}: Thời hạn bảo hành không phải số" ;
                    break;
                }

                $checkStmt->execute();
                $checkStmt->store_result();                               

                if ($checkStmt->num_rows > 0) {
                    // Nếu mã serial đã tồn tại, thực hiện cập nhật thông tin
                    $updateStmt->execute();
                } else {
                    // Nếu mã serial không tồn tại, thực hiện thêm dữ liệu mới
                    $insertStmt->execute();
                }
                            
            }

            // break tới đay!
            if (!empty($errorMessages)) {
                $conn->rollback(); // Rollback transaction nếu có lỗi
                echo "<script>alert('$errorMessages'); window.location.href='index.php';</script>";
                throw new Exception("FORMAT ERROR");
            } else {
                $conn->commit(); // Commit transaction nếu không có lỗi
                echo "<script>alert('Cập nhật dữ liệu Excel thành công!'); window.location.href='index.php';</script>";
            }

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
