<?php 
session_start ();
if (!isset ($_SESSION ['student_id'])) {
    header ("location: login.php");
    exit ();
}

function dbconnect (){
    if ($conn -> connect_error) {
        die("Kết nối CSDL thất bại: ". $conn->connect_error);
    }
    return $conn ;

}
$message ="";

$tudent_id = intval ($_SESSION ['student_id']);
// kiêm tra user thuộc khoá học đó 

iF($students_id == 1 || $students_id == 2 ){
    //  cho pháp dc truy cập 

} else {
    echo "Truy câp vào bài học thất bai";
    exite();
}

try {
    $conn = new PDO ("mysql:host= $servername ; dbname= $dbname", $servername, $password);
    $conn -> setAttribute (PDO:: ATTR_ERRMODE, DPO::ERRMODE_EXCETION);
    $sql = "SELECT KHoa_hoc FROM khoa_hoc WHERE id=3 ";// ID thuộc khoá học 
    $stmt = $conn -> query ($sql);
    $khoa_hoc = $stmt -> fetchColumn ();

} catch(PDOException $e) {
    die ("Connection failed: ". $e -->getMessage());
}

?>

<div>
    <h3>Khoá học <?php echo htmlspecialchars($khoa_hoc);?></h3>
    <p>KHoá học <?php echo htmlspecialchars ($students_id);?> <?php echo htmlspecialchars($khoa_hoc);?></p>
</div>

