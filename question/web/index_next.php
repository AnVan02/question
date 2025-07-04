<?php
style('AIFilterCV', 'nhansu');
script('AIFilterCV', 'script');
?>
<div class="container">
    <div class="sidebar">
        <?php include('camera_list.php'); ?>
        <?php include('add_camera.php'); ?>
    </div>
    <div class="main-content">
        <div class="header">
            <div class="header-left-group">   
               
                <form method="POST" action="<?php p($_['del_url']); ?>" id="delete-form">                   
                    <?php include('buttons.php'); ?>
                
                
                    <form method="POST" action="<?php p($_['export_url']); ?>" id="xuat">
                        <button type="submit" class="btn-train">Xuat</button>
                    </form>
            </div>
            
        </div>
        <div class="form-container">
         
            <?php include('employee.php'); ?>
        </div>
    </form> 
        

        <?php include('add_employee_popup.php'); ?> 
         
        <?php include('edit_form.php'); ?>


        <div class="twocontainer">
            <form method="POST" action="<?php p($_['train_url']); ?>" id="train-form">
                <button type="submit" class="btn-train">Làm mới</button>
            </form>
       
            <form method="POST" action="<?php p($_['export_url']); ?>" id="xuat">
                <button type="submit" class="btn-train">mm</button>
            </form>

        </div>
        <!-- Message Popup -->
        <!-- <?php if (!empty($_['message']) || !empty($_['error'])): ?>
            <div id="popup" class="popup <?php echo empty($_['error']) ? 'success' : 'error'; ?>">
                <span><?php p($_['message'] ?? $_['error']); ?></span>
            </div>
        <?php endif; ?> -->
        
    </div>
    <div class="lastcontainer">
   
        <?php if (!empty($_['data'])): ?>
            <?php include('batch_add.php'); ?>
            <?php include('upload_schedule.php'); ?>
            <?php include('retrieve.php'); ?>
        <?php endif; ?>
    </div>
</div>
----
<script>

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý form xuất
    const xuatForm = document.getElementById('xuat');
    if (xuatForm) {
        xuatForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Ngăn form submit mặc định
            if (confirm('Bạn có chắc muốn xuất dữ liệu không?')) {
                this.submit(); // Nếu xác nhận thì submit form
            }
        });
    }

    // Xử lý check all
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.emp-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = checkAll.checked;
            });
        });
    }

    // Xử lý xác nhận xóa
    const deleteForm = document.getElementById('delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            if (!confirm('Bạn có chắc muốn xóa nhân viên đã chọn không?')) {
                e.preventDefault();
            }
        });
    }

    // Xử lý chuyển đổi hình ảnh
    const img = document.getElementById('employee-image');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    if (img && prevBtn && nextBtn) {
        const images = JSON.parse(img.dataset.images);
        let currentIndex = 0;

        function updateImage() {
            img.src = 'data:image/jpeg;base64,' + images[currentIndex];
        }

        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateImage();
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % images.length;
            updateImage();
        });
    }

    // Xử lý nút Sửa trong buttons.php
    const editBtn = document.querySelector('.btn-edit');
    const checkboxes = document.querySelectorAll('.emp-checkbox');
    
    if (editBtn && checkboxes) {
        // Bật/tắt nút Sửa dựa trên checkbox
        function toggleEditButton() {
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            editBtn.disabled = checkedCount !== 1; // Chỉ bật khi chọn đúng 1 nhân viên
        }

        // Cập nhật trạng thái nút Sửa khi checkbox thay đổi
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    checkboxes.forEach(otherCb => {
                        if (otherCb !== this) otherCb.checked = false;
                    });
                    checkAll.checked = false;
                }
                toggleEditButton();
            });
        });

        // Xử lý khi nhấn nút Sửa
        editBtn.addEventListener('click', showEditEmployeePopup);

        // Khởi tạo trạng thái ban đầu
        toggleEditButton();
    }

    // Hàm hiển thị popup chỉnh sửa
    function showEditEmployeePopup() {
        const checkedCb = Array.from(checkboxes).find(cb => cb.checked);
        if (!checkedCb) {
            alert('Vui lòng chọn một nhân viên để chỉnh sửa.');
            return;
        }

        // Lấy dữ liệu từ checkbox
        const id = checkedCb.value;
        const name = checkedCb.dataset.name;
        const role = checkedCb.dataset.role;
        const telegramId = checkedCb.closest('tr').querySelector('.telegram-id').textContent;

        // Điền dữ liệu vào form
        document.getElementById('old_id').value = id;
        document.getElementById('old_name').value = name;
        document.getElementById('new_id').value = id;
        document.getElementById('new_name').value = name;
        document.getElementById('new_chatID').value = telegramId;
        document.getElementById('display_telegram_id').textContent = telegramId;
        document.getElementById('new_admin').checked = (role === 'admin');

        // Hiển thị popup và overlay
        document.getElementById('edit-form-container').style.display = 'block';
        document.getElementById('overlay').classList.add('show');

        // Cuộn đến popup
        window.scrollTo({
            top: document.getElementById('edit-form-container').offsetTop - 100,
            behavior: 'smooth'
        });
    }

    // Hàm hiển thị popup thêm nhân viên
    function showAddEmployeePopup() {
        const addPopup = document.getElementById('addEmployeePopup');
        const overlay = document.getElementById('overlay');
        if (addPopup && overlay) {
            addPopup.style.display = 'block';
            overlay.classList.add('show');

            // Cuộn đến popup
            window.scrollTo({
                top: addPopup.offsetTop - 100,
                behavior: 'smooth'
            });
        }
    }

    // Hàm đóng popup
    function hidePopup() {
        document.getElementById('edit-form-container').style.display = 'none';
        document.getElementById('addEmployeePopup').style.display = 'none';
        document.getElementById('overlay').classList.remove('show');
    }

    // Gắn sự kiện đóng popup cho nút Hủy của edit form
    const cancelButton = document.getElementById('edit');
    if (cancelButton) {
        cancelButton.addEventListener('click', hidePopup);
    }

    // Gắn sự kiện đóng popup cho nút Đóng của add employee popup
    const closeAddPopupButton = document.querySelector('#addEmployeePopup .close-popup');
    if (closeAddPopupButton) {
        closeAddPopupButton.addEventListener('click', hidePopup);
    }

    // Đóng popup khi nhấn vào overlay
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.addEventListener('click', hidePopup);
    }

    // Gắn sự kiện cho nút Thêm để hiển thị popup
    const addBtn = document.querySelector('.btn-add');
    if (addBtn) {
        addBtn.addEventListener('click', showAddEmployeePopup);
    }
});
</script>
