<!-- bảng có khung             -->
# border-left: 1px solid #e6e6e6;

sql cập nhập nó nhã về số 0 thì dùng
# ALTER TABLE `login`
# MODIFY COLUMN `Id` INT(11) NOT NULL AUTO_INCREMENT,
# ADD PRIMARY KEY (`Id`);

<!-- lấy dữ liệu id khoá học từ bảng khoa_hoc  -->
# sql ="SELECT khoa_hoc FROM khoa_hoc WHERE id= số "id khoa_hoc"

<!-- kết nối sql  -->

# function dbconnect () {
#    $conn = new mysql ("localhost" , "root" ,"", "student");
#    if($conn -> connect_error) {
#        die("lỗi kết nối CSDL: ".$conn -> connect_error);
#    }
#    return $conn 
# }

<!-- // quyền truy cập vào vào bài 1  -->

# // Kiểm tra quyền truy cập
# if ($student_id == 1 ) {
#   // Cho phép truy cập
#  } else {
#   echo "Bạn không có quyền truy cập khoá học này";
#   exit();
}

<!-- // hiển thị thông báo  -->

#    $stmt = $conn -> prepare ("SELECT ten_test FROM test WHERE id_test = ?");
#    $stmt ->bind_param ("i", $id_test);
#    $stmt -> execute ();
#   $result = $stmt -> get_result ();

#   if ($row = $result -> fetch_assoc ()) {
#        $ten_test = $row ['ten_test'];
        
#    } else {
#        echo "<script> 
#                alert ('Không tim thấy bài test cho ID = $id_test');
#                windown.location.href ='login.php';
#            </script>";
#    exit();
#    }

<!-- // kiêm tra  -->
-	nêu nhập id_test sai thì thông báo khi nhập sai 
-   kiểm tra thông tin truy cập từng tài khoản 
-   
