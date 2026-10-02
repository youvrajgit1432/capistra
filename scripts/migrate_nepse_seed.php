<?php
declare(strict_types=1);

/**
 * Capistra - migrate the legacy hard-coded NEPSE company list
 * (`admin/investment/assets/js/stock.js`) into the `securities` master.
 *
 * The legacy list is UNTRUSTED. This script:
 *   * parses rows without executing the JavaScript,
 *   * reports duplicate symbols / suspicious entries,
 *   * keeps the first row per symbol and skips the rest (never overwrites),
 *   * records the original sector text in notes, and
 *   * imports every row as `unverified` (no fabricated listing status).
 *
 * Usage:
 *   php scripts/migrate_nepse_seed.php            # dry run (report only)
 *   php scripts/migrate_nepse_seed.php --commit   # write to the database
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/lib/nepse.php';

$commit = in_array('--commit', $argv ?? [], true);
$seedFile = dirname(__DIR__) . '/admin/investment/assets/js/stock.js';

if (!is_file($seedFile)) {
    fwrite(STDERR, "Seed file not found: $seedFile\n");
    exit(1);
}

$content = (string) file_get_contents($seedFile);
$pattern = '/\{\s*name:\s*"((?:[^"\\\\]|\\\\.)*)",\s*symbol:\s*"((?:[^"\\\\]|\\\\.)*)",\s*sector:\s*"((?:[^"\\\\]|\\\\.)*)"\s*\}/';
preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

$rows = [];
foreach ($matches as $m) {
    $rows[] = ['name' => stripcslashes($m[1]), 'symbol' => strtoupper(stripcslashes($m[2])), 'sector' => stripcslashes($m[3])];
}

// Legacy sector slug => canonical DB sector slug.
$sectorMap = [
    'commercial_bank'        => 'commercial-bank',
    'development_bank'       => 'development-bank',
    'finance'                => 'finance',
    'microfinance'           => 'microfinance',
    'insurance'              => 'life-insurance',
    'non_life_insurance'     => 'non-life-insurance',
    'micro_insurance_life'   => 'micro-life-insurance',
    'micro_insurance_nonlife'=> 'micro-non-life-insurance',
    'hydro'                  => 'hydropower',
    'manufacturing'          => 'manufacturing',
    'hotel'                  => 'hotel-tourism',
    'trading'                => 'trading',
    'telecom'                => 'telecom',
    'others'                 => 'others',
];

echo "Parsed " . count($rows) . " legacy rows.\n";

$seen = [];
$imported = [];
$skipped = [];
foreach ($rows as $row) {
    if (isset($seen[$row['symbol']])) {
        $skipped[] = sprintf('%s (%s) — duplicate symbol, first row kept', $row['symbol'], $row['name']);
        continue;
    }
    $seen[$row['symbol']] = true;

    // Unit schemes / non-equity symbols.
    $type = preg_match('/^(UT\d|CMF1)/i', $row['symbol']) ? 'other' : 'equity';

    $imported[] = $row + [
        'sector_slug' => $sectorMap[$row['sector']] ?? 'others',
        'security_type' => $type,
    ];
}

echo "Unique symbols: " . count($imported) . "\n";
echo "Duplicate rows skipped: " . count($skipped) . "\n";
foreach ($skipped as $s) {
    echo "  ! $s\n";
}

if (!$commit) {
    echo "\nDRY RUN — nothing written. Re-run with --commit to import.\n";
    exit(0);
}

$pdo = capistra_pdo();
$exchangeId = capistra_default_exchange_id();

// Ensure canonical sectors exist.
$sectorIds = [];
foreach (capistra_sectors($exchangeId) as $s) {
    $sectorIds[(string) $s['slug']] = (int) $s['id'];
}
$needed = array_unique(array_column($imported, 'sector_slug'));
foreach ($needed as $slug) {
    if (!isset($sectorIds[$slug])) {
        $name = ucwords(str_replace('-', ' ', $slug));
        $pdo->prepare('INSERT INTO market_sectors (exchange_id, name, slug, is_active, sort_order) VALUES (?, ?, ?, 1, 500)')
            ->execute([$exchangeId, $name, $slug]);
        $sectorIds[$slug] = (int) $pdo->lastInsertId();
    }
}

$written = 0;
$existing = 0;
foreach ($imported as $row) {
    if (capistra_security_by_symbol($row['symbol'], $exchangeId)) {
        $existing++;
        continue;
    }
    capistra_security_save([
        'exchange_id'   => $exchangeId,
        'sector_id'     => $sectorIds[$row['sector_slug']] ?? 0,
        'symbol'        => $row['symbol'],
        'company_name'  => $row['name'],
        'security_type' => $row['security_type'],
        'listing_status'=> 'unverified',
        'notes'         => 'Imported from legacy stock.js; legacy sector: ' . $row['sector'],
        'is_active'     => 1,
    ]);
    $written++;
}

echo "Imported $written securities ($existing already existed). All marked 'unverified'.\n";
