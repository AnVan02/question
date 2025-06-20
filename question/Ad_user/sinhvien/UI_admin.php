<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu khóa học theo Student ID</title>
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

        button:hover {
            background-color: #00796b;
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

        a:hover {
            text-decoration: underline;
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

    $stmt = $conn->prepare("SELECT Khoahoc FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $khoahoc_ids = explode(',', $row['Khoahoc']);
        $placeholders = implode(',', array_fill(0, count($khoahoc_ids), '?'));
        $types = str_repeat('i', count($khoahoc_ids));

        $sql2 = "SELECT id, khoa_hoc FROM khoa_hoc WHERE id IN ($placeholders)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param($types, ...array_map('intval', $khoahoc_ids));
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        echo "<h3>Các khóa học của sinh viên ID <strong>$student_id</strong>:</h3><ul>";
        while ($row2 = $result2->fetch_assoc()) {
            echo "<li>
                {$row2['khoa_hoc']} 
                - <a href='?student_id=$student_id&khoa_hoc_id={$row2['id']}'>Xem thêm</a>
            </li>";
        }
        echo "</ul>";
    } else {
        echo "<div class='error'>Không tìm thấy sinh viên với ID: $student_id</div>";
    }
}

// Xử lý khi nhấn "Xem thêm" để hiển thị danh sách bài test
if (isset($_GET['khoa_hoc_id'])) {
    $khoa_hoc_id = intval($_GET['khoa_hoc_id']);

    $stmt3 = $conn->prepare("SELECT ten_test FROM test WHERE id_khoa = ?");
    $stmt3->bind_param("i", $khoa_hoc_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    echo "<h3>Các bài test thuộc khóa học ID <strong>$khoa_hoc_id</strong>:</h3><ul>";
    if ($result3->num_rows > 0) {
        while ($test = $result3->fetch_assoc()) {
            echo "<li>{$test['ten_test']}</li>";
        }
    } else {
        echo "<li>Không có bài test nào.</li>";
    }
    echo "</ul>";
}
//  Hiển thị câu hỏi của bài test
if (isset($_GET['cauhoi'])) {
    $cauhoi = intval($_GET['cauhoi']);

    $stmt4 = $conn->prepare("SELECT question FROM quiz WHERE id_cauhoi = ?");
    $stmt4->bind_param("i", $cauhoi);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    echo "<h3>Câu hỏi trong bài test ID <strong>$cauhoi</strong>:</h3><ul>";
    if ($result4->num_rows > 0) {
        while ($q = $result4->fetch_assoc()) {
            echo "<li>{$q['question_text']}</li>";
        }
    } else {
        echo "<li>Không có câu hỏi nào.</li>";
    }
    echo "</ul>";
}

$conn->close();

?>

</body>
</html>
