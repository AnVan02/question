<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu khóa học theo Student ID</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2 80%);
            margin: 0;
            padding: 0;
            font-size: 17px;
            color: #222;
            max-width: 1350px;
            margin: 45px auto;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

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

        .course-header, .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
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
            margin-bottom: 5px;
            font-size: 1.0em;
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
            color: #1976d2;
            font-weight: bold;
            margin-left: 10px;
            font-size: 1.2em;
        }
        .icon-cross {
            color:rgb(255, 19, 19);
            font-weight: bold;
            font-size: 1.2em;

        }
        
        .completed {
            color:rgb(17, 128, 23);
            font-weight: bold;
            font-size: 1.2em;

        }
        
        .passed {
            color:rgb(17, 128, 23);
            font-weight: bold;
            font-size: 1.3em;
        }
        
        .not-completed {
            color:rgb(255, 19, 19);
            font-size: 1.2em;

        }
        
        .score-detail {
            margin-top: 5px;
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

    // Lấy thông tin khóa học của sinh viên
    $stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $khoahoc_ids = explode(',', $row['Khoahoc']);
        
        if (empty($khoahoc_ids[0])) {
            echo "<div class='error'>Sinh viên không có khóa học nào.</div>";
        } else {
            $placeholders = implode(',', array_fill(0, count($khoahoc_ids), '?'));
            $types = str_repeat('i', count($khoahoc_ids));

            $sql2 = "SELECT id, khoa_hoc FROM khoa_hoc WHERE id IN ($placeholders)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param($types, ...array_map('intval', $khoahoc_ids));
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            echo "<h3>Các khóa học của sinh viên ID: <strong>$student_id</strong></h3><ul>";
            
            while ($row2 = $result2->fetch_assoc()) {
                $khoa_id = $row2['id'];
                
           // Trong phần kiểm tra hoàn thành khóa học, sửa lại như sau:
            $stmt_completed = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT kt.Test_ID) as total_tests, 
                    SUM(CASE WHEN kq.kq_cao_nhat IS NOT NULL AND kq.kq_cao_nhat >= kt.Pass THEN 1 ELSE 0 END) as passed_tests,
                    COUNT(DISTINCT CASE WHEN kq.kq_cao_nhat IS NOT NULL THEN kq.test_id END) as attempted_tests
                FROM kiem_tra kt
                LEFT JOIN ket_qua kq ON kt.Student_ID = kq.student_id 
                    AND kt.Khoa_ID = kq.khoa_id 
                    AND kt.Test_ID = kq.test_id
                WHERE kt.Student_ID = ? AND kt.Khoa_ID = ?
            ");

            $stmt_completed->bind_param("si", $student_id, $khoa_id);
            $stmt_completed->execute();
            $completed_result = $stmt_completed->get_result();
            $completed_data = $completed_result->fetch_assoc();

            
            // Khởi tạo giá trị mặc định cho các biến
            $total_tests = $completed_data['total_tests'] ?? 0;
            $passed_tests = $completed_data['passed_tests'] ?? 0; // Thêm dòng này để khởi tạo biến
            $attempted_tests = $completed_data['attempted_tests'] ?? 0;

            // Kiểm tra xem sinh viên đã hoàn thành khóa học chưa
            // (Tùy chọn 1: Đã làm tất cả bài test)
            $is_completed = ($total_tests > 0 && $attempted_tests == $total_tests);

            // Hoặc (Tùy chọn 2: Đã đậu tất cả bài test)
            // $is_completed = ($total_tests > 0 && $passed_tests == $total_tests);

            
           $status = $is_completed 
                ? "<span class='completed'>Hoàn thành</span>" 
                : "<span class='not-completed'>Chưa hoàn thành</span>";
            echo "<li>
                <div class='course-header'>
                    <span><strong>{$row2['khoa_hoc']}</strong></span>
                    <span>$status</span>
                </div>
                <div class='test-info'>
                    <span>Đã làm: $attempted_tests / $total_tests bài test</span>
                </div>
                <div class='test-actions'>
                    <a href='?student_id=$student_id&khoa_hoc_id={$row2['id']}'>Xem bài test</a>
                </div>
            </li>";
            
            }
            echo "</ul>";
        }
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
    $stmt3 = $conn->prepare("SELECT t.id_test, t.ten_test, kt.Pass 
                            FROM test t
                            JOIN kiem_tra kt ON t.id_test = kt.Test_ID AND t.id_khoa = kt.Khoa_ID
                            WHERE t.id_khoa = ? AND kt.Student_ID = ?");
    $stmt3->bind_param("is", $khoa_hoc_id, $student_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    echo "<h3>Các bài test thuộc khóa học: <strong>$khoa_name</strong></h3><ul>";
    
    if ($result3->num_rows > 0) {
        while ($test = $result3->fetch_assoc()) {
            $test_id = $test['id_test'];
            $pass_score = $test['Pass'];
            
            // Lấy thông tin từ bảng ket_qua
            $stmt_ketqua = $conn->prepare("SELECT kq_cao_nhat, tt_bai_test FROM ket_qua 
                                          WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
            $stmt_ketqua->bind_param("sii", $student_id, $khoa_hoc_id, $test_id);
            $stmt_ketqua->execute();
            $ketqua_result = $stmt_ketqua->get_result();
            $ketqua_data = $ketqua_result->fetch_assoc();
            
            $diem_cao_nhat = $ketqua_data['kq_cao_nhat'] ?? 'Chưa có';
            $tt_bai_test = $ketqua_data['tt_bai_test'] ?? '';
            
            // Đếm số lần thử từ tt_bai_test
            $so_lan_thu = $ketqua_data ? substr_count($tt_bai_test, 'Câu') : 0;
            $lan_thu_cho_phep = $test['lan_thu'] ?? 1;
            
            // Lấy tổng số câu hỏi trong bài test
            $stmt_total_questions = $conn->prepare("SELECT COUNT(*) as total_questions 
                FROM quiz 
                WHERE id_baitest = (SELECT ten_test FROM test WHERE id_test = ?) 
                AND ten_khoa = ?");
            $stmt_total_questions->bind_param("is", $test_id, $khoa_name);
            $stmt_total_questions->execute();
            $total_questions_result = $stmt_total_questions->get_result();
            $total_questions = $total_questions_result->fetch_assoc()['total_questions'];

            
            // Tính số câu đúng thực tế (dựa trên điểm cao nhất)
            if (is_numeric($diem_cao_nhat)) {
                $so_cau_dung = round(($diem_cao_nhat / 100) * $total_questions);
            }

            // Kiểm tra xem bài test đã đậu chưa
            $is_passed = is_numeric($diem_cao_nhat) && ($diem_cao_nhat >= round(($pass_score / 100) * $total_questions));
            $passed_status = $is_passed ? "<span class='passed'>Đạt</span>" : 
                            "<span class='not-completed'>Chưa đạt</span>";
            echo "<li>
                <div class='test-header'>
                    <span><strong>{$test['ten_test']}</strong></span>
                    <span>$passed_status</span>
                </div>
                <div class='test-info'>
                    <span>Điểm cao nhất: $diem_cao_nhat/$total_questions ($pass_score/$diem_cao_nhat) </span>
                    <span>Yêu cầu đậu: $pass_score% </span>
                    <span>Số lần thử: $so_lan_thu/$total_questions</span>
                    
                </div>
                <div class='test-actions'>
                    <a href='?student_id=$student_id&khoa_hoc_id=$khoa_hoc_id&xem_ket_qua={$test['id_test']}'>Xem kết quả chi tiết</a>
                </div>
            </li>";
        }
    } else {
        echo "<li>Không có bài test nào.</li>";
    }
    echo "</ul>";
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