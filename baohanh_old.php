<?php
function dbconnect(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "123";
    
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

function checkAndRemoveExpired($serial, $currentDate){
    $conn = dbconnect();
    
    // Xóa sản phẩm hết hạn
    $deleteSql = "DELETE FROM sanpham WHERE SoSerial = ? AND NgayXuat + INTERVAL ThoiHanBH MONTH < ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("ss", $serial, $currentDate);
    $stmt->execute();

    // nêu sản phẩm hết hạn sẽ xoá khỏi sql
    $sql = "DELETE FROM sanpham WHERE MaHang,TenHang,SoSerial,NgayXuat,ThoiHanBh = ?";
    
    // Kiểm tra nếu sản phẩm tồn tại
    $checkSql = "SELECT MaHang, TenHang, SoSerial, NgayXuat, ThoiHanBH FROM sanpham WHERE SoSerial = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $serial);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();

    return $result;
}
?>
<div class="main-content fl-right">
    <div class="section" id="detail-blog-wp">
        <div class="section-head clearfix">
            <h3 class="section-title">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                        <p><h3 style="color:dodgerblue;">Tra cứu thông tin sản phẩm </h3></p>
                    </div>
                </div>
            </h3>
        </div>
        <div class="section-detail">
            <span class="create-date">
                <div class="border border-zinc-300 p-4 rounded mb-4">
                    <form name="test" action="#" method="POST">
                        <input name="search" type="text" placeholder="NHẬP MÃ SERIAL CẦN TÌM" class="border border-zinc-300 p-2 rounded w-full" style="width: 80%"/>
                        <button type="submit" class="bg-blue-500 text-red p-2 rounded" style="background-color:#FF3333; border: none; color:#FFFFFF;width: 15%">Tra cứu</button>
                    </form>
                </div>
            </span>
        </div>
        <?php
        if (isset($_POST['search'])) {
            $search = $_POST['search'];
            $currentDate = date('Y-m-d'); // Lấy ngày hiện tại
            $result = checkAndRemoveExpired($search, $currentDate);
            $flag = 0;

            if ($result->num_rows > 0) {
                // output data of each row
                while ($row = $result->fetch_assoc()) {
                    $flag = 1;

                    // Định dạng kết quả tìm kiếm với màu sắc và kiểu dáng
                    echo '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px; background-color: #F9F9F9;">';
                    echo '<p><strong style="color:dodgerblue;">Số Serial:</strong> <span style="font-weight: bold; color: #FF0000;">' . htmlspecialchars($row["SoSerial"]) . '</span></p>';
                    echo '<p><strong style="color:dodgerblue;">Mã Hàng:</strong> <span style="color: #000000;">' . htmlspecialchars($row["MaHang"]) . '</span></p>';
                    echo '<p><strong style="color:dodgerblue;">Tên sản phẩm:</strong> <span style="color: #000000;">' . htmlspecialchars($row["TenHang"]) . '</span></p>';
                    echo '<p><strong style="color:dodgerblue;">Ngày Xuất:</strong> <span style="color: #000000;">' . htmlspecialchars($row["NgayXuat"]) . '</span></p>';
                    echo '<p><strong style="color:dodgerblue;">Thời hạn bảo hành:</strong> <span style="color: #000000;">' . htmlspecialchars($row["ThoiHanBH"]) . ' tháng</span></p>';
                    echo '</div>';
                }
            }
            if ($flag == 0) {
                echo '<p style="color: #FF0000; font-weight: bold;">Chưa có thông tin liên quan hoặc mã <span style="color: #0000FF;">' . htmlspecialchars($search) . '</span> đã hết hạn bảo hành </p>';
            }
        }
        ?>
    </div>
</div>
<style>
     /* CSS cho toàn bộ form */
form {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

form input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 80%;
    font-size: 16px;
}

form button {
    padding: 10px;
    background-color: #FF3333;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    width: 15%;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #e60000;
}

/* CSS cho phần thông tin sản phẩm */
.section-detail {
    margin-top: 20px;
}

.section-title h3 {
    font-size: 24px;
    color: dodgerblue;
    margin-bottom: 10px;
}

.section-detail .create-date {
    margin-bottom: 20px;
}

.border {
    border: 1px solid #e0e0e0;
    padding: 15px;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.border input {
    font-size: 16px;
}

.result-box {
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    background-color: #F9F9F9;
}

.result-box p {
    font-size: 16px;
    margin: 5px 0;
}

.result-box p strong {
    color: dodgerblue;
}

.result-box p span {
    font-weight: bold;
    color: #FF0000;
}

.no-result {
    color: #FF0000;
    font-weight: bold;
}

/* CSS cho khối chứa form */
.flex {
    display: flex;
}

.justify-between {
    justify-content: space-between;
}

.items-center {
    align-items: center;
}

.mb-4 {
    margin-bottom: 20px;
}

/* Điều chỉnh khi màn hình nhỏ */
@media screen and (max-width: 768px) {
    form {
        flex-direction: column;
    }

    form input,
    form button {
        width: 100%;
        margin-bottom: 10px;
    }

    form button {
        width: 100%;
    }
}
         

    </style>
   
  
