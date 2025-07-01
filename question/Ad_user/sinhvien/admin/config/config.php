<?php
    // Kết nối cơ sở dữ liệu
    $mysqli = new mysqli ("localhost","root","", "student");

    // Check connection
    if ($mysqli->connect_errno) {
        die("kết nối cơ sơ dữ liệu thất bại: ".$mysqli ->connect_error);
    }
// Set charset to UTF-8
$mysqli->set_charset("utf8"); 

?>
