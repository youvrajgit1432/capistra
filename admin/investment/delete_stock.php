<?php
  require_once('../../protect/session_check.php');
require_once('../config/dbcon.php');
require_once('includes/functions.php');

// Verify authentication if needed

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $delete_id = (int)$_GET['id'];
        
        // Verify record exists first
        $check_query = "SELECT id FROM stock_investments WHERE id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'i', $delete_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) === 0) {
            throw new Exception("Stock record not found");
        }
        
        // Delete the record
        $delete_query = "DELETE FROM stock_investments WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $delete_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Delete failed: " . mysqli_error($conn));
        }
        
       
    
    } catch (Exception $e) {
     
    }
}

// Redirect back to portfolio
header("Location: dis_stock.php");
exit();
?>