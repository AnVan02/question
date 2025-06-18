<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['khoa_hoc']) || !isset($_SESSION['student_id'])) {
    header("Location: temp.php");
    exit;
}

// Kết nối database
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy danh sách câu hỏi
$khoa_hoc = $_SESSION['khoa_hoc'];
$sql = "SELECT * FROM quiz WHERE ten_khoa = ? ORDER BY Id_cauhoi";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error); 
}
$stmt->bind_param("s", $khoa_hoc);
$stmt->execute(); // chạy câu lệnh sql
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);


// Lấy câu hỏi hiện tại
$current_question = isset($_GET['q']) ? (int)$_GET['q'] : 1;
if ($current_question < 1) $current_question = 1;
if (!empty($questions) && $current_question > count($questions)) {
    $current_question = count($questions);
}

// Xử lý khi submit câu trả lời
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($questions)) {
    $question_id = $_POST['question_id'];
    $answer = $_POST['answer'];
    $student_id = $_SESSION['student_id'];

    // Lưu câu trả lời vào database
    $sql = "INSERT INTO kiem_tra (Student_ID, Khoa_ID, Test_ID, Best_Score) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE Best_Score = ?";
    $stmt = $conn->prepare($sql);
    $test_id = "Test_" . $question_id;
    $score = ($answer == $questions[$current_question-1]['dap_an']) ? 1 : 0;
    $stmt->bind_param("sssii", $student_id, $_SESSION['khoa_id'], $test_id, $score, $score);
    $stmt->execute();
}

// Lấy câu trả lời đã lưu (nếu có)
$student_id = $_SESSION['student_id'];
$sql = "SELECT Best_Score FROM kiem_tra WHERE Student_ID = ? AND Khoa_ID = ? AND Test_ID = ?";
$stmt = $conn->prepare($sql);
$test_id = "Test_" . $questions[$current_question-1]['Id_cauhoi'];
$stmt->bind_param("sss", $student_id, $_SESSION['khoa_id'], $test_id);
$stmt->execute();
$result = $stmt->get_result();
$saved_answer = $result->fetch_assoc();

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn-> setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql ="SELECT khoa_hoc FROM khoa_hoc WHERE id= K001";
    $stmt = $conn ->query ($sql);
    $khoa_hoc = $stmt ->fetchColum();
} catch (PDOException $e) {
    die("Connection failed: ". $e -> getMessage());
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trả lời câu hỏi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

         
        .question img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .options {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .option {
            display: flex;
            align-items: flex-start;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 18px 20px;
            transition: background 0.3s, box-shadow 0.3s;
            min-height: 60px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .option:hover, .option:has(input[type="radio"]:checked) {
            background: #eaf6ff;
            box-shadow: 0 2px 8px rgba(52,152,219,0.08);
            border-color: #3498db;
        }

        .option input[type="radio"] {
            margin-right: 16px;
            margin-top: 3px;
            accent-color: #3498db;
            transform: scale(1.2);
        }

        .option label {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 2px;
        }

        .explanation {
            font-size: 14px;
            color: #666;
            margin-top: 2px;
            font-style: italic;
        }

        button {
            padding: 12px 25px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        button:hover {
            background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .navigation a {
            text-decoration: none;
        }

        .navigation button {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }


        .error {
            color: #e74c3c;
            margin: 20px 0;
            padding: 15px;
            background: #fdf3f2;
            border-radius: 8px;
            border-left: 4px solid #e74c3c;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: #eee;
            border-radius: 5px;
            margin: 20px 0;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            margin-top: 20px;
            display: inline-block;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            .question {
                padding: 15px;
            }

            .option {
                padding: 10px;
            }

            button {
                width: 100%;
                margin: 5px 0;
            }

            .navigation {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- hiển thị tên môn học, loại bài thì  -->
    <div class="container">
        <?php if (!empty ($questions)):?>
        <h2>
            <?php
                echo "Môn học: <span style='color:#1565c0; margin:5px'>" . htmlspecialchars($questions[0]['ten_khoa']) . "</span><br>";
                echo "Bài thi: <span style='color:#e67e22; margin:5px'>" . htmlspecialchars($questions[0]['id_baitest']) . "</span>";               
            ?>
        </h2>
            <?php endif; ?>

        <!-- <h2>Câu hỏi <?php echo $current_question; ?> / <?php echo count($questions); ?></h2> -->
        
        <?php if (!empty($questions)): ?>
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo ($current_question / count($questions)) * 100; ?>%"></div>
            </div>
            <!-- Hiện thị câu hỏi, hình ảnh (nếu có ) -->
            <div class="question">
                <h3><?php echo htmlspecialchars($questions[$current_question-1]['cauhoi']); ?></h3>
                <?php if ($questions[$current_question-1]['hinhanh']): ?>
                    <img src="<?php echo htmlspecialchars($questions[$current_question-1]['hinhanh']); ?>" alt="Hình ảnh câu hỏi">
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="question_id" value="<?php echo $questions[$current_question-1]['Id_cauhoi']; ?>">
                    <div class="options">
                        <?php
                        $options = [

                              'A' => ['text' => $questions[$current_question-1]['cau_a']],
                              'B' => ['text' => $questions[$current_question-1]['cau_b']],
                              'C' => ['text' => $questions[$current_question-1]['cau_c']],
                              'D' => ['text' => $questions[$current_question-1]['cau_d']] 
                        ];

                        foreach ($options as $key => $option) {
                            $checked = ($saved_answer && $saved_answer['Best_Score'] == 1 && $key == $questions[$current_question-1]['dap_an']) ? 'checked' : '';
                            echo "<div class='option'>";
                            // Đặt input bên trong label để click vào đâu cũng chọn được
                            echo "<label for='option_$key' style='width:100%;display:flex;align-items:center;cursor:pointer;'>";
                            echo "<input type='radio' name='answer' value='$key' id='option_$key' $checked required style='margin-right:12px;'>";
                            echo htmlspecialchars($option['text']);
                            echo "</label>";
                            echo "</div>";
                        }
                        ?>

                    </div>
                    <!-- <button type="submit">Lưu câu trả lời</button> -->
                </form>
            </div>
            <!-- Nút chuyển câu trước sau  -->
            <div class="navigation">
                <?php if ($current_question > 1): ?>
                    <a href="?q=<?php echo $current_question-1; ?>"><button type="button">← Câu trước</button></a>
                <?php endif; ?>
                
                <?php if ($current_question < count($questions)): ?>
                    <a href="?q=<?php echo $current_question+1; ?>"><button type="button">Câu tiếp →</button></a>
                <?php endif; ?>
            </div>

          <!-- Nếu không có câu hỏi hỏi báo lỗi  -->
        <?php else: ?>
            <p class="error">Không tìm thấy câu hỏi nào cho khóa học: <?php echo htmlspecialchars($khoa_hoc); ?></p>
            <p>Vui lòng kiểm tra lại mã khóa học của bạn.</p>
        <?php endif; ?>
        

        <!-- <div style="text-align: center; margin-top: 30px;">
            <a href="logout.php"><button type="button" class="logout-btn">Đăng xuất</button></a>
        </div> -->
        
    
    </div>
</body>
</html>

