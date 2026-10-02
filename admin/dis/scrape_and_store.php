<?php
// Admin-only legacy market-data scrape. Experimental / optional.
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
set_time_limit(60);
date_default_timezone_set('Asia/Kathmandu');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration is provided by the central Capistra bootstrap.
require_once dirname(__DIR__, 2) . '/config/app.php';

// The page to redirect to after successful operation
$redirectUrl = '../investment/dis_stock.php'; // Change this to your desired destination

function scrapeStockData() {
    $url = "https://merolagani.com/LatestMarket.aspx";
    
    // Initialize cURL with headers
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive'
        ]
    ]);
    
    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception("CURL Error: ".curl_error($ch));
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
        throw new Exception("Received HTTP code: $httpCode");
    }
    
    curl_close($ch);
    
    if (empty($html)) {
        throw new Exception("Empty response from server");
    }
    
    // Save HTML for debugging
    file_put_contents('last_scraped.html', $html);
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    
    // Find the stock table - try multiple selectors
    $table = $xpath->query('//table[@id="ctl00_ContentPlaceHolder1_LiveTrading"]');
    if ($table->length === 0) {
        $table = $xpath->query('//table[contains(@class, "table") and contains(@class, "table-hover")]');
    }
    
    if ($table->length === 0) {
        throw new Exception("Could not find stock table. Check last_scraped.html");
    }
    
    $data = [];
    $rows = $xpath->query('.//tbody/tr', $table->item(0));
    
    foreach ($rows as $row) {
        $cols = $xpath->query('.//td', $row);
        
        // Verify we have enough columns (including volume)
        if ($cols->length >= 8) {
            $volumeStr = trim($cols->item(7)->nodeValue);
            $volume = (int)str_replace(',', '', $volumeStr);
            
            // If volume is 0 but the string wasn't empty, there might be a formatting issue
            if ($volume === 0 && !empty($volumeStr)) {
                // Handle cases where volume might be in thousands (K) or millions (M)
                if (strpos($volumeStr, 'K') !== false) {
                    $volume = (float)str_replace(['K', ','], '', $volumeStr) * 1000;
                } elseif (strpos($volumeStr, 'M') !== false) {
                    $volume = (float)str_replace(['M', ','], '', $volumeStr) * 1000000;
                }
            }
            
            $data[] = [
                'symbol' => trim($cols->item(0)->nodeValue),
                'ltp' => (float)str_replace(',', '', trim($cols->item(1)->nodeValue)),
                'change' => (float)str_replace(',', '', trim($cols->item(2)->nodeValue)),
                'percent_change' => trim($cols->item(3)->nodeValue),
                'volume' => $volume
            ];
        }
    }
    
    if (empty($data)) {
        throw new Exception("No data rows found in table");
    }
    
    return $data;
}

function storeStockData($data) {
    if (empty($data)) {
        throw new Exception("No data provided for storage");
    }
    
    $conn = capistra_mysqli();
    if ($conn->connect_error) {
        throw new Exception("DB Connection failed: " . $conn->connect_error);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Clear old records
        if (!$conn->query("TRUNCATE TABLE stock_prices")) {
            throw new Exception("Failed to clear old records: " . $conn->error);
        }
        
        // Prepare insert statement
        $stmt = $conn->prepare("INSERT INTO stock_prices 
            (symbol, ltp, price_change, percent_change, volume, fetched_at) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $now = date('Y-m-d H:i:s');
        $inserted = 0;
        
        foreach ($data as $stock) {
            $stmt->bind_param("sddsis", 
                $stock['symbol'],
                $stock['ltp'],
                $stock['change'],
                $stock['percent_change'],
                $stock['volume'],
                $now
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $inserted++;
        }
        
        $conn->commit();
        return $inserted;
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    } finally {
        if (isset($stmt)) $stmt->close();
        $conn->close();
    }
}

// Main execution
try {
    // Start output buffering
    ob_start();
    
    echo "=== Starting MeroLagani Scraper ===\n";
    echo "Time: " . date('Y-m-d H:i:s') . "\n";
    
    // Step 1: Scrape data
    echo "Scraping data from MeroLagani...\n";
    $stockData = scrapeStockData();
    echo "Successfully scraped " . count($stockData) . " records\n";
    
    // Step 2: Store data
    echo "Storing data in database...\n";
    $inserted = storeStockData($stockData);
    echo "Successfully inserted $inserted records\n";
    
    // Log success
    file_put_contents('scrape.log', "Data updated at " . date('Y-m-d H:i:s') . " - $inserted records\n", FILE_APPEND);
    echo "=== Scraping completed successfully ===\n";
    
    // Clean output buffer and redirect
    ob_end_clean();
    header("Location: $redirectUrl");
    exit();
    
} catch (Exception $e) {
    // Clean any output before showing error
    ob_end_clean();
    
    $errorMsg = "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
    echo $errorMsg;
    file_put_contents('scrape_error.log', $errorMsg, FILE_APPEND);
    exit(1);
}
?>