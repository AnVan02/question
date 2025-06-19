<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set timezone

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$ma_khoa = '1'; // Course ID
$id_test = '19'; // Test ID
$student_id = $_SESSION['student_id'];

// Check course access
$stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $khoahoc = $row['Khoahoc']; // e.g., "6,4"
    $khoahoc_list = array_map('intval', explode(',', $khoahoc));
    if (!in_array(intval($ma_khoa), $khoahoc_list)) {
        echo "<script>
            alert('Bạn không có quyền truy cập khóa học này!');
            window.location.href = 'login.php';
        </script>";
        exit();
    }
} else {
    echo "<script>
        alert('Không tìm thấy thông tin sinh viên!');
        window.location.href = 'login.php';
    </script>";
    exit();
}
$stmt->close();

// Get test name
$stmt = $conn->prepare("SELECT ten_test FROM test WHERE id_test = ?");
$stmt->bind_param("i", $id_test);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>alert('ID bài test ($id_test) không tồn tại trong hệ thống. Vui lòng kiểm tra lại!');</script>";
    exit();
}
$row = $result->fetch_assoc();
$id_baitest = $row['ten_test'];
$stmt->close();

// Get courses from database
function getCoursesFromDB($conn) {
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    return $courses;
}

// Get test info (max attempts)
function getTestInfo($conn, $ten_test, $ten_khoa) {
    $courses = getCoursesFromDB($conn);
    $id_khoa = array_search($ten_khoa, $courses);
    if ($id_khoa === false) {
        die("Lỗi: Không tìm thấy khóa học '$ten_khoa'");
    }
    $sql = "SELECT lan_thu FROM test WHERE ten_test = ? AND id_khoa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $ten_test, $id_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['lan_thu'];
    }
    $stmt->close();
    return 1;
}

// xử ly dữ liệu
$ten_khoa = '';
$current_index = isset($_SESSION['current_index']) ? intval($_SESSION['current_index']) : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];
$score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$highest_score = isset($_SESSION['highest_score']) ? $_SESSION['highest_score'] : 0;
$attempts = isset($_SESSION['attempts']) ? $_SESSION['attempts'] : 0;
$pass_score = ''; // Passing score

// xử lý lấy cấu hỏi 
$stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
$stmt->bind_param("s", $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $ten_khoa = $row['khoa_hoc'];
    $stmt2 = $conn->prepare("SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?");
    $stmt2->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $questions = [];
    while ($row2 = $result2->fetch_assoc()) {
        $questions[] = [
            'id' => $row2['Id_cauhoi'],
            'question' => $row2['cauhoi'],
            'choices' => [
                'A' => $row2['cau_a'],
                'B' => $row2['cau_b'],
                'C' => $row2['cau_c'],
                'D' => $row2['cau_d']
            ],
            'explanations' => [
                'A' => $row2['giaithich_a'],
                'B' => $row2['giaithich_b'],
                'C' => $row2['giaithich_c'],
                'D' => $row2['giaithich_d']
            ],
            'correct' => $row2['dap_an'],
            'image' => $row2['hinhanh']
        ];
    }
    if (count($questions) < 1) {
        die("Lỗi: Không đủ câu hỏi cho '$ten_khoa' và '$id_baitest'.");
    }
    $_SESSION['questions'] = $questions;
    $_SESSION['ten_khoa'] = $ten_khoa;
    $_SESSION['id_baitest'] = $id_baitest;
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 1;
    }
} else {
    die("Lỗi: Không tìm thấy khóa học với mã '$ma_khoa'");
}
$stmt->close();
$stmt2->close();


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['answer']) && isset($_SESSION['questions'])) {
    $user_answer = $_POST['answer'];
    $current_question = $_SESSION['questions'][$current_index];
    $is_correct = ($user_answer === $current_question['correct']);
    $answers[$current_index] = [
        'selected' => $user_answer,
        'is_correct' => $is_correct
    ];
    $_SESSION['answers'] = $answers;
    if ($is_correct) {
        $score++;
        $_SESSION['score'] = $score;
        if ($score > $highest_score) {
            $_SESSION['highest_score'] = $score;
        }
    }
    $current_index++;
    $_SESSION['current_index'] = $current_index;
}

// chuyển tiếp câu trước câu sau
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["skip"])) {
    if ($current_index < count($_SESSION['questions']) - 1) {
        $current_index++;
        $_SESSION['current_index'] = $current_index;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["goBack"])) {
    if ($current_index > 0) {
        $current_index--;
        $_SESSION['current_index'] = $current_index;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle reset
if (isset($_POST['reset'])) {
    $attempts++;
    $_SESSION['attempts'] = $attempts;
    $_SESSION['score'] = 0;
    $_SESSION['answers'] = [];
    $_SESSION['current_index'] = 0;
    $current_index = 0;
    $score = 0;
    $answers = [];
}

// Get max attempts
$max_attempts = getTestInfo($conn, $id_baitest, $ten_khoa);
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo htmlspecialchars($ten_khoa); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #ffffff, #b2ebf2);
            margin: 0;
            padding: 20px;
            font-size: 17px;
            color: #333;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #2c3e50;
            text-align: center;
        }
       
        .question-box h3 {
            color: #007bff;
            margin-top: 0;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f1f1f1;
        }
        ul li label {
            font-size: 17px;
            cursor: pointer;
        }
        li.correct {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        li.incorrect {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        button, a.try-again, a.back-to-quiz {
            padding: 10px 28px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        a.try-again.disabled {
            background-color: #ccc;
            pointer-events: none;
            cursor: not-allowed;
        }
        img {
            max-width: 300px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #eee;
            display: block;
        }
        .question-block {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .question-block h4 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 15px;
        }

        .question-block p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 10px;
            color: #555;
        }

        .question-block ul {
            list-style: none;
            padding-left: 0;
        }

        .question-block li {
            margin: 8px 0;
            font-size: 1rem;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .question-block li:hover {
            background-color: #f1f1f1;
        }

        .correct {
            color: green;
            font-weight: bold;
            background-color: #e8f5e9; /* light green background for correct answers */
        }

        .incorrect {
            color: red;
            font-weight: bold;
            background-color: #f8d7da; /* light red background for incorrect answers */
        }

        .question-block img {
            max-width: 300px;
            display: block;
            margin-top: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .score-info {
            background-color: #e7f3fe;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .score-info h4 {
            margin: 0;
            font-size: 1.4rem;
            color: #333;
        }

        .score-info p {
            margin: 5px 0;
            font-size: 1rem;
            color: #555;
        }

        .correct-answer {
            color: green;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .incorrect-answer {
            color: red;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .answer-explanation {
            font-size: 1rem;
            color: #333;
            margin-top: 10px;
            background-color: #f0f8ff;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .answer-explanation h5 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 10px;
        }

        .correct-answer {
            color: #2e7d32;
            font-weight: bold;
        }
        .no-answers {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }
        .btn-area {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($current_index < count($_SESSION['questions'])): ?>
            <?php $question = $_SESSION['questions'][$current_index]; ?>
            <h2>
                Môn học: <span style="color:#1565c0;"><?php echo htmlspecialchars($ten_khoa); ?></span><br>
                Bài thi: <span style="color:#e67e22;"><?php echo htmlspecialchars($id_baitest); ?></span>
            </h2>
            <form method="POST" action="">
                <div class="question-box">
                    <h3>Câu <?php echo $current_index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></h3>
                    <?php if (!empty($question['image'])): ?>
                        <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($question['choices'] as $key => $value): ?>
                            <li>
                                <label>
                                    <input type="radio" name="answer" value="<?php echo $key; ?>" required> <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="btn-area">
                        <button type="submit" name="goBack" <?php echo $current_index == 0 ? 'disabled' : ''; ?>>Câu trước</button>
                        <button type="submit" name="skip" <?php echo $current_index == count($_SESSION['questions']) - 1 ? 'disabled' : ''; ?>>Câu tiếp</button>
                    </div>
                    <input type="hidden" name="current_index" value="<?php echo $current_index; ?>">
                    <button type="submit">Trả lời »</button>
                </div>
            </form>
        <?php else: ?>
            <?php
            // lưu đáp án vào bảng table ket_qua "Câu 1: A, Câu 2: B, ..."
            $tt_bai_test = '';
            if (!empty($answers)) {
                $answer_pairs = [];
                foreach ($answers as $index => $answer) {
                    $answer_pairs[] = "Câu " . ($index + 1) . ": " . $answer['selected'];
                }
                $tt_bai_test = implode(", ", $answer_pairs);
                if (strlen($tt_bai_test) > 1000) {
                    $tt_bai_test = substr($tt_bai_test, 0, 997) . '...';
                }
            } else {
                $tt_bai_test = 'Không có câu trả lời';
            }



            // lưu kq_cao_nhat vào bảng ket_qua
            $conn = new mysqli("localhost", "root", "", "student");
            if ($conn->connect_error) {
                die("Kết nối thất bại: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("SELECT kq_cao_nhat FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
            $stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($highest_score > $row['kq_cao_nhat']) {
                    $stmt = $conn->prepare("UPDATE ket_qua SET kq_cao_nhat = ?, tt_bai_test = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
                    $stmt->bind_param("issis", $highest_score, $tt_bai_test, $student_id, $ma_khoa, $id_test);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("UPDATE ket_qua SET tt_bai_test = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
                    $stmt->bind_param("siss", $tt_bai_test, $student_id, $ma_khoa, $id_test);
                    $stmt->execute();
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO ket_qua (student_id, khoa_id, test_id, kq_cao_nhat, tt_bai_test) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isiss", $student_id, $ma_khoa, $id_test, $highest_score, $tt_bai_test);
                $stmt->execute();
            }
            $stmt->close();
            $conn->close();

            // luu tt_bai_test vào bảng ket_qua
                $conn = new mysqli ("localhost", "root", "", "student");
                if($conn ->connect_error) {
                    die ("Kết nối thất bại: ".$conn->connect_error);
                }
                      $stmt = $conn->prepare("SELECT tt_bai_test FROM ket_qua WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
            $stmt->bind_param("sis", $student_id, $ma_khoa, $id_test);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($highest_score > $row['tt_bai_test']) {
                    $stmt = $conn->prepare("UPDATE ket_qua SET tt_bai_test= ?, tt_bai_test = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
                    $stmt->bind_param("issis", $highest_score, $tt_bai_test, $student_id, $ma_khoa, $id_test);
                    $stmt->execute();
                } else {
                    $stmt = $conn->prepare("UPDATE ket_qua SET tt_bai_test = ? WHERE student_id = ? AND khoa_id = ? AND test_id = ?");
                    $stmt->bind_param("siss", $tt_bai_test, $student_id, $ma_khoa, $id_test);
                    $stmt->execute();
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO ket_qua (student_id, khoa_id, test_id, kq_cao_nhat, tt_bai_test) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isiss", $student_id, $ma_khoa, $id_test, $highest_score, $tt_bai_test);
                $stmt->execute();
            }
            $stmt->close();
            $conn->close();


            ?>
            <h1>Kết quả Quiz - <?php echo htmlspecialchars($ten_khoa); ?> - <?php echo htmlspecialchars($id_baitest); ?></h1>
            <p><strong>Khóa học:</strong> <?php echo htmlspecialchars($ten_khoa); ?></p>
            <p><strong>Bài test:</strong> <?php echo htmlspecialchars($id_baitest); ?></p>
            <p><strong>Thời gian hoàn thành:</strong> <?php echo date('H:i:s d/m/Y'); ?></p>
            <p><strong>Tổng điểm:</strong> <?php echo $score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Điểm cao nhất:</strong> <?php echo $highest_score; ?> / <?php echo count($_SESSION['questions']); ?></p>
            <p><strong>Số lần làm bài:</strong> <?php echo $attempts; ?> / <?php echo $max_attempts; ?></p>
            <p><strong>Trạng thái:</strong> <?php echo $score >= $pass_score ? 'Đạt' : 'Không đạt'; ?></p>
            <hr>
            <hr>
            <?php if (empty($answers)): ?>
                <p class="no-answers">Bạn chưa trả lời câu hỏi nào! <a class="back-to-quiz" href="?reset=1">Quay lại làm bài</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                    <div class="question-block">
                        <h4>Câu <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question']); ?></h4>
                        <?php if (!empty($question['image'])): ?>
                            <img src="<?php echo htmlspecialchars($question['image']); ?>" alt="Hình ảnh câu hỏi">
                        <?php endif; ?>
                        <ul>
                            <?php foreach ($question['choices'] as $key => $value): ?>
                                <?php
                                $is_selected = isset($answers[$index]) && $key === $answers[$index]['selected'];
                                $is_correct = $key === $question['correct'];
                                $class = '';
                                if ($is_selected) {
                                    $class = $is_correct ? 'correct' : 'incorrect';
                                } elseif ($is_correct) {
                                    $class = 'correct';
                                }
                                ?>
                                <li class="<?php echo $class; ?>">
                                    <?php echo $key; ?>. <?php echo htmlspecialchars($value); ?>
                                    <?php if ($is_selected && !$is_correct): ?> (Sai) <?php endif; ?>
                                    <?php if ($is_correct): ?> (Đúng) <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (isset($answers[$index]) && !$answers[$index]['is_correct']): ?>
                            <div class="answer-explanation">
                                <h5>Giải thích:</h5>
                                <p><?php echo htmlspecialchars($question['explanations'][$answers[$index]['selected']]); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <form method="POST" action="">
                <button type="submit" name="reset" value="1" <?php echo $attempts >= $max_attempts ? 'disabled' : ''; ?>>🔁 Làm lại (<?php echo $attempts; ?> / <?php echo $max_attempts; ?>)</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>