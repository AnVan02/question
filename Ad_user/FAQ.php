<?php
session_start();

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['bai_hoc']) || !isset($_SESSION['ten_khoa'])) {
    header("Location: FAQ.php");
    exit;
}

// Hàm kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy thông tin khóa học từ bảng khoa_hoc
function getCoursesFromDB() {
    $conn = dbconnect();
    $sql = "SELECT id, khoa_hoc FROM khoa_hoc";
    $result = $conn->query($sql);
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row['khoa_hoc'];
    }
    $conn->close();
    return $courses;
}

// Lấy số lần thử từ bảng test
function getTestInfo($ten_test, $ten_khoa) {
    $conn = dbconnect();
    $courses = getCoursesFromDB();
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
        $conn->close();
        return $row['lan_thu'];
    }
    $stmt->close();
    $conn->close();
    return 1; // Mặc định 1 lần nếu không tìm thấy
}

// Lấy câu hỏi từ cơ sở dữ liệu
function getQuestionsFromDB($ten_khoa, $id_baitest) {
    $conn = dbconnect();
    $sql = "SELECT * FROM quiz WHERE ten_khoa = ? AND id_baitest = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ten_khoa, $id_baitest);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    if ($result->num_rows > 0) {
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
    }
    $stmt->close();
    $conn->close();

    // Nếu không đủ 5 câu hỏi của khoá học đó thì báo lỗi
    if (count($questions) < 5) {
        die("Lỗi: Không đủ 5 câu hỏi cho khóa học '$ten_khoa' thuộc bài  '$id_baitest'. Vui lòng thêm câu hỏi.");
    }
    return $questions;
}


// Lấy tham số từ URL
$ten_khoa = $_GET['ten_khoa'] ?? $_SESSION['ten_khoa'];
$id_baitest = $_GET['id_baitest'] ?? $_SESSION['id_baitest'];

// Kiểm tra ten_khoa có khớp với khóa học của tài khoản
if ($ten_khoa !== $_SESSION['ten_khoa']) {
    die("Lỗi: Bạn không có quyền truy cập khóa học '$ten_khoa'");
}


// Lấy số lần thử tối đa
$max_attempts = getTestInfo($id_baitest, $ten_khoa);

// Lấy danh sách câu hỏi
$questions = getQuestionsFromDB($ten_khoa, $id_baitest);

// Khởi tạo biến session
if (!isset($_SESSION["current"])) $_SESSION["current"] = 0;
if (!isset($_SESSION["score"])) $_SESSION["score"] = 0;
if (!isset($_SESSION["feedback"])) $_SESSION["feedback"] = "";
if (!isset($_SESSION["answers"])) $_SESSION["answers"] = [];
if (!isset($_SESSION["attempts"])) $_SESSION["attempts"] = 0;
if (!isset($_SESSION["highest_score"])) $_SESSION["highest_score"] = 0;
if (!isset($_SESSION["time"])) $_SESSION["time"] = date("d-m-Y H:i:s");


// Chọn ngẫu nhiên 5 câu hỏi
if (!isset($_SESSION["selected_questions"])) {
    $question_keys = array_keys($questions);
    shuffle($question_keys);
    $_SESSION["selected_questions"] = array_slice($question_keys, 0, 5);
}

// Kiểm tra giới hạn số lần thử
if ($_SESSION["attempts"] >= $max_attempts) {
    header("Location: ketqua.php?limit_exceeded=1");
    exit;
}


// Xử lý reset
if (isset($_GET["reset"]) && $_SESSION["attempts"] < $max_attempts) {
    $_SESSION["current"] = 0;
    $_SESSION["score"] = 0;
    $_SESSION["feedback"] = "";
    $_SESSION["answers"] = [];
    $_SESSION["time"] = date("d-m-Y H:i:s");
    $question_keys = array_keys($questions);
    shuffle($question_keys);
    $_SESSION["selected_questions"] = array_slice($question_keys, 0, 5);
    header("Location: FAQ.php?ten_khoa=" . urlencode($ten_khoa) . "&id_baitest=" . urlencode($id_baitest));
    exit;
}

$current = $_SESSION["current"];
$total = 5;

// Xử lý gửi biểu mẫu
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["next"])) {
    if (isset($_POST["answer"])) {
        $selected = $_POST["answer"];
        $question_index = $_SESSION["selected_questions"][$current];
        $correct = $questions[$question_index]["correct"];

        // Lưu câu trả lời
        $_SESSION["answers"][$current] = [
            "selected" => $selected,
            "is_correct" => ($selected === $correct),
            "question_index" => $question_index
        ];

        // Cập nhật điểm
        if ($selected === $correct) {
            $_SESSION["score"]++;
        }

        $_SESSION["current"]++;
        $_SESSION["feedback"] = "";
        header("Location: FAQ.php?ten_khoa=" . urlencode($ten_khoa) . "&id_baitest=" . urlencode($id_baitest));
        exit;
    } else {
        $_SESSION["feedback"] = "<div style='color: orange;'>⚠️ Vui lòng chọn một đáp án!</div>";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["goBack"])) {
    if ($_SESSION["current"] > 0) {
        $_SESSION["current"]--;
        $_SESSION["feedback"] = "";
    }
    header("Location: FAQ.php?ten_khoa=" . urlencode($ten_khoa) . "&id_baitest=" . urlencode($id_baitest));
    exit;
}

// Kiểm tra nếu hoàn thành bài kiểm tra
if ($current >= $total) {
    $_SESSION["attempts"]++;
    if ($_SESSION["score"] > $_SESSION["highest_score"]) {
        $_SESSION["highest_score"] = $_SESSION["score"];
    }
    $_SESSION["time"] = date("d-m-Y H:i:s");
    header("Location: ketqua.php");
    exit;
}

// Lấy câu hỏi hiện tại
$question_index = $_SESSION["selected_questions"][$current];
$question_data = $questions[$question_index];

// Gán nhãn cho các đáp án
$answer_labels = ['A', 'B', 'C', 'D'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?= htmlspecialchars($ten_khoa) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #2d3748;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 0 auto;
        }
        .question {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
            text-align: center;
        }
        .question-image-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .answer {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f7fafc;
            transition: background 0.3s ease;
        }
        .answer:hover {
            background: #edf2f7;
        }
        .answer input[type="radio"] {
            margin-right: 10px;
            accent-color: #3182ce;
        }
        .answer label {
            font-size: 1rem;
            color: #2d3748;
            cursor: pointer;
        }
        .content-area {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .left-area {
            text-align: center;
        }
        .progress {
            font-size: 1.1rem;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 10px;
        }
        .result-box {
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
            background: #fff5f5;
            color: #e53e3e;
            font-size: 0.95rem;
        }
        .btn-area {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .btn-prev,
        .btn-next {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-prev {
            background: #a0aec0;
            color: #ffffff;
        }
        .btn-prev:hover {
            background: #718096;
            transform: translateY(-2px);
        }
        .btn-next {
            background: #3182ce;
            color: #ffffff;
        }
        .btn-next:hover {
            background: #2b6cb0;
            transform: translateY(-2px);
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .question {
                font-size: 1.2rem;
            }
            .answer label {
                font-size: 0.9rem;
            }
            .btn-prev,
            .btn-next {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post">
            <div class="question">Câu hỏi: <?= htmlspecialchars($question_data['question']) ?></div>
            <?php if (!empty($question_data['image'])): ?>
                <div class="question-image-container">
                    <img src="<?= htmlspecialchars($question_data['image']) ?>" alt="Hình ảnh câu hỏi" class="question-image">
                </div>
            <?php endif; ?>

            <?php foreach ($question_data['choices'] as $key => $value): ?>
                <?php $label = $answer_labels[array_search($key, array_keys($question_data['choices']))]; ?>
                <div class="answer">
                    <input type="radio" name="answer" value="<?= htmlspecialchars($key) ?>" id="<?= htmlspecialchars($key) ?>"
                        <?php if (isset($_SESSION['answers'][$current]['selected']) && $_SESSION['answers'][$current]['selected'] === $key): ?>
                            checked
                        <?php endif; ?>
                    >
                    <label for="<?= htmlspecialchars($key) ?>"><?= $label ?>. <?= htmlspecialchars($value) ?></label>
                </div>
            <?php endforeach; ?>

            <div class="content-area">
                <div class="left-area">
                    <div class="progress">Câu <?= $current + 1 ?> / <?= $total ?> (Lần thử: <?= $_SESSION["attempts"] + 1 ?>/<?= $max_attempts ?>)</div>
                    <?php if ($_SESSION['feedback']): ?>
                        <div class="result-box">
                            <?= $_SESSION['feedback'] ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="btn-area">
                    <button type="submit" name="goBack" class="btn-prev">⬅️ Quay lại</button>
                    <button type="submit" name="next" class="btn-next">Tiếp theo ➡️</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>