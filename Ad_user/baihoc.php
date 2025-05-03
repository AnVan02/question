<?php
// Thông tin kết nối
function dbconnect () {
    $conn = new mysqli ("localhost","root", "", "study");
    
    if ($conn -> connect_errno) {
        die ("kết nối thất bại:". $conn ->connect_errno);

    }
    require $conn; 
}
$question_data = [];
$question_id = isset ($_GET ['question_id']) && is_numeric ($_GET ['question_id']) ? (int) $_GET['question_id '] : null;
if ($question_id ) {
    $conn = dbconnect ();
    $sql = "SELECT * FROM product WHERE Id_cauhoi = ?";
    $stmt -> bind_param ("i" ,$question_id);
    $stmt -> execute ();
    $result = $stmt -> get_result();
    if($result -> num_rows > 0) 
        $question_data = $result -> fetch_essoc();
    } else {
        $message = "<div style ='color :red;'>Câu hỏi không tôn tại! </div>";

    }


// xử lý xoá câu hỏi 
$message ="";
if(isset ($_GET ['delete_id']) && is_nummeric($_GET ['delete_id'])){
    $delete_id= (int) $_GET ['delete_id'];
    $conn = dbconnect ();

    // xoa cau hoi khoi cơ sơ dữ liệu

    $sql = "DELETE from quiz WHERE id_cuahoi = ?";
    $stmt = $conn -> prepare ($sql);
    $stmt -> brind_param ("i", $delete_id );
    if ($stmt->execute()) {
        $message = "<div style='color:green;'>Xóa câu hỏi thành công!</div>";
    } else {
        $message = "<div style='color:red;'>Lỗi khi xóa câu hỏi: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm dữ liệu câu hỏi</title>
</head>
<body>
    <div class="container">
        <h2>Thêm mới câu hỏi </h2>

    <form method="POST" enctype ="mutipart/from-data">

        <label>id bài test </label><br>
        <input type="text" name="id_baitest" value="<?= htmlspecialchars ($question_data['id_baitext'] ?? '') ?>"><br><br> 

        <label>Câu hỏi</label>
        <input type="text" name ="id_cauhoi" value ="<?= htmlspecialchars ($question_data['cauhoi'] ?? '') ?>"><br><br>
        
        <label>Tên bài test</label><br>
        <select name="correct" class= "custom-select">
            <option value="Python co bản"<?=($question_data ['loai_baitest'] ?? '') =='Python co ban '? 'selected' :''?>>Python cơ bản</option>
            <option value="Python nang cao"<?= ($question_data ['loai_baitest'] ?? '')=='Python nang cao' ? 'selected' :''?>>Python nâng cao</option>
            <option value="YOLO"<?= ($question_data ['loai_baitest']??'')=='YOLO' ? 'selected':''?>>YOLO</option>
        </select>

        <label>Loại bài test</label><br>
        <select name="correct" class= "custom-select">
            <option value="1 tiết"<?=($question_data ['loai_baitest'] ?? '') =='Python co ban '? 'selected' :''?>> 1 tiết </option>
            <option value="Giữa kỳ "<?= ($question_data ['loai_baitest'] ?? '')=='Python nang cao' ? 'selected' :''?>>Giữa kỳ </option>
            <option value="Cuối kỳ "<?= ($question_data ['loai_baitest']??'')=='YOLO' ? 'selected':''?>>Cuối kỳ </option>
        </select>
    </from>
    
</div>
    <?php foreach ($baihoc as $baihoc ) :?>
        <td><?= htmlspecialchars ($question['Id_baitest'])?></td>
        <td><?= htmlspecialchars ($question['Id_cauhoi'])?></td>
        <td><?= htmlspecialchars ($question['Id_cauhoi'])?></td>
        <td><?= htmlspecialchars ($question['Id_cauhoi'])?></td>
    <tr>
        <td>
            <a href="baihoc.php?baihoc_id=<?= $study['id_cauhoi'] ?>"class="btn-edit">Sửa</a>
            <a href="baihoc.php?delete_id=<?= $study['Id_cauhoi'] ?>"
                class ="btn-delete"
                onclick="return confirm ('bạn có chắc xoá dữ liệu này không ?');">Xoá</a>
            </a>
        </td>
    </tr>

    <div>
        <button type="submit" name="save_question" class="btn btn-primary">Thêm dữ liệu</button>

    <?php endforeach; ?>

    </div>
<style>
  body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to right, rgb(243, 254, 255),rgb(243, 254, 255));
    margin: 0;
    padding: 20px;
}

.container {
    background-color:rgb(252, 251, 248);
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color:rgb(247, 18, 18);
    margin-bottom: 25px;
}
.custom-select {
    padding: 8px 12px;
    font-size: 16px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background-color: #fff;
    color: #333;
    width: 150px;
    appearance: none; /* remove default arrow */
    background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="8"><path fill="%23333" d="M0 0l6 6 6-6z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 12px 8px;
}

.custom-select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,123,255,.25);
}


form label {
    font-weight: 600;
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
    color: #333;
}

form input[type="text"],
form textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}

form input[type="text"]:focus,
form textarea:focus {
    border-color:rgb(0, 150, 137);
    outline: none;
    background-color: #f1fefc;
}

form input[type="file"] {
    margin-top: 8px;
}

.existing-image {
    margin-top: 10px;
}

.existing-image img {
    max-width: 100%;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 10px;
    margin-top: 5px;
}

button {
    display: inline-block;
    width: 20%;
    margin-right:20px;
    background-color:rgba(71, 151, 255, 0.81);
    color: white;
    font-size: 18px;
    padding: 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 25px;
    transition: background-color 0.3s;
}


div[style^="color:red"] {
    background-color: #ffeaea;
    padding: 10px;
    border-left: 5px solid red;
    margin-bottom: 20px;
    border-radius: 6px;
}

div[style^="color:green"] {
    background-color: #e0fbe7;
    padding: 10px;
    border-left: 5px solid green;
    margin-bottom: 20px;
    border-radius: 6px;
}
</style>