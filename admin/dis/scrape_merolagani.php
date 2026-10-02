<?php
// Admin-only legacy market-data scrape helper. Experimental / optional.
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
// Set execution time limit and timezone
set_time_limit(0);
date_default_timezone_set('Asia/Kathmandu');

// URL to scrape
$url = "https://merolagani.com/LatestMarket.aspx";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
curl_close($ch);

// Load HTML into DOM parser
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();

// Get table rows
$xpath = new DOMXPath($dom);
$rows = $xpath->query('//table[@id="ctl00_ContentPlaceHolder1_LiveTrading"]/tr');

// Store extracted data
$data = [];

foreach ($rows as $i => $row) {
    if ($i === 0) continue; // skip header row
    $cols = $row->getElementsByTagName('td');

    if ($cols->length > 0) {
        $symbol = trim($cols->item(0)->nodeValue);
        $ltp = trim($cols->item(1)->nodeValue);
        $change = trim($cols->item(2)->nodeValue);
        $percent_change = trim($cols->item(3)->nodeValue);
        $volume = trim($cols->item(7)->nodeValue);

        $data[] = [
            'symbol' => $symbol,
            'ltp' => $ltp,
            'change' => $change,
            'percent_change' => $percent_change,
            'volume' => $volume
        ];
    }
}
?>
