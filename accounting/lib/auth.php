<?php
declare(strict_types=1);

/**
 * Capistra - accounting module bootstrap: authentication, CSRF and audit.
 */

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/session.php';
require_once __DIR__ . '/ledger.php';

if (!function_exists('capistra_current_user')) {
    /**
     * @return array{id:int,username:string,full_name:string}|null
     */
    function capistra_current_user(): ?array
    {
        if (empty($_SESSION['username']) || empty($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id'        => (int) $_SESSION['user_id'],
            'username'  => (string) $_SESSION['username'],
            'full_name' => (string) ($_SESSION['full_name'] ?? $_SESSION['username']),
        ];
    }
}

if (!function_exists('capistra_require_login')) {
    /**
     * Gate the current request behind authentication.
     */
    function capistra_require_login(): array
    {
        capistra_session_start();

        if (!capistra_enforce_timeout()) {
            capistra_session_destroy();
            header('Location: ../index.php?timeout=1');
            exit;
        }

        $user = capistra_current_user();
        if ($user === null) {
            header('Location: ../index.php?error=session_expired');
            exit;
        }
        return $user;
    }
}

if (!function_exists('capistra_audit')) {
    /**
     * Append an audit-trail record. Never let audit failure break a request.
     */
    function capistra_audit(PDO $pdo, string $action, string $entityType, ?int $entityId = null, ?string $details = null): void
    {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address)
                 VALUES (:uid, :action, :etype, :eid, :details, :ip)'
            );
            $stmt->execute([
                ':uid'     => $_SESSION['user_id'] ?? null,
                ':action'  => $action,
                ':etype'   => $entityType,
                ':eid'     => $entityId,
                ':details' => $details,
                ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $e) {
            error_log('audit failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('e')) {
    /** Escape output for HTML context. */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
