<?php
declare(strict_types=1);

/**
 * Capistra - login processing.
 *
 * Hardening: canonical session, CSRF verification, per-account rate limiting,
 * generic error messages, session id regeneration on success.
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/session.php';

capistra_session_start();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ../index.php');
    exit;
}

if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
    $_SESSION['login_error'] = 'Security token mismatch. Please try again.';
    header('Location: ../index.php');
    exit;
}

$usernameOrEmail = trim((string) ($_POST['username'] ?? ''));
$password        = (string) ($_POST['password'] ?? '');

// Generic failure helper: never reveal whether the account exists.
$fail = static function (string $message = 'Invalid username or password'): never {
    $_SESSION['login_error'] = $message;
    header('Location: ../index.php');
    exit;
};

if ($usernameOrEmail === '' || $password === '') {
    $fail('Username/email and password are required');
}

try {
    $conn = capistra_mysqli();

    $stmt = $conn->prepare('SELECT id, username, email, password, is_active, failed_attempts, locked_until
                            FROM adminusers WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        $fail();
    }

    if ((int) $user['is_active'] !== 1) {
        $fail('This account is not active.');
    }

    if (!empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
        $fail('Too many attempts. Please try again later.');
    }

    if (!password_verify($password, (string) $user['password'])) {
        $attempts = (int) $user['failed_attempts'] + 1;
        $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
        $upd = $conn->prepare('UPDATE adminusers SET failed_attempts = ?, locked_until = ? WHERE id = ?');
        $upd->bind_param('isi', $attempts, $lockUntil, $user['id']);
        $upd->execute();
        $fail();
    }

    // Success
    $reset = $conn->prepare('UPDATE adminusers SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?');
    $reset->bind_param('i', $user['id']);
    $reset->execute();

    session_regenerate_id(true);
    $_SESSION['user_id']       = (int) $user['id'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['email']         = $user['email'];
    $_SESSION['last_activity'] = time();
    $_SESSION['created']       = time();

    header('Location: ../admin/index.php');
    exit;
} catch (Throwable $e) {
    error_log('Login error: ' . $e->getMessage());
    $fail('System error. Please try again later.');
}
