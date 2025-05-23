let employeeData = [
    {id: 1, ma_nv: 'NV001', ten_nv: 'Nguyễn Văn A', tk_nextcloud: 'nva', chuc_vu: 'Quản lý'},
    {id: 2, ma_nv: 'NV002', ten_nv: 'Trần Thị B', tk_nextcloud: 'ttb', chuc_vu: 'Nhân viên'},
    {id: 3, ma_nv: 'NV003', ten_nv: 'Lê Văn C', tk_nextcloud: 'lvc', chuc_vu: 'Nhân viên'}
];

document.addEventListener('DOMContentLoaded', function() {
    // Hiển thị danh sách nhân viên khi trang được tải
    loadEmployees();

    // Hiển thị popup Thêm nhân viên
    document.getElementById('addButton').onclick = function() {
        document.getElementById('addEmployeePopup').style.display = 'block';
    };

    // Đóng popup
    document.querySelectorAll('.close-popup').forEach(function(btn) {
        btn.onclick = function() {
            document.querySelectorAll('.popup').forEach(function(popup) {
                popup.style.display = 'none';
            });
        };
    });

    // Hiển thị popup Sửa nhân viên
    document.getElementById('editButton').onclick = function() {
        const selectedRows = document.querySelectorAll('.row-checkbox:checked');
        if(selectedRows.length !== 1) {
            showMessage('Vui lòng chọn một nhân viên để sửa', true);
            return;
        }
        const row = selectedRows[0].closest('tr');
        const id = Number(row.dataset.employeeId);
        const employee = employeeData.find(e => e.id === id);
        if(employee) {
            document.getElementById('old_id').value = employee.id;
            document.getElementById('editMaNV').value = employee.ma_nv;
            document.getElementById('editTenNV').value = employee.ten_nv;
            document.getElementById('editTkNextcloud').value = employee.tk_nextcloud;
            document.getElementById('editPosition').value = employee.chuc_vu;
            document.getElementById('editEmployeePopup').style.display = 'block';
        }
    };

    // Hiển thị popup Quản lý phòng ban
    document.getElementById('manageDepartmentButton').onclick = function() {
        document.getElementById('manageDepartmentPopup').style.display = 'block';
    };

    // Nút Xóa
    document.getElementById('deleteButton').onclick = function() {
        const selectedRows = document.querySelectorAll('.row-checkbox:checked');
        if(selectedRows.length === 0) {
            showMessage('Vui lòng chọn nhân viên cần xóa', true);
            return;
        }
        if(confirm('Bạn có chắc chắn muốn xóa các nhân viên đã chọn?')) {
            const idsToDelete = Array.from(selectedRows).map(cb => Number(cb.value));
            employeeData = employeeData.filter(emp => !idsToDelete.includes(emp.id));
            showMessage('Xóa nhân viên thành công');
            loadEmployees();
        }
    };

    // Nút Xuất (chỉ demo, không thực hiện xuất file)
    document.getElementById('exportButton').onclick = function(e) {
        showMessage('Chức năng xuất chỉ demo giao diện!', false);
        e.preventDefault();
    };

    // Xử lý form thêm nhân viên
    document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const newId = employeeData.length ? Math.max(...employeeData.map(e => e.id)) + 1 : 1;
        const formData = {
            id: newId,
            ma_nv: document.getElementById('maNV').value,
            ten_nv: document.getElementById('tenNV').value,
            tk_nextcloud: document.getElementById('tkNextcloud').value,
            chuc_vu: document.getElementById('position').value
        };
        employeeData.push(formData);
        showMessage('Thêm nhân viên thành công');
        document.getElementById('addEmployeePopup').style.display = 'none';
        loadEmployees();
        this.reset();
    });

    // Xử lý form sửa nhân viên
    document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = Number(document.getElementById('old_id').value);
        const employee = employeeData.find(e => e.id === id);
        if(employee) {
            employee.ma_nv = document.getElementById('editMaNV').value;
            employee.ten_nv = document.getElementById('editTenNV').value;
            employee.tk_nextcloud = document.getElementById('editTkNextcloud').value;
            employee.chuc_vu = document.getElementById('editPosition').value;
            showMessage('Cập nhật nhân viên thành công');
            document.getElementById('editEmployeePopup').style.display = 'none';
            loadEmployees();
        }
    });

    // Xử lý tìm kiếm
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const keyword = e.target.value.trim().toLowerCase();
        if(keyword) {
            const filtered = employeeData.filter(emp =>
                emp.ma_nv.toLowerCase().includes(keyword) ||
                emp.ten_nv.toLowerCase().includes(keyword) ||
                emp.tk_nextcloud.toLowerCase().includes(keyword) ||
                emp.chuc_vu.toLowerCase().includes(keyword)
            );
            updateEmployeeTable(filtered);
        } else {
            updateEmployeeTable(employeeData);
        }
    });

    // Xử lý checkbox "Chọn tất cả"
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
});

// Hàm load danh sách nhân viên
function loadEmployees() {
    updateEmployeeTable(employeeData);
}

// Hàm cập nhật bảng nhân viên
function updateEmployeeTable(employees) {
    const tbody = document.querySelector('#employeeTable tbody');
    tbody.innerHTML = '';
    employees.forEach((employee, index) => {
        const tr = document.createElement('tr');
        tr.dataset.employeeId = employee.id;
        tr.dataset.employeeName = employee.ten_nv;
        tr.dataset.nextcloudAccount = employee.tk_nextcloud;
        tr.dataset.position = employee.chuc_vu === 'Quản lý' ? 'manager' : '';
        tr.innerHTML = `
            <td><input type="checkbox" name="emp_ids[]" value="${employee.id}" class="row-checkbox"></td>
            <td>${index + 1}</td>
            <td>${employee.ma_nv}</td>
            <td>${employee.ten_nv}</td>
            <td>${employee.tk_nextcloud}</td>
            <td>${employee.chuc_vu}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Hàm hiển thị thông báo
function showMessage(message, isError = false) {
    const popup = document.getElementById('messagePopup');
    const messageTitle = document.getElementById('messageTitle');
    const messageText = document.getElementById('messageText');
    messageTitle.textContent = isError ? 'Lỗi' : 'Thông báo';
    messageText.textContent = message;
    popup.style.display = 'flex';
    messageTitle.style.color = isError ? '#e74c3c' : '#2ecc71';
}