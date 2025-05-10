<?php
session_start();

// Kết nối cơ sở dữ liệu
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "study");
    if ($conn->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
    return $conn;
}

// Lấy thông tin khóa học, bài test và số lần làm bài
function getCourseTestInfo() {
    $conn = dbconnect();
    $sql = "SELECT kh.khoa_hoc, t.ten_test, t.lan_thu
            FROM khoa_hoc kh
            LEFT JOIN test t ON kh.id = t.id_khoa";
    $result = $conn->query($sql);
    $course_test_info = [];
    while ($row = $result->fetch_assoc()) {
        $course_test_info[] = [
            'khoa_hoc' => $row['khoa_hoc'],
            'ten_test' => $row['ten_test'] ?? 'Chưa có bài test',
            'lan_thu' => $row['lan_thu'] ?? 'Chưa có bài test'
        ];
    }
    $conn->close();
    return $course_test_info;
}

// Lấy danh sách câu hỏi thi từ bảng quiz
function getAllQuestions() {
    $conn = dbconnect();
    $sql = "SELECT q.Id_cauhoi, q.ten_khoa, q.id_baitest, q.cauhoi, q.cau_a, q.cau_b, q.cau_c, q.cau_d, q.dap_an
            FROM quiz q
            ORDER BY q.ten_khoa, q.id_baitest";
    $result = $conn->query($sql);
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'id' => $row['Id_cauhoi'],
            'ten_khoa' => $row['ten_khoa'],
            'id_baitest' => $row['id_baitest'],
            'cauhoi' => $row['cauhoi'],
            'choices' => [
                'A' => $row['cau_a'],
                'B' => $row['cau_b'],
                'C' => $row['cau_c'],
                'D' => $row['cau_d']
            ],
            'dap_an' => $row['dap_an']
        ];
    }
    $conn->close();
    return $questions;
}

// Lấy dữ liệu
$course_test_info = getCourseTestInfo();
$questions = getAllQuestions();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin Khóa học, Bài test và Câu hỏi thi</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            color: #2c3e50;
            text-align: center;
        }

        .course-test-info, .questions-info {
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .course-test-info table, .questions-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .course-test-info th, .course-test-info td,
        .questions-info th, .questions-info td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .course-test-info th, .questions-info th {
            background-color: #3498db;
            color: #fff;
        }

        .course-test-info tr:hover, .questions-info tr:hover {
            background-color: #f1f1f1;
        }

        .no-data {
            color: #e74c3c;
            text-align: center;
            font-weight: bold;
        }

        .question-text {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .choices {
            margin-left: 20px;
        }

        .correct-answer {
            color: #2e7d32;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Thông tin Khóa học, Bài test và Câu hỏi thi 📚</h1>

        <!-- Hiển thị thông tin khóa học và số lần làm bài -->
        <div class="course-test-info">
            <h2>Khóa học và Bài test</h2>
            <?php if (empty($course_test_info)): ?>
                <p class="no-data">Không có dữ liệu khóa học hoặc bài test nào!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Khóa học</th>
                            <th>Bài test</th>
                            <th>Số lần làm bài</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($course_test_info as $info): ?>
                            <tr>
                                <td><?= htmlspecialchars($info['khoa_hoc']) ?></td>
                                <td><?= htmlspecialchars($info['ten_test']) ?></td>
                                <td><?= htmlspecialchars($info['lan_thu']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Hiển thị danh sách câu hỏi thi -->
        <div class="questions-info">
            <h2>Câu hỏi thi</h2>
            <?php if (empty($questions)): ?>
                <p class="no-data">Không có câu hỏi thi nào!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Khóa học</th>
                            <th>Bài test</th>
                            <th>Câu hỏi</th>
                            <th>Đáp án đúng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?= htmlspecialchars($question['ten_khoa']) ?></td>
                                <td><?= htmlspecialchars($question['id_baitest']) ?></td>
                                <td>
                                    <div class="question-text"><?= htmlspecialchars($question['cauhoi']) ?></div>
                                    <div class="choices">
                                        <p>A. <?= htmlspecialchars($question['choices']['A']) ?></p>
                                        <p>B. <?= htmlspecialchars($question['choices']['B']) ?></p>
                                        <p>C. <?= htmlspecialchars($question['choices']['C']) ?></p>
                                        <p>D. <?= htmlspecialchars($question['choices']['D']) ?></p>
                                    </div>
                                </td>
                                <td class="correct-answer"><?= htmlspecialchars($question['dap_an']) ?>. <?= htmlspecialchars($question['choices'][$question['dap_an']]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>