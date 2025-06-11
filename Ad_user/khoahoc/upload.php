<?php
function dbconnect () {
    $conn = new mysql ("location", "root", "", "study");
    if($conn -> connect_error) {
        die ("kết nối cơ sở dữ liệu thất bại :". $conn ->connect_error);
    }
    return $conn; 
}

?>

