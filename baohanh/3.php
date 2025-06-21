<?php
require_once('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "123"; // Thay thế với tên cơ sở dữ liệu của bạn

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST["import"])) {
    // Kiểm tra nếu có file được upload
    if ($_FILES["file"]["error"] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES["file"]["tmp_name"];
        
        try {
            // Load file Excel
            $spreadsheet = IOFactory::load($fileTmpPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            // Bắt đầu transaction
            $conn->begin_transaction();
            $errorMessages = []; // Danh sách lỗi
            $countInserted = 0; // Đếm số lượng mã đã thêm
            $countUpdated = 0; // Đếm số lượng mã đã cập nhật

            // Prepare statements
            $checkStmt = $conn->prepare("SELECT SoSerial FROM sanpham WHERE SoSerial = ?");
            $insertStmt = $conn->prepare("INSERT INTO sanpham (SoSerial, MAHANG, TENHANG, NGAYXUAT, THOIHANBH) VALUES (?, ?, ?, ?, ?)");
            $updateStmt = $conn->prepare("UPDATE sanpham SET MAHANG = ?, TENHANG = ?, NGAYXUAT = ?, THOIHANBH = ? WHERE SoSerial = ?");

            // Lặp qua từng hàng trong dữ liệu để kiểm tra mã serial
            foreach ($data as $key => $row) {
                if ($key === 0) { // Bỏ qua hàng tiêu đề
                    continue;
                }

                // Lấy dữ liệu từ mảng theo thứ tự mà người dùng cung cấp
                $mahang = trim($row[0]);
                $tenhang = trim($row[1]);
                $so_serial = trim($row[2]);
                $ngayxuat = trim($row[3]);
                $thoihanbh = trim($row[4]);

                // Kiểm tra thông tin
                if (empty($mahang) || empty($tenhang) || empty($so_serial) || empty($ngayxuat) || empty($thoihanbh)) {
                    $errorMessages[] = "Hàng " . ($key + 1) . ": Thiếu thông tin.";
                    continue; // Bỏ qua hàng này
                }

                // Kiểm tra mã serial đã tồn tại trong cơ sở dữ liệu
                $checkStmt->bind_param("s", $so_serial);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    // Nếu mã serial đã tồn tại, thực hiện cập nhật thông tin
                    $updateStmt->bind_param("ssssi", $mahang, $tenhang, $ngayxuat, $thoihanbh, $so_serial);
                    if ($updateStmt->execute()) {
                        $countUpdated++; // Tăng đếm số lượng mã đã cập nhật
                    } else {
                        $errorMessages[] = "Cập nhật không thành công cho hàng " . ($key + 1);
                    }
                } else {
                    // Nếu mã serial không tồn tại, thực hiện thêm dữ liệu mới
                    $insertStmt->bind_param("ssssi", $so_serial, $mahang, $tenhang, $ngayxuat, $thoihanbh);
                    if ($insertStmt->execute()) {
                        $countInserted++; // Tăng đếm số lượng mã đã thêm
                    } else {
                        $errorMessages[] = "Thêm mới không thành công cho hàng " . ($key + 1);
                    }
                }
            }

            // Kiểm tra và xử lý lỗi
            if (!empty($errorMessages)) {
                $conn->rollback(); // Rollback transaction nếu có lỗi
                echo "<script>alert('Có lỗi xảy ra trong quá trình import: " . implode(", ", $errorMessages) . "'); window.location.href='index.php';</script>";
            } else {
                $conn->commit(); // Commit transaction nếu không có lỗi
                echo "<script>alert('Cập nhật dữ liệu Excel thành công! Đã thêm: $countInserted, Đã cập nhật: $countUpdated'); window.location.href='index.php';</script>";
            }

            // Đóng tất cả các prepared statements
            $checkStmt->close();
            $insertStmt->close();
            $updateStmt->close();
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction nếu có lỗi
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
