<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Khuyến Mãi</title>
    <style>
        /* Popup Background */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Popup Content */
        .popup-content {
            position: relative;
            width: 40vw;
            max-width: 90vw;
            min-width: 20rem;
            padding: 0;
            background: none;
        }

        /* Close Button */
        .popup-close {
            position: absolute;
            top: -15px;
            right: -15px;
            background: #fff;
            border: 2px solid #000;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            line-height: 26px;
            text-align: center;
            color: #000;
            transition: background 0.3s, color 0.3s;
        }

        .popup-close:hover {
            background: #ff0000;
            color: #fff;
            border-color: #fff;
        }

        /* Popup Image */
        .popup-image {
            width: 70%;
            height: auto;
            border-radius: 10px;
            display: block;
        }

        /* Media Queries for smaller screens */
        @media screen and (max-width: 768px) {
            .popup-content {
                width: 90vw;
                min-width: 15rem;
            }
        }

        @media screen and (max-width: 480px) {
            .popup-content {
                width: 95vw;
            }
        }
    </style>
</head>
<body>
    <!-- Popup -->
    <div class="popup-overlay" id="popup">
        <div class="popup-content">
            <button class="popup-close" id="popup-close">×</button>
            <img src="popup.jpg" alt="Khuyến Mãi" class="popup-image" id="popup-img" onerror="hidePopup()">
        </div>
    </div>

    <script>
        const popup = document.getElementById('popup');
        const popupImage = document.getElementById('popup-img');
        const closeButton = document.getElementById('popup-close');

        // Check if image exists before showing popup
        function checkImageExists(url, callback) {
            const img = new Image();
            img.src = url;
            img.onload = () => callback(true);
            img.onerror = () => callback(false);
        }

        // Hide popup if image fails to load
        function hidePopup() {
            popup.style.display = 'none';
        }

        // Show popup on page load if image exists
        window.onload = () => {
            checkImageExists(popupImage.src, (exists) => {
                if (exists) {
                    popup.style.display = 'flex';
                    // Auto-close after 30 seconds
                    setTimeout(() => {
                        popup.style.display = 'none';
                    }, 30000);
                } else {
                    hidePopup();
                }
            });
        };

        // Close popup when clicking the close button
        closeButton.onclick = () => {
            popup.style.display = 'none';
        };

        // Close popup when clicking outside the content
        popup.onclick = (event) => {
            const popupContent = document.querySelector('.popup-content');
            if (!popupContent.contains(event.target)) {
                popup.style.display = 'none';
            }
        };

        // Close popup when pressing "Esc"
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                popup.style.display = 'none';
            }
        });
    </script>
</body>
</html>