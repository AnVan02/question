<?php
function dbconnect() {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "student"; // <-- thay bằng tên cơ sở dữ liệu thực tế của bạn

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}
?>
