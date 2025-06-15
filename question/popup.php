
    <title>Popup Khuyến Mãi</title>

    <!-- Popup -->
    <div class="popup-overlay" id="popup">
        <div class="popup-content">
            <button class="popup-close" id="popup-close"></button>
            <img src="assets/images/popupkm.jpg" alt="Khuyến Mãi" class="popup-image"
             id="popup-img" onerror="hidePopup()">
        </div>
    </div>

    <script>
        const popup = document.getElementById('popup');
        const popupImage = document.getElementById('popup-img');
        const closeButton = document.getElementById('popup-close');

        // Kiểm tra ảnh có tồn tại hay không trước khi hiển thị popup
        function checkImageExists(url, callback) {
            const img = new Image();
            img.src = url;
            img.onload = function () {
                callback(true);
            };
            img.onerror = function () {
                callback(false);
            };
        }

        // Ẩn popup nếu ảnh không tải được
        function hidePopup() {
            popup.style.display = 'none';
        }

        window.onload = function () {
            checkImageExists(popupImage.src, function (exists) {
                if (exists) {
                    popup.style.display = 'flex'; // Hiển thị popup nếu ảnh hợp lệ

                    // Tự động đóng popup sau 30 giây
                    setTimeout(function () {
                        popup.style.display = 'none';
                    }, 30000);
                } else {
                    hidePopup();
                }
            });
        };

        // Đóng popup khi bấm nút đóng
        closeButton.onclick = function () {
            popup.style.display = 'none';
        };

        // Đóng popup khi bấm ra ngoài nội dung popup
        popup.onclick = function (event) {
            const popupContent = document.querySelector('.popup-content');
            if (!popupContent.contains(event.target)) {
                popup.style.display = 'none';
            }
        };

        
        // Đóng popup khi nhấn phím "Esc"
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                popup.style.display = 'none';
            }
        });
    </script>
<style>
        /* Popup Background */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(27, 26, 26, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Popup Content */
        .popup-content {
            border-radius: 16px;
            width: 30%;
            padding: 20px;
            text-align: center;
            position: relative;
            color: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        /* Close Button */
      

        .popup-close:hover {
            background: rgb(235, 18, 2);
            color: rgb(247, 247, 247);
        }

        /* Popup Image */
        .popup-image {
            width: 110%;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        /* Media Queries cho màn hình nhỏ hơn */
        @media screen and (max-width: 600px) {
            .popup-content {
                width: 95%; /* Chiếm gần hết màn hình trên thiết bị rất nhỏ */
                padding: 10px;
            }

            .popup-close {
                font-size: 14px;
                width: 20px;
                height: 20px;
                line-height: 20px;
                right: -25px; /* Điều chỉnh vị trí cho màn hình nhỏ */
            }

            .popup-image {
                width: 95%;
                margin-bottom: 10px;
            }
        }
    </style>