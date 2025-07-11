<title>Chính Sách Bảo Hành</title>

<?php
require "header.php" ;
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">


	<!-- Main content -->
<div class="letter-content">
<p><strong style="font-size: 1.5em; color: #FF0000;">Chính Sách Bảo Hành</strong></p>    
        <h4>1. Thời Hạn Bảo Hành: </h4>
        <p><b>03 năm</b> kể từ ngày xuất hóa đơn bán hàng</p>
        
        <h4>2. Nơi Nhận Bảo Hành: </h4>
        <p>Quý khách có thể gửi bảo hành tại các <b>đại lý uỷ quyền ROSA</b> trên toàn quốc.</p>
        <a href="/DSDL.php"> Xem danh sách đại lý ROSA</a>
        <br><br>
        
        <h4>3. Điều Kiện Bảo Hành:</h4>
        <h5>3.1. Khách hàng được hưởng chế độ bảo hành cho sản phảm nếu thỏa mãn các điều kiện sau:</h5>
        <p>– Thiết bị còn trong thời hạn bảo hành.</p>
        <!--<P>– Đổi trả trong vòng 7 ngày kể từ ngày mua nếu có lỗi từ nhà sản xuất.</P>-->
        <!--<p>– Tem/Phiếu bảo hành, nhãn hiệu Rosa, Code, Serial, Part number phải còn nguyên vẹn</p>-->
        <p>– Thông tin số Serial/Part number trên sản phẩm phải rõ ràng, không bị tẩy xóa, sửa chữa.</p>
        <!--<p>– Thông tin ghi trên Tem/Phiếu bảo hành phải rõ ràng, không bị tẩy xóa, sửa chữa.</p>-->
        <h5>3.2. Phòng Bảo hành & Sửa chữa Công ty Toàn Việt từ chối bảo hành những trường hợp sau:</h5>
        <p>– Sản phẩm được sử dụng sai hướng dẫn hoặc lắp đặt không đúng quy cách.</p>
        <!--<p>– Tem/Phiếu bảo hành, Code, Serial, Part number trên sản phẩm không hợp lệ, bị rách, bị tẩy xoá hoặc giả mạo.</p>-->
        <p>– Thông tin số Serial/Part number trên sản phẩm không hợp lệ, bị tẩy xoá hoặc giả mạo.</p>
        <p>– Các sản phẩm bị thiệt hại về mặt vật lý (như bị cháy nổ, nứt, gãy, thiếu, sứt mẻ, ướt, cong,…)</p>
        <p>– Các sản phẩm bị tháo và sửa chữa bởi các cá nhân, kỹ thuật không phải là nhân viên Công ty Toàn Việt hoặc Đại lý được ủy quyền.</p>
        
        <h4>3. Thời Gian Trả Bảo hành: </h4>
        <p>Chúng tôi cam kết trả hàng bảo hành cho Quý khách trong 03 ngày làm việc kể từ ngày chúng tôi nhận được sản phẩm. Trong trường hợp quá 03 ngày,
            chúng tôi sẽ xuất ứng cho khách hàng mượn dùng tạm hoặc đổi sang 01 thiết bị khác có tính năng kỹ thuật tương đương.</p>
        
        

</section>
</div>
<style>
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.main_nav_container {
    width: 100%;
    background: #FFFFFF;
    margin-top: 0; 
    box-shadow: 0 0 16px rgba(0, 0, 0, 0.15);
    position: fixed;
    height: 120px;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999; /* Đảm bảo phần header luôn nằm trên */
}

.letter-content {
    background-color: #ffffff;
    padding: 35px;
    border-radius: 8px;
    max-width: 930px;
    margin: 0 auto;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
  /*  margin-top: 122px; /* Thêm khoảng cách từ top để tránh bị che bởi header */
}

.letter-content p {
    font-size: 16px;
    line-height: 1.6;
    color: #333;
    margin-bottom: 15px;
}

.letter-content strong {
    font-size: 18px;
    color: #000;
    display: block;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    .letter-content {
        padding: 20px;
        max-width: 100%;
        margin-top: 180px; /* Tăng khoảng cách nếu cần */
    }

    .letter-content p {
        font-size: 14px;
    }

    .letter-content strong {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 5px;
    }

    .letter-content {
        padding: 15px;
        margin-top: 116px; /* Thêm khoảng cách để tránh bị che */
    }

    .letter-content p {
        font-size: 12px;
    }

    .letter-content strong {
        font-size: 14px;
    }
}


</style>
	<!-- Footer -->
<?php
	require "footer.php" ;
?>
