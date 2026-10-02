<?php
// Admin-only legacy market-data import. Experimental / optional.
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
include 'scrape_merolagani.php'; // Assumes $data[] is populated

// Central Capistra database bootstrap (no hard-coded credentials).
require_once dirname(__DIR__, 2) . '/config/app.php';
$conn = capistra_mysqli();

$now = date('Y-m-d H:i:s');

// Clear old records (optional, for demo purposes)
$conn->query("DELETE FROM stock_prices");

// Insert latest scraped data
$stmt = $conn->prepare("INSERT INTO stock_prices (symbol, ltp, price_change, percent_change, volume, fetched_at) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($data as $stock) {
    $stmt->bind_param(
        "sddsis",
        $stock['symbol'],
        $stock['ltp'],
        $stock['change'],
        $stock['percent_change'],
        $stock['volume'],
        $now
    );
    $stmt->execute();
}
$stmt->close();
$conn->close();

echo "Data fetched and stored successfully at $now.";
?>
