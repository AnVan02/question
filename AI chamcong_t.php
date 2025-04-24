<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HỆ THỐNG CHẤM CÔNG BẰNG NHẬN DIỆN KHUÔN MẶT SỬ DỤNG IP CAMERA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
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
            font-size: 1rem;
            margin: 10px 0;
            color: #444;
        }

        .news-detail ol {
            margin: 15px 0 15px 20px;
            padding-left: 20px;
        }

        .news-detail ol p {
            margin: 8px 0;
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
                font-size: 0.9rem;
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
                font-size: 0.85rem;
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
            <h3>HỆ THỐNG CHẤM CÔNG BẰNG NHẬN DIỆN KHUÔN MẶT SỬ DỤNG IP CAMERA</h3>

            <h4>1. Công nghệ đột phá của giải pháp</h4>
            <p>
                Ứng dụng nền tảng Thị giác máy tính (Computer Vision) và Trí tuệ nhân tạo (AI), hệ thống mang đến giải pháp chấm công hiện đại, thay thế, khắc phục toàn diện phương pháp truyền thống như thẻ RFID (khó tránh gian lận), vân tay (khả năng mất dấu, trùng lặp cao) và bảng chấm công khuôn mặt treo tường (chi phí, độ trễ cao). Hệ thống linh hoạt tận dụng camera giám sát sẵn có trong doanh nghiệp, hoặc dễ dàng triển khai với IP Camera phổ thông, giúp giảm chi phí, thời gian chấm công, quản lý hiệu quả đồng thời tạo nền tảng cho một môi trường làm việc hiện đại và minh bạch hơn.
            </p>
            <img src="../assets/upload/study/chamcong.jpg" alt="Hệ thống chấm công">

            <h4>2. Lợi ích khác biệt dành cho doanh nghiệp</h4>
            <ol>
                <p><b>Tốc độ nhanh chóng:</b> Phát hiện và nhận diện nhân viên dưới 0.5 giây, khoảng cách xa 1–5m.</p>
                <p><b>Đa nhiệm thông minh:</b> Nhận diện chính xác +10 người/khung hình cùng lúc, sử dụng song song nhiều camera trong quá trình chấm công.</p>
                <p><b>An ninh tối ưu:</b> Xác thực sinh trắc đặc điểm khuôn mặt, ngăn chặn tốt hành vi mạo danh.</p>
                <p><b>Linh hoạt triển khai:</b> Vận hành trên mọi IP Camera tiêu chuẩn từ 2MP hoặc tích hợp vào hệ thống Camera có sẵn của doanh nghiệp.</p>
                <p><b>Báo cáo thời gian thực:</b> Tự động đồng bộ dữ liệu, gửi thông báo tức thời qua Nextcloud Talk và xuất báo cáo Excel 1-click.</p>
                <p><b>Quản lý dữ liệu chấm công:</b> Hồ sơ chấm công kèm hình ảnh/xác thực, sẵn sàng kiểm tra/đối chiếu khi cần.</p>
                <p><b>Theo dõi hệ thống dễ dàng:</b> Theo dõi và quản lý hệ thống trực tiếp trên Web App hoặc Mobile App.</p>
            </ol>
            <img src="../assets/upload/study/chamcong1.jpg" alt="Lợi ích hệ thống">

            <h4>3. Quy trình dễ dàng</h4>
            <ol>
                <p><b>Bước 1</b> - Khởi tạo: Nhân viên gửi yêu cầu chấm công qua ứng dụng Nextcloud Talk.</p>
                <p><b>Bước 2</b> - Xác thực: Hệ thống tự động kiểm tra lịch làm việc → Kích hoạt Camera nếu hợp lệ.</p>
                <p><b>Bước 3</b> - Nhận diện: Phân tích các đặc điểm nhận dạng khuôn mặt trong 0.5s – 0.7s, ghi nhận múi giờ thực tế.</p>
                <p><b>Bước 4</b> - Hoàn tất: Lưu trữ nội bộ an toàn, thông báo xác nhận cho nhân viên/quản trị.</p>
                <p><b>Bước 5</b> - Xuất kết quả: Quản trị viên sử dụng phần mềm để xuất báo cáo tổng kết chấm công dưới dạng file Excel.</p>
            </ol>

            <h4>4. Yêu cầu kỹ thuật</h4>
            <ol>
                <p>Hệ thống yêu cầu kết nối mạng ổn định.</p>
            </ol>

            <h4>5. Cam kết hiệu quả</h4>
            <ol>
                <p>Giảm 40% thời gian quản lý chấm công</p>
                <p>Tối ưu 30% chi phí phần cứng</p>
                <p>Tỉ lệ chính xác cao 99.7%</p>
            </ol>
        </section>
    </div>

    <?php require "footer.php" ?>
</body>
</html>