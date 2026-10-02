<?php
 require_once('../../protect/session_check.php');
require_once('../config/dbcon.php');

header('Content-Type: application/json');

try {
    $result = $conn->query("SELECT TIMESTAMPDIFF(SECOND, MAX(fetched_at), NOW()) as seconds_since_update FROM stock_prices");
    $data = $result->fetch_assoc();
    echo json_encode([
        'seconds_since_update' => $data['seconds_since_update'] ?? 0,
        'status' => 'success'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>