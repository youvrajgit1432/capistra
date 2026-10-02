<?php
// Capistra - delete a generated backup file (admin only).
// The previous build `require_once`d includes/config.ini (an INI file), which
// echoed its contents into the response; this script needs no configuration.
require_once __DIR__ . '/protect/session_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['filename'])) {
    echo json_encode(['success' => false, 'message' => 'No filename specified']);
    exit;
}

$filename = basename($_POST['filename']);
$backupFolder = realpath(__DIR__ . '/backups');
$filepath = $backupFolder . DIRECTORY_SEPARATOR . $filename;

// Security check - only allow deletion of .sql files in the backups folder
if (!file_exists($filepath) || !str_ends_with($filename, '.sql')) {
    echo json_encode(['success' => false, 'message' => 'Invalid backup file']);
    exit;
}

if (unlink($filepath)) {
    echo json_encode(['success' => true, 'message' => 'Backup deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete backup']);
}