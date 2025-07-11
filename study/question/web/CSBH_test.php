<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nextcloud: Giải pháp đám mây an toàn và bảo mật</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.7;
            color: #333;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .news-detail {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }

        .news-detail h3 {
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            color: #222;
            margin-bottom: 30px;
        }

        .news-detail h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #000;
            margin: 30px 0 15px;
        }

        .news-detail p {
            font-size: 19px;
            margin: 10px 0;
            /* color: #444; */
        }
        
        .news-detail a {
            font-size: 19px;
            margin: 10px 0;
            color: #007bff;
            text-decoration: none; /* Loại bỏ gạch chân */
            
        }

        .news-detail ol {
            margin: 15px 0 15px 20px;
            padding-left: 20px;
        }

        .news-detail ol p {
            margin: 10px 0;
        }

        .news-detail img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px auto;
            display: block;
        }

        .news-detail b {
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .news-detail {
                padding: 20px;
            }

            .news-detail h3 {
                font-size: 1.5rem;
            }

            .news-detail h4 {
                font-size: 1.2rem;
            }

            .news-detail p {
                font-size: 1rem;
            }

            .news-detail img {
                margin: 15px auto;
            }
        }

        @media (max-width: 480px) {
            .news-detail h3 {
                font-size: 1.3rem;
            }

            .news-detail h4 {
                font-size: 1.1rem;
            }

            .news-detail p {
                font-size: 0.9rem;
            }

            .news-detail ol {
                margin-left: 15px;
                padding-left: 15px;
            }
        }
    </style>
</head>
<body>
    <?php require "header.php" ?>

    <div class="container">
        <section class="news-detail">
            <h3>CHINH SÁCH BẢO HÀNH</h3>

            <h4>1. Thời hạn bảo hành </h4>
            
            <p><b>Bảo hành 03 Năm </b>kể từ ngày xuất hoá đơn bán hàng</p>

            <h4>2. Nơi nhận bảo hành </h4>

            <p>Quý khách có thể gửi bảo hành tại các <b>đại lý uỷ quyền ROSA</b> trên toàn quốc</p>
            <a href="/DSDL.php" style='color:red'> Xem danh sách đại lý ROSA</a>

            <h4>3. Điều kiện bảo hành </h4>
            <ol>
                <h5>3.1 Khách hàng sẽ được hưởng chế độ bảo hành cho sản phẩm nếu thoả mản các điều kiện sau:</h5>

                <p>- Thiết bị còn trong thời hạ bảo hành</p>

                <!-- <p>- Đổi trả trong vòng 7 ngày kể từ ngày mua nếu có lỗi từ nhà sản xuất .</p> -->

                <!-- <p>– Tem/Phiếu bảo hành, nhãn hiệu Rosa, Code, Serial, Part number phải còn nguyên vẹn</p> -->

                <p>- Thông tin số Serial/Part number trên sản phẩm phải rõ ràng, không bị tẩy xoá ,sữa chữa</p>
                
                <!-- <p>– Thông tin ghi trên Tem/Phiếu bảo hành phải rõ ràng, không bị tẩy xóa, sửa chữa.</p> -->

                <h5>3.2 Phòng bảo hành & sửa chưa Công ty toàn việt từ chối bảo hành các trường hợp sau</h5>

                <p>- Sản phẩm được sử dụng sai hướng dẫn hoăc lắp đặt không đúng cách </p>

                <!-- <p>– Tem/Phiếu bảo hành, Code, Serial, Part number trên sản phẩm không hợp lệ, bị rách, bị tẩy xoá hoặc giả mạo.</p> -->

                <p>- Thông tin số Serial/ Part number trên sản phẩm không hợp bị tẩy xoá hoặc giả mạo</p>

                <p>- Các sản phẩm bị thiệt hại vè mặt vật lý (như bị cháy nổ, nứt, gãy, thiếu ,sức mẻ , ướt, cong,...)</p>

                <p>– Các sản phẩm bị tháo và sửa chữa bởi các cá nhân, kỹ thuật không phải là nhân viên Công ty Toàn Việt hoặc Đại lý được ủy quyền.</p>
            </ol>

            <h4>4. Thời gian trả bảo hành: </h4>

            <p>Chúng tôi cam kết trả hàng bảo hành cho quy khách trong 03 ngày làm việc kể từ ngay chúng tôi nhận được sản phẩm. Trong trường hợp quá 03 ngày, chúng tôi sẽ xuất ứng
                cho khách hàng mượn tạm hoặc đổi sang 01 thiết bị khác có tính năng kỹ thuật tương đương.    
            </p>

           
        </section>
    </div>

    <?php require "footer.php" ?>
</body>
</html>