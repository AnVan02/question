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
        input {
            padding: auto;
            width: 50px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius:5px;
            font-size:17px;
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
            color: rgb(255, 19, 19);
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .completed {
            color: rgb(17, 128, 23);
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .passed {
            color: rgb(17, 128, 23);
            font-weight: bold;
            font-size: 1.3em;
        }
        
        .not-completed {
            color: rgb(255, 19, 19);
            font-size: 1.2em;
        }
        
        .score-detail {
            margin-top: 5px;
            font-weight: bold;
        }
        
        .explanation-detail {
            margin-top: 5px;
            padding: 8px;
            background-color: #f5f5f5;
            border-radius: 4px;
            font-style: normal;
        }
        
        .question-id {
            color: #666;
            font-size: 0.9em;
        }
        
        .json-format {
            font-family: monospace;
            white-space: pre-wrap;
            background: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
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
if (isset($_GET['student_id']) && !empty(trim($_GET['student_id']))) {
    $student_id = $conn->real_escape_string(trim($_GET['student_id']));

    // Lấy thông tin khóa học của sinh viên
    $sql = "SELECT Khoahoc FROM students WHERE Student_ID = '$student_id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $khoahoc_ids = array_filter(explode(',', $row['Khoahoc']));
        
        if (empty($khoahoc_ids)) {
            echo "<div class='error'>Sinh viên không có khóa học nào.</div>";
        } else {
            $ids_str = implode(",", array_map('intval', $khoahoc_ids));
            $sql2 = "SELECT id, khoa_hoc FROM khoa_hoc WHERE id IN ($ids_str)";
            $result2 = $conn->query($sql2);

            echo "<h3>Các khóa học của sinh viên ID: <strong>$student_id</strong></h3><ul>";
            
            while ($row2 = $result2->fetch_assoc()) {
                $khoa_id = $row2['id'];
                
                // Truy vấn tối ưu hóa để lấy thông tin hoàn thành khóa học
                $sql_completed = "
                    SELECT 
                        COUNT(DISTINCT kt.Test_ID) as total_tests,
                        SUM(CASE 
                            WHEN kq.kq_cao_nhat IS NOT NULL 
                            AND kq.kq_cao_nhat = (
                                SELECT COUNT(*) 
                                FROM quiz q 
                                INNER JOIN test t ON q.id_baitest = t.ten_test 
                                WHERE t.id_test = kt.Test_ID AND q.ten_khoa = '{$row2['khoa_hoc']}'
                            )
                            THEN 1 
                            ELSE 0 
                        END) as perfect_tests,
                        COUNT(DISTINCT CASE WHEN kq.kq_cao_nhat IS NOT NULL THEN kq.test_id END) as attempted_tests
                    FROM kiem_tra kt
                    LEFT JOIN ket_qua kq ON kt.Student_ID = kq.student_id 
                        AND kt.Khoa_ID = kq.khoa_id 
                        AND kt.Test_ID = kq.test_id
                    WHERE kt.Student_ID = '$student_id' AND kt.Khoa_ID = $khoa_id
                ";

                $completed_result = $conn->query($sql_completed);
                $completed_data = $completed_result->fetch_assoc();

                $total_tests = $completed_data['total_tests'] ?? 0;
                $perfect_tests = $completed_data['perfect_tests'] ?? 0;
                $attempted_tests = $completed_data['attempted_tests'] ?? 0;

                $is_completed = ($total_tests > 0 && $perfect_tests == $total_tests);

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

// Xử lý khi nhấn "Xem bài test"
if (isset($_GET['khoa_hoc_id'])) {
    $khoa_hoc_id = intval($_GET['khoa_hoc_id']);
    $student_id = $conn->real_escape_string($_GET['student_id'] ?? '');

    // Lấy thông tin khóa học
    $khoa_result = $conn->query("SELECT khoa_hoc FROM khoa_hoc WHERE id = $khoa_hoc_id");
    if ($khoa_result && $khoa_result->num_rows > 0) {
        $khoa_name = $khoa_result->fetch_assoc()['khoa_hoc'];
        
        // Lấy danh sách bài test với thông tin kết quả và phần trăm đạt từ bảng test
        $sql_tests = "
            SELECT t.id_test, t.ten_test, t.Pass as required_pass_percent, 
                   t.lan_thu as max_attempts,
                   kq.kq_cao_nhat, kq.tt_bai_test,
                   (SELECT COUNT(*) FROM quiz q WHERE q.id_baitest = t.ten_test AND q.ten_khoa = '$khoa_name') as total_questions
            FROM test t
            JOIN kiem_tra kt ON t.id_test = kt.Test_ID AND t.id_khoa = kt.Khoa_ID
            LEFT JOIN ket_qua kq ON kq.student_id = '$student_id' AND kq.khoa_id = $khoa_hoc_id AND kq.test_id = t.id_test
            WHERE t.id_khoa = $khoa_hoc_id AND kt.Student_ID = '$student_id'
        ";
        
        
        $result_tests = $conn -> query ($sql_tests);
        
        echo "<h3>Các bài test thuộc khóa học: <strong>$khoa_name</strong></h3><ul>";
        
        if ($result_tests && $result_tests->num_rows > 0) {
            while ($test = $result_tests->fetch_assoc()) {
                $test_id = $test['id_test'];
                $diem_cao_nhat = $test['kq_cao_nhat'] ?? 'Chưa có';
                $tt_bai_test = $test['tt_bai_test'] ?? '';
                $total_questions = $test['total_questions'] ?? 0;
                $required_pass_percent = $test['required_pass_percent'] ?? '80';
                $so_lan_thu = $tt_bai_test ? substr_count($tt_bai_test, 'Câu'): 0;

                $max_attempts = $test['max_attempts'] ?? 1;
                
                // Tính số lần đã làm bài test này
                $attempt_count_sql = "SELECT COUNT(*) as attempt_count FROM ket_qua 
                                     WHERE student_id = '$student_id' AND khoa_id = $khoa_hoc_id AND test_id = '$test_id'";
                $attempt_count_result = $conn->query($attempt_count_sql);
                $attempt_count = $attempt_count_result ? $attempt_count_result->fetch_assoc()['attempt_count'] : 0;
                
                $required_score = ceil($total_questions * $required_pass_percent / 100);
                
                $is_passed = is_numeric($diem_cao_nhat) && $total_questions > 0 && ($diem_cao_nhat >= $required_score);
                $passed_status = $is_passed ? "<span class='passed'>Đạt</span>" : "<span class='not-completed'>Chưa đạt</span>";
                
                $percentage = is_numeric($diem_cao_nhat) && $total_questions > 0 ? 
                             round(($diem_cao_nhat / $total_questions) * 100, 1) : 0;

                echo "<li>
                    <div class='test-header'>
                        <span><strong>{$test['ten_test']}</strong></span>
                        <span>$passed_status</span>
                    </div>
                    <div class='test-info'>
                        <span>Điểm cao nhất: $diem_cao_nhat/$total_questions ($percentage%)</span>
                        <span>Yêu cầu đậu: $required_pass_percent% </span>
                        <span>Số lần thử: $attempt_count/$max_attempts</span>
                    </div>
                    <div class='test-actions'>";
                
                // Hiển thị số lần thử thuộc bài test đó 
                if ($attempt_count < $max_attempts) {
                } else {
                   echo "<span class='not-completed'>Đã hết lượt làm bài </span>"; 
                }
                echo "<a href='?student_id=$student_id&khoa_hoc_id=$khoa_hoc_id&xem_ket_qua={$test['id_test']}'>Xem chi tiết kết quả</a>
                    </div>
                </li>";
            }
        } else {
            echo "<li>Không có bài test nào.</li>";
        }
        echo "</ul>";
    } else {
        echo "<div class='error'>Không tìm thấy khóa học.</div>";
    }
}

// Xử lý khi nhấn "Xem kết quả chi tiết"
if (isset($_GET['xem_ket_qua'])) {
    $test_id = $conn->real_escape_string($_GET['xem_ket_qua']);
    $student_id = $conn->real_escape_string($_GET['student_id'] ?? '');
    $khoa_hoc_id = intval($_GET['khoa_hoc_id'] ?? 0);
    
    // Lấy thông tin khóa học và test
    $khoa_result = $conn->query("SELECT khoa_hoc FROM khoa_hoc WHERE id = $khoa_hoc_id");
    $test_result = $conn->query("SELECT ten_test, lan_thu FROM test WHERE id_test = '$test_id'");
    
    if ($khoa_result && $khoa_result->num_rows > 0 && $test_result && $test_result->num_rows > 0) {
        $khoa_name = $khoa_result->fetch_assoc()['khoa_hoc'];
        $test_data = $test_result->fetch_assoc();
        $test_name = $test_data['ten_test'];
        $max_attempts = $test_data['lan_thu'];
        
        // Tính số lần đã làm bài test này
        $attempt_count_sql = "SELECT COUNT(*) as attempt_count FROM ket_qua 
                             WHERE student_id = '$student_id' AND khoa_id = $khoa_hoc_id AND test_id = '$test_id'";
        $attempt_count_result = $conn->query($attempt_count_sql);
        $attempt_count = $attempt_count_result ? $attempt_count_result->fetch_assoc()['attempt_count'] : 0;
        
        // Lấy kết quả của sinh viên cho bài test này
        $ketqua_sql = "SELECT tt_bai_test, kq_cao_nhat FROM ket_qua 
                       WHERE student_id = '$student_id' AND khoa_id = $khoa_hoc_id AND test_id = '$test_id'
                       ORDER BY kq_cao_nhat DESC LIMIT 1";
        $ketqua = $conn->query($ketqua_sql);
        
        echo "<h3>Kết quả chi tiết bài test: <strong>$test_name</strong></h3>";
        echo "<div class='test-info'></div>";
        echo "<div class='test-result'>";
        
        if ($ketqua && $ketqua->num_rows > 0) {
            $ketqua_data = $ketqua->fetch_assoc();
            $tt_bai_test = $ketqua_data['tt_bai_test'];
            $kq_cao_nhat = $ketqua_data['kq_cao_nhat'];
            
            // Phân tích tt_bai_test dạng "5:B;6:B"
            $user_answers = [];
            if (!empty($tt_bai_test)) {
                $pairs = explode(';', $tt_bai_test);
                foreach ($pairs as $pair) {
                    if (!empty($pair) && strpos($pair, ':') !== false) {
                        list($id, $answer) = explode(':', $pair, 2);
                        $id = trim($id);
                        $answer = strtoupper(trim($answer));
                        if (!empty($id) && !empty($answer)) {
                            $user_answers[$id] = $answer;
                        }
                    }
                }
            }
            

            // Lấy danh sách câu hỏi và hiển thị
            $quiz_result = $conn->query("
                SELECT Id_cauhoi, cauhoi, cau_a, cau_b, cau_c, cau_d, dap_an, 
                       giaithich_a, giaithich_b, giaithich_c, giaithich_d
                FROM quiz 
                WHERE id_baitest = '$test_name' AND ten_khoa = '$khoa_name' 
                ORDER BY Id_cauhoi
            ");
            
            if ($quiz_result && $quiz_result->num_rows > 0) {
                $question_number = 1;
                while ($q = $quiz_result->fetch_assoc()) {
                    $question_id = $q['Id_cauhoi'];
                    $user_answer = $user_answers[$question_id] ?? null;
                    $dap_an_dung = strtoupper(trim($q['dap_an']));
                    
                    echo "<div class='question-container'>";
                    echo "<p><strong>Câu $question_number</strong> <span class='question-id'>(ID: $question_id)</span>: " . htmlspecialchars($q['cauhoi']) . "</p>";
                    
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
                    
                    // echo "<div class='explanation'>";
                    // if ($user_answer !== null) {
                    //     echo "Bạn chọn: <span class='user-answer'>$user_answer</span>";
                    //     if ($user_answer !== $dap_an_dung) {
                    //         echo " | Đáp án đúng: $dap_an_dung";
                    //         // Hiển thị giải thích cho đáp án đúng
                    //         $explanation_field = 'giaithich_' . strtolower($dap_an_dung);
                    //         $explanation = $q[$explanation_field];
                    //         if (!empty($explanation)) {
                    //             echo "<div class='explanation-detail'><strong>Giải thích:</strong> $explanation</div>";
                    //         }
                    //     } else {
                    //         // Hiển thị giải thích khi trả lời đúng
                    //         $explanation_field = 'giaithich_' . strtolower($dap_an_dung);
                    //         $explanation = $q[$explanation_field];
                    //         if (!empty($explanation)) {
                    //             echo "<div class='explanation-detail'><strong>Giải thích:</strong> $explanation</div>";
                    //         }
                    //     }
                    // } else {
                    //     echo "Bạn chưa trả lời câu này";
                    // }
                    // echo "</div>";
                    
                    echo "</div>";
                    $question_number++;
                }
            } else {
                echo "<div class='error'>Không tìm thấy câu hỏi nào cho bài test này.</div>";
            }
        } else {
            echo "<div class='error'>Sinh viên chưa làm bài test này.</div>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>Không tìm thấy thông tin khóa học hoặc bài test.</div>";
    }
}


$conn->close();
?>
</body>
</html>