<?php
require "header.php";
?>

<?php if(isset($_SESSION["message"])):?>
    <script>
        function message() {
            window.alert("<?php echo $_SESSION["message"];?>");
        }
    </script>
<?php endif;?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">TRANG CHỦ</a></li>
            <li class="breadcrumb-item active" aria-current="page">GIỚI THIỆU VỀ ROSA</li>
        </ol>
    </nav>

    <!-- Main content -->
    <div class="main-banner mission">
        <div class="banner-content">
            <h3>SỨ MỆNH</h3>
            <p>Thương hiệu ROSA thuộc CÔNG TY TNHH ĐIỆN TỬ VÀ TIN HỌC TOÀN VIỆT, mang đến máy tính bộ chất lượng cao, mạnh mẽ và bền bỉ, đáp ứng nhu cầu học tập, công việc và giải trí của người Việt. ROSA cam kết nâng cao hiệu quả làm việc và trải nghiệm người dùng với các sản phẩm công nghệ đáng tin cậy.</p>
        </div>
        <div class="banner-image">
            <img src="assets/images/blog3.png" alt="Gaming PC" />
        </div>
    </div>

    <div class="main-banner vision">
        <div class="banner-content">
            <h3>TẦM NHÌN</h3>
            <p>ROSA hướng đến vị trí thương hiệu máy tính bộ hàng đầu Việt Nam, góp phần phát triển công nghệ trong nước và nâng cao vị thế hàng Việt. Với nỗ lực không ngừng cải tiến, ROSA tạo ra sản phẩm tiên tiến, mang đậm dấu ấn Việt, trở thành niềm tự hào và lựa chọn tin cậy của người tiêu dùng.</p>
        </div>
        <div class="banner-image">
            <img src="assets/images/blog.jpg" alt="Gaming PC" />
        </div>
    </div>
</div>

<?php if(isset($_SESSION["message"])) {echo 'onload="message()"';unset($_SESSION["message"]);}?>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    background-color: #f5f5f5;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    background: #fff;
    padding: 10px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.breadcrumb-item a {
    color: #1e90ff;
    text-decoration: none;
    font-size: 14px;
}

.breadcrumb-item.active {
    color: #333;
    font-weight: 500;
}

.main-banner {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg,rgb(0, 0, 0) 0%,rgb(0, 0, 0) 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: #fff;
}

.mission .banner-content h3 {
    color: #1e90ff;
}

.vision .banner-content h3 {
    color: #ff3333;
}

.banner-content {
    flex: 1;
    padding-right: 20px;
}

.banner-content h3 {
    font-size: 24px;
    margin-bottom: 15px;
    font-weight: 600;
    text-transform: uppercase;
}

.banner-content p {
    font-size: 16px;
    line-height: 1.8;
    color: #dcdcdc;
}

.banner-image {
    flex: 1;
    text-align: right;
}

.banner-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* Mobile Styles */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }

    .breadcrumb {
        padding: 8px 10px;
    }

    .breadcrumb-item a {
        font-size: 12px;
    }

    .main-banner {
        flex-direction: column;
        padding: 20px;
    }

    .banner-content {
        padding-right: 0;
        text-align: center;
        margin-bottom: 20px;
    }

    .banner-content h3 {
        font-size: 20px;
    }

    .banner-content p {
        font-size: 14px;
    }

    .banner-image {
        text-align: center;
    }

    .banner-image img {
        max-width: 80%;
    }
}

@media (max-width: 480px) {
    .breadcrumb {
        display: flex;
        flex-wrap: wrap;
    }

    .main-banner {
        padding: 15px;
    }

    .banner-content h3 {
        font-size: 18px;
    }

    .banner-content p {
        font-size: 13px;
    }

    .banner-image img {
        max-width: 100%;
    }
}
</style>

<?php require "footer.php"; ?>