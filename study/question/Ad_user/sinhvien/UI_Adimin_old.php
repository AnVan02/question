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
        
        .format-info {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9em;
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

// Hàm chuyển đổi từ định dạng mới (id_cauhoi:A;id_cauhoi:B) sang JSON
function convertSemicolonFormatToJson($semicolon_data) {
    $result = [];
    $pairs = explode(';', $semicolon_data);
    
    foreach ($pairs as $pair) {
        if (preg_match('/(\d+):([A-D])/i', trim($pair), $matches)) {
            $result[] = [
                'id_cauhoi' => (int)$matches[1],
                'dapan' => strtoupper(trim($matches[2]))
            ];
        }
    }
    
    return json_encode($result);
}

// Hàm chuyển đổi từ JSON sang định dạng mới (id_cauhoi:A;id_cauhoi:B)
function convertJsonToSemicolonFormat($json_data) {
    $decoded = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return '';
    }
    
    $pairs = [];
    foreach ($decoded as $answer) {
        if (isset($answer['id_cauhoi']) && isset($answer['dapan'])) {
            $pairs[] = $answer['id_cauhoi'] . ':' . strtoupper($answer['dapan']);
        }
    }
    
    return implode(';', $pairs);
}

// Hàm chuyển đổi định dạng cũ sang định dạng mới
function convertOldFormatToSemicolon($old_data) {
    $pairs = explode(',', $old_data);
    $new_pairs = [];
    
    foreach ($pairs as $pair) {
        if (preg_match('/Cau\s*(\d+)\s*:\s*([A-D])/i', trim($pair), $matches)) {
            $new_pairs[] = $matches[1] . ':' . strtoupper(trim($matches[2]));
        }
    }
    
    return implode(';', $new_pairs);
}

// Hàm phân tích tt_bai_test từ mọi định dạng (bao gồm định dạng mới)
function parseTestData($tt_bai_test) {
    $user_answers = [];
    
    if (empty($tt_bai_test)) {
        return $user_answers;
    }
    
    // Kiểm tra định dạng mới trước (id_cauhoi:A;id_cauhoi:B)
    if (strpos($tt_bai_test, ';') !== false && strpos($tt_bai_test, ':') !== false && 
        !preg_match('/Cau\s*\d+/i', $tt_bai_test)) {
        $pairs = explode(';', $tt_bai_test);
        foreach ($pairs as $pair) {
            if (preg_match('/(\d+):([A-D])/i', trim($pair), $matches)) {
                $user_answers[(int)$matches[1]] = strtoupper(trim($matches[2]));
            }
        }
        return $user_answers;
    }
    
    // Thử phân tích như JSON
    $json_data = json_decode($tt_bai_test, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        // Định dạng JSON (mới hoặc cũ)
        foreach ($json_data as $answer) {
            if (isset($answer['id_cauhoi']) && isset($answer['dapan'])) {
                $user_answers[(int)$answer['id_cauhoi']] = strtoupper(trim($answer['dapan']));
            } 
            elseif (isset($answer['id']) && isset($answer['answer'])) {
                $user_answers[(int)$answer['id']] = strtoupper(trim($answer['answer']));
            }
        }
    } else {
        // Định dạng text cũ ("Cau 1:B, Cau2:B")
        $pairs = explode(',', $tt_bai_test);
        foreach ($pairs as $pair) {
            if (preg_match('/Cau\s*(\d+)\s*:\s*([A-D])/i', trim($pair), $matches)) {
                $user_answers[(int)$matches[1]] = strtoupper(trim($matches[2]));
            }
        }
    }
    
    return $user_answers;
}

// Hàm tạo chuỗi định dạng mới từ mảng đáp án
function createSemicolonFormat($answers_array) {
    $pairs = [];
    foreach ($answers_array as $question_id => $answer) {
        $pairs[] = $question_id . ':' . strtoupper($answer);
    }
    return implode(';', $pairs);
}

// Hàm xác định định dạng dữ liệu
function detectDataFormat($data) {
    if (empty($data)) return 'empty';
    
    // Kiểm tra định dạng semicolon mới
    if (strpos($data, ';') !== false && strpos($data, ':') !== false && 
        !preg_match('/Cau\s*\d+/i', $data)) {
        return 'semicolon';
    }
    
    // Kiểm tra JSON
    json_decode($data);
    if (json_last_error() === JSON_ERROR_NONE) {
        return 'json';
    }
    
    // Kiểm tra định dạng cũ
    if (preg_match('/Cau\s*\d+/i', $data)) {
        return 'old_format';
    }
    
    return 'unknown';
}

// Xử lý khi xem kết quả chi tiết
if (isset($_GET['xem_ket_qua'])) {
    $test_id = $conn->real_escape_string($_GET['xem_ket_qua']);
    $student_id = $conn->real_escape_string($_GET['student_id'] ?? '');
    $khoa_hoc_id = intval($_GET['khoa_hoc_id'] ?? 0);
    
    // Lấy thông tin bài test
    $test_info = $conn->query("
        SELECT t.ten_test, t.Pass as required_pass_percent 
        FROM test t 
        WHERE t.id_test = '$test_id'
    ");
    
    // Lấy thông tin khóa học
    $khoa_info = $conn->query("SELECT khoa_hoc FROM khoa_hoc WHERE id = $khoa_hoc_id");
    
    if ($test_info && $test_info->num_rows > 0 && $khoa_info && $khoa_info->num_rows > 0) {
        $test_data = $test_info->fetch_assoc();
        $test_name = $test_data['ten_test'];
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
            
            // Hiển thị thông tin định dạng dữ liệu
            $format = detectDataFormat($tt_bai_test);
            echo "<div class='format-info'>";
            echo "<strong>Định dạng dữ liệu:</strong> ";
            switch($format) {
                case 'semicolon':
                    echo "Định dạng mới (id:đáp_án;id:đáp_án)";
                    break;
                case 'json':
                    echo "Định dạng JSON";
                    break;
                case 'old_format':
                    echo "Định dạng cũ (Cau X:Y)";
                    break;
                default:
                    echo "Không xác định";
            }
            echo "<br><strong>Dữ liệu gốc:</strong> " . htmlspecialchars($tt_bai_test);
            echo "</div>";
            
            // Phân tích dữ liệu bài test
            $user_answers = parseTestData($tt_bai_test);
            
            // Lấy danh sách câu hỏi từ bảng quiz
            $quiz_result = $conn->query("
                SELECT Id_cauhoi, cauhoi, cau_a, cau_b, cau_c, cau_d, dap_an, 
                       giaithich_a, giaithich_b, giaithich_c, giaithich_d
                FROM quiz 
                WHERE id_baitest = '$test_name' AND ten_khoa = '$khoa_name' 
                ORDER BY Id_cauhoi
            ");
            
            if ($quiz_result && $quiz_result->num_rows > 0) {
                echo "<h3>Chi tiết bài test: $test_name</h3>";
                echo "<div class='test-details'>";
                
                $question_number = 1;
                $correct_count = 0;
                $total_questions = 0;
                
                while ($q = $quiz_result->fetch_assoc()) {
                    $question_id = $q['Id_cauhoi'];
                    $user_answer = $user_answers[$question_id] ?? null;
                    $correct_answer = strtoupper($q['dap_an']);
                    $total_questions++;
                    
                    if ($user_answer === $correct_answer) {
                        $correct_count++;
                    }
                    
                    // Hiển thị câu hỏi và ID
                    echo "<div class='question'>";
                    echo "<h4>Câu $question_number (ID: $question_id)</h4>";
                    echo "<p>" . htmlspecialchars($q['cauhoi']) . "</p>";
                    
                    // Hiển thị các lựa chọn
                    echo "<div class='options'>";
                    $choices = [
                        'A' => $q['cau_a'],
                        'B' => $q['cau_b'], 
                        'C' => $q['cau_c'],
                        'D' => $q['cau_d']
                    ];
                    
                    foreach ($choices as $key => $value) {
                        $is_selected = ($user_answer === $key);
                        $is_correct = ($key === $correct_answer);
                        
                        $class = '';
                        if ($is_selected) {
                            $class = $is_correct ? 'correct' : 'incorrect';
                        } elseif ($is_correct) {
                            $class = 'correct-answer';
                        }
                        
                        echo "<div class='option $class'>";
                        echo "$key. " . htmlspecialchars($value);
                        if ($is_selected) {
                            echo $is_correct ? " ✓" : " ✗";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                    
                    // Hiển thị thông tin đáp án
                    echo "<div class='answer-info'>";
                    if ($user_answer !== null) {
                        echo "<p>Bạn chọn: <strong>$user_answer</strong></p>";
                        
                        if ($user_answer !== $correct_answer) {
                            echo "<p>Đáp án đúng: <strong>$correct_answer</strong></p>";
                        }
                        
                        // Hiển thị giải thích
                        $explanation_field = 'giaithich_' . strtolower($correct_answer);
                        if (!empty($q[$explanation_field])) {
                            echo "<div class='explanation'>";
                            echo "<strong>Giải thích:</strong> " . htmlspecialchars($q[$explanation_field]);
                            echo "</div>";
                        }
                    } else {
                        echo "<p>Bạn chưa trả lời câu này</p>";
                    }
                    echo "</div>";
                    
                    echo "</div>"; // Kết thúc question
                    $question_number++;
                }
                
                // Hiển thị tổng kết
                $score_percentage = $total_questions > 0 ? round(($correct_count / $total_questions) * 100, 2) : 0;
                echo "<div class='test-result'>";
                echo "<h4>Kết quả tổng kết:</h4>";
                echo "<p>Số câu đúng: <strong>$correct_count/$total_questions</strong></p>";
                echo "<p>Điểm số: <strong>$score_percentage%</strong></p>";
                
                // Hiển thị các định dạng khác nhau
                echo "<h4>Dữ liệu ở các định dạng khác:</h4>";
                echo "<p><strong>Định dạng mới:</strong> " . createSemicolonFormat($user_answers) . "</p>";
                if ($format !== 'json') {
                    $json_format = convertSemicolonFormatToJson(createSemicolonFormat($user_answers));
                    echo "<p><strong>Định dạng JSON:</strong> " . htmlspecialchars($json_format) . "</p>";
                }
                echo "</div>";
                
                echo "</div>"; // Kết thúc test-details
            } else {
                echo "<div class='error'>Không tìm thấy câu hỏi nào cho bài test này.</div>";
            }
        } else {
            echo "<div class='error'>Không tìm thấy kết quả bài test.</div>";
        }
    } else {
        echo "<div class='error'>Không tìm thấy thông tin bài test hoặc khóa học.</div>";
    }
}

$conn->close();
?>

</body>
</html>