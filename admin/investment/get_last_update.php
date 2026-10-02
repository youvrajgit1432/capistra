<?php
 require_once('../../protect/session_check.php');
require_once('../config/dbcon.php');

header('Content-Type: application/json');

try {
    $query = "SELECT MAX(fetched_at) as last_update FROM stock_prices";
    $result = mysqli_query($conn, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'last_update' => $row['last_update'],
            'status' => 'success'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No data found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>