<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "header.php";

function dbconnect(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "database";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}

function checkAndRemoveExpired($SOSERI_SP, $currentDate){
    $conn = dbconnect();
    $deleteSql = "DELETE FROM baohanh WHERE SOSERI_SP = ? AND DATE_ADD(NGAYXUAT, INTERVAL THOIHANBH MONTH) < ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("ss", $SOSERI_SP, $currentDate);
    $stmt->execute();
    $stmt->close();

    $checkSql = "SELECT SOSERI_SP, SOSERI_PC, LOAI, TENSP, MASP, NGAYXUAT, THOIHANBH 
                FROM baohanh WHERE SOSERI_SP = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $SOSERI_SP);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
    $result = $stmt->get_result();


}
?>

<div class="main-content fl-right">
    <div class="section" id="detail-blog-wp">
        <div class="section-head clearfix">
            <h3 class="section-title">Tra cứu bảo hành</h3>
        </div>
        <div class="section-detail">
            <div class="search-container">
                <form name="warranty-search" action="#" method="POST" onsubmit="return validateForm()">
                    <input name="search" type="text" placeholder="Nhập mã serial sản phẩm" 
                           class="search-input" required/>
                    <button type="submit" class="search-button">Tra cứu</button>
                </form>
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
                            echo '<p class="error-message">Mã ' . htmlspecialchars($serial_sp) . 
                                 ' có nhiều sản phẩm với tên khác nhau. Vui lòng kiểm tra lại.</p>';
                        } else {
                            displayProductInfoBySerial($conn, $serial_sp);
                        }
                    } else {
                        $soseri_pc = $products[0]['SOSERI_PC'];
                        $tensp = $products[0]['TENSP'];

                        if (empty($tensp)) {
                            echo '<p class="error-message">Sản phẩm với mã ' . htmlspecialchars($serial_sp) . 
                                 ' không có tên sản phẩm.</p>';
                        } else {
                            displayProductInfo($conn, $soseri_pc);
                        }
                    }
                } else {
                    echo '<p class="error-message">Không tìm thấy thông tin bảo hành cho mã ' . 
                         htmlspecialchars($serial_sp) . '.</p>';
                }
                $conn->close();

            }
            
            
            function displayProductInfoBySerial($conn, $serial_sp) {
                $stmt = $conn->prepare("
                    SELECT SOSERI_SP, SOSERI_PC, LOAI, TENSP, NGAYXUAT, THOIHANBH
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
                            <th>Ngày Xuất</th>
                            <th>Thời Hạn BH</th>
                          </tr></thead><tbody>';
                    $firstRow = true;
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td class="serial">' . htmlspecialchars($row["SOSERI_SP"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["LOAI"]) . '</td>';
                        echo $firstRow ? '<td>' . htmlspecialchars($row["TENSP"]) . '</td>' : '<td></td>';
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
                    SELECT SOSERI_SP, SOSERI_PC, LOAI, TENSP, NGAYXUAT, THOIHANBH
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
                            <th>Ngày Xuất</th>
                            <th>Thời Hạn BH</th>
                          </tr></thead><tbody>';

                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td class="serial">' . htmlspecialchars($row["SOSERI_SP"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["LOAI"]) . '</td>';
                        echo '<td>' . htmlspecialchars($row["TENSP"]) . '</td>';
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
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
}

.main-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.section-title {
    color: #1e90ff;
    font-size: 24px;
    margin-bottom: 20px;
    text-align: center;
}

.search-container {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s;
}

.search-input:focus {
    border-color: #1e90ff;
}

.search-button {
    padding: 12px 24px;
    background: #ff3333;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

.search-button:hover {
    background: #cc0000;
}

.result-container {
    margin: 20px 0;
}

.result-container h3 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.warranty-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.warranty-table th,
.warranty-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

.warranty-table th {
    background: #ff3333;
    color: white;
    text-transform: uppercase;
    font-size: 14px;
}

.warranty-table td.serial {
    font-weight: bold;
    color: #ff3333;
}

.warranty-table tr:nth-child(even) {
    background: #f9f9f9;
}

.warranty-table tr:hover {
    background: #f1f1f1;
}

.error-message {
    color: #ff3333;
    text-align: center;
    font-weight: bold;
    margin: 20px 0;
    padding: 10px;
    background: #ffe6e6;
    border-radius: 4px;
}

/* Mobile Styles */
@media screen and (max-width: 768px) {
    .main-content {
        padding: 15px;
    }

    .section-title {
        font-size: 20px;
    }

    form {
        flex-direction: column;
        gap: 15px;
    }

    .search-input,
    .search-button {
        width: 100%;
        font-size: 14px;
    }

    .search-container {
        padding: 15px;
    }

    .warranty-table {
        font-size: 14px;
    }

    .warranty-table th,
    .warranty-table td {
        padding: 8px;
        font-size: 12px;
    }

    .result-container h3 {
        font-size: 18px;
    }
}

@media screen and (max-width: 480px) {
    .warranty-table {
        display: block;
    }

    .warranty-table thead {
        display: none;
    }

    .warranty-table tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #eee;
        border-radius: 4px;
        padding: 10px;
    }

    .warranty-table td {
        display: block;
        text-align: left;
        border: none;
        position: relative;
        padding-left: 50%;
    }

    .warranty-table td:before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 45%;
        font-weight: bold;
        color: #333;
    }

    .warranty-table td.serial {
        color: #ff3333;
    }
}
</style>

<script>
function validateForm() {
    const input = document.forms["warranty-search"]["search"].value.trim();
    if (input === "") {
        alert("Vui lòng nhập mã serial sản phẩm!");
        return false;
    }
    return true;
}
</script>

<?php require 'footer.php'; ?>