<?php 
 require_once('../../protect/session_check.php');
include('../config/dbcon.php');
include('includes/functions.php');
include('../head/header.php'); 
include('../head/topbar.php'); 

// Fetch investment summary data
$summary = [];
$types = ['stock', 'business', 'loan', 'real_estate'];

foreach ($types as $type) {
    $query = "SELECT SUM(invested_amount) as total FROM investments WHERE investment_type = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $summary[$type] = $row['total'] ? $row['total'] : 0;
}

// Calculate overall total
$overall_total = array_sum($summary);
?>
<div class="content-wrapper">
<div class="container py-5">
    
<h2 class="mb-4">Investment Portfolio</h2>
<div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add Investment Details </h4>
                </div>
    <!-- Investment Options -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <a href="stock.php" class="card investment-card h-100 text-decoration-none">
                <div class="card-body text-center py-4">
                    <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                    <h4>Add Stock Investment</h4>
                    <p class="text-muted">Invest in company shares and securities</p>
                    <div class="badge bg-primary text-white"><?= number_format($summary['stock'], 2) ?> invested</div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="business.php" class="card investment-card h-100 text-decoration-none">
                <div class="card-body text-center py-4">
                    <i class="fas fa-business-time fa-3x mb-3 text-success"></i>
                    <h4>Add Business Investment</h4>
                    <p class="text-muted">Invest in businesses and startups</p>
                    <div class="badge bg-success text-white"><?= number_format($summary['business'], 2) ?> invested</div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="loan.php" class="card investment-card h-100 text-decoration-none">
                <div class="card-body text-center py-4">
                    <i class="fas fa-hand-holding-usd fa-3x mb-3 text-warning"></i>
                    <h4>Add Loan Investment</h4>
                    <p class="text-muted">Provide loans with interest</p>
                    <div class="badge bg-warning text-dark"><?= number_format($summary['loan'], 2) ?> invested</div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="real_estate.php" class="card investment-card h-100 text-decoration-none">
                <div class="card-body text-center py-4">
                    <i class="fas fa-home fa-3x mb-3 text-info"></i>
                    <h4>Add Real Estate</h4>
                    <p class="text-muted">Invest in properties and land</p>
                    <div class="badge bg-info text-white"><?= number_format($summary['real_estate'], 2) ?> invested</div>
                </div>
            </a>
        </div>
    </div>


 
    
    <!-- Summary Cards -->
    <div class="row mb-5">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h4 class="mb-0 text-dark">Investment Summary</h4>
                <p class="text-muted mb-0">Click on any sector to view detailed investments</p>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <!-- Stock Card -->
                    <div class="col-md-3 p-3 text-center border-end hover-highlight">
                        <a href="dis_stock.php" class="text-decoration-none d-block">
                            <div class="icon-circle bg-primary-light mb-3 mx-auto">
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                            <h5 class="text-dark mb-1">Stocks</h5>
                            <div class="display-5 text-primary fw-bold mb-1"><?= number_format($summary['stock'], 2) ?></div>
                            <div class="progress mx-auto mb-2" style="height: 6px; width: 80%">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?= $overall_total > 0 ? ($summary['stock']/$overall_total)*100 : 0 ?>%" 
                                     aria-valuenow="<?= $overall_total > 0 ? ($summary['stock']/$overall_total)*100 : 0 ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted"><?= $overall_total > 0 ? round(($summary['stock']/$overall_total)*100, 2) : 0 ?>% of portfolio</small>
                        </a>
                    </div>
                    
                    <!-- Business Card -->
                    <div class="col-md-3 p-3 text-center border-end hover-highlight">
                        <a href="dis_business.php" class="text-decoration-none d-block">
                            <div class="icon-circle bg-success-light mb-3 mx-auto">
                                <i class="fas fa-business-time fa-2x text-success"></i>
                            </div>
                            <h5 class="text-dark mb-1">Business</h5>
                            <div class="display-5 text-success fw-bold mb-1"><?= number_format($summary['business'], 2) ?></div>
                            <div class="progress mx-auto mb-2" style="height: 6px; width: 80%">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $overall_total > 0 ? ($summary['business']/$overall_total)*100 : 0 ?>%" 
                                     aria-valuenow="<?= $overall_total > 0 ? ($summary['business']/$overall_total)*100 : 0 ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted"><?= $overall_total > 0 ? round(($summary['business']/$overall_total)*100, 2) : 0 ?>% of portfolio</small>
                        </a>
                    </div>
                    
                    <!-- Loan Card -->
                    <div class="col-md-3 p-3 text-center border-end hover-highlight">
                        <a href="dis_loan.php" class="text-decoration-none d-block">
                            <div class="icon-circle bg-warning-light mb-3 mx-auto">
                                <i class="fas fa-hand-holding-usd fa-2x text-warning"></i>
                            </div>
                            <h5 class="text-dark mb-1">Loans</h5>
                            <div class="display-5 text-warning fw-bold mb-1"><?= number_format($summary['loan'], 2) ?></div>
                            <div class="progress mx-auto mb-2" style="height: 6px; width: 80%">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: <?= $overall_total > 0 ? ($summary['loan']/$overall_total)*100 : 0 ?>%" 
                                     aria-valuenow="<?= $overall_total > 0 ? ($summary['loan']/$overall_total)*100 : 0 ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted"><?= $overall_total > 0 ? round(($summary['loan']/$overall_total)*100, 2) : 0 ?>% of portfolio</small>
                        </a>
                    </div>
                    
                    <!-- Real Estate Card -->
                    <div class="col-md-3 p-3 text-center hover-highlight">
                        <a href="dis_real_estate.php" class="text-decoration-none d-block">
                            <div class="icon-circle bg-info-light mb-3 mx-auto">
                                <i class="fas fa-home fa-2x text-info"></i>
                            </div>
                            <h5 class="text-dark mb-1">Real Estate</h5>
                            <div class="display-5 text-info fw-bold mb-1"><?= number_format($summary['real_estate'], 2) ?></div>
                            <div class="progress mx-auto mb-2" style="height: 6px; width: 80%">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: <?= $overall_total > 0 ? ($summary['real_estate']/$overall_total)*100 : 0 ?>%" 
                                     aria-valuenow="<?= $overall_total > 0 ? ($summary['real_estate']/$overall_total)*100 : 0 ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted"><?= $overall_total > 0 ? round(($summary['real_estate']/$overall_total)*100, 2) : 0 ?>% of portfolio</small>
                        </a>
                    </div>
                </div>
                
                <!-- Total Investment -->
                <div class="row border-top">
                    <div class="col-md-12 py-3 text-center bg-light">
                        <h4 class="mb-0 text-dark">Total Investment: <span class="text-primary"><?= number_format($overall_total, 2) ?></span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<style>
    .hover-highlight:hover {
        background-color: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-primary-light {
        background-color: rgba(13, 110, 253, 0.1);
    }
    .bg-success-light {
        background-color: rgba(25, 135, 84, 0.1);
    }
    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
    }
    .bg-info-light {
        background-color: rgba(13, 202, 240, 0.1);
    }
</style>
</div>

<style>
.investment-card {
    transition: transform 0.3s, box-shadow 0.3s;
    border: none;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.investment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
</style>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Common validation
        $investment_type = sanitizeInput($_POST['investment_type']);
        $invested_amount = filter_var($_POST['invested_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $investment_date = sanitizeInput($_POST['investment_date']);
        $remarks = isset($_POST['remarks']) ? sanitizeInput($_POST['remarks']) : null;

        // Validate required fields
        if(empty($investment_type) || empty($invested_amount) || empty($investment_date)) {
            throw new Exception("All required fields must be filled");
        }

        // Start transaction
        mysqli_begin_transaction($conn);

        // Insert into investments table
        $query = "INSERT INTO investments (investment_type, invested_amount, investment_date, remarks) 
                  VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sdss', $investment_type, $invested_amount, $investment_date, $remarks);
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to save investment: " . mysqli_error($conn));
        }
        
        $investment_id = mysqli_insert_id($conn);

        // Handle specific investment types
        switch ($investment_type) {
            case 'stock':
                require_once('includes/process/process_stock.php');
                break;
                
            case 'business':
                require_once('includes/process/process_business.php');
                break;
                
            case 'loan':
                require_once('includes/process/process_loan.php');
                break;
                
            case 'real_estate':
                require_once('includes/process/process_real_estate.php');
                break;
                
            default:
                throw new Exception("Invalid investment type");
        }

        // Commit transaction
        mysqli_commit($conn);
        
        // Success - redirect to listing page
        $_SESSION['success_message'] = ucfirst(str_replace('_', ' ', $investment_type)) . " investment added successfully!";
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

include('../head/footer.php'); 
?>