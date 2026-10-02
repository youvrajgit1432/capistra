<?php
declare(strict_types=1);

/**
 * Capistra - safe Composer autoloader.
 *
 * Composer packages (FPDF, PHPMailer, Google client) are OPTIONAL: they only
 * power PDF reports and email. The application must keep working when the
 * `vendor/` directory is absent or incomplete, so this shim loads the
 * autoloader only when it actually exists.
 *
 * Require this file instead of `vendor/autoload.php` directly.
 */

$capistraAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($capistraAutoload)) {
    try {
        // Suppress the stream warning an incomplete vendor tree would emit;
        // the thrown Error is handled below.
        @require_once $capistraAutoload;
    } catch (\Throwable $e) {
        // A missing or incomplete vendor tree must never break the app.
        error_log('Composer autoloader unavailable: ' . $e->getMessage());
    }
}

if (!function_exists('capistra_optional_class_missing')) {
    /**
     * Fail loudly but safely when an optional Composer package is absent.
     *
     * Composer packages (FPDF, PHPMailer) are optional: the rest of Capistra runs
     * without `vendor/`. Calling this instead of letting PHP throw
     * "Class not found" keeps such requests from turning into HTTP 500s.
     */
    function capistra_optional_class_missing(string $class, string $package): void
    {
        error_log(sprintf('Optional dependency missing: %s (required by %s).', $package, $class));

        if (!headers_sent()) {
            http_response_code(501);
            header('Content-Type: text/html; charset=UTF-8');
        }

        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<title>Feature unavailable</title></head><body>'
            . '<h1>Feature unavailable</h1>'
            . '<p>This feature needs the optional Composer package <code>'
            . htmlspecialchars($package, ENT_QUOTES, 'UTF-8') . '</code>, which is not installed.</p>'
            . '<p>Run <code>composer install</code> in the project root and try again.</p>'
            . '</body></html>';
        exit;
    }
}

unset($capistraAutoload);
