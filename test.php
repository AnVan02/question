<?php
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("<div class='error'>Kết nối thất bại: " . $conn->connect_error . "</div>");
}

// B1: Hiển thị các khóa học
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
                {$row2['khoa_hoc']} - 
                <a href='?student_id=$student_id&khoa_hoc_id={$row2['id']}'>Xem thêm</a>
            </li>";
        }
        echo "</ul>";
    } else {
        echo "<div class='error'>Không tìm thấy sinh viên với ID: $student_id</div>";
    }
}

// B2: Hiển thị danh sách bài test
if (isset($_GET['khoa_hoc_id'])) {
    $khoa_hoc_id = intval($_GET['khoa_hoc_id']);

    $stmt3 = $conn->prepare("SELECT id, ten_test FROM test WHERE id_khoa = ?");
    $stmt3->bind_param("i", $khoa_hoc_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();

    echo "<h3>Bài test thuộc khóa học ID <strong>$khoa_hoc_id</strong>:</h3><ul>";
    if ($result3->num_rows > 0) {
        while ($test = $result3->fetch_assoc()) {
            echo "<li>
                {$test['ten_test']} - 
                <a href='?student_id={$_GET['student_id']}&khoa_hoc_id=$khoa_hoc_id&test_id={$test['id']}'>Xem câu hỏi</a>
            </li>";
        }
    } else {
        echo "<li>Không có bài test nào.</li>";
    }
    echo "</ul>";
}

// B3: Hiển thị câu hỏi của bài test
if (isset($_GET['test_id'])) {
    $test_id = intval($_GET['test_id']);

    $stmt4 = $conn->prepare("SELECT question_text FROM questions WHERE test_id = ?");
    $stmt4->bind_param("i", $test_id);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    echo "<h3>Câu hỏi trong bài test ID <strong>$test_id</strong>:</h3><ul>";
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
