<?php
// display_stocks.php
// Admin-only legacy market-data viewer. Experimental / optional.
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
// Central Capistra database bootstrap (no hard-coded credentials).
require_once dirname(__DIR__, 2) . '/config/app.php';
$conn = capistra_mysqli();

// Check if table exists and has data
$tableCheck = $conn->query("SELECT 1 FROM stock_prices LIMIT 1");
if ($tableCheck === FALSE) {
    die("Error: The stock_prices table doesn't exist. Run create_table.php first.");
}

$result = $conn->query("SELECT * FROM stock_prices ORDER BY symbol ASC");
$rowCount = $result->num_rows;

// Get last update time
$lastUpdate = $conn->query("SELECT MAX(fetched_at) as last_update FROM stock_prices")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nepal Stock Prices</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: right; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; text-align: center; }
        .positive { color: green; }
        .negative { color: red; }
        .info { margin: 20px 0; padding: 10px; background: #f8f9fa; }
    </style>
</head>
<body>
    <h1>Nepal Stock Prices</h1>
    
    <div class="info">
        <?php if ($rowCount > 0): ?>
            Last updated: <?= $lastUpdate['last_update'] ?> | 
            Total stocks: <?= $rowCount ?>
        <?php else: ?>
            <strong>No data available.</strong> The scraper may not have run yet.
        <?php endif; ?>
    </div>

    <?php if ($rowCount > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Symbol</th>
                <th>LTP</th>
                <th>Change</th>
                <th>% Change</th>
                <th>Volume</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['symbol']) ?></td>
                <td><?= number_format($row['ltp'], 2) ?></td>
                <td class="<?= $row['price_change'] >= 0 ? 'positive' : 'negative' ?>">
                    <?= number_format($row['price_change'], 2) ?>
                </td>
                <td class="<?= strpos($row['percent_change'], '+') === 0 ? 'positive' : 'negative' ?>">
                    <?= htmlspecialchars($row['percent_change']) ?>
                </td>
                <td><?= number_format($row['volume']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="info">
        Data source: <a href="https://merolagani.com/LatestMarket.aspx" target="_blank">Mero Lagani</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>