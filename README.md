bảng có khung            
# border-left: 1px solid #e6e6e6;

sql cập nhập nó nhã về số 0 thì dùng
# ALTER TABLE `login`
# MODIFY COLUMN `Id` INT(11) NOT NULL AUTO_INCREMENT,
# ADD PRIMARY KEY (`Id`);

lấy dữ liệu id khoá học từ bảng khoa_hoc 
# sql ="SELECT khoa_hoc FROM khoa_hoc WHERE id= số "id khoa_hoc"

kết nối sql 

# function dbconnect () {
#    $conn = new mysql ("localhost" , "root" ,"", "student");
#    if($conn -> connect_error) {
#        die("lỗi kết nối CSDL: ".$conn -> connect_error);
#    }
#    return $conn 
# }

// quyền truy cập vào vào bài 1 

# // Kiểm tra quyền truy cập
# if ($student_id == 1 ) {
#   // Cho phép truy cập
#  } else {
#   echo "Bạn không có quyền truy cập khoá học này";
#   exit();
}
