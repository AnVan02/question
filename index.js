document.addEventListener('DOMContentLoaded', function() {
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

    // Xử lý nút sửa
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const chatID = this.dataset.chatid;
            const role = this.dataset.role;
            
            document.getElementById('edit-form-container').style.display = 'block';
            
            document.getElementById('old_id').value = id;
            document.getElementById('old_name').value = name;
            document.getElementById('new_id').value = id;
            document.getElementById('new_name').value = name;
            document.getElementById('new_chatid').value = chatID;
            document.getElementById('actions').value = role;
     
            window.scrollTo({
                top: document.getElementById('edit-form-container').offsetTop - 100,
                behavior: 'smooth'
            });
        });
    });
});
nv
// Xử lý sự kiện nút Sửa
document.getElementById('editButton').addEventListener('click', function() {
    const selectedRows = document.querySelectorAll('.row-checkbox:checked');
    
    if (selectedRows.length === 0) {
        showMessagePopup('Thông báo', 'Vui lòng chọn ít nhất một nhân viên để sửa');
        return;
    }
    
    if (selectedRows.length > 1) {
        showMessagePopup('Thông báo', 'Chỉ có thể sửa một nhân viên tại một thời điểm');
        return;
    }
    
    const row = selectedRows[0].closest('tr');
    openEditEmployeePopup(row);
});

// Mở popup chỉnh sửa nhân viên
function openEditEmployeePopup(row) {
    const popup = document.getElementById('editEmployeePopup');
    const employeeId = row.dataset.employeeId;
    const employeeName = row.dataset.employeeName;
    const nextcloudAccount = row.dataset.nextcloudAccount;
    const isManager = row.dataset.position === 'manager';
    
    // Điền thông tin vào form
    document.getElementById('editEmployeeId').value = employeeId;
    document.getElementById('editMaNV').value = employeeId;
    document.getElementById('editTenNV').value = employeeName;
    document.getElementById('editTkNextcloud').value = nextcloudAccount;
    document.getElementById('editPosition').value = isManager ? 'manager' : '';
    
    
    // Hiển thị popup
    popup.style.display = 'block';
    document.getElementById('result-overlay').style.display = 'block';
}

// Đóng tất cả popup
function closeAllPopups() {
    document.querySelectorAll('.popup').forEach(popup => {
        popup.style.display = 'none';
    });
    document.getElementById('result-overlay').style.display = 'none';
}

// Hiển thị popup thông báo
function showMessagePopup(title, message) {
    document.getElementById('messageTitle').textContent = title;
    document.getElementById('messageText').textContent = message;
    document.getElementById('messagePopup').style.display = 'block';
    document.getElementById('result-overlay').style.display = 'block';
}

// Đóng popup thông báo
function closeMessagePopup() {
    document.getElementById('messagePopup').style.display = 'none';
    document.getElementById('result-overlay').style.display = 'none';
}

// Thêm sự kiện đóng popup
document.querySelectorAll('.close-popup').forEach(button => {
    button.addEventListener('click', closeAllPopups);
});

// Mở popup chỉnh sửa
function openEditEmployeePopup(row) {
    const employeeId = row.dataset.employeeId;
    const employeeName = row.dataset.employeeName;
    const nextcloudAccount = row.dataset.nextcloudAccount;
    const isManager = row.dataset.position === 'manager';
    const department = row.dataset.department || '';
    
    // Điền dữ liệu vào form
    document.getElementById('old_id').value = employeeId;
    document.getElementById('old_name').value = employeeName;
    document.getElementById('editEmployeeId').value = employeeId;
    document.getElementById('editMaNV').value = employeeId;
    document.getElementById('editTenNV').value = employeeName;
    document.getElementById('editTkNextcloud').value = nextcloudAccount;
    document.getElementById('editPosition').value = isManager ? 'manager' : '';
    document.getElementById('editDepartment').value = department;
    
    // Load ảnh nếu có
    const imagePath = row.dataset.imagePath || '';
    if (imagePath) {
        document.getElementById('editImagePreview').src = imagePath;
        document.getElementById('editImagePreview').style.display = 'block';
    }
    
    // Hiển thị popup
    document.getElementById('editEmployeePopup').style.display = 'block';
}

// Đóng popup
document.getElementById('cancel-edit').addEventListener('click', function() {
    document.getElementById('editEmployeePopup').style.display = 'none';
});

// Xử lý khi click nút Sửa
document.getElementById('editButton').addEventListener('click', function() {
    const selectedRows = document.querySelectorAll('.row-checkbox:checked');
    
    if (selectedRows.length === 0) {
        alert('Vui lòng chọn ít nhất một nhân viên để sửa');
        return;
    }
    
    if (selectedRows.length > 1) {
        alert('Chỉ có thể sửa một nhân viên tại một thời điểm');
        return;
    }
    
    const row = selectedRows[0].closest('tr');
    openEditEmployeePopup(row);
});

// Xử lý khi chọn file ảnh
document.getElementById('editImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('editImagePreview').src = event.target.result;
            document.getElementById('editImagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});


