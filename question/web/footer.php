<div class="container rosa-footer-contact">
    <div class="rosa-footer-newsletter">
        <h3 style="color: red; font-weight: bold;">Nhận thông tin từ ROSA</h3>
        <p>Đăng ký email để nhận các thông tin mới nhất từ ROSA</p>
        <form id="rosaSubscribeForm">
            <input type="email" name="email" id="rosaEmailInput" placeholder="Email của bạn" required
                style="padding: 10px; border: none; border-radius: 20px; width: 200px; text-align: center;">
            <button type="button" id="rosaOpenPopup"
                style="padding: 10px 20px; background-color: red; color: white; border: none; border-radius: 20px; cursor: pointer;">
                ĐĂNG KÝ
            </button>
        </form>
        <div id="rosaMessageBox" style="display: none; margin-top: 10px;"></div>

        <!-- Popup Form -->
        <div class="rosa-overlay" id="rosaOverlay"></div>
        <div id="rosaPopupForm" style="display: none; position: fixed; top: 31%; left: 50%; transform: translate(-50%, -50%);
            background: white; padding: 20px; box-shadow: 5px 5px 15px rgba(253, 23, 23, 0.3); border-radius: 10px; width: 30%; border: 2px solid red;">
            <span id="rosaClosePopup" style="position: absolute; top: 10px; right: 15px; cursor: pointer; font-size: 20px; color: red;">✖</span>
            <h4><i class="fas fa-user-check" style="color:red; margin-right: 5px;"></i> THÔNG TIN KHÁCH HÀNG</h4>
            <label for="name">Tên của bạn: <span style="color: red;">*</span></label>
            <input type="text" id="rosaNameInput" required style="width: 100%; padding: 5px; margin: 5px 0; border: 1px solid #ff0000; border-radius: 5px;">
            <label for="phone">Số điện thoại: <span style="color: red;">*</span></label>
            <input type="tel" id="rosaPhoneInput" required style="width: 100%; padding: 5px; margin: 5px 0; border: 1px solid #ff0000; border-radius: 5px;">
            <button id="rosaSubmitForm" style="background: red; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Gửi</button>
            <button id="rosaClosePopupBtn" style="background: gray; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">Đóng</button>
        </div>
    </div>

    <div class="rosa-footer-support">
        <h3 style="color:red;font-weight: bold;">Thông tin hỗ trợ</h3>
        <p>Kinh doanh: (028) 39293765</p>
        <p>Kỹ thuật & bảo hành: (028) 39260996</p>
    </div>
    <div class="rosa-footer-social">
        <h3 style="color:red;font-weight: bold;">Liên kết social</h3>
        <p>Theo dõi ROSA tại các kênh mạng xã hội</p>
        <a href="https://www.facebook.com/people/ROSA-AI-Computer/61559427752479/"><i class="fab fa-facebook" style="color:#1877F2;"></i></a>
        <a href="https://www.linkedin.com/in/rosa-ai-computer-20980b352/"><i class="fab fa-linkedin" style="color:#1877F2;"></i></a>
    </div>
</div>
<br>

<footer class="rosa-footer">
    <div class="container_info">
        <div class="rosa-footer-sections">
            <div class="rosa-footer-column">
                <h3 style="color:red;font-weight: bold;">THÔNG TIN CÔNG TY</h3>
                <!--<p><img src="https://img.icons8.com/dusk/25/FA5252/skyscrapers.png" alt="" class="img-fluid">Thửa đất số 13A, Tờ bản đồ C2, Khu phố 1B, Phường An Phú, Thành phố Thuận An, Tỉnh Bình Dương</p>-->
                <p><img src="https://img.icons8.com/dusk/25/FA5252/skyscrapers.png" alt="" class="img-fluid">Chi nhánh & TTBH HCM: 150 Ter, đường Bùi Thị Xuân, phường Phạm Ngũ Lão, Quận 1, TP. Hồ Chí Minh.</p>
                <p><img src="https://img.icons8.com/dusk/25/FA5252/skyscrapers.png" alt="" class="img-fluid">Chi nhánh Hà Nội: Tầng 4, Tòa nhà Viet Tower, số 01 phố Thái Hà, P. Trung Liệt, Q. Đống Đa, TP Hà Nội</p>
            </div>
            <div class="rosa-footer-column">
                <h3 style="color:red;font-weight: bold;">CHÍNH SÁCH CÔNG TY</h3>
                <ul class="rosa-list-info">
                    <li><a href="/DSDL.php"><i class="fas fa-angle-right" style="color: #FF0000; margin-right: 5px;"></i>DANH SÁCH ĐẠI LÝ</a></li>
                    <li><a href="/CSBH.php"><i class="fas fa-angle-right" style="color: #FF0000; margin-right: 5px;"></i>CHÍNH SÁCH BẢO HÀNH</a></li>
                        <?php
    						$count_file = "total_count.txt";
    
    						$visitor_file = "visitor_count.txt";
    
    						$user_ip = $_SERVER['REMOTE_ADDR'];
    						
    						$total_count_default = 88888;
    						$total_count_real = file_exists($count_file) ? (int)file_get_contents($count_file) : 1;
    						$total_count_display = $total_count_default + $total_count_real;
    
    						$visitor_ips = file_exists($visitor_file) ? file($visitor_file, FILE_IGNORE_NEW_LINES) : [];
    
    						if (!in_array($user_ip, $visitor_ips)) {
    							$visitor_ips[] = $user_ip;
    							file_put_contents($visitor_file, implode(PHP_EOL, $visitor_ips) . PHP_EOL);
    						}
    
    						$total_count_real++;
    
    						file_put_contents($count_file, $total_count_real);
    
    						// Số lượng người truy cập duy nhất
    						$unique_visitors_default = 11111;
    						$unique_visitors_real = count($visitor_ips);
    						$unique_visitors_display = $unique_visitors_default + $unique_visitors_real;
    						?>
    							<p  style="font-size: 16px; color:#000000"><img src="https://img.icons8.com/glyph-neue/25/crowd.png" alt="" class="img-fluid">
                                    Số lượt truy cập duy nhất: <?php echo $unique_visitors_display; ?>
                                </p>
                                <p style="font-size: 16px; color:#000000"><img src="https://img.icons8.com/glyph-neue/25/crowd.png" alt="" class="img-fluid">
                                    Tổng số lượt truy cập: <?php echo $total_count_display; ?>
                                </p>
    
                        </ul>
                </ul>
                
            </div>
            <div class="rosa-footer-column">
                <h3 style="color:red;font-weight: bold;">PHẦN MỀM & KHÓA HỌC</h3>
                <ul class="rosa-list-info">
                    <li><a href="/courses/python-course.php"><i class="fas fa-angle-right" style="color: #FF0000; margin-right: 5px;"></i>PYTHON CƠ BẢN</a></li>
                    <li><a href="/courses/yolo-course.php"><i class="fas fa-angle-right" style="color: #FF0000; margin-right: 5px;"></i>THỊ GIÁC MÁY TÍNH</a></li>
                    <li><a href="/courses/Nextcloud.php"><i class="fas fa-angle-right" style="color: #FF0000; margin-right: 5px;"></i>QUẢN TRỊ DOANH NGHIỆP</a></li>
                </ul>
            </div>
            <div class="rosa-footer-column">
                <h3 style="color:red;font-weight: bold;">CỘNG ĐỒNG ROSA</h3>
                <ul class="rosa-list-info">
                    <li><a href="https://www.facebook.com/people/ROSA-AI-Computer/61559427752479/"><i class="fab fa-facebook" style="color:#1877F2;"></i> ROSA AI COMPUTER</a></li>
                    <li><a href="https://www.linkedin.com/in/rosa-ai-computer-20980b352/"><i class="fab fa-linkedin" style="color:#1877F2;"></i> ROSA AI COMPUTER</a></li>
                    <li><i class="fas fa-envelope" style="color:#FF0000;"></i> support@rosacomputer.ai</li>
                    <li><i class="fas fa-phone-alt" style="color:#FF0000;"></i> Phòng kinh doanh: (028) 39293765</li>
                    <li><i class="fas fa-phone-alt" style="color:#FF0000;"></i> Phòng kỹ thuật: (028) 39260996</li>
                </ul>
            </div>
        </div>
    </div>
    <div id="rosa-foot-bot">
        <div class="container_info">
            <p>© 2024 Bản quyền thuộc về <a href="https://rosacomputer.vn/">CÔNG TY TNHH ĐIỆN TỬ VÀ TIN HỌC TOÀN VIỆT</a></p>
        </div>
    </div>
</footer>
<div class="contact-fixed">
    <a href="https://zalo.me/909749126673606301" target="_blank">
        <img width="48" height="48" src="https://img.icons8.com/color/48/zalo.png" alt="zalo"/>
    </a>
    <a href="https://www.facebook.com/rosaaicomputer/" target="_blank">
        <img width="48" height="48" src="https://img.icons8.com/fluency/48/facebook-new.png" alt="facebook-new"/>    
    </a>
</div>





<!-- JavaScript -->
<script>
(function() {
    const originalUrl = window.location.href;

    // Hàm mở popup
    function openPopup() {
        const popup = document.getElementById("rosaPopupForm");
        const overlay = document.getElementById("rosaOverlay");
        if (popup && overlay) {
            popup.style.display = "block";
            overlay.style.display = "block";
            if (!window.location.search.includes("?popup=open")) {
                const newUrl = originalUrl.includes("?") 
                    ? `${originalUrl}&popup=open` 
                    : `${originalUrl}?popup=open`;
                window.history.pushState({ popup: true }, "Đăng ký", newUrl);
            }
        }
    }

    // Hàm đóng popup
    function closePopup() {
        const popup = document.getElementById("rosaPopupForm");
        const overlay = document.getElementById("rosaOverlay");
        if (popup && overlay) {
            popup.style.display = "none";
            overlay.style.display = "none";
            window.history.pushState({ popup: false }, "Trang chủ", originalUrl);
        }
    }

    // Khi nhấn nút "ĐĂNG KÝ"
    const openPopupBtn = document.getElementById("rosaOpenPopup");
    if (openPopupBtn) {
        openPopupBtn.addEventListener("click", function() {
            const email = document.getElementById("rosaEmailInput").value.trim();
            if (email === "") {
                alert("⚠ Vui lòng nhập email trước!");
                return;
            }
            openPopup();
        });
    }

    // Khi nhấn nút "X" hoặc "Đóng"
    const closePopupBtn = document.getElementById("rosaClosePopup");
    const closePopupBtnAlt = document.getElementById("rosaClosePopupBtn");
    if (closePopupBtn) {
        closePopupBtn.addEventListener("click", closePopup);
    }
    if (closePopupBtnAlt) {
        closePopupBtnAlt.addEventListener("click", closePopup);
    }

    // Khi nhấn nút "Gửi"
    const submitFormBtn = document.getElementById("rosaSubmitForm");
    if (submitFormBtn) {
        submitFormBtn.addEventListener("click", function() {
        const email = document.getElementById("rosaEmailInput").value.trim();
        const name = document.getElementById("rosaNameInput").value.trim();
        const phone = document.getElementById("rosaPhoneInput").value.trim();
        const messageBox = document.getElementById("rosaMessageBox");

        // Validation (giữ nguyên code của bạn)
        if (name === "" || !/^[A-Za-zÀ-Ỹà-ỹ\s]+$/.test(name) || phone === "" || !/^[0-9]{9,11}$/.test(phone)) {
            // Validation logic...
            return;
        }

        const formData = new FormData();
        formData.append("email", email);
        formData.append("name", name);
        formData.append("phone", phone);

        console.log("Sending data:", { email, name, phone });
        fetch("test_mail/send_email.php", { // Sửa đường dẫn nếu cần
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error(`Server error: ${response.status}`);
            return response.text();
        })
        .then(data => {
            console.log("Response:", data);
            messageBox.style.display = "block";
            messageBox.innerHTML = data.includes("✅") 
                ? `<span style="color: green;">${data}</span>` 
                : `<span style="color: red;">${data}</span>`;
            if (data.includes("✅")) {
                closePopup();
                document.getElementById("rosaSubscribeForm").reset();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            messageBox.style.display = "block";
            messageBox.innerHTML = `<span style="color: red;">❌ Lỗi: ${error.message}</span>`;
        });
    });
    // Kiểm tra URL ngay khi mã chạy
    (function checkPopupOnLoad() {
        const currentUrl = window.location.href;
        if (currentUrl.includes("?popup=open")) {
            openPopup();
        }
    })();

    // Xử lý nút back/forward của trình duyệt
    window.addEventListener("popstate", function(event) {
        const currentUrl = window.location.href;
        if (currentUrl.includes("?popup=open")) {
            openPopup();
        } else {
            closePopup();
        }
    });
}})();
</script>

<style>

.contact-fixed {
    position: fixed;  /* Cố định vị trí */
    bottom: 20px;     /* Khoảng cách từ dưới lên */
    right: 20px;      /* Khoảng cách từ bên phải vào */
    display: flex;    /* Sử dụng flexbox để sắp xếp */
    flex-direction: column; /* Sắp xếp theo cột */
    gap: 15px;        /* Khoảng cách giữa các biểu tượng */
}

.contact-fixed a {
    display: block;    /* Hiển thị liên kết dưới dạng khối */
    width: 50px;      /* Kích thước biểu tượng */
    height: 50px;     /* Kích thước biểu tượng */
}

.contact-fixed img {
    width: 130%;      /* Chiếm toàn bộ chiều rộng của liên kết */
    height: 110%;     /* Chiếm toàn bộ chiều cao của liên kết */
    object-fit: contain; /* Giữ nguyên tỷ lệ hình ảnh */
    border-radius: 50%;  /* Bo tròn biểu tượng */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Đổ bóng cho biểu tượng */
    transition: transform 0.3s; /* Hiệu ứng chuyển tiếp khi hover */
}

.contact-fixed img:hover {
    transform: scale(1.1); /* Phóng to biểu tượng khi hover */
}
    
/* Đảm bảo CSS chỉ áp dụng cho footer của ROSA */
.rosa-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

#rosaPopupForm {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    width: 350px;
    border: 2px solid red;
    z-index: 1000;
}

#rosaPopupForm h4 {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: black;
    display: flex;
    align-items: center;
}
p {
    font-size: 17px;
    margin-bottom: 10px;
    color: #000000;
}

#rosaClosePopup {
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    font-size: 20px;
    color: red;
    font-weight: bold;
}

#rosaPopupForm input {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #ff0000;
    border-radius: 5px;
    outline: none;
    font-size: 14px;
}

#rosaPopupForm input:focus {
    border-color: red;
    box-shadow: 0px 0px 5px rgba(255, 0, 0, 0.5);
}

#rosaSubmitForm {
    background: red;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    margin-top: 10px;
}

#rosaSubmitForm:hover {
    background: darkred;
}

#rosaClosePopupBtn {
    background: gray;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    margin-top: 5px;
}

#rosaClosePopupBtn:hover {
    background: darkgray;
}

#rosa-foot-bot a {
    text-decoration: none !important;
    border-bottom: none !important;
    color: red;
}

.rosa-footer-contact {
    display: flex;
    justify-content: space-between;
    background-color: #edeaea !important;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    flex-direction: row;
}

.rosa-footer-newsletter, .rosa-footer-support, .rosa-footer-social {
    flex: 1;
    padding: 10px;
}

.rosa-footer-contact a {
    color: #FF0000;
}

.rosa-footer-contact p {
    font-size: 16px;
    margin-bottom: 10px;
    color: #000000;
}

.rosa-footer-social a {
    margin: 0 5px;
    font-size: 20px;
}

.rosa-footer-social a:hover {
    color: red;
}

.rosa-footer {
    background-color: #edeaea;
    padding: 10px 0;
    font-family: Arial, sans-serif;
}

.rosa-footer .container_info{
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.rosa-footer-sections {
    display: flex;
    justify-content: space-between;
    flex-wrap: nowrap;
    gap: 20px;
}

.rosa-footer-column {
    width: 23%;
    padding: 20px;
    text-align: left;
}

.rosa-footer-column h3 {
    font-size: 22px;
    color: rgb(255, 13, 13);
    margin-bottom: 15px;
    position: relative;
    display: inline-block;
}

.rosa-footer-column h3::after {
    content: "";
    display: block;
    width: 60px;
    height: 4px;
    background-color: rgb(255, 4, 4);
    margin-top: 8px;
}

.rosa-footer-column ul {
    list-style: none;
    padding: 0;
}

.rosa-footer-column ul li a {
    text-decoration: none;
    color: #000000;
    font-size: 16px;
    display: block;
    padding: 5px 0;
}

.rosa-footer-column ul li a:hover {
    color: red;
}

#rosa-foot-bot p {
    text-align: center;
    margin: 0;
    padding: 10px 0;
    font-size: 19px;
    background-color: rgb(234, 228, 228);
}

@media (max-width: 768px) {
    .rosa-footer-sections {
        flex-direction: column;
        gap: 15px;
    }
    .rosa-footer-column {
        width: 100%;
        padding: 10px;
    }
    .rosa-footer-contact {
        flex-direction: column;
        align-items: center;
    }
    .rosa-footer-newsletter form {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    .rosa-footer-social a {
        font-size: 18px;
        margin: 5px;
    }
    #rosa-foot-bot p {
        font-size: 16px;
        font-family: Arial, sans-serif;
    }
}
</style>