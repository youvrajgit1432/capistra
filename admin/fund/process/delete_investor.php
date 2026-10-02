<?php
  include('../../config/dbcon.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "UPDATE investors SET deleted = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../investorprofile.php");
exit();
?>