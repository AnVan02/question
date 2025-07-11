<?php
include('../../config/config.php');

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tăng giới hạn upload cho video
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', 300);

// Kiểm tra kết nối CSDL
if (!$mysqli) {
    die("Lỗi kết nối MySQL: " . mysqli_connect_error());
}

// Nhận dữ liệu từ form
$data = $_GET['data'] ?? null;
$article_links = json_decode($data, true);
$article_id = $_POST['article_id'] ?? null;
$article_author = $_POST['article_author'] ?? null;
$article_title = $_POST['article_title'] ?? null;
$article_summary = $_POST['article_summary'] ?? null;
$article_content = $_POST['article_content'] ?? null;
$article_link = $_POST['article_link'] ?? '';
$article_link = trim($article_link);
$article_link = str_replace(' ', '-', $article_link);
$article_image = $_FILES['article_image']['name'] ?? null;
$article_image_tmp = $_FILES['article_image']['tmp_name'] ?? null;
$article_image = $article_image ? time() . '_' . basename($article_image) : null;
$article_date = $_POST['article_date'] ?? date('Y-m-d');
$article_status = $_POST['article_status'] ?? null;
$article_tag = $_POST['article_tag'] ?? null;
$article_tag = trim($article_tag); // Xóa khoảng trắng thừa
$article_tag = strtolower($article_tag); // Chuyển thành chữ thường
$article_tag = preg_replace('/\s+/', ' ', $article_tag); // Xóa khoảng trắng thừa ở giữa
$article_tag = str_replace('#', '', $article_tag);


// Thư mục lưu trữ
$imageDir = "uploads/images/";
$videoDir = "uploads/videos/";

if (!file_exists($imageDir)) mkdir($imageDir, 0777, true);
if (!file_exists($videoDir)) mkdir($videoDir, 0777, true);

// Xử lý upload video (nếu có)
$videoName = '';
$video_link = '';
if (!empty($_FILES['video']['name'])) {
    $video = $_FILES['video'];
    $videoName = time() . '_' . basename($video['name']);
    $targetVideoPath = $videoDir . $videoName;
    $videoType = strtolower(pathinfo($targetVideoPath, PATHINFO_EXTENSION));
    $allowedVideoTypes = ['mp4', 'webm', 'ogg', 'mov', 'avi'];

    if (in_array($videoType, $allowedVideoTypes) && $video['size'] <= 500 * 1024 * 1024) {
        if (move_uploaded_file($video['tmp_name'], $targetVideoPath)) {
            $relativePath = "../admin/modules/blog/" . $targetVideoPath;
            $video_link = '<video width="720" height="620" controls>
                            <source src="' . $relativePath . '" type="video/' . $videoType . '">
                            Your browser does not support the video tag.
                          </video>';
        } else {
            error_log("Lỗi upload video: " . print_r($_FILES['video'], true));
            die("Không thể upload video: " . error_get_last()['message']);
        }
    } else {
        die("Định dạng hoặc kích thước video không hợp lệ.");
    }
}

// Thêm bài viết
if (isset($_POST['article_add'])) {
    if (empty($article_link)) {
        $article_link = uniqid('article_');
    }

    // Kiểm tra trùng lặp bài viết
    $sql_check = "SELECT * FROM article WHERE article_link = ?";
    $stmt = $mysqli->prepare($sql_check);
    if (!$stmt) die("Lỗi SQL: " . $mysqli->error);
    $stmt->bind_param("s", $article_link);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("<script>alert('Lỗi: Đường dẫn bài viết đã tồn tại!'); window.history.back();</script>");
    }

    // Upload ảnh nếu có
    if ($article_image && $article_image_tmp) {
        move_uploaded_file($article_image_tmp, 'uploads/' . $article_image);
    }
    
    // Kết hợp summary với video link (nếu có)
    $full_summary = $article_summary . ($video_link ? $video_link : '');
 
    // Thêm vào database
    $sql_add = "INSERT INTO article (article_link, article_author, article_title, article_summary, article_content, article_image, article_date, article_tag, article_status, article_video) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql_add);
    if (!$stmt) die("Lỗi SQL: " . $mysqli->error);
    $stmt->bind_param("ssssssssss", $article_link, $article_author, $article_title, $full_summary, $article_content, $article_image, $article_date, $article_tag, $article_status, $videoName);
    
    if ($stmt->execute()) {
        header('Location: ../../index.php?action=article&query=article_list');
        exit;
    } else {
        die("Lỗi khi thêm bài viết: " . $stmt->error);
    }

// Sửa bài viết
} elseif (isset($_POST['article_edit'])) {
    if (empty($article_link)) {
        die("<script>alert('Lỗi: Không tìm thấy đường dẫn bài viết!'); window.history.back();</script>");
    }

    // Kiểm tra ảnh mới
    if ($article_image && $article_image_tmp) {
        move_uploaded_file($article_image_tmp, 'uploads/' . $article_image);
        
        // Xóa ảnh cũ
        $sql_old_image = "SELECT article_image FROM article WHERE article_link = ?";
        $stmt = $mysqli->prepare($sql_old_image);
        if (!$stmt) die("Lỗi SQL: " . $mysqli->error);
        $stmt->bind_param("s", $article_link);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['article_image']) && file_exists('uploads/' . $row['article_image'])) {
                unlink('uploads/' . $row['article_image']);
            }
        }
    } else {
        // Nếu không có ảnh mới, giữ nguyên ảnh cũ
        $sql_get_old_image = "SELECT article_image FROM article WHERE article_link=?";
        $stmt = $mysqli->prepare($sql_get_old_image);
        if (!$stmt) die("Lỗi SQL: " . $mysqli->error);
        $stmt->bind_param("s", $article_link);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $article_image = $row['article_image'];
        }
    }

    // Cập nhật bài viết
    $sql_update = "UPDATE article SET article_author=?, article_title=?, article_summary=?, article_content=?, article_image=?, article_date=?, article_tag=?, article_status=? WHERE article_link=?";
    $stmt = $mysqli->prepare($sql_update);
    if (!$stmt) die("Lỗi SQL: " . $mysqli->error);
    $stmt->bind_param("sssssssss", $article_author, $article_title, $article_summary, $article_content, $article_image, $article_date, $article_tag, $article_status, $article_link);
    if (!$stmt->execute()) {
        die("Lỗi UPDATE: " . $stmt->error);
    }
    
    header('Location: ../../index.php?action=article&query=article_list');
}
// Xóa bài viết
else {
    foreach ($article_links as $id) {
        $sql = "SELECT * FROM article WHERE article_link = '$id' LIMIT 1";
        $query = mysqli_query($mysqli, $sql);
        while ($row = mysqli_fetch_array($query)) {
            if (!empty($row['article_image']) && file_exists('uploads/' . $row['article_image'])) {
                unlink('uploads/' . $row['article_image']);
            }
        }
        $sql_delete = "DELETE FROM article WHERE article_link = '$id'";
        mysqli_query($mysqli, $sql_delete);
    }
    header('Location: ../../index.php?action=article&query=article_list');
}

// Truy vấn các bài viết thuộc tag
if (isset($_GET['tag'])) {
    $tag = strtolower(mysqli_real_escape_string($mysqli, $_GET['tag'])); // Lấy tag từ URL và chuyển về chữ thường

    // Debug xem tag lấy ra từ URL có đúng không
    echo "Tag từ URL: " . htmlspecialchars($tag) . "<br>";

    // $sql_tag = "SELECT * FROM article WHERE FIND_IN_SET('$tag', LOWER(article_tag)) > 0 ORDER BY article_id DESC";
    $sql_tag = "SELECT * FROM article WHERE LOWER(article_tag) LIKE '%$tag%' ORDER BY article_id DESC";

    // Debug truy vấn SQL
    echo "SQL Query: " . $sql_tag . "<br>";

    $query_tag = mysqli_query($mysqli, $sql_tag);

    if (mysqli_num_rows($query_tag) > 0) {
        while ($row = mysqli_fetch_array($query_tag)) {
            echo "<h2><a href='" . urlencode($row['article_link']) . "'>" . htmlspecialchars($row['article_title']) . "</a></h2>";
            echo "<p><strong>Tóm tắt:</strong> " . htmlspecialchars($row['article_summary']) . "</p>";
            echo "<div><strong>Nội dung:</strong> " . nl2br(htmlspecialchars($row['article_content'])) . "</div>";
            echo "<hr>";
        }
    } else {
        echo "Không có bài viết nào thuộc tag này.";
    }
}



?>
    

