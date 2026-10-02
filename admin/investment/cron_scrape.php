<?php
// cron_scrape.php - Enhanced version for cron job execution
require_once('../../protect/session_check.php');
// 1. Set execution environment
set_time_limit(120);
date_default_timezone_set('Asia/Kathmandu');
error_reporting(E_ALL);

// 2. Define paths and logging
define('LOG_DIR', __DIR__ . '/logs/');
if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

// 3. Custom error handler for cron
function handleCronError($message) {
    $logEntry = date('Y-m-d H:i:s') . " - CRON ERROR: " . $message . "\n";
    file_put_contents(LOG_DIR . 'cron_errors.log', $logEntry, FILE_APPEND);
    exit(1); // Non-zero exit code for cron to detect failure
}

// 4. Execute the scraper
try {
    require __DIR__ . '/dis/scrape_and_store.php';
    
    // Verify the script executed successfully by checking output
    $output = ob_get_clean();
    $response = json_decode($output, true);
    
    if (!isset($response['status']) || $response['status'] !== 'success') {
        handleCronError("Scraping failed: " . ($response['message'] ?? 'Unknown error'));
    }
    
    // Log successful execution
    $successLog = date('Y-m-d H:i:s') . " - Successfully updated " . 
                 ($response['records'] ?? 0) . " records in " . 
                 ($response['execution_time'] ?? 0) . "s\n";
    file_put_contents(LOG_DIR . 'cron_success.log', $successLog, FILE_APPEND);
    
    exit(0); // Success exit code
    
} catch (Exception $e) {
    handleCronError($e->getMessage());
}
?>