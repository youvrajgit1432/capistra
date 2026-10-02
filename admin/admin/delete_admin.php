<?php
session_start();
require_once('../config/dbcon.php'); // Include your database connection

// Verify admin is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid admin ID";
    header('Location: admin_data.php');
    exit;
}

$admin_id = (int)$_GET['id'];

try {
    // Prevent deleting the last admin
    $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM adminusers");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['admin_count'] <= 1) {
        $_SESSION['error'] = "Cannot delete the last admin user";
        header('Location: admin_data.php');
        exit;
    }

    // First check if the admin exists
    $stmt = $pdo->prepare("SELECT id FROM adminusers WHERE id = ?");
    $stmt->execute([$admin_id]);
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Admin user not found";
        header('Location: admin_data.php');
        exit;
    }

    // Delete the admin
    $stmt = $pdo->prepare("DELETE FROM adminusers WHERE id = ?");
    $stmt->execute([$admin_id]);
    
    $_SESSION['message'] = "Admin deleted successfully!";
    
    // Log the deletion (optional)
    error_log("Admin ID {$admin_id} was deleted by {$_SESSION['username']}");
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log("Delete admin error: " . $e->getMessage());
}

header('Location: admin_data.php');
exit;
?>