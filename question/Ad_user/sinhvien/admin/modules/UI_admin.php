<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu khóa học theo Student ID</title>
    <style>

        h2, h3 {
            text-align: center;
            color: #00796b;
        }

        form {
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 20px;
        }

        label, input, button {
            font-size: 1rem;
        }

        input[type="text"] {
            padding: 10px;
            width: 100%;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: #009688;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        li {
            background: #f3f3f3;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 5px;
        }

        .error {
            color: #b71c1c;
            background: #ffebee;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .success {
            color: #256029;
            background: #e8f5e9;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        a {
            color: #00796b;
            text-decoration: none;
            font-weight: bold;
        }

        .test-info {
            display: flex;
            flex-direction: column; 
            justify-content: space-between;
            align-items: flex-start; /* tránh bị dồn về phải */
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #555;
            margin-top: 10px;
            gap: 10px;
        }
        
        .test-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        
        .test-actions a {
            padding: 5px 10px;
            background-color: #e0f2f1;
            border-radius: 4px;
        }
        
        .question-container {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .options {
            margin-top: 10px;
        }
        
        .option {
            margin-bottom: 5px;
            padding: 8px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        
        .correct {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        
        .incorrect {
            background-color: #ffebee;
            border-left: 4px solid #b71c1c;
        }
        
        .explanation {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        
        .test-result {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .user-answer {
            color: #b71c1c;
            font-weight: bold;
        }
        .icon-tick {
            color: #1976d2; /* Xanh dương */
            font-weight: bold;
            margin-left: 10px;
            font-size: 1.2em;
        }
        .icon-cross {
            color: #ef5350; /* Màu đỏ nhạt cho biểu tượng ✘ */
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>Tra cứu khóa học của sinh viên</h2>

    <form method="GET">
        <label for="student_id">Nhập Student ID:</label>
        <input type="text" id="student_id" name="student_id" required>
        <button type="submit">Tra cứu</button>
    </form>

<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("<div class='error'>Kết nối thất bại: " . $conn->connect_error . "</div>");
}

// Xử lý tra cứu khóa học theo Student ID
if (isset($_GET['student_id']) && !empty($_GET['student_id'])) {
    $student_id = htmlspecialchars(trim($_GET['student_id']));

    $stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $khoahoc_ids = explode(',', $row['Khoahoc']);
        $placeholders = implode(',', array_fill(0, count($khoahoc_ids), '?'));
        $types = str_repeat('i', count($khoahoc_ids));

        $sql2 = "SELECT id, khoa_hoc FROM khoa_hoc WHERE id IN ($placeholders)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param($types, ...array_map('intval', $khoahoc_ids));
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        echo "<h3>Các khóa học của sinh viên ID <strong>$student_id</strong>:</h3><ul>";
        while ($row2 = $result2->fetch_assoc()) {
            echo "<div class=test-result'>";
            echo "<li>
                {$row2['khoa_hoc']} 
                - <a href='?student_id=$student_id&khoa_hoc_id={$row2['id']}'>Xem bài test</a>
            </li>";
        }
        echo "<li>
                <span>Tổng % :$pass</span>
                
            </li>";

        echo "</ul>";
    } else {
        echo "<div class='error'>Không tìm thấy sinh viên với ID: $student_id</div>";
    }
}

// Xử lý khi nhấn "Xem bài test" để hiển thị danh sách bài test
if (isset($_GET['khoa_hoc_id'])) {
    $khoa_hoc_id = intval($_GET['khoa_hoc_id']);
    $student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';

    // Lấy tên khóa học
    $stmt_khoa = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
    $stmt_khoa->bind_param("i", $khoa_hoc_id);
    $stmt_khoa->execute();
    $khoa_result = $stmt_khoa->get_result();
    $khoa_name = $khoa_result->fetch_assoc()['khoa_hoc'];

    // Lấy danh sách bài test
    $stmt3 = $conn->prepare("SELECT id_test, ten_test FROM test WHERE id_khoa = ?");
    $stmt3->bind_param("i", $khoa_hoc_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    echo "<h3>Các bài test thuộc khóa học: <strong>$khoa_name</strong>:</h3><ul>";
    if ($result3->num_rows > 0) {
        while ($test = $result3->fetch_assoc()) {
            $test_id = $test['id_test'];
            $pass_status = ($score >= $pass_score) ? 'Đạt' : 'Không đạt';
            
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
            
            // Xác định trạng thái 
            $pass_status = '';
            if (is_numeric($score)) {
                $pass_status = ($score >= $pass_score) 
                    ? '<span class="test-status passed">ĐẬU <i class="fas fa-check-circle"></i></span>' 
                    : '<span class="test-status failed">RỚT <i class="fas fa-times-circle"></i></span>';
            }
            echo "<li>
                <div><strong>{$test['ten_test']}</strong></div>
                <div class='test-info'>
                    <span>Điểm cao nhất: $diem_cao_nhat </span>
                    <span>Số lần thử: $so_lan_thu </span>
                    <span>Bài :$score_display</span>
                </div>

                <div class='info-run'>
                    <span>Trạng thái: $score </span>
                    <sapn>Trang thái: $pass_score </span>
                <div>
                    
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

// Xử lý khi nhấn "câu hỏi" để hiển thị danh sách câu hỏi
if (isset($_GET['xem_cau_hoi'])) {
    $test_id = intval($_GET['xem_cau_hoi']);
    $student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
    $khoa_hoc_id = isset($_GET['khoa_hoc_id']) ? $_GET['khoa_hoc_id'] : '';
    
    // Lấy thông tin bài test
    $stmt_test = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
    $stmt_test->bind_param("i", $test_id);
    $stmt_test->execute();
    $test_result = $stmt_test->get_result();
    $test_name = $test_result->fetch_assoc()['ten_test'];

    // Lấy thông tin khóa học
    $stmt_khoa = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
    $stmt_khoa->bind_param("i", $khoa_hoc_id);
    $stmt_khoa->execute();
    $khoa_result = $stmt_khoa->get_result();
    $khoa_name = $khoa_result->fetch_assoc()['khoa_hoc'];

    // Lấy danh sách câu hỏi
    $stmt4 = $conn->prepare("SELECT Id_cauhoi, cauhoi, cau_a, cau_b, cau_c, cau_d, dap_an, 
                            giaithich_a, giaithich_b, giaithich_c, giaithich_d 
                            FROM quiz WHERE id_baitest = ? AND ten_khoa = ?");

    $stmt4->bind_param("ss", $test_name, $khoa_name);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    echo "<h3>Câu hỏi trong bài test: <strong>$test_name</strong> (Môn: $khoa_name):</h3>";
    if ($result4->num_rows > 0) {
        while ($q = $result4->fetch_assoc()) {
            echo "<div class='question-container'>";
            echo "<div><strong>Câu hỏi {$q['Id_cauhoi']}:</strong> {$q['cauhoi']}</div>";
            echo "<div class='options'>";
            
            // Hiển thị các lựa chọn
            $options = [
                'A' => ['text' => $q['cau_a'], 'explanation' => $q['giaithich_a']],
                'B' => ['text' => $q['cau_b'], 'explanation' => $q['giaithich_b']],
                'C' => ['text' => $q['cau_c'], 'explanation' => $q['giaithich_c']],
                'D' => ['text' => $q['cau_d'], 'explanation' => $q['giaithich_d']]
            ];
            
            foreach ($options as $key => $option) {
                $is_correct = ($key == $q['dap_an']);
                $class = $is_correct ? 'correct' : '';
                
                echo "<div class='option $class'>";
                echo "<strong>$key.</strong> {$option['text']}";
                if ($is_correct) {
                    // echo "<div class='explanation'>{$option['explanation']}</div>";
                }
                echo "</div>";
            }
            
            echo "</div></div>";
        }
    } else {
        echo "<div class='error'>Không có câu hỏi nào trong bài test này.</div>";
    }
    
}
// Xử lý khi nhấn "Xem kết quả" để hiển thị chi tiết kết quả bài test
if (isset($_GET['xem_ket_qua'])) {
    $test_id = intval($_GET['xem_ket_qua']);
    $student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
    $khoa_hoc_id = isset($_GET['khoa_hoc_id']) ? $_GET['khoa_hoc_id'] : '';
    
    // Lấy thông tin bài test
    $stmt_test = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
    $stmt_test->bind_param("i", $test_id);
    $stmt_test->execute();
    $test_result = $stmt_test->get_result();
    if (!$test_result->num_rows) {
        echo "<div class='error'>Không tìm thấy bài test ID: $test_id.</div>";
        return;
    }
    $test_name = $test_result->fetch_assoc()['ten_test'];
    
    // Lấy thông tin khóa học
    $stmt_khoa = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
    $stmt_khoa->bind_param("i", $khoa_hoc_id);
    $stmt_khoa->execute();
    $khoa_result = $stmt_khoa->get_result();
    if (!$khoa_result->num_rows) {
        echo "<div class='error'>Không tìm thấy khóa học ID: $khoa_hoc_id.</div>";
        return;
    }
    $khoa_name = $khoa_result->fetch_assoc()['khoa_hoc'];
    
    // Lấy kết quả từ bảng ket_qua
    $stmt_ketqua = $conn->prepare("SELECT kq_cao_nhat, tt_bai_test FROM ket_qua 
                                   WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
    $stmt_ketqua->bind_param("iis", $student_id, $khoa_hoc_id, $test_id);
    $stmt_ketqua->execute();
    $ketqua_result = $stmt_ketqua->get_result();
    $ketqua_data = $ketqua_result->fetch_assoc();
    
    if ($ketqua_data) {
        $tt_bai_test = $ketqua_data['tt_bai_test'];
        $kq_cao_nhat = $ketqua_data['kq_cao_nhat'];
        
        echo "<h3>Kết quả bài test: <strong>$test_name</strong> (Môn: $khoa_name)</h3>";
        echo "<div class='test-result'>";
        echo "<p><strong>Điểm cao nhất:</strong> " . htmlspecialchars($kq_cao_nhat) . "</p>";
        
        // Phân tích tt_bai_test
        $user_answers = [];
        if (!empty($tt_bai_test)) {
            if (preg_match_all('/Câu\s+(\d+):\s*([A-D])/', $tt_bai_test, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $question_id = trim($match[1]);
                    $answer = strtoupper(trim($match[2]));
                    $user_answers[$question_id] = $answer;
                }
            } else {
                echo "<div class='error'>Không thể phân tích tt_bai_test. Định dạng không khớp.</div>";
            }
        } else {
            echo "<div class='error'>tt_bai_test trống.</div>";
        }
        
        // Lấy danh sách câu hỏi
        $stmt_quiz = $conn->prepare("SELECT Id_cauhoi, cauhoi, cau_a, cau_b, cau_c, cau_d, dap_an 
                                     FROM quiz WHERE id_baitest = ? AND ten_khoa = ? ORDER BY Id_cauhoi");
        $stmt_quiz->bind_param("ss", $test_name, $khoa_name);
        $stmt_quiz->execute();
        $quiz_result = $stmt_quiz->get_result();
        
        if ($quiz_result->num_rows == 0) {
            echo "<div class='error'>Không tìm thấy câu hỏi nào cho bài test này.</div>";
        } else {
            $question_number = 1;
            $index = 1; // Sử dụng index để ánh xạ theo thứ tự nếu Id_cauhoi không khớp
            while ($q = $quiz_result->fetch_assoc()) {
                $cau_id = $q['Id_cauhoi'];
                $user_answer = isset($user_answers[$index]) ? $user_answers[$index] : null; // Thử ánh xạ theo thứ tự
                $dap_an_dung = strtoupper($q['dap_an']);
                
                echo "<div class='question-block'>";
                echo "<p><strong>Câu $question_number (ID: $cau_id):</strong> " . htmlspecialchars($q['cauhoi']) . "</p>";
                
                echo "<ul>";
                $choices = [
                    'A' => $q['cau_a'],
                    'B' => $q['cau_b'],
                    'C' => $q['cau_c'],
                    'D' => $q['cau_d']
                ];
                
                foreach ($choices as $key => $value) {
                    $is_selected = ($user_answer === $key);
                    $is_correct = ($key === $dap_an_dung);
                    $class = '';
                    
                    if ($is_selected) {
                        $class = $is_correct ? 'correct' : 'incorrect';
                    } elseif ($is_correct) {
                        $class = 'correct';
                    }
                    
                    $icon = '';
                    if ($is_selected && $is_correct) {
                        $icon = '<span class="icon-tick">✔</span>';
                    } elseif ($is_selected && !$is_correct) {
                        $icon = '<span class="icon-cross">✘</span>';
                    }
                    
                    echo "<li class='$class'>";
                    echo $key . ". " . htmlspecialchars($value) . " $icon";
                    echo "</li>";
                }
                echo "</ul>";
                
                if ($user_answer !== null) {
                    echo "<div class='answer-info'>";
                    echo "</div>";
                } else {
                    echo "<div class='answer-info'>";
                    echo "<p>Bạn chưa trả lời câu này (Debug: cau_id = $cau_id, Đáp án: " . ($user_answer !== null ? $user_answer : 'Không tìm thấy') . ", Index: $index)</p>";
                    echo "</div>";
                }
                
                echo "</div>";
                $question_number++;
                $index++; // Tăng index để ánh xạ theo thứ tự
            }
        }
        echo "</div>";
    } else {
        echo "<div class='error'>Sinh viên chưa làm bài test này (student_id: $student_id, khoa_id: $khoa_hoc_id, test_id: $test_id).</div>";
    }
}
$conn->close();
?>

</body>
</html>