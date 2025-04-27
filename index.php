<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>ROSA</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if(isset($_SESSION["message"])): ?>
        <script>
            function message() {
                window.alert("<?php echo $_SESSION["message"]; ?>");
            }
            window.onload = message;
        </script>
    <?php endif; ?>
    <?php require "common.php"; ?>
    <?php require "header.php"; ?>
    <?php require "popupkm.php"; ?>
</head>
<body>
    <!-- Banner -->
    <div class="banner">
        <div class="container">
            <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="assets/images/bannerrosa2.jpg" class="d-block w-100" alt="Gaming Banner" onclick="window.location.href='product.php#gaming'">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/banner27.03.jpg" class="d-block w-100" alt="Promotion Banner" onclick="window.location.href='product.php#gaming'">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/images/bannerrosa3.jpg" class="d-block w-100" alt="Office Banner" onclick="window.location.href='product.php#vanphong'">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>

    <!-- News Section -->
    <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "database";
        $conn = new mysqli($servername, $username, $password, $dbname);
        $conn->set_charset("utf8");
        if ($conn->connect_error) {
            die("Kết nối thất bại: " . $conn->connect_error);
        }
        $sql = "SELECT article_tag, article_title, article_date, article_image, article_link, article_content
                FROM article 
                ORDER BY article_date DESC 
                LIMIT 1";
        $result = $conn->query($sql);
    ?>
    <section class="news-section container my-5">
        <h3 class="section-title">TIN TỨC & KHUYẾN MÃI</h3>
        <div class="news-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="news-card">
                    <a href="/tintuc/<?= htmlspecialchars($row['article_link'], ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="/tintuc_test/admin/modules/blog/uploads/<?= htmlspecialchars($row['article_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="News Image">
                    </a>
                    <div class="news-content">
                        <p class="news-tag">Tin tức</p>
                        <h4 class="news-title">
                            <a href="/tintuc/<?= htmlspecialchars($row['article_link'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($row['article_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </h4>
                    </div>
                </div>
            <?php endwhile; ?>
            <div class="news-card">
                <a href="product.php#vanphong">
                    <img src="assets/images/456.jpg" alt="ROSA VĂN PHÒNG">
                </a>
                <div class="news-content">
                    <p class="news-tag">Khuyến mãi</p>
                    <h4 class="news-title"><a href="product.php#vanphong">ROSA VĂN PHÒNG</a></h4>
                    <p>Cấu hình mạnh mẽ, hiệu suất tối ưu cho công việc văn phòng.</p>
                </div>
            </div>
            <div class="news-card">
                <a href="product.php#ai">
                    <img src="assets/images/123.jpg" alt="ROSA AI">
                </a>
                <div class="news-content">
                    <p class="news-tag">Khuyến mãi</p>
                    <h4 class="news-title"><a href="product.php#ai">ROSA AI</a></h4>
                    <p>Giải pháp tối ưu cho lập trình AI, bảo hành 3 năm.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Overview Section -->
    <section class="product-section container my-5">
        <h3 class="section-title">DÒNG MÁY PHÙ HỢP VỚI BẠN</h3>
        <p>Khám phá các dòng máy ROSA phù hợp với nhu cầu sử dụng của bạn</p>
        <div class="product-grid">
            <div class="product-card">
                <a href="product.php#vanphong"><img src="assets/images/name (3).jpg" alt="Office Background" class="large"></a>
                <a href="product.php#vanphong"><img src="assets/images/Rosa_Office_3-removebg-preview.png" alt="Office PC" class="small"></a>
                <h4>ROSA OFFICE</h4>
                <p>Hiệu suất ổn định, bảo mật cao cho công việc văn phòng.</p>
                <ul class="logo-list">
                    <li><img src="https://img.icons8.com/fluency/48/windows-11.png" alt="Windows"></li>
                    <li><img src="https://img.icons8.com/color/48/microsoft-powerpoint-2019--v1.png" alt="PowerPoint"></li>
                    <li><img src="https://img.icons8.com/color/48/microsoft-excel-2019--v1.png" alt="Excel"></li>
                    <li><img src="https://img.icons8.com/fluency/48/microsoft-word-2019.png" alt="Word"></li>
                </ul>
                <a href="product.php#vanphong" class="btn">KHÁM PHÁ</a>
            </div>
            <div class="product-card">
                <a href="product.php#ai"><img src="assets/images/deep-learning.jpg" alt="AI Background" class="large"></a>
                <a href="product.php#ai"><img src="assets/images/2.png" alt="AI PC" class="small"></a>
                <h4>ROSA AI / SERVER</h4>
                <p>Tối ưu cho lập trình AI với cấu hình mạnh mẽ.</p>
                <ul class="logo-list">
                    <li><img src="https://img.icons8.com/color/48/python--v1.png" alt="Python"></li>
                    <li><img src="https://img.icons8.com/color/48/visual-studio-code-2019.png" alt="VS Code"></li>
                    <li><img src="https://img.icons8.com/fluency/48/pytorch.png" alt="PyTorch"></li>
                    <li><img src="https://scikit-learn.org/stable/_static/favicon.ico" alt="Scikit-learn"></li>
                </ul>
                <a href="product.php#ai" class="btn">KHÁM PHÁ</a>
            </div>
            <div class="product-card">
                <a href="product.php#gaming"><img src="assets/images/name (6).jpg" alt="Gaming Background" class="large"></a>
                <a href="product.php#gaming"><img src="assets/images/case 510 (1).png" alt="Gaming PC" class="small"></a>
                <h4>ROSA GAMING</h4>
                <p>Hiệu suất vượt trội cho các tựa game yêu thích.</p>
                <ul class="logo-list">
                    <li><img src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/73206bd9-257c-4f50-b1f3-59a306e24084/di02q99-d9964ac7-2ca4-4a86-9a65-cc92fa8ebea5.png?token=..." alt="Garena"></li>
                    <li><img src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/c78bc3fc-9f08-47ca-81ae-d89055c7ec49/da3boqn-b579891b-64ec-4829-87fd-30ec09c5105f.png?token=..." alt="Free Fire"></li>
                    <li><img src="https://www.freepnglogos.com/uploads/apex-legends-logo-png/apex-legends-characters-circle-logo-transparent-png-24.png" alt="Apex Legends"></li>
                    <li><img src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/73206bd9-257c-4f50-b1f3-59a306e24084/dfnd3kn-3aaf12be-fed0-4e71-8e67-764a792c5849.png?token=..." alt="Game"></li>
                </ul>
                <a href="product.php#gaming" class="btn">KHÁM PHÁ</a>
            </div>
            <div class="product-card">
                <a href="product.php#mini"><img src="assets/images/name (4).jpg" alt="Mini Background" class="large"></a>
                <a href="product.php#mini"><img src="assets/images/1.2.png" alt="Mini PC" class="small"></a>
                <h4>ROSA MINI</h4>
                <p>Nhỏ gọn, mạnh mẽ cho công việc và giải trí.</p>
                <ul class="logo-list">
                    <li><img src="https://img.icons8.com/fluency/48/windows-11.png" alt="Windows"></li>
                    <li><img src="https://img.icons8.com/color/48/microsoft-powerpoint-2019--v1.png" alt="PowerPoint"></li>
                    <li><img src="https://img.icons8.com/color/48/microsoft-excel-2019--v1.png" alt="Excel"></li>
                    <li><img src="https://img.icons8.com/fluency/48/microsoft-word-2019.png" alt="Word"></li>
                </ul>
                <a href="product.php#mini" class="btn">KHÁM PHÁ</a>
            </div>
        </div>
    </section>

    <!-- Software Courses Section -->
    <section class="software-section container my-5">
        <h3 class="section-title">GIẢI PHÁP PHẦN MỀM & KHÓA HỌC</h3>
        <p>Khám phá các khóa học và phần mềm AI từ ROSA</p>
        <div class="software-container">
            <div class="software-content">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                PYTHON CƠ BẢN
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Khóa học Python cơ bản giúp bạn xây dựng nền tảng lập trình.</p>
                                <ul>
                                    <li>Học các khái niệm cốt lõi: biến, vòng lặp, hàm, cấu trúc dữ liệu.</li>
                                    <li>Làm chủ cú pháp Python và thư viện như NumPy, Pandas.</li>
                                </ul>
                                <a href="/courses/python-course.php" class="explore-link">Khám phá</a>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                THỊ GIÁC MÁY TÍNH
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Khám phá thuật toán tiên phong trong lĩnh vực Thị giác máy tính.</p>
                                <ul>
                                    <li>Phù hợp từ người mới bắt đầu đến chuyên gia.</li>
                                    <li>Xây dựng giải pháp thông minh cho bài toán thực tiễn.</li>
                                </ul>
                                <a href="/courses/yolo-course.php" class="explore-link">Khám phá</a>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                PHẦN MỀM QUẢN TRỊ DOANH NGHIỆP
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Nextcloud - giải pháp cloud mã nguồn mở.</p>
                                <ul>
                                    <li>Lưu trữ tệp an toàn, quản lý dễ dàng.</li>
                                    <li>Kiểm soát truy cập và chia sẻ linh hoạt.</li>
                                </ul>
                                <a href="/courses/Nextcloud.php" class="explore-link">Khám phá</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="software-image">
                <img id="accordionImage" src="assets/images/education.jpg" alt="Software Illustration">
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section container my-5">
        <h3 class="section-title">CÂU HỎI LIÊN QUAN ĐẾN ROSA</h3>
        <p>Những câu hỏi phổ biến nhất về ROSA</p>
        <div class="faq-container">
            <div class="faq-left">
                <div class="tab-buttons">
                    <button class="active" onclick="setActive(this)">Tư vấn</button>
                    <button onclick="setActive(this)">Bảo hành</button>
                    <button onclick="setActive(this)">Cấu hình</button>
                    <button onclick="setActive(this)">Giao hàng</button>
                    <button onclick="setActive(this)">Phương thức thanh toán</button>
                    <button onclick="setActive(this)">Sản phẩm & linh kiện</button>
                </div>
                <p class="faq-contact-text">Không tìm thấy câu trả lời? Gửi câu hỏi cho chúng tôi!</p>
                <a href="https://zalo.me/909749126673606301" class="faq-contact-btn">Liên hệ</a>
            </div>
            <div class="faq-right">
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require "footer.php"; ?>

    <!-- JavaScript for FAQ and Accordion -->
    <script>
        const faqData = {
            "Tư vấn": [
                {"question": "Rosa hiện tại có chi nhánh không?", "answer": "Hiện tại Rosa chỉ có một địa chỉ tại: 150 Ter, đường Bùi Thị Xuân, phường Phạm Ngũ Lão, Quận 1, TP. Hồ Chí Minh"},
                {"question": "Giờ làm việc của Rosa?", "answer": "Thứ 2 đến Thứ 7, giờ hành chính. Xem chi tiết tại: <a href='https://rosacomputer.vn/'>rosacomputer.vn</a>"},
                {"question": "Rosa có bán trên sàn TMĐT không?", "answer": "Rosa phân phối qua cửa hàng online và đại lý. Mua trực tiếp tại showroom hoặc website: <a href='https://rosacomputer.vn/'>rosacomputer.vn</a>"}
            ],
            "Cấu hình": [
                {"question": "CPU AMD Ryzen 3 4350G", "answer": "4 nhân 8 luồng, 3.8GHz, cache L2: 2MB, L3: 4MB, tích hợp Radeon RX Vega 6."},
                {"question": "CPU Intel Core i5 14400", "answer": "10 nhân 16 luồng, turbo 4.7GHz, cache 20MB, hỗ trợ DDR5-4800."},
                {"question": "CPU AMD Ryzen 5 5600X", "answer": "6 nhân 12 luồng, 3.7GHz, cache L3: 32MB, tối ưu gaming."}
            ],
            "Phương thức thanh toán": [
                {"question": "ROSA hỗ trợ thanh toán nào?", "answer": "Chuyển khoản ngân hàng và tiền mặt."}
            ],
            "Bảo hành": [
                {"question": "Liên hệ bảo hành ở đâu?", "answer": "Xem tại: <a href='https://rosacomputer.vn/'>rosacomputer.vn</a> hoặc showroom: 150 Ter, Bùi Thị Xuân, Quận 1, TP. HCM"},
                {"question": "Máy bộ Rosa bảo hành bao lâu?", "answer": "3 năm theo quy định nhà sản xuất."},
                {"question": "SĐT trung tâm bảo hành?", "answer": "(028) 3926 0996"}
            ],
            "Giao hàng": [
                {"question": "Thời gian giao hàng?", "answer": "Tùy vị trí, Rosa giao nhanh nhất có thể."}
            ],
            "Sản phẩm & linh kiện": [
                {"question": "Rosa cung cấp sản phẩm gì?", "answer": "Máy bộ PC cho học tập, văn phòng, gaming, lập trình."},
                {"question": "Các dòng máy chính?", "answer": "ROSA AI, ROSA VĂN PHÒNG, ROSA GAMER"},
                {"question": "ROSA AI là gì?", "answer": "Máy thiết kế cho lập trình AI, cấu hình mạnh, cài sẵn công cụ AI."}
            ]
        };

        function setActive(button) {
            document.querySelectorAll(".tab-buttons button").forEach(btn => btn.classList.remove("active"));
            button.classList.add("active");
            const category = button.innerText;
            const faqList = faqData[category] || [];
            const faqContainer = document.getElementById("faqAccordion");
            faqContainer.innerHTML = faqList.map((item, index) => `
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#faq${index}">
                            ${item.question}
                        </button>
                    </h2>
                    <div id="faq${index}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">${item.answer}</div>
                    </div>
                </div>
            `).join('');
        }

        // Initialize FAQ
        document.addEventListener("DOMContentLoaded", () => {
            setActive(document.querySelector(".tab-buttons button"));
        });

        // Accordion Image Switch
        document.querySelectorAll('.accordion-button').forEach((button, index) => {
            button.addEventListener('click', () => {
                const imagePaths = [
                    'assets/images/python.jpg',
                    'assets/images/computer-vision.jpg',
                    'assets/images/nextcloud.jpg'
                ];
                document.getElementById('accordionImage').src = imagePaths[index] || 'assets/images/education.jpg';
            });
        });
    </script>

    <!-- CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .section-title {
            color: red;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 2px;
            background: red;
            margin: 5px auto;
        }

        /* Banner */
        .banner {
            margin: 20px 0;
        }
        .banner img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }
        .carousel-control-prev-icon, .carousel-control-next-icon {
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 10px;
        }

        /* News Section */
        .news-section {
            padding: 40px 0;
        }
        .news-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .news-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .news-card:hover {
            transform: translateY(-5px);
        }
        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .news-content {
            padding: 15px;
        }
        .news-tag {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        .news-title a {
            font-size: 18px;
            font-weight: bold;
            color: black;
            text-decoration: none;
        }
        .news-title a:hover {
            color: red;
        }
        .news-content p {
            font-size: 14px;
            color: #333;
            margin: 10px 0 0;
        }

        /* Product Section */
        .product-section {
            padding: 40px 0;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .product-card {
            position: relative;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: center;
            padding-bottom: 20px;
        }
        .product-card img.large {
            width: 100%;
            height: 200px;
            object-fit: cover;
            filter: brightness(80%) blur(2px);
        }
        .product-card img.small {
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            max-width: 250px;
            height: 150px;
            object-fit: contain;
            background: white;
            padding: 5px;
            border-radius: 5px;
        }
        .product-card h4 {
            font-size: 20px;
            font-weight: bold;
            margin: 160px 0 10px;
        }
        .product-card p {
            font-size: 14px;
            color: #333;
            padding: 0 15px;
            margin-bottom: 15px;
        }
        .logo-list {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 0;
            list-style: none;
        }
        .logo-list li img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            transition: transform 0.3s;
        }
        .logo-list li img:hover {
            transform: scale(1.1);
        }
        .btn {
            display: inline-block;
            background: red;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #cc0000;
        }

        /* Software Section */
        .software-section {
            padding: 40px 0;
        }
        .software-container {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .software-content {
            flex: 1;
        }
        .software-image {
            flex: 1;
        }
        .software-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }
        .accordion-button {
            font-weight: bold;
            font-size: 16px;
        }
        .accordion-body {
            font-size: 14px;
        }
        .accordion-body ul {
            padding-left: 20px;
        }
        .accordion-body ul li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 15px;
        }
        .accordion-body ul li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: red;
        }
        .explore-link {
            color: red;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .explore-link:hover {
            text-decoration: underline;
        }

        /* FAQ Section */
        .faq-section {
            padding: 40px 0;
        }
        .faq-container {
            display: flex;
            gap: 30px;
        }
        .faq-left {
            flex: 1;
        }
        .faq-right {
            flex: 2;
        }
        .tab-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab-buttons button {
            border: 1px solid #ddd;
            background: none;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
        }
        .tab-buttons .active {
            background: red;
            color: white;
            border-color: red;
        }
        .faq-contact-text {
            font-size: 14px;
            color: red;
            margin: 20px 0;
        }
        .faq-contact-btn {
            display: inline-block;
            background: red;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
        }
        .faq-contact-btn:hover {
            background: #cc0000;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .banner img {
                height: 250px;
            }
            .news-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .news-card img {
                height: 150px;
            }
            .news-title a {
                font-size: 16px;
            }
            .news-content p {
                font-size: 13px;
            }
            .product-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .product-card img.large {
                height: 180px;
            }
            .product-card img.small {
                width: 70%;
                max-width: 200px;
                height: 120px;
                top: 20px;
            }
            .product-card h4 {
                margin-top: 140px;
                font-size: 18px;
            }
            .product-card p {
                font-size: 13px;
            }
            .logo-list li img {
                width: 30px;
                height: 30px;
            }
            .software-container {
                flex-direction: column;
                gap: 20px;
            }
            .software-image img {
                height: 200px;
            }
            .accordion-button {
                font-size: 14px;
            }
            .accordion-body {
                font-size: 13px;
            }
            .faq-container {
                flex-direction: column;
                gap: 20px;
            }
            .faq-left, .faq-right {
                flex: 1;
            }
            .tab-buttons button {
                font-size: 12px;
                padding: 8px 12px;
            }
            .section-title {
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .banner img {
                height: 200px;
            }
            .news-card img {
                height: 120px;
            }
            .news-title a {
                font-size: 14px;
            }
            .news-content p {
                font-size: 12px;
            }
            .product-card img.large {
                height: 150px;
            }
            .product-card img.small {
                width: 65%;
                max-width: 180px;
                height: 100px;
                top: 15px;
            }
            .product-card h4 {
                margin-top: 120px;
                font-size: 16px;
            }
            .product-card p {
                font-size: 12px;
            }
            .logo-list li img {
                width: 25px;
                height: 25px;
            }
            .software-image img {
                height: 150px;
            }
            .accordion-button {
                font-size: 12px;
            }
            .accordion-body {
                font-size: 12px;
            }
            .tab-buttons button {
                font-size: 11px;
                padding: 6px 10px;
            }
            .faq-contact-text {
                font-size: 12px;
            }
            .faq-contact-btn {
                padding: 8px 15px;
                font-size: 12px;
            }
            .section-title {
                font-size: 18px;
            }
        }
    </style>
</body>
</html>
```