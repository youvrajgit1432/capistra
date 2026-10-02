<?php
include 'scrape_merolagani.php'; // Assumes $data[] is populated

$host = "localhost";
$user = "root";
$pass = "";
$db = "capistra";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

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
