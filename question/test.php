// Trong phần xử lý hiển thị danh sách bài test
if (isset($_GET['khoa_hoc_id'])) {
    // ... [phần code trước giữ nguyên] ...

    echo "<h3>Các bài test thuộc khóa học: <strong>$khoa_name</strong>:</h3><ul>";
    if ($result3->num_rows > 0) {
        while ($test = $result3->fetch_assoc()) {
            $test_id = $test['id_test'];
            $pass_score = $test['diem_dat']; // Điểm đạt yêu cầu

            // Lấy kết quả cao nhất
            $stmt_ketqua = $conn->prepare("SELECT kq_cao_nhat FROM ket_qua 
                                        WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
            $stmt_ketqua->bind_param("iis", $student_id, $khoa_hoc_id, $test_id);
            $stmt_ketqua->execute();
            $ketqua_result = $stmt_ketqua->get_result();
            $ketqua_data = $ketqua_result->fetch_assoc();
            
            $score = $ketqua_data['kq_cao_nhat'] ?? null;
            $score_display = $score ?? 'Chưa làm';
            
            // Xác định trạng thái
            $pass_status = '';
            if (is_numeric($score)) {
                $pass_status = ($score >= $pass_score) 
                    ? '<span class="test-status passed">ĐẬU <i class="fas fa-check-circle"></i></span>' 
                    : '<span class="test-status failed">RỚT <i class="fas fa-times-circle"></i></span>';
            }

            echo "<li>
                <div class='test-header'>
                    <div><strong>{$test['ten_test']}</strong></div>
                    $pass_status
                </div>
                <div class='test-info'>
                    <span>Điểm: $score_display</span>
                    <span>Điểm đạt: $pass_score</span>
                </div>
                <div class='test-actions'>
                    <a href='?student_id=$student_id&khoa_hoc_id=$khoa_hoc_id&xem_ket_qua={$test['id_test']}'>
                        Xem chi tiết
                    </a>
                </div>
            </li>";
        }
    } else {
        echo "<li>Không có bài test nào.</li>";
    }
    echo "</ul>";
}

echo "<h3>Các bài test thuộc khóa học: <strong>$khoa_name</strong>:</h3><ul>";
    if ($result3->num_rows > 0) {
        while ($test = $result3->fetch_assoc()) {
            $test_id = $test['id_test'];
            // $pass_score = $test['diem_dat']; // Điểm đạt yêu cầu

            
            // Lấy thông tin từ bảng ket_qua
            $stmt_ketqua = $conn->prepare("SELECT kq_cao_nhat, tt_bai_test FROM ket_qua 
                                          WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
            $stmt_ketqua->bind_param("iis", $student_id, $khoa_hoc_id, $test_id);
            $stmt_ketqua->execute();
            $ketqua_result = $stmt_ketqua->get_result();
            $ketqua_data = $ketqua_result->fetch_assoc();
            
            $diem_cao_nhat = $ketqua_data['kq_cao_nhat'] ?? 'Chưa có';
            
            $tt_bai_test = $ketqua_data['tt_bai_test'] ?? '';
            
            // Đếm số lần thử từ tt_bai_test (mỗi câu hỏi là một lần thử)
            $so_lan_thu = $ketqua_data ? substr_count($tt_bai_test, 'Câu') : 0;
            
            // Xác định trang thái đậu hay rớt 
            $status ='';
            if (is_numeric ($diem_cao_nhat)) {
                    '<span class="test-status passed">ĐẬU</span>' ;
                    '<span class="test-status failed">RỚT</span>';
                    
            }
            // Xác định trạng thái
            
            
            echo "<li>
                <div><strong>{$test['ten_test']}</strong></div>
                <div class='test-info'>
                    <span>Điểm cao nhất: $diem_cao_nhat</span>
                    <span>Số lần thử: $so_lan_thu </span>
                    
                </div>

                <div class='test-actions'>
                    <a href='?student_id=$student_id&khoa_hoc_id=$khoa_hoc_id&xem_ket_qua={$test['id_test']}'>Kết qủa sinh viên </a>
                </div>
            </li>";
        }
    } else {
        echo "<li>Không có bài test nào.</li>";
    }
    echo "</ul>";
}

