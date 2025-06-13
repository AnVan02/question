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
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);

// Lấy câu hỏi hiện tại
$current_question = isset($_GET['q']) ? (int)$_GET['q'] : 1;
if ($current_question < 1) $current_question = 1;
if (!empty($questions) && $current_question > count($questions)) {
    $current_question = count($questions);
}

// Xử lý khi submit câu trả lời từng câu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['answer']) && !isset($_POST['submit_quiz'])) {
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

// Lấy đáp án đã lưu (nếu có)
$student_id = $_SESSION['student_id'];
$sql = "SELECT Test_ID, Best_Score FROM kiem_tra WHERE Student_ID = ? AND Khoa_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $student_id, $_SESSION['khoa_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_answers = [];
while ($row = $result->fetch_assoc()) {
    $qid = str_replace("Test_", "", $row['Test_ID']);
    $user_answers[$qid] = $row['Best_Score'];
}

// Nếu đã nộp bài, hiển thị kết quả chi tiết
if (isset($_POST['submit_quiz'])) {
    $score = 0;
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Kết quả Quiz</title>
        <style>
            body { font-family: Arial, sans-serif; background: #e0f7fa; }
            .container { max-width: 900px; margin: 30px auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 8px 16px rgba(0,0,0,0.08);}
            h2 { color: #2c3e50; text-align: center; }
            .question-block { margin-bottom: 30px; padding: 20px; border-left: 6px solid #3498db; background: #f9f9f9; border-radius: 8px;}
            .question-text { font-size: 18px; font-weight: bold; margin-bottom: 10px;}
            ul { list-style: none; padding: 0;}
            li { padding: 10px; border-radius: 5px; margin-bottom: 6px; background: #f1f1f1;}
            li.correct { background: #d4edda; color: #155724; font-weight: bold;}
            li.incorrect { background: #f8d7da; color: #721c24; font-weight: bold;}
            .explanation-block { margin-top: 10px; padding: 15px; border-left: 6px solid; background: #fff3cd; border-radius: 6px;}
            .score { font-size: 20px; color: #e67e22; text-align: center; margin: 20px 0;}
            img { max-width: 100%; border-radius: 8px; margin-top: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);}
            .btn { display: block; margin: 30px auto 0; padding: 12px 30px; background: #3498db; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;}
            .btn:hover { background: #2980b9;}
        </style>
    </head>
    <body>
    <div class="container">
        <h2>Kết quả Quiz</h2>
            <?php if (!empty($questions)):?>
                <div style="text-align:center; margin-bottom: 30px;">
                    <div style="display:inline-block; background:linear-gradient(90deg,#e3f0ff 60%,#bbdefb 100%); border-radius:10px; padding:18px 300px; box-shadow:0 2px 8px #e3f0ff;">
                        <div style="font-size:1.15rem; color:#1565c0; margin-bottom:6px;">
                            <b>Môn học:</b> <?= htmlspecialchars($questions[0]['ten_khoa']) ?>
                        </div>
                        <div style="font-size:1.15rem; color:#e67e22;">
                            <b>Bài thi:</b> <?= htmlspecialchars($questions[0]['id_baitest']) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
       <?php foreach ($questions as $i => $q): 
            $qid = $q['Id_cauhoi'];
            $user_correct = isset($user_answers[$qid]) ? $user_answers[$qid] : 0;
            if ($user_correct) $score++;
            $dap_an_dung = $q['dap_an'];
            $giai_thich_dung = isset($q['giaithich_' . strtolower($dap_an_dung)]) ? $q['giaithich_' . strtolower($dap_an_dung)] : '';
            // Nếu muốn lấy đáp án người chọn, cần lưu đáp án người chọn vào database/session
            // Ví dụ: $user_selected = ...;
        ?>
            <div class="question-block">
                <p class="question-text">Câu <?= $i+1 ?>: <?= htmlspecialchars($q['cauhoi']) ?></p>
                <?php if (!empty($q['hinhanh'])): ?>
                    <img src="<?= htmlspecialchars($q['hinhanh']) ?>" alt="Hình ảnh câu hỏi">
                <?php endif; ?>
                <ul>
                    <?php foreach (['A','B','C','D'] as $key): 
                        $is_correct = ($key == $q['dap_an']);
                        $li_class = $is_correct ? 'correct' : '';
                    ?>
                        <li class="<?= $li_class ?>">
                            <?= $key ?>. <?= htmlspecialchars($q['cau_' . strtolower($key)]) ?>
                            <?php if ($is_correct): ?> <b></b> <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="explanation-block" style="border-color: <?= $user_correct ? 'green' : 'red' ?>;">
                    <?php if ($user_correct): ?>
                        <strong>Giải thích:</strong> <?= htmlspecialchars($giai_thich_dung) ?>
                    <?php else: ?>
                   
                        <strong>Giải thích: </strong> <?= htmlspecialchars($giai_thich_dung) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <form method="get" action="quiz_test.php">
            <button class="btn" type="submit">Làm lại</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trả lời câu hỏi</title>
    <style>
        body { font-family: Arial, sans-serif; background: #e0f7fa; }
        .container { max-width: 900px; margin: 30px auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 8px 16px rgba(0,0,0,0.08);}
        h2 { color: #2c3e50; text-align: center; }
        .progress-bar { width: 100%; height: 10px; background: #eee; border-radius: 5px; margin: 20px 0; overflow: hidden;}
        .progress { height: 100%; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); border-radius: 5px; transition: width 0.3s ease;}
        .question { margin-bottom: 30px; padding: 35px 30px; border: 2px solid #3498db; border-radius: 18px; background: linear-gradient(120deg, #e3f0ff 0%, #f9fcff 100%); box-shadow: 0 4px 16px rgba(52,152,219,0.10);}
        .question h3 { color: #1565c0; font-size: 22px; margin-bottom: 22px; line-height: 1.6; font-weight: bold; letter-spacing: 0.5px; text-shadow: 0 2px 8px #e3f0ff; background: linear-gradient(90deg, #e3f0ff 60%, #bbdefb 100%); padding: 10px 18px; border-radius: 8px; display: inline-block; box-shadow: 0 1px 4px rgba(21,101,192,0.08);}
        .question img { max-width: 100%; height: auto; border-radius: 8px; margin: 15px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .options { margin-top: 20px; display: flex; flex-direction: column; gap: 18px;}
        .option { display: flex; align-items: flex-start; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 18px 20px; transition: background 0.3s, box-shadow 0.3s; min-height: 60px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);}
        .option:hover, .option:has(input[type="radio"]:checked) { background: #eaf6ff; box-shadow: 0 2px 8px rgba(52,152,219,0.08); border-color: #3498db;}
        .option input[type="radio"] { margin-right: 16px; margin-top: 3px; accent-color: #3498db; transform: scale(1.2);}
        .option label { font-size: 16px; color: #2c3e50; font-weight: 500; cursor: pointer; margin-bottom: 2px;}
        button { padding: 12px 25px; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        button:hover { background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%); transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2);}

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align:center;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($questions)):?>
        <h2>
            <?php
                echo "Môn học: <span style='color:#1565c0; margin:5px'>" . htmlspecialchars($questions[0]['ten_khoa']) . "</span><br>";
                echo "Bài thi: <span style='color:#e67e22; margin:5px'>" . htmlspecialchars($questions[0]['id_baitest']) . "</span>";               
            ?>
        </h2>
    
        <?php endif; ?>

        <?php if (!empty($questions)): ?>
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo ($current_question / count($questions)) * 100; ?>%"></div>
            </div>
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
                                $checked = (isset($user_answers[$questions[$current_question-1]['Id_cauhoi']]) && $key == $questions[$current_question-1]['dap_an'] && $user_answers[$questions[$current_question-1]['Id_cauhoi']] == 1) ? 'checked' : '';
                                echo "<div class='option'>";
                                echo "<label for='option_$key' style='width:100%;display:flex;align-items:center;cursor:pointer;'>";
                                echo "<input type='radio' name='answer' value='$key' id='option_$key' $checked required style='margin-right:12px;'>";
                                echo htmlspecialchars($option['text']);
                                echo "</label>";
                                echo "</div>";
                            }
                            ?>
                        </div><br>
                    </div>
                    <div class="navigation">
                        <?php if ($current_question > 1): ?>
                            <a href="?q=<?php echo $current_question-1; ?>"><button type="button">← Câu trước</button></a>
                        <?php endif; ?>
                        
                        <?php if ($current_question < count($questions)): ?>
                            <a href="?q=<?php echo $current_question+1; ?>"><button type="button">Câu tiếp →</button></a>
                        <?php endif; ?>
                    </div>
                    <?php if ($current_question == count($questions)): ?>
                        <button type="submit" name="submit_quiz" style="margin-top:20px;background:#e67e22;">Nộp bài & Xem đáp án</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>