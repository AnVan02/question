<title>Kiểm Tra Đơn Hàng</title>

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
<head>
<!-- Banner -->
<div class="banner">
		<div class="container">
			<div class="row">
			<div class="col-lg-12 mb-3">
<!-- <body class="h-100" style="background-image: ">
<div id="carouselExampleSlidesOnly" class="carousel slide" data-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
        <img src="assets/images/1.jpg" class="d-block w-100" alt="...">
    </div>
    <div class="carousel-item">
      <img src="assets/images/2.jpg" class="d-block w-100" alt="...">
    </div>
    <div class="carousel-item">
      <img src="assets/images/3.jpg" class="d-block w-100" alt="...">
    </div>
   
  </div>
</div> -->

<?php if(isset($_SESSION["message"])) {echo 'onload="message()"';unset($_SESSION["message"]);}?>


    <style>
        */
        .tab {
            padding: 20px;
            background-color: white;
            margin: 20px auto;
            width: 80%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .tab h1 {
            font-size: 24px;
            color: #333;
            display: inline-block;
            padding-bottom: 5px;
        }
        .tab p {
            color: red;
            font-size: 14px;
        }
        .tab label {
            font-size: 16px;
            color: #333;
        }
        .tab input[type="text"] {
            padding: 10px;
            width: 200px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .tab button {
            padding: 10px 20px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .container button:hover {
            background-color: black;
        }
        .table {
            display: none;
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .table th {
            background-color: red;
            color: white;
        }
      
    </style>
	<!-- Main content -->
	<nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/./">TRANG CHỦ</a></li>
            <li class="breadcrumb-item"><a href="../check.php">KIỂM TRA ĐƠN HÀNG</a></li>
        </ol>
    </nav>
    <div class="tab">
        <h1>Kiểm tra đơn hàng</h1>
        <p>(Dành cho đơn đặt hàng online trên website)</p>
        <label for="order-code">Nhập mã đơn hàng để kiểm tra đơn hàng của bạn.</label><br><br>
        <input type="text" id="order-code">
        <button id="check_id">Kiểm tra</button>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Mã đơn hàng</th>
                <th>Họ tên</th>
                <th>Điện thoại</th>
                <th>Ngày đặt hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
            </tr>
        </table>
        
        <div id="order_content"></div>
    </div>
    <script>
        document.getElementById("check_id").addEventListener("click", function () {
            const orderCode = document.getElementById("order-code").value.trim();

            if (!orderCode) {
                alert("Vui lòng nhập mã đơn hàng");
                return;
            }

            fetch(`https://rosacomputer.vn/api.php?order_code=${orderCode}`)
                .then(response => response.json())
                .then(data => {
                    const table = document.querySelector(".table");
                    table.innerHTML = `
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Họ tên</th>
                            <th>Điện thoại</th>
                            <th>Ngày đặt hàng</th>
                            <th>Trạng thái</th>
                        </tr>
                    `;

                    if (data.success) {
                        const row = `
                            <tr>
                                <td>${data.data.formatted_order_id}</td>
                                <td>${data.data.customer_name}</td>
                                <td>${data.data.customer_phone}</td>
                                <td>${data.data.order_date}</td>
                                <td>${data.data.status}</td>
                            </tr>
                        `;
                        table.style.display = "table";
                        table.innerHTML += row;
                        // document.querySelector(".tab").innerHTML += `<div>${data.data.order}</div>`;
                        document.getElementById('order_content').innerHTML = data.data.order;
                    } else {
                        alert(data.message);
                        table.style.display = "none";
                        // clear order content
                        document.getElementById('order_content').innerHTML = "";
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Đã xảy ra lỗi khi kết nối với server!");
                });
        });
    </script>

        <!-- Closing div tags for the container and row -->
            </div>
        </div>
    </div>

<style>
    
/* Consolidated and responsive CSS */
.tab {
    padding: 15px;
    background-color: white;
    margin: 15px auto;
    width: 90%; /* More flexible for mobile */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.tab h1 {
    font-size: clamp(1.5rem, 5vw, 1.8rem); /* Responsive font size */
    color: #333;
    display: inline-block;
    padding-bottom: 5px;
}

.tab p {
    color: red;
    font-size: clamp(0.9rem, 4vw, 1.2rem); /* Responsive font size */
}

.tab label {
    font-size: clamp(0.9rem, 3.5vw, 1rem);
    color: #333;
}

.tab input[type="text"] {
    padding: 8px;
    width: 100%; /* Full width on mobile */
    max-width: 300px; /* Prevent overly wide inputs on larger screens */
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box; /* Ensure padding doesn't affect width */
}

.tab button {
    padding: 10px 20px;
    background-color: red;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: clamp(0.9rem, 3.5vw, 1rem);
    width: 100%; /* Full width on mobile */
    max-width: 150px; /* Limit width on larger screens */
}

.tab button:hover {
    background-color: black;
}

.table {
    display: none;
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 8px;
    text-align: left; /* Left-align for better readability */
    border: 1px solid #ddd;
}

.table th {
    background-color: red;
    color: white;
}

/* Mobile-specific styles */
@media (max-width: 768px) {
    .tab {
        width: 95%; /* Almost full width on mobile */
        padding: 10px;
    }

    .tab input[type="text"], .tab button {
        max-width: 100%; /* Full width on mobile */
    }

    /* Stack table rows for mobile */
    .table {
        display: block; /* Override display: none when populated */
    }

    .table thead {
        display: none; /* Hide headers on mobile */
    }

    .table tr {
        display: block;
        margin-bottom: 10px;
        border: 1px solid #ddd;
    }

    .table td {
        display: block;
        text-align: right;
        position: relative;
        padding-left: 50%;
        border: none;
        border-bottom: 1px solid #ddd;
    }

    .table td::before {
        content: attr(data-label); /* Use data-label for mobile */
        position: absolute;
        left: 10px;
        width: 45%;
        font-weight: bold;
        text-align: left;
    }
}

/* Ensure container button hover */
.container button:hover {
    background-color: black;
}
</style>

<?php
    require "footer.php";
?>
