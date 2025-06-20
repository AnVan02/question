<?php
session_start();

// Hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

// Hàm kết nối CSDL
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Khởi tạo biến
$student_data = [];
$questions = [];
$answers = [];
$message = "";
$ten_khoa = "";
$ten_baitest = "";
$highest_score = 0;
$status = "";

// Lấy biến từ URL
$ma_khoa = $_GET['ma_khoa'] ?? '';
$id_baitest = $_GET['id_baitest'] ?? '';
$mode = $_GET['mode'] ?? '';
$student_id = $_GET['student_id'] ?? '';

if ($mode == 'edit' && !empty($ma_khoa) && !empty($id_baitest) && !empty($student_id)) {
    // Kết nối CSDL chỉ khi có đủ tham số
    $conn = dbconnect();

    // Lấy thông tin sinh viên từ DB
    $stmt = $conn->prepare("SELECT Student_ID, Khoahoc FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $student_data = $row;
        // Kiểm tra quyền truy cập khóa học
        $khoahoc_list = array_map('intval', explode(',', $row['Khoahoc']));
        if (!in_array(intval($ma_khoa), $khoahoc_list)) {
            $message = "Lỗi: Sinh viên không được đăng ký khóa học này (mã khóa: $ma_khoa).";
        }
    } else {
        $message = "Lỗi: Không tìm thấy thông tin sinh viên với ID: $student_id.";
    }
    $stmt->close();

    if (empty($message)) {
        // Lấy tên khóa học
        $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("s", $ma_khoa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $ten_khoa = $row['khoa_hoc'];
        } else {
            $message = "Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'";
        }
        $stmt->close();
    }

    if (empty($message)) {
        // Lấy tên bài test
        $stmt = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
        $stmt->bind_param("s", $id_baitest);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $message = "ID bài test ($id_baitest) không tồn tại trong hệ thống.";
        } else {
            $row = $result->fetch_assoc();
            $ten_baitest = $row['ten_test'];
        }
        $stmt->close();
    }

    if (empty($message)) {
        // Lấy thông tin kết quả từ ket_qua
        $stmt = $conn->prepare("SELECT kq_cao_nhat, tt_bai_test FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
        $stmt->bind_param("sis", $student_id, $ma_khoa, $id_baitest);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $highest_score = $row['kq_cao_nhat'];
            $status = $row['tt_bai_test'];
        } else {
            $message = "Chưa có kết quả cho bài test này.";
        }
        $stmt->close();
    }

    if (empty($message)) {
        // Lấy câu hỏi từ quiz
        $stmt = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
        $stmt->bind_param("ss", $ten_khoa, $ten_baitest);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $questions[] = [
                'id' => $row['Id_cauhoi'],
                'question' => $row['cauhoi'],
                'choices' => [
                    'A' => $row['cau_a'],
                    'B' => $row['cau_b'],
                    'C' => $row['cau_c'],
                    'D' => $row['cau_d']
                ],
                'explanations' => [
                    'A' => $row['giaithich_a'],
                    'B' => $row['giaithich_b'],
                    'C' => $row['giaithich_c'],
                    'D' => $row['giaithich_d']
                ],
                'correct' => $row['dap_an'],
                'image' => $row['hinhanh']
            ];
        }
        
        if (count($questions) < 1) {
            $message = "Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$ten_baitest'.";
        }
        $stmt->close();

        // Lấy câu trả lời của sinh viên từ trường tt_bai_test
        if (!empty($status)) {
            $answer_parts = explode(', ', $status);
            foreach ($answer_parts as $part) {
                if (preg_match('/Câu (\d+):\s*([A-D])/', $part, $matches)) {
                    $question_num = (int)$matches[1];
                    $answers[$question_num] = $matches[2];
                }
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu kết quả bài test</title>
    <style>
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background: linear-gradient(135deg, #e0f7fa, #b2ebf2 80%);
                margin: 0;
                padding: 0;
                font-size: 17px;
                color: #222;
                max-width: 1100px;
                margin: 40px auto;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            }

            .form-container {
                background: #fff;
                padding: 24px 30px;
                border-radius: 10px;
                margin-bottom: 30px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.07);
                border: 1px solid #b2ebf2;
            }

            .form-student {
                display: grid;
                grid-template-columns: 120px 1fr;
                gap: 18px 24px;
                margin-bottom: 15px;
                align-items: center;
            }

            h1 {
                text-align: center;
                font-size: 2.3rem;
                margin-bottom: 32px;
                color: #00796b;
                letter-spacing: 1px;
                text-shadow: 0 2px 8px #b2ebf2;
            }

            h3 {
                text-align: center;
                font-size: 1.3rem;
                color: #0097a7;
                margin-bottom: 18px;
            }

            input[type="text"] {
                padding: 10px 12px;
                border: 1.5px solid #b2ebf2;
                border-radius: 5px;
                font-size: 1rem;
                width: 100%;
                box-sizing: border-box;
                background: #f7fafd;
                transition: border-color 0.2s;
            }

            input[type="text"]:focus {
                border-color: #0097a7;
                outline: none;
                background: #e0f7fa;
            }

            button {
                background: linear-gradient(90deg, #009688, #4dd0e1);
                color: white;
                border: none;
                padding: 12px 0;
                border-radius: 5px;
                cursor: pointer;
                font-size: 1.1rem;
                font-weight: bold;
                transition: background 0.2s;
                width: 100%;
                margin-top: 10px;
                box-shadow: 0 2px 8px #b2ebf2;
            }

            button:hover {
                background: linear-gradient(90deg, #00796b, #00bcd4);
            }

            .message, .error {
                padding: 12px 18px;
                margin-bottom: 18px;
                border-radius: 5px;
                font-weight: bold;
                font-size: 1.1rem;
                box-shadow: 0 1px 4px #b2ebf2;
            }

            .message {
                color: #256029;
                background: #e8f5e9;
                border: 1px solid #a5d6a7;
            }

            .error {
                color: #b71c1c;
                background: #ffebee;
                border: 1px solid #ef9a9a;
            }

            .question-block {
                margin-bottom: 32px;
                border-bottom: 1.5px solid #b2ebf2;
                padding-bottom: 22px;
                background: #fafcff;
                border-radius: 8px;
                box-shadow: 0 1px 6px #b2ebf2;
                padding-left: 18px;
                padding-right: 18px;
                Padding: 15px;
            }

            .question-block img {
                max-width: 320px;
                display: block;
                margin: 12px 0 0 0;
                border-radius: 6px;
                box-shadow: 0 2px 8px #b2ebf2;
            }

            ul {
                padding-left: 0;
                margin-top: 10px;
            }

            li {
                margin-bottom: 10px;
                padding: 12px 18px;
                border-radius: 7px;
                font-size: 1.08rem;
                background: #f3f3f3;
                color: #222;
                list-style: none;
                font-weight: normal;
                border: none;
                transition: background 0.2s, color 0.2s;
            }
            /* câu đúng */
            .correct {
                background: #d4edda !important;
                color: #218838 !important;
                font-weight: bold;
            }
            /* câu sai */
            .incorrect {
                background: #ef9a9a !important;   /* màu đỏ */
                color: #d35400 !important;        /* Cam đậm */
                font-weight: bold;
            }

            li:hover {
                background: #e0e0e0;
            }

            .score-info {
                background: #e7f3fe;
                padding: 15px 20px;
                border-radius: 7px;
                margin-bottom: 24px;
                max-width: 1100px;
                margin:  40px auto;
                box-sizing: border-box;
                border: 1px solid #b2ebf2;
            }

            /* Responsive */
            @media (max-width: 700px) {
                body, .score-info, .form-container {
                    max-width: 98vw;
                    padding: 10px;
                }
                .form-student {
                    grid-template-columns: 1fr;
                    gap: 10px;
                }
                .question-block img {
                    max-width: 98vw;
                }
            }

            .icon-tick {
                color: #1976d2; /* Xanh dương */
                font-weight: bold;
                margin-left: 10px;
                font-size: 1.2em;
            }
            .icon-cross {
                color: #e53935; /* Đỏ */
                font-weight: bold;
                margin-left: 10px;
                font-size: 1.2em;
            }
        
    </style>
</head>
<body>
    <h1>Tra cứu kết quả bài test</h1>
    
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-container">
            <h3>Nhập thông tin tra cứu</h3>
            <div class="form-student">
                <label><strong>Student_ID:</strong></label>
                <input type="text" name="student_id" value="<?php echo htmlspecialchars($student_id ?? ''); ?>" required>

                <label><strong>Khoa_ID:</strong></label>
                <input type="text" name="ma_khoa" value="<?php echo htmlspecialchars($ma_khoa ?? ''); ?>" required>
                
                <label><strong>Test_ID:</strong></label>
                <input type="text" name="id_baitest" value="<?php echo htmlspecialchars($id_baitest ?? ''); ?>" required>
            </div>
            <input type="hidden" name="mode" value="edit">
            <button type="submit">Check</button>
        </div>
    </form>

    <?php if (!empty($message)): ?>
        <div class="<?php echo strpos($message, 'Lỗi') === false ? 'message' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
            <p><strong>Kết quả :</strong> <?php echo $highest_score; ?>/<?php echo count($questions); ?></p>
            
    <?php if ($mode == 'edit' && empty($message) && !empty($student_data)): ?>
        <h3 >Chi tiết bài làm</h3>
        <?php foreach ($questions as $index => $question): ?>
            <div class="question-block">
                <p><strong>Câu <?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($question['question']); ?></p>
                <?php if (!empty($question['image'])): ?>
                    <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi" style="max-width: 300px;">
                <?php endif; ?>
                
                <ul>
                    <?php foreach ($question['choices'] as $key => $value): ?>
                        <?php
                        $question_num = $index + 1;
                        $is_selected = isset($answers[$question_num]) && $key === $answers[$question_num];
                        $is_correct = $key === $question['correct'];
                        $class = '';

                        if ($is_selected) {
                            $class = $is_correct ? 'correct' : 'incorrect';
                        } elseif ($is_correct) {
                            $class = 'correct';
                        }

                        // Icon cho đáp án được chọn
                        $icon = '';
                        if ($is_selected && $is_correct) {
                            $icon = '<span class="icon-tick">✔</span>';
                        } elseif ($is_selected && !$is_correct) {
                            $icon = '<span class="icon-cross">✘</span>';
                        }
                        ?>
                        <li class="<?php echo $class; ?>">
                            <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                            <?php echo $icon; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>