<?php
$conn = new mysqli("localhost", "root", "", "student");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$id_khoa = isset($_GET['id_khoa']) ? (int)$_GET['id_khoa'] : 0;
$tests = [];
if ($id_khoa > 0) {
    $sql = "SELECT id_test, ten_test FROM test WHERE id_khoa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_khoa);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tests[] = $row;
    }
    $stmt->close();
}
$conn->close();
header('Content-Type: application/json');
echo json_encode($tests); 