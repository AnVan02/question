<?php 
// Trong phần xử lý xem_ket_qua
if (isset($_GET['xem_ket_qua'])) {
    $test_id = $conn->real_escape_string($_GET['xem_ket_qua']);
    $student_id = $conn->real_escape_string($_GET['student_id'] ?? '');
    $khoa_hoc_id = intval($_GET['khoa_hoc_id'] ?? 0);
    
    // Lấy thông tin bài test bao gồm so_cau_hien_thi
    $test_info = $conn->query("
        SELECT t.ten_test, t.Pass as required_pass_percent, t.so_cau_hien_thi 
        FROM test t 
        WHERE t.id_test = '$test_id'
    ");
    $khoa_info = $conn->query("SELECT khoa_hoc FROM khoa_hoc WHERE id = $khoa_hoc_id");
    
    if ($test_info && $test_info->num_rows > 0 && $khoa_info && $khoa_info->num_rows > 0) {
        $test_data = $test_info->fetch_assoc();
        $test_name = $test_data['ten_test'];
        $required_pass_percent = $test_data['required_pass_percent'] ?? '80';
        $so_cau_hien_thi = $test_data['so_cau_hien_thi'] ?? 10; // Lấy số câu hiển thị
        $khoa_name = $khoa_info->fetch_assoc()['khoa_hoc'];
        
        // Lấy kết quả chi tiết
        $ketqua = $conn->query("
            SELECT kq_cao_nhat, tt_bai_test 
            FROM ket_qua 
            WHERE student_id = '$student_id' AND khoa_id = $khoa_hoc_id AND test_id = '$test_id'
        ");
        
        if ($ketqua && $ketqua->num_rows > 0) {
            $ketqua_data = $ketqua->fetch_assoc();
            $tt_bai_test = $ketqua_data['tt_bai_test'];
            $kq_cao_nhat = $ketqua_data['kq_cao_nhat'];
            
            // Tính toán dựa trên so_cau_hien_thi thay vì tổng số câu hỏi
            $total_questions = $so_cau_hien_thi;
            $percentage = is_numeric($kq_cao_nhat) && $total_questions > 0 ? 
                         round(($kq_cao_nhat / $total_questions) * 100, 1) : 0;
            
            $required_score = ceil($total_questions * $required_pass_percent / 100);
            $is_passed = is_numeric($kq_cao_nhat) && ($kq_cao_nhat >= $required_score);
            
            echo "<h3>Kết quả bài test: <strong>$test_name</strong> (Môn: $khoa_name)</h3>";
            echo "<div class='test-result'>";
            echo "<p>Số câu hỏi: $so_cau_hien_thi | Yêu cầu đạt: $required_pass_percent% (≥$required_score câu)</p>";
            
            if ($is_passed) {
                echo "<p style='color: green; font-weight: bold;'>✅ Đạt yêu cầu! ($kq_cao_nhat/$so_cau_hien_thi câu đúng)</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>⚠️ Chưa đạt! ($kq_cao_nhat/$so_cau_hien_thi câu đúng - cần ≥$required_score)</p>";
            }
            
            // Phân tích tt_bai_test
            $user_answers = [];
            if (!empty($tt_bai_test)) {
                if (preg_match_all('/(Câu\s*(\d+)\s*:\s*([A-Da-d]))/i', $tt_bai_test, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $question_num = trim($match[2]);
                        $answer = strtoupper(trim($match[3]));
                        $user_answers[$question_num] = $answer;
                    }
                }
            }
            
            // Lấy danh sách câu hỏi ngẫu nhiên giới hạn bởi so_cau_hien_thi
            $quiz_result = $conn->query("
                SELECT Id_cauhoi, cauhoi, cau_a, cau_b, cau_c, cau_d, dap_an 
                FROM quiz 
                WHERE id_baitest = '$test_name' AND ten_khoa = '$khoa_name' 
                ORDER BY RAND() 
                LIMIT $so_cau_hien_thi
            ");
            
            if ($quiz_result && $quiz_result->num_rows > 0) {
                $question_number = 1;
                while ($q = $quiz_result->fetch_assoc()) {
                    $user_answer = $user_answers[$question_number] ?? null;
                    $dap_an_dung = strtoupper($q['dap_an']);
                    
                    echo "<div class='question-container'>";
                    echo "<p><strong>Câu $question_number (ID: {$q['Id_cauhoi']}):</strong> " . htmlspecialchars($q['cauhoi']) . "</p>";
                    
                    echo "<div class='options'>";
                    $choices = ['A' => $q['cau_a'], 'B' => $q['cau_b'], 'C' => $q['cau_c'], 'D' => $q['cau_d']];
                    
                    foreach ($choices as $key => $value) {
                        $is_selected = ($user_answer === $key);
                        $is_correct = ($key === $dap_an_dung);
                        $class = '';
                        
                        if ($is_selected) {
                            $class = $is_correct ? 'correct' : 'incorrect';
                        } elseif ($is_correct) {
                            $class = 'correct';
                        }
                        
                        $icon = $is_selected ? 
                               ($is_correct ? '<span class="icon-tick">✔</span>' : '<span class="icon-cross">✘</span>') : '';
                        
                        echo "<div class='option $class'>";
                        echo $key . ". " . htmlspecialchars($value) . " $icon";
                        echo "</div>";
                    }
                    echo "</div>";
                    echo "</div>";
                    $question_number++;
                }
            } else {
                echo "<div class='error'>Không tìm thấy câu hỏi nào cho bài test này.</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='error'>Sinh viên chưa làm bài test này.</div>";
        }
    } else {
        echo "<div class='error'>Không tìm thấy thông tin bài test hoặc khóa học.</div>";
    }
}

?>