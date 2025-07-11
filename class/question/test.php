<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("<div class='error'>Kết nối CSDL thất bại: " . $conn->connect_error . "</div>");
}

// Hàm chuẩn hóa dữ liệu đầu vào
function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Hàm lưu kết quả bài test
function save_test_result($conn, $student_id, $khoa_id, $test_id, $answers) {
    // Tính điểm
    $score = 0;
    $detailed_answers = [];
    
    foreach ($answers as $answer) {
        $id_cauhoi = (int)$answer['id_cauhoi'];
        $user_answer = strtoupper($answer['dapan']);
        
        // Lấy đáp án đúng từ CSDL
        $quiz_query = $conn->query("SELECT dap_an FROM quiz WHERE Id_cauhoi = $id_cauhoi");
        if ($quiz_query && $quiz_query->num_rows > 0) {
            $correct_answer = strtoupper($quiz_query->fetch_assoc()['dap_an']);
            $is_correct = ($user_answer === $correct_answer);
            
            if ($is_correct) {
                $score++;
            }
            
            $detailed_answers[] = [
                'id_cauhoi' => $id_cauhoi,
                'dapan' => $user_answer,
                'dapan_dung' => $correct_answer,
                'dung' => $is_correct
            ];
        }
    }
    
    // Chuẩn bị dữ liệu JSON
    $tt_bai_test = json_encode($detailed_answers);
    
    // Lưu vào CSDL
    $sql = "INSERT INTO ket_qua (student_id, khoa_id, test_id, kq_cao_nhat, tt_bai_test)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            kq_cao_nhat = VALUES(kq_cao_nhat),
            tt_bai_test = VALUES(tt_bai_test)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisis", $student_id, $khoa_id, $test_id, $score, $tt_bai_test);
    
    return $stmt->execute();
}

// Hàm hiển thị kết quả bài test
function display_test_result($conn, $student_id, $khoa_id, $test_id) {
    $query = "SELECT kq_cao_nhat, tt_bai_test FROM ket_qua 
              WHERE student_id = ? AND khoa_id = ? AND test_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $student_id, $khoa_id, $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $score = $row['kq_cao_nhat'];
        $test_data = json_decode($row['tt_bai_test'], true);
        
        // Lấy thông tin bài test
        $test_info = $conn->query("SELECT ten_test, Pass FROM test WHERE id_test = '$test_id'");
        $test_name = $test_info->fetch_assoc()['ten_test'];
        
        // Lấy tổng số câu hỏi
        $total_questions = $conn->query("SELECT COUNT(*) as total FROM quiz WHERE id_baitest = '$test_name'")->fetch_assoc()['total'];
        
        echo "<div class='result-container'>";
        echo "<h2>Kết quả bài test: $test_name</h2>";
        echo "<div class='summary'>";
        echo "<p>Điểm số: <strong>$score/$total_questions</strong></p>";
        echo "<p>Tỉ lệ đúng: <strong>" . round(($score/$total_questions)*100, 2) . "%</strong></p>";
        echo "</div>";
        
        echo "<div class='detailed-results'>";
        echo "<h3>Chi tiết từng câu hỏi:</h3>";
        echo "<table>";
        echo "<tr><th>ID Câu hỏi</th><th>Câu hỏi</th><th>Đáp án của bạn</th><th>Đáp án đúng</th><th>Kết quả</th></tr>";
        
        foreach ($test_data as $item) {
            $id_cauhoi = $item['id_cauhoi'];
            $user_answer = $item['dapan'];
            $correct_answer = $item['dapan_dung'];
            $is_correct = $item['dung'];
            
            // Lấy nội dung câu hỏi
            $question = $conn->query("SELECT cauhoi FROM quiz WHERE Id_cauhoi = $id_cauhoi")->fetch_assoc()['cauhoi'];
            
            echo "<tr class='" . ($is_correct ? 'correct' : 'incorrect') . "'>";
            echo "<td>$id_cauhoi</td>";
            echo "<td>" . clean_input($question) . "</td>";
            echo "<td>$user_answer</td>";
            echo "<td>$correct_answer</td>";
            echo "<td>" . ($is_correct ? '✔ Đúng' : '✗ Sai') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='error'>Không tìm thấy kết quả bài test.</div>";
    }
}

// Xử lý form nộp bài test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_test'])) {
    $student_id = (int)$_POST['student_id'];
    $khoa_id = (int)$_POST['khoa_id'];
    $test_id = clean_input($_POST['test_id']);
    
    // Thu thập các câu trả lời
    $answers = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'answer_') === 0) {
            $id_cauhoi = str_replace('answer_', '', $key);
            $answers[] = [
                'id_cauhoi' => (int)$id_cauhoi,
                'dapan' => clean_input($value)
            ];
        }
    }
    
    // Lưu kết quả
    if (save_test_result($conn, $student_id, $khoa_id, $test_id, $answers)) {
        echo "<div class='success'>Lưu kết quả bài test thành công!</div>";
    } else {
        echo "<div class='error'>Có lỗi khi lưu kết quả bài test.</div>";
    }
}

// Hiển thị giao diện
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống quản lý bài test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #333;
        }
        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .success {
            color: #256029;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr.correct {
            background-color: #e8f5e9;
        }
        tr.incorrect {
            background-color: #ffebee;
        }
        .result-container {
            margin-top: 30px;
        }
        .summary {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .test-form {
            margin-top: 30px;
        }
        .question {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .options {
            margin-left: 20px;
        }
        input[type="submit"] {
            background: #2196f3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background: #0d8aee;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hệ thống quản lý bài test</h1>
        
        <?php
        // Hiển thị form làm bài test hoặc kết quả
        if (isset($_GET['view_result']) && isset($_GET['student_id']) && isset($_GET['khoa_id']) && isset($_GET['test_id'])) {
            // Hiển thị kết quả
            display_test_result(
                $conn,
                (int)$_GET['student_id'],
                (int)$_GET['khoa_id'],
                clean_input($_GET['test_id'])
            );
            
            echo "<a href='index.php'>Quay lại</a>";
        } else {
            // Hiển thị form làm bài test
            $test_id = 'TEST001'; // ID bài test mẫu
            $test_name = 'Bài test giữa kỳ';
            
            // Lấy danh sách câu hỏi
            $questions = $conn->query("SELECT * FROM quiz WHERE id_baitest = '$test_name' ORDER BY Id_cauhoi");
            
            if ($questions && $questions->num_rows > 0) {
                echo "<div class='test-form'>";
                echo "<h2>$test_name</h2>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='student_id' value='1'>"; // Sinh viên mẫu
                echo "<input type='hidden' name='khoa_id' value='1'>"; // Khóa học mẫu
                echo "<input type='hidden' name='test_id' value='$test_id'>";
                
                while ($question = $questions->fetch_assoc()) {
                    echo "<div class='question'>";
                    echo "<h3>Câu hỏi #{$question['Id_cauhoi']}</h3>";
                    echo "<p>{$question['cauhoi']}</p>";
                    
                    echo "<div class='options'>";
                    echo "<label><input type='radio' name='answer_{$question['Id_cauhoi']}' value='A' required> A. {$question['cau_a']}</label><br>";
                    echo "<label><input type='radio' name='answer_{$question['Id_cauhoi']}' value='B'> B. {$question['cau_b']}</label><br>";
                    echo "<label><input type='radio' name='answer_{$question['Id_cauhoi']}' value='C'> C. {$question['cau_c']}</label><br>";
                    echo "<label><input type='radio' name='answer_{$question['Id_cauhoi']}' value='D'> D. {$question['cau_d']}</label>";
                    echo "</div>";
                    echo "</div>";
                }
                
                echo "<input type='submit' name='submit_test' value='Nộp bài'>";
                echo "</form>";
                echo "</div>";
            } else {
                echo "<div class='error'>Không tìm thấy câu hỏi nào cho bài test này.</div>";
            }
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>