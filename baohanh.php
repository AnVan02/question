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
            if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
                $serial_sp = trim($_POST['search']);
                $conn = dbconnect();
                $checkStmt = $conn->prepare("SELECT SOSERI_SP, SOSERI_PC, TENSP FROM baohanh WHERE SOSERI_SP = ?");
                $checkStmt->bind_param("s", $serial_sp);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                if ($checkResult->num_rows > 0) {
                    $products = [];
                    while ($row = $checkResult->fetch_assoc()) {
                        $products[] = $row;
                    }
                    $checkStmt->close();

                    if (count($products) > 1) {
                        $uniqueTENSP = array_unique(array_column($products, 'TENSP'));
                        if (count($uniqueTENSP) > 1) {
                            echo '<p class="error-message">Mã ' . htmlspecialchars($serial_sp) . ' có nhiều sản phẩm với tên khác nhau. Vui lòng kiểm tra lại.</p>';
                        } else {
                            displayProductInfoBySerial($conn, $serial_sp);
                        }
                    } else {
                        $soseri_pc = $products[0]['SOSERI_PC'];
                        $tensp = $products[0]['TENSP'];

                        if (empty($tensp)) {
                            echo '<p class="error-message">Sản phẩm với mã ' . htmlspecialchars($serial_sp) . ' không có tên sản phẩm.</p>';
                        } else {
                            displayProductInfo($conn, $soseri_pc);
                        }
                    }
                } else {
                    echo '<p class="error-message">Không tìm thấy thông tin bảo hành cho mã ' . htmlspecialchars($serial_sp) . '.</p>';
                }
                $conn->close();
            }

            function displayProductInfoBySerial($conn, $serial_sp) {
                $stmt = $conn->prepare("
                    SELECT SOSERI_SP, SOSERI_PC, LOAI, TENSP, MASP, NGAYXUAT, THOIHANBH
                    FROM baohanh
                    WHERE SOSERI_SP = ?
                    ORDER BY 
                        CASE 
                            WHEN LOWER(LOAI) LIKE '%pc%' THEN 0
                            ELSE 1
                        END, NGAYXUAT ASC
                ");
                $stmt->bind_param("s", $serial_sp);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '<div class="result-container">';
                    echo '<h3>Thông Tin Bảo Hành</h3>';
                    echo '<div class="table-responsive">';
                    echo '<table class="warranty-table">';
                    echo '<thead><tr>
                            <th>Số Serial SP</th>
                            <th>Loại</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Mã Hàng</th>
                            <th>Ngày Xuất</th>
                            <th>Thời Hạn BH</th>
                          </tr></thead><tbody>';
                    $firstRow = true;
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td class="serial">' . htmlspecialchars($row["SOSERI_SP"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["LOAI"]) . '</td>';
                        echo $firstRow ? '<td>' . htmlspecialchars($row["TENSP"]) . '</td>' : '<td></td>';
                        echo '<td>' . htmlspecialchars($row["MASP"]) . '</td>';
                        echo '<td>' . date("d-m-Y", strtotime($row["NGAYXUAT"])) . '</td>';
                        echo '<td>' . htmlspecialchars($row["THOIHANBH"]) . ' tháng</td>';
                        echo '</tr>';
                        $firstRow = false;
                    }
                    echo '</tbody></table></div></div>';
                }
                $stmt->close();
            }

            function displayProductInfo($conn, $soseri_pc) {
                $stmt = $conn->prepare("
                    SELECT SOSERI_SP, SOSERI_PC, LOAI, TENSP, MASP, NGAYXUAT, THOIHANBH
                    FROM baohanh
                    WHERE SOSERI_PC = ?
                    ORDER BY 
                        CASE 
                            WHEN LOWER(LOAI) LIKE '%pc%' THEN 0
                            ELSE 1
                        END, NGAYXUAT ASC
                ");
                $stmt->bind_param("s", $soseri_pc);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '<div class="result-container">';
                    echo '<h3>Thông Tin Bảo Hành</h3>';
                    echo '<div class="table-responsive">';
                    echo '<table class="warranty-table">';
                    echo '<thead><tr>
                            <th>Số Serial SP</th>
                            <th>Loại</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Mã Hàng</th>
                            <th>Ngày Xuất</th>
                            <th>Thời Hạn BH</th>
                          </tr></thead><tbody>';
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td class="serial">' . htmlspecialchars($row["SOSERI_SP"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["LOAI"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["TENSP"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["MASP"]) . '</td>';
                        echo '<td>' . date("d-m-Y", strtotime($row["NGAYXUAT"])) . '</td>';
                        echo '<td>' . htmlspecialchars($row["THOIHANBH"]) . ' tháng</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table></div></div>';
                }
                $stmt->close();
            }
            
            ?>
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
   
  
