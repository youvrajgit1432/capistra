<?php
declare(strict_types=1);

/**
 * Capistra - investment / market-data module configuration.
 *
 * Database credentials come from the canonical bootstrap (.env). Only
 * market-data scraper settings are defined here. Market data is optional:
 * the application works offline using the locally cached `stock_prices` table.
 */

require_once dirname(__DIR__, 2) . '/config/app.php';

// Backwards-compatible alias expected by legacy modules.
if (!defined('DB_PASS')) {
    define('DB_PASS', DB_PASSWORD);
}

// Market data (optional scraper) settings.
if (!defined('MEROLAGANI_URL')) { define('MEROLAGANI_URL', 'https://merolagani.com/LatestMarket.aspx'); }
if (!defined('CACHE_DURATION')) { define('CACHE_DURATION', 300); }
if (!defined('USER_AGENT'))     { define('USER_AGENT', 'Capistra/1.0 (+https://example.test)'); }

// Log to the module log directory, never to the document root.
$logDir = __DIR__ . '/logs';
if (is_dir($logDir) && is_writable($logDir)) {
    ini_set('error_log', $logDir . '/investment.log');
}

/**
 * Shared connection accessor for legacy callers.
 */
function getDBConnection(): mysqli
{
    return capistra_mysqli();
}
