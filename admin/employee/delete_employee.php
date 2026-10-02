<?php
require_once('../../protect/session_check.php');

include('../config/dbcon.php');

// Check if employee ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Employee ID is required";
    header("Location: employee_management.php");
    exit();
}

$employee_id = intval($_GET['id']);

// Check if employee exists
$query = "SELECT * FROM employees WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Employee not found";
    header("Location: employee_management.php");
    exit();
}

$employee = mysqli_fetch_assoc($result);

// Delete employee photo if exists
if (!empty($employee['photo_path']) && file_exists($employee['photo_path'])) {
    unlink($employee['photo_path']);
}

// Delete employee from database
$delete_query = "DELETE FROM employees WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $employee_id);
$delete_result = mysqli_stmt_execute($delete_stmt);

if ($delete_result) {
    $_SESSION['success_message'] = "Employee deleted successfully";
} else {
    $_SESSION['error_message'] = "Error deleting employee: " . mysqli_error($conn);
}

header("Location: employee_management.php");
exit();
?>