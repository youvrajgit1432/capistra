<?php
// Set unlimited execution time
set_time_limit(0);

// Load configuration
$config = parse_ini_file(__DIR__ . '/config.ini', true);
date_default_timezone_set($config['backup']['timezone']);

// Path constants
define('BACKUP_DIR', __DIR__ . '/backups');
define('LOG_DIR', BACKUP_DIR . '/logs');
define('LOCK_FILE', __DIR__ . '/lock/backup.lock');

// Ensure directories exist
foreach ([BACKUP_DIR, LOG_DIR, __DIR__ . '/lock'] as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Main scheduler loop
while (true) {
    try {
        // Check if backup is needed
        if (shouldRunBackup($config)) {
            runBackup($config);
        }
    } catch (Exception $e) {
        logError($e->getMessage());
    }
    
    // Sleep for 1 hour before checking again
    sleep(3600);
}

function shouldRunBackup($config) {
    $lastBackup = getLastBackupTime();
    $interval = (int)$config['backup']['backup_interval_hours'] * 3600;
    
    return (time() - $lastBackup) >= $interval;
}

function getLastBackupTime() {
    $backups = glob(BACKUP_DIR . '/*.sql');
    if (empty($backups)) {
        return 0;
    }
    
    rsort($backups);
    return filemtime($backups[0]);
}

function runBackup($config) {
    // Create lock file
    if (file_exists(LOCK_FILE)) {
        throw new Exception('Backup already in progress');
    }
    file_put_contents(LOCK_FILE, time());
    
    try {
        $output = [];
        $command = sprintf(
            '"%s" --user=%s --password=%s %s > "%s/backup_%s.sql" 2>&1',
            $config['backup']['mysqldump_path'],
            $config['database']['user'],
            $config['database']['password'],
            $config['database']['name'],
            BACKUP_DIR,
            date('Y-m-d_H-i-s')
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Backup failed: ' . implode("\n", $output));
        }
        
        logSuccess('Backup completed successfully');
        cleanupOldBackups($config['backup']['max_files']);
    } finally {
        // Always remove lock file
        if (file_exists(LOCK_FILE)) {
            unlink(LOCK_FILE);
        }
    }
}

function cleanupOldBackups($maxFiles) {
    $backups = glob(BACKUP_DIR . '/*.sql');
    if (count($backups) <= $maxFiles) {
        return;
    }
    
    // Sort by modification time (oldest first)
    usort($backups, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // Delete oldest files
    $toDelete = count($backups) - $maxFiles;
    for ($i = 0; $i < $toDelete; $i++) {
        unlink($backups[$i]);
    }
}

function logSuccess($message) {
    $logMessage = sprintf("[%s] SUCCESS: %s\n", date('Y-m-d H:i:s'), $message);
    file_put_contents(LOG_DIR . '/backup.log', $logMessage, FILE_APPEND);
}

function logError($message) {
    $logMessage = sprintf("[%s] ERROR: %s\n", date('Y-m-d H:i:s'), $message);
    file_put_contents(LOG_DIR . '/error.log', $logMessage, FILE_APPEND);
}