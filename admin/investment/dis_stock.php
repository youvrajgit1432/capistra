<?php
 require_once('../../protect/session_check.php');
require_once('../config/dbcon.php');
require_once('includes/functions.php');
require_once('../head/header.php');
include('../head/topbar.php'); 
// Verify authentication if needed

/**
 * Get stock price from local database (stock_prices table)
 */
function getNepseStockPrice($symbol, $conn) {
    // Query the stock_prices table for the given symbol
    $query = "SELECT ltp, price_change, percent_change, volume 
              FROM stock_prices 
              WHERE symbol = ? 
              ORDER BY fetched_at DESC 
              LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $symbol);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return [
            'price' => (float)$row['ltp'],
            'change' => (float)$row['price_change'],
            'percent_change' => $row['percent_change'],
            'volume' => (int)$row['volume'],
            'source' => 'local_db'
        ];
    }
    
    return false;
}

// Display messages
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-'.$_SESSION['message']['type'].' alert-dismissible fade show">'
        . $_SESSION['message']['text'] . 
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    unset($_SESSION['message']);
}

// Fetch all stock investments
$stocks = [];
$summary = [
    'total_investment' => 0,
    'total_value' => 0,
    'total_gain' => 0,
    'total_gain_percent' => 0
];

try {
    $query = "SELECT s.*, i.investment_date 
              FROM stock_investments s
              JOIN investments i ON s.investment_id = i.id
              ORDER BY s.company_name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }
    
    while ($row = mysqli_fetch_assoc($result)) {
        $priceData = getNepseStockPrice($row['company_symbol'], $conn);
        $investment = $row['base_price'] * $row['total_units'];
        $currentValue = 0;
        $gain = 0;
        $gainPercent = 0;
        
        if ($priceData) {
            $currentValue = $priceData['price'] * $row['total_units'];
            $gain = $currentValue - $investment;
            $gainPercent = ($investment > 0) ? ($gain / $investment) * 100 : 0;
            
            $summary['total_value'] += $currentValue;
            $summary['total_gain'] += $gain;
        }
        
        $summary['total_investment'] += $investment;
        
        $stocks[] = [
            'id' => $row['id'],
            'name' => $row['company_name'],
            'symbol' => $row['company_symbol'],
            'type' => $row['investment_term'] ?? 'long_term',
            'base_price' => $row['base_price'],
            'units' => $row['total_units'],
            'investment' => $investment,
            'current_price' => $priceData ? $priceData['price'] : null,
            'change' => $priceData ? $priceData['change'] : null,
            'percent_change' => $priceData ? $priceData['percent_change'] : null,
            'current_value' => $currentValue,
            'gain' => $gain,
            'gain_percent' => $gainPercent,
            'date' => $row['investment_date'],
            'agreement' => $row['agreement_pdf'],
            'source' => $priceData ? $priceData['source'] : null,
            'stale' => false // No stale data since we're using local DB
        ];
    }
    
    // Calculate overall gain percentage
    if ($summary['total_investment'] > 0) {
        $summary['total_gain_percent'] = ($summary['total_gain'] / $summary['total_investment']) * 100;
    }
    
} catch (Exception $e) {
 
}
?>
<div class="content-wrapper">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
       

        <!-- Main Content -->
        <main class="col-md-12 ms-sm-auto col-lg-13 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Stock Portfolio</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="stock.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Stock
                        </a>
                        <button id="refreshAll" class="btn btn-sm btn-outline-secondary position-relative">
                            <i class="fas fa-sync-alt me-1"></i>Refresh Prices
                            <span id="refreshStatus" class="position-absolute top-0 start-100 translate-middle p-1" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card border-start border-primary border-4 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-2">Total Investment</h6>
                                    <h3 class="mb-0">Rs. <?= number_format($summary['total_investment'], 2) ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 rounded p-3">
                                    <i class="fas fa-wallet fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card border-start border-info border-4 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-2">Current Value</h6>
                                    <h3 class="mb-0">Rs. <?= number_format($summary['total_value'], 2) ?></h3>
                                </div>
                                <div class="bg-info bg-opacity-10 rounded p-3">
                                    <i class="fas fa-chart-bar fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card border-start <?= $summary['total_gain'] >= 0 ? 'border-success' : 'border-danger' ?> border-4 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-2">Gain/Loss</h6>
                                    <h3 class="mb-0 <?= $summary['total_gain'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        Rs. <?= number_format($summary['total_gain'], 2) ?>
                                        <small>(<?= number_format($summary['total_gain_percent'], 2) ?>%)</small>
                                    </h3>
                                </div>
                                <div class="<?= $summary['total_gain'] >= 0 ? 'bg-success' : 'bg-danger' ?> bg-opacity-10 rounded p-3">
                                    <i class="fas fa-arrow-trend-<?= $summary['total_gain'] >= 0 ? 'up' : 'down' ?> fa-2x text-<?= $summary['total_gain'] >= 0 ? 'success' : 'danger' ?>"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stocks Table -->
            <div class="card mb-4">
                <div class="card-body">
                    <?php if (!empty($stocks)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="stockTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Company</th>
                                    <th>Symbol</th>
                                    <th>Units</th>
                                    <th class="text-end">Base Price</th>
                                    <th class="text-end">Current Price</th>
                                    <th class="text-end">Invested</th>
                                    <th class="text-end">Current Value</th>
                                    <th class="text-end">Gain/Loss</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stocks as $index => $stock): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol-circle bg-primary bg-opacity-10 text-primary me-3">
                                                <?= strtoupper(substr($stock['name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($stock['name']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $stock['type']))) ?></small>
                                                <?php if ($stock['source']): ?>
                                                    <small class="d-block text-info"><i class="fas fa-info-circle"></i> <?= $stock['source'] ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($stock['symbol']) ?></td>
                                    <td><?= number_format($stock['units'], 2) ?></td>
                                    <td class="text-end">Rs. <?= number_format($stock['base_price'], 2) ?></td>
                                    <td class="text-end">
                                        <?php if ($stock['current_price'] !== null): ?>
                                            Rs. <?= number_format($stock['current_price'], 2) ?>
                                            <?php if ($stock['percent_change'] !== null): ?>
                                                <br>
                                                <small class="<?= $stock['change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= $stock['percent_change'] ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Listed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">Rs. <?= number_format($stock['investment'], 2) ?></td>
                                    <td class="text-end">
                                        <?= $stock['current_value'] ? 'Rs. ' . number_format($stock['current_value'], 2) : 'Not-Listed' ?>
                                    </td>
                                    <td class="text-end <?= $stock['gain'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?php if ($stock['current_price'] !== null): ?>
                                            Rs. <?= number_format($stock['gain'], 2) ?>
                                            <br>
                                            <small><?= number_format($stock['gain_percent'], 2) ?>%</small>
                                        <?php else: ?>
                                            Not-Listed
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_stock.php?id=<?= $stock['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_stock.php?id=<?= $stock['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this stock investment?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                            <?php if ($stock['agreement']): ?>
                                                <a href="<?= htmlspecialchars($stock['agreement']) ?>" class="btn btn-outline-secondary" title="View Agreement" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                            <h4>No Stock Investments Found</h4>
                            <p class="text-muted">You haven't added any Nepse stock investments yet</p>
                            <a href="stock.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Your First Stock
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
</div>
<script>
$(document).ready(function() {
    // Make table responsive
    $('#stockTable').DataTable({
        responsive: true,
        autoWidth: false,
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, // Company name
            { responsivePriority: 2, targets: 5 }, // Current price
            { responsivePriority: 3, targets: 9 }, // Actions
            { responsivePriority: 4, targets: 8 }, // Gain/Loss
            { responsivePriority: 5, targets: 7 }, // Current value
            { responsivePriority: 6, targets: 0 }  // #
        ]
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const refreshButton = document.getElementById('refreshAll');
    const refreshStatus = document.getElementById('refreshStatus');
    let isRefreshing = false;

    // Handle manual refresh
    refreshButton.addEventListener('click', function(e) {
        e.preventDefault();
        if (!isRefreshing) {
            refreshData();
        }
    });

    // Auto-refresh function
    function refreshData() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        refreshStatus.style.display = 'block';
        refreshButton.disabled = true;
        
        fetch('../dis/scrape_and_store.php')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(data => {
                // Show check mark briefly before reload
                refreshStatus.innerHTML = '<i class="fas fa-check text-success"></i>';
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            })
            .catch(error => {
                refreshStatus.innerHTML = '<i class="fas fa-times text-danger"></i>';
                console.error('Refresh error:', error);
                setTimeout(() => {
                    refreshStatus.style.display = 'none';
                    isRefreshing = false;
                    refreshButton.disabled = false;
                }, 2000);
            });
    }

    // Auto-refresh every 5 minutes (300,000 ms)
    setInterval(refreshData, 300000);
    
    // Initial check if data needs refresh
    checkDataFreshness();
    
    function checkDataFreshness() {
        fetch('check_data_freshness.php')
            .then(response => response.json())
            .then(data => {
                if (data.seconds_since_update > 300) { // 5 minutes
                    refreshData();
                }
            })
            .catch(error => console.error('Freshness check error:', error));
    }
});
</script>

<style>
    /* Custom styles for the refresh indicator */
#refreshStatus {
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}

#refreshAll:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner-border {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}
.symbol-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* DataTables responsive adjustments */
div.dataTables_wrapper div.dataTables_filter input {
    width: 150px !important;
}
</style>

<?php
include('../head/footer.php');
?>