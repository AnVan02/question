<?php 
require_once __DIR__ . '/helpers.php';
style('AIFilterCV', 'nhansu'); 
script('AIFilterCV', 'nhanvien');


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

?>

<div class="container">
    <div class="sidebar">
        <ul>
            <li><button class="action-btk"><i class="fas fa-book-open"></i> Nhân sự</button></li>
            <li><button class="action-btk"><i class="fas fa-address-card"></i> Chấm công</button></li>
            <li><button class="action-btk"><i class="fas fa-chalkboard-teacher"></i> Giám sát</button></li>
            <li><button class="action-btk active"><i class="fas fa-cog"></i> Cài đặt</button></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">           
            <div class="header-left-group">
                <button id="addButton"><b>Thêm</b></button>
                <button id="deleteButton"><b>Xóa</b></button> 
                <button id="editButton"><b>Sửa</b></button> 
                <form method="POST" action="<?php p($_['export_url']); ?>" id="export_form" style="display:inline;">
                    <input type="hidden" name="export_excel" value="true">
                    <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>">
                    <button type="submit" id="exportButton"><b>Xuất</b></button>
                </form>
                <button id="manageDepartmentButton"><b>Phòng Ban</b></button> 
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Tìm kiếm...">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>

    <div id="result-overlay" class="overlay"></div>
        <div class="form-container">
            <div class="employee-table-container">
                <table id="employeeTable" class="employee-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th><b>STT</b></th>
                            <th><b>Mã Nhân viên</b></th>
                            <th><b>Tên Nhân viên</b></th>
                            <th><b>Tài khoản Nextcloud</b></th>
                            <th><b>QL</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu sẽ được JS render -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Popup Form thêm nhân viên -->
        <div id="addEmployeePopup" class="popup" style="display: none;">
            <div class="popup-content">
                <h2>Thêm Nhân Viên</h2>
                <form id="addEmployeeForm" method="POST" action="" enctype="multipart/form-data">
                    <div class="employee-info-row">
                        <div class="employee-info-field">
                            <label for="maNV">Mã NV:</label>
                            <input type="text" id="maNV" name="maNV" required>
                        </div>
                        <div class="employee-info-field">
                            <label for="tenNV">Tên NV:</label>
                            <input type="text" id="tenNV" name="tenNV" required>
                        </div>
                    </div>

                    <label for="department">Phòng ban:</label>
                    <select id="department" name="department">
                        <option value="">Chọn phòng ban</option>
                        <!-- Options sẽ được thêm bằng JavaScript nếu cần -->
                    </select>

                    <label for="position">Chức vụ:</label>
                    <select id="position" name="position">
                        <option value="Nhân viên">Nhân viên</option>
                        <option value="Quản lý">Quản lý</option>
                    </select>

                    <label for="tkNextcloud">TK Nextcloud:</label>
                    <input type="text" id="tkNextcloud" name="tkNextcloud" required>
                    
                    <label for="image">Thư mục chứa hình ảnh:</label>
                    <input type="file" id="image" name="images[]" accept="image/*" webkitdirectory directory multiple>
                    
                    <div class="popup-buttons">
                        <button type="submit">Thêm Nhân Viên</button>
                        <button type="button" class="close-popup">Đóng</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup thông báo -->
        <div id="messagePopup" class="popup" style="display: none;">
            <div class="popup-content">
                <h2 id="messageTitle">Thông báo</h2>
                <p id="messageText"></p>
                <button type="button" class="close-popup" onclick="closeMessagePopup()">Đóng</button>
            </div>
        </div>

        <!-- Popup Form chỉnh sửa nhân viên -->
        <!-- code 1  -->
        <div id="editEmployeePopup" class="popup" style="display: none;">
            <div class="popup-content">
                <h2>Chỉnh sửa Nhân Viên</h2>
                <form method="POST" action="" id="editEmployeeForm" enctype="multipart/form-data">
                    <input type="hidden" name="old_id" id="old_id">
                    
                    <div class="employee-info-row">
                        <div class="employee-info-field">
                            <label for="editMaNV">Mã NV:</label>
                            <input type="text" id="editMaNV" name="new_id" required>
                        </div>
                        <div class="employee-info-field">
                            <label for="editTenNV">Tên NV:</label>
                            <input type="text" id="editTenNV" name="new_name" required>
                        </div>
                    </div>

                    <label for="editDepartment">Phòng ban:</label>
                    <select id="editDepartment" name="department">
                        <option value="">Chọn phòng ban</option>
                    </select>

                    <label for="editPosition">Chức vụ:</label>
                    <select id="editPosition" name="new_role">
                        <option value="Nhân viên">Nhân viên</option>
                        <option value="Quản lý">Quản lý</option>
                    </select>

                    <label for="editTkNextcloud">TK Nextcloud:</label>
                    <input type="text" id="editTkNextcloud" name="new_chatid" required>
                    
                    <label for="editImage">Ảnh đại diện:</label>
                    <input type="file" id="editImage" name="editImage" accept="image/*">
                    
                    <div class="employee-image-preview">
                        <img id="editImagePreview" src="" alt="Ảnh nhân viên" style="display: none; max-width: 200px;">
                    </div>
                    
                    <div class="popup-buttons">
                        <button type="submit">Lưu thay đổi</button>
                        <button type="button" class="close-popup">Đóng</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- code 2 -->
         <div class="container" id="edit-form-container" style="display: none; margin-top: 30px;">
            <h3>Chỉnh sửa nhân viên</h3>
            <form method="POST" action="<?php p($_['edit_url']); ?>" id="edit-form">
                <input type="hidden" name="old_id" id="old_id">
                <input type="hidden" name="old_name" id="old_name">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label for="new_id">ID nhân viên:</label>
                        <input type="text" name="new_id" id="new_id" required>
                    </div>

                    <div>
                        <label for="new_name">Tên nhân viên:</label>
                        <input type="text" name="new_name" id="new_name" required>
                    </div>

                    <div>
                        <label for="new_chatid">Telegram Chat ID:</label>
                        <input type="text" name="new_chatid" id="new_chatid">
                    </div>

                    <div>
                        <label for="new_role">Vai trò:</label>
                        <select name="new_role" id="new_role">
                            <option value="normal">Nhân viên</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" name="submit" value="edit" class="btn-update">Cập nhật</button>
                    <button type="button" id="cancel-edit" class="btn-cancel" style="margin-left: 10px;">Hủy bỏ</button>
                </div>
            </form>
        </div>

        <!-- Popup quản lý phòng ban -->
        <div id="manageDepartmentPopup" class="popup" style="display: none;">
            <div class="popup-content">
                <h2>Quản lý phòng ban</h2>
                <div class="department-container">
                    <div class="department-list">
                        <h3>Danh sách phòng ban</h3>
                        <ul id="departmentList">
                            <!-- Danh sách phòng ban sẽ được thêm bằng JavaScript -->
                        </ul>
                    </div>
                    <div class="department-form">
                        <h3>Thêm/Sửa phòng ban</h3>
                        <input type="text" id="departmentName" placeholder="Nhập tên phòng ban" required>
                        <div class="department-buttons">
                            <button id="addOrSaveDepartmentButton">Thêm</button>
                            <button id="deleteDepartmentButton" style="display: none;">Xóa</button>
                        </div>
                    </div>
                </div>
                <div class="popup-buttons">
                    <button type="button" class="close-popup">Đóng</button>
                </div>
            </div>
        </div>

        

        <div class="twocontainer">
            <div class="twocontainer-left-group"><button id="batchAddButton"><b>Thêm loạt</b></button></div>
            <form method="POST" action="" id="reload-form">
                <div class="twocontainer-right-group">
                    <button type="submit"><b>Làm mới</b></button>
                </div>
            </form>
            <?php /* Bỏ qua popup thông báo PHP cũ nếu sử dụng JS showMessage */ ?>
        </div> 
    </div>

    <div class="lastcontainer">
        <!-- Nội dung cho lastcontainer có thể được thêm vào đây -->
    </div>
</div>
