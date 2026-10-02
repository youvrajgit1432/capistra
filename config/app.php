<?php
declare(strict_types=1);

/**
 * Capistra - canonical application bootstrap.
 *
 * Every entry point should `require_once` this file. It provides:
 *   - the product brand (single place to change the public name)
 *   - configuration loaded from `.env` (falling back to safe defaults)
 *   - PDO and mysqli connection factories
 *
 * No real credentials are stored in the repository; configuration comes from
 * the git-ignored `.env` file (see `.env.example`).
 */

require_once __DIR__ . '/env.php';

// Load environment from the project root (application root = one level up).
capistra_load_env(dirname(__DIR__) . '/.env');

if (!defined('CAPISTRA_BOOTSTRAPPED')) {
    define('CAPISTRA_BOOTSTRAPPED', true);

    // ---- Public product brand -------------------------------------------------
    // Change the public name in ONE place. Internal identifiers (table names,
    // historic comments) are intentionally decoupled from this value.
    define('APP_NAME', (string) (capistra_env('APP_NAME', 'Capistra')));
    define('APP_SLUG', 'capistra');
    define('APP_TAGLINE', 'Self-hosted Accounting, Cash-Flow & Investment Management Platform');
    define('APP_VERSION', '1.0.0');

    // ---- Runtime --------------------------------------------------------------
    define('APP_ENV', (string) (capistra_env('APP_ENV', 'local')));
    define('APP_DEBUG', capistra_env('APP_DEBUG', 'false') === 'true');
    define('APP_URL', (string) (capistra_env('APP_URL', 'http://localhost/gmic')));
    define('APP_BASE_CURRENCY', (string) (capistra_env('APP_BASE_CURRENCY', 'NPR')));
    define('APP_KEY', (string) (capistra_env('APP_KEY', '')));

    date_default_timezone_set((string) (capistra_env('APP_TIMEZONE', 'Asia/Kathmandu')));

    // ---- Database -------------------------------------------------------------
    define('DB_HOST', (string) (capistra_env('DB_HOST', '127.0.0.1')));
    define('DB_PORT', (string) (capistra_env('DB_PORT', '3306')));
    define('DB_NAME', (string) (capistra_env('DB_NAME', APP_SLUG)));
    define('DB_USER', (string) (capistra_env('DB_USER', 'root')));
    define('DB_PASSWORD', (string) (capistra_env('DB_PASSWORD', '')));
    define('DB_CHARSET', (string) (capistra_env('DB_CHARSET', 'utf8mb4')));

    // Never leak PHP errors to the browser outside of local debugging.
    if (APP_DEBUG) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
    }
}

if (!function_exists('capistra_pdo')) {
    /**
     * Shared PDO connection (preferred interface).
     */
    function capistra_pdo(): PDO
    {
        static $pdo = null;
        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return $pdo;
    }
}

if (!function_exists('capistra_mysqli')) {
    /**
     * Shared mysqli connection for legacy modules that were written against it.
     */
    function capistra_mysqli(): mysqli
    {
        static $mysqli = null;
        if ($mysqli instanceof mysqli) {
            return $mysqli;
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, (int) DB_PORT);
        $mysqli->set_charset(DB_CHARSET);

        return $mysqli;
    }
}

if (!function_exists('capistra_currency')) {
    /**
     * Format an amount using the configured base currency.
     * Locale-aware formatting is intentionally simple and predictable.
     */
    function capistra_currency(float|int|string $amount, string $code = APP_BASE_CURRENCY): string
    {
        $value = number_format((float) $amount, 2);

        return match (strtoupper($code)) {
            'NPR'   => 'Rs. ' . $value,
            'USD'   => '$' . $value,
            'EUR'   => '€' . $value,
            'GBP'   => '£' . $value,
            default => $value . ' ' . strtoupper($code),
        };
    }
}
