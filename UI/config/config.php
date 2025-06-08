<?php
    // local

    $mysqli = new mysqli ("localhost", "root","","study");

    //kiêm tra connection 
    if ($mysqli -> connect_errno) {
        die("Kết nối cơ sơ dữ liêu thất bại: ". $mysqli -> connect_error_error);

    }

    $mysqli -> set_charset("utf8");

?>