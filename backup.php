 <?php
// Browser requests must be authenticated; scheduled runs (Task Scheduler / the
// Windows service) execute this script through the PHP CLI and have no session.
if (PHP_SAPI !== 'cli') {
    require_once __DIR__ . '/protect/session_check.php';
}
require_once __DIR__ . '/includes/backup_config.php';

// Configuration comes from .env via config/app.php; includes/config.ini is an
// optional override for backup tuning only (never for credentials).
$config = capistra_backup_config();
date_default_timezone_set($config['timezone']);

// Database credentials
$dbUser = $config['user'];
$dbPass = $config['password'];
$dbName = $config['name'];

// Backup settings
$backupFolder = realpath(__DIR__ . '/backups') ?: __DIR__ . '/backups';
$filename = "capistra_backup_" . date('Y-m-d_H-i-s') . ".sql";
$filepath = $backupFolder . DIRECTORY_SEPARATOR . $filename;
$maxFiles = (int) $config['max_files'];

// Ensure directories exist
if (!file_exists($backupFolder)) {
    mkdir($backupFolder, 0755, true);
}
if (!file_exists($backupFolder . '/logs')) {
    mkdir($backupFolder . '/logs', 0755, true);
}

// Find mysqldump
$mysqldumpPath = $config['mysqldump_path'];
if (!file_exists($mysqldumpPath)) {
    $mysqldumpPath = 'mysqldump'; // Fallback to system PATH
}

// Execute backup
$command = sprintf(
    '"%s" --user=%s --password=%s %s > "%s" 2>&1',
    $mysqldumpPath,
    $dbUser,
    $dbPass,
    $dbName,
    $filepath
);

exec($command, $output, $result);

// Handle results
if ($result === 0 && file_exists($filepath)) {
    $response = [
        'success' => true,
        'message' => "Backup successful: $filename",
        'filename' => $filename,
        'size' => filesize($filepath)
    ];
    logResult(true, $response['message'], $filepath);
    
    // Cleanup old backups
    if ($maxFiles > 0) {
        $backups = glob($backupFolder . "/capistra_backup_*.sql");
        if (count($backups) > $maxFiles) {
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $toDelete = count($backups) - $maxFiles;
            for ($i = 0; $i < $toDelete; $i++) {
                if (file_exists($backups[$i])) {
                    unlink($backups[$i]);
                    logResult(true, "Deleted old backup: " . basename($backups[$i]));
                }
            }
        }
    }
} else {
    $errorMessage = "Backup failed. Error code: $result";
    if (!empty($output)) {
        $errorMessage .= "\nOutput: " . implode("\n", $output);
    }
    $response = [
        'success' => false,
        'message' => $errorMessage
    ];
    logResult(false, $errorMessage);
}

// Return appropriate response
if (isManualRequest()) {
    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isCliRequest()) {
    echo $response['message'] . "\n";
    exit($result === 0 ? 0 : 1);
} else {
    // For direct browser access (not through UI)
    echo $result === 0 
        ? "✅ " . $response['message'] 
        : "❌ " . $response['message'];
    if (!$response['success'] && !empty($output)) {
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
}

// Helper functions
function isManualRequest(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function isCliRequest(): bool {
    return php_sapi_name() === 'cli' || isset($_GET['scheduled']);
}

function logResult(bool $success, string $message, string $filepath = null): void {
    $logMessage = sprintf(
        "[%s] %s: %s%s\n",
        date('Y-m-d H:i:s'),
        $success ? 'SUCCESS' : 'ERROR',
        $message,
        $filepath ? " (Size: " . round(filesize($filepath)/1024, 2) . " KB)" : ""
    );
    
    file_put_contents(
        __DIR__ . '/backups/logs/' . ($success ? 'backup.log' : 'error.log'),
        $logMessage,
        FILE_APPEND
    );
}