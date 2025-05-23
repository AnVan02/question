
<?php 
style('AIFilterCV', 'style'); 
script('AIFilterCV', 'script');
?> 

<div class="container"> 
    <h2>Huấn Luyện Mô Hình</h2> 
    <form method="POST" action="<?php p($_['train_url']); ?>" id="train-form"> 
        <button type="submit">Huấn Luyện Mô Hình</button> 
    </form> 
</div>

<div class="container" style="margin-top: 30px;">
    <form method="POST" action="<?php p($_['add_url']); ?>" id="add-form">
        <button type="submit">➕ Thêm Nhân Viên</button>
    </form>
</div>


<div class="container" style="margin-top: 30px;">
    <form method="POST" action="<?php p($_['batch_url']); ?>" id="add-form">
        <button type="submit"> Thêm Loat</button>
    </form>
</div>

<?php if (!empty($_['message']) || !empty($_['error'])): ?>
    <div id="popup" class="popup <?php echo empty($_['error']) ? 'success' : 'error'; ?>">
        <span><?php p($_['message'] ?? $_['error']); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($_['data'])): ?>
    <div class="container">
        <h3>Danh Sách Nhân Viên</h3>

        <form method="POST" action="<?php p($_['del_url']); ?>" id="delete-form">
            <div style="margin-bottom: 10px; display: flex; gap: 10px; align-items: center;">
                <button type="submit" style="background-color: #d9534f; color: white; border: none; padding: 8px 16px; cursor: pointer;">
                    Xóa nhân viên đã chọn
                </button>
                <button type="button" id="edit-selected-btn" style="background-color: #5bc0de; color: white; border: none; padding: 8px 16px; cursor: pointer; display: none;">
                    Sửa nhân viên đã chọn
                </button>
            </div>

            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc;">
                <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                    <thead style="position: sticky; top: 0; background-color: #f9f9f9; z-index: 1;">
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th style="width: 50px; text-align: center;">STT</th>
                            <th style="width: 80px;">ID</th>
                            <th>Tên Nhân Viên</th>
                            <th>Telegram ID</th>
                            <th style="width: 120px; text-align: center;">Phân Quyền</th>
                            <th style="width: 80px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_['data'] as $index => $row): ?>
                            <tr data-id="<?= htmlspecialchars($row[0]) ?>">
                                <td style="text-align: center;">
                                    <input type="checkbox" name="emp_ids[]" value="<?= htmlspecialchars($row[0]) ?>" class="emp-checkbox">
                                </td>
                                <td style="text-align: center;"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($row[0]) ?></td>
                                <td><?= htmlspecialchars($row[1]) ?></td>
                                <td><?= htmlspecialchars($row[2]) ?></td>
                                <td style="text-align: center;">
                                    <?php if (!empty($row[3])): ?>
                                        <span class="admin">Admin</span>
                                    <?php else: ?>
                                        <span class="normal">Nhân viên</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="edit-btn"
                                        data-id="<?= htmlspecialchars($row[0]) ?>"
                                        data-name="<?= htmlspecialchars($row[1]) ?>"
                                        data-chatid="<?= htmlspecialchars($row[2]) ?>"
                                        data-role="<?= !empty($row[3]) ? 'admin' : 'normal' ?>">
                                        ✏️
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <!-- <body> -->
    <div class="container" style="margin-top: 30px;">
        <h2>Thêm Nhân Viên Hàng Loạt</h2>
        
        <form method="POST" action="<?php p($_['batch_url']); ?>" enctype="multipart/form-data" id="batch-add-form">
            <div style="margin-bottom: 15px;">
                <label for="excel_file" style="display: block; margin-bottom: 5px; font-weight: bold;">Chọn file Excel:</label>
                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="image_dir" style="display: block; margin-bottom: 5px; font-weight: bold;">Thư mục ảnh nhân viên:</label>
                <input type="text" name="image_dir" id="image_dir" required 
                    placeholder="Ví dụ: uploads/images" 
                    style="padding: 8px; width: 100%; max-width: 400px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <button type="submit" style="background-color: #5cb85c; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                ➕ Thêm Nhân Viên Hàng Loạt
            </button>
        </form>
    </div>
    <!-- </body> -->

    <!-- Form chỉnh sửa -->
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
<?php endif; ?>

