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

if (!function_exists('capistra_legacy_login_url')) {
    /**
     * Absolute login URL for legacy redirects.
     *
     * Legacy pages live at different nesting depths (`admin/x.php`,
     * `admin/fund/process/x.php`, ...), so a hard-coded relative target such as
     * `../index.php` lands on a non-existent page for the deeper ones. Resolve
     * against the canonical APP_URL instead, falling back to a relative path
     * only when APP_URL is empty.
     */
    function capistra_legacy_login_url(string $query = ''): string
    {
        $base = rtrim((string) APP_URL, '/');
        if ($base === '') {
            return '../index.php' . $query;
        }
        return $base . '/index.php' . $query;
    }
}

capistra_session_start();

$conn = capistra_mysqli();

if (!capistra_enforce_timeout()) {
    capistra_session_destroy();
    header('Location: ' . capistra_legacy_login_url('?timeout=1'));
    exit;
}

if (!isset($_SESSION['username'])) {
    header('Location: ' . capistra_legacy_login_url('?error=session_expired'));
    exit;
}

$username = $_SESSION['username'];
$stmt = $conn->prepare('SELECT id, email, full_name, address, profile_image, role FROM adminusers WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    capistra_session_destroy();
    header('Location: ' . capistra_legacy_login_url('?error=user_not_found'));
    exit;
}

$user = $result->fetch_assoc();
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'] ?? 'admin';
if (!empty($user['full_name']))     { $_SESSION['full_name'] = $user['full_name']; }
if (!empty($user['address']))       { $_SESSION['address'] = $user['address']; }
if (!empty($user['profile_image'])) { $_SESSION['profile_image'] = $user['profile_image']; }
