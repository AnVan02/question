<!-- hiên thị bài học -->
<?php
if ($student_id == 1 || $student_id == 2) { // bài cần hiển thị khoá học
    // Cho phép truy cập
} else {
    echo "Bạn không có quyền truy cập khoá học này";
    exit();
}
?>