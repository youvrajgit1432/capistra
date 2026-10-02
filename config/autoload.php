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

unset($capistraAutoload);
