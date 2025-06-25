<?php
function dbconnect() {
    $conn = new mysqli("localhost", "root", "", "student");
    if ($conn->connect_error) {
        die("Kết nối CSDL thất bại: " . $conn->connect_error);
    }
    return $conn;
}

$message = "";

// Xử lý xóa khóa học và tất cả dữ liệu liên quan
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn = dbconnect();
    
    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    $conn->begin_transaction();
    
    try {
        // 1. Lấy tên khóa học để xóa các câu hỏi quiz liên quan
        $stmt = $conn->prepare("SELECT khoa_hoc FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $khoa_hoc = $result->fetch_assoc()['khoa_hoc'];
        $stmt->close();
        
        // 2. Xóa các bài kiểm tra (test) liên quan đến khóa học
        // Trước tiên lấy danh sách test_id để xóa các kết quả liên quan
        $test_ids = [];
        $stmt = $conn->prepare("SELECT id_test FROM test WHERE id_khoa = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $test_ids[] = $row['id_test'];
        }
        $stmt->close();
        
        if (!empty($test_ids)) {
            // Xóa các kết quả kiểm tra (ket_qua) liên quan đến các bài test
            $test_ids_str = implode("','", $test_ids);
            $conn->query("DELETE FROM ket_qua WHERE test_id IN ('$test_ids_str')");
            
            // Xóa các bản ghi kiểm tra (kiem_tra) liên quan
            $conn->query("DELETE FROM kiem_tra WHERE Khoa_ID = $id");
            
            // Xóa các bài test
            $conn->query("DELETE FROM test WHERE id_khoa = $id");
        }
        
        // 3. Xóa các câu hỏi quiz liên quan đến khóa học
        $conn->query("DELETE FROM quiz WHERE ten_khoa = '$khoa_hoc'");
        
        // 4. Cập nhật các sinh viên đang tham gia khóa học này
        // Lấy danh sách sinh viên có khóa học này
        $students = [];
        $result = $conn->query("SELECT Student_ID, Khoahoc FROM students WHERE Khoahoc LIKE '%$id%'");
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        // Cập nhật từng sinh viên
        foreach ($students as $student) {
            $courses = explode(',', $student['Khoahoc']);
            $new_courses = array_filter($courses, function($course) use ($id) {
                return $course != $id;
            });
            $new_courses_str = implode(',', $new_courses);
            
            $stmt = $conn->prepare("UPDATE students SET Khoahoc = ? WHERE Student_ID = ?");
            $stmt->bind_param("ss", $new_courses_str, $student['Student_ID']);
            $stmt->execute();
            $stmt->close();
        }
        
        // 5. Cuối cùng xóa khóa học
        $stmt = $conn->prepare("DELETE FROM khoa_hoc WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction nếu mọi thứ thành công
        $conn->commit();
        $message = "<div class='message success'>Đã xóa khóa học và tất cả dữ liệu liên quan thành công!</div>";
    } catch (Exception $e) {
        // Rollback nếu có lỗi xảy ra
        $conn->rollback();
        $message = "<div class='message error'>Lỗi khi xóa: " . $e->getMessage() . "</div>";
    }
    
    $conn->close();
}

// ... (phần còn lại của code giữ nguyên)
?>