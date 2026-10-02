<?php
declare(strict_types=1);

/**
 * Capistra - canonical secure session bootstrap.
 *
 * All entry points must start the session through this function so the session
 * name, cookie flags and lifetime are identical everywhere. (The original
 * application started sessions inconsistently, which broke authentication.)
 */

if (!function_exists('capistra_session_start')) {
    function capistra_session_start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (capistra_env('SESSION_SECURE_COOKIE', 'false') === 'true')
            || (($_SERVER['HTTPS'] ?? '') === 'on');

        session_name('CAPISTRASESSID');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', '1800');

        session_start();
    }
}

if (!function_exists('capistra_session_destroy')) {
    function capistra_session_destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}

if (!function_exists('capistra_csrf_token')) {
    function capistra_csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['csrf_token'];
    }
}

if (!function_exists('capistra_csrf_field')) {
    function capistra_csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(capistra_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('capistra_csrf_verify')) {
    function capistra_csrf_verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals((string) $_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('capistra_enforce_timeout')) {
    /**
     * Enforce a 30-minute inactivity timeout and periodically rotate the id.
     */
    function capistra_enforce_timeout(int $seconds = 1800): bool
    {
        $now = time();
        if (isset($_SESSION['last_activity']) && ($now - (int) $_SESSION['last_activity']) > $seconds) {
            return false;
        }
        $_SESSION['last_activity'] = $now;

        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = $now;
        } elseif ($now - (int) $_SESSION['created'] > $seconds) {
            session_regenerate_id(true);
            $_SESSION['created'] = $now;
        }

        return true;
    }
}
