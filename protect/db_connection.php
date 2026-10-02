<?php
declare(strict_types=1);

/**
 * Capistra - authentication area shared connection (canonical).
 */

require_once dirname(__DIR__) . '/config/app.php';

$conn = capistra_mysqli();
