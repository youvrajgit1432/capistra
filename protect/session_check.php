<?php
declare(strict_types=1);

/**
 * Capistra - authentication guard for legacy admin pages.
 *
 * Uses the single canonical session bootstrap (consistent session name and
 * cookie flags) and the canonical database connection.
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/session.php';

capistra_session_start();

$conn = capistra_mysqli();

if (!capistra_enforce_timeout()) {
    capistra_session_destroy();
    header('Location: ../index.php?timeout=1');
    exit;
}

if (!isset($_SESSION['username'])) {
    header('Location: ../index.php?error=session_expired');
    exit;
}

$username = $_SESSION['username'];
$stmt = $conn->prepare('SELECT id, email, full_name, address, profile_image, role FROM adminusers WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    capistra_session_destroy();
    header('Location: ../index.php?error=user_not_found');
    exit;
}

$user = $result->fetch_assoc();
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'] ?? 'admin';
if (!empty($user['full_name']))     { $_SESSION['full_name'] = $user['full_name']; }
if (!empty($user['address']))       { $_SESSION['address'] = $user['address']; }
if (!empty($user['profile_image'])) { $_SESSION['profile_image'] = $user['profile_image']; }
