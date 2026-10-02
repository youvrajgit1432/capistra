<?php
declare(strict_types=1);

/**
 * Capistra - admin shared connection (canonical).
 *
 * Kept for backwards compatibility with legacy pages that expect a `$conn`
 * mysqli handle. Credentials and the database name come from `.env` via the
 * single canonical bootstrap, so there is exactly one place to configure.
 */

require_once dirname(__DIR__, 2) . '/config/app.php';

$conn = capistra_mysqli();
