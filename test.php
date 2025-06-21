<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "header.php";

function dbconnect(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "123";

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
                    <input name="search" type="text" placeholder="Nhập mã serial sản phẩm" class="search-input" required />
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
    </div>
</div>

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
