<?php
include('../../config/dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM investors WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $investor = $result->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($investor);
    exit();
}
?>