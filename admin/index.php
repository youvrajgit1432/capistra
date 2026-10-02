<?php
 require_once('../protect/session_check.php');

// Handle PDF Export
if (isset($_GET['export_pdf'])) {
    // Include Composer autoloader
    require_once __DIR__ . '/../config/autoload.php';

    // PDF export needs the optional composer package; fail safely without it.
    if (!class_exists('FPDF')) {
        capistra_optional_class_missing('FPDF', 'setasign/fpdf');
    }

    // Include DB connection
    include('config/dbcon.php');

    // Fetch the current year
    $currentYear = date('Y');

    // Fetch data for the PDF report
    $totalIncomeThisYear = getTotalIncomeForYear($conn, $currentYear);
    $totalExpenseThisYear = getTotalExpenseForYear($conn, $currentYear);
    $netProfitThisYear = $totalIncomeThisYear - $totalExpenseThisYear;

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Add Background Gradient (Optional)
    $pdf->SetFillColor(240, 244, 247); // Light gray background
    $pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight(), 'F');

    // Add Logo (Left Side)
    $pdf->Image('assets/dist/img/middlelogo.png', 10, 10, 30); // Replace with your logo path

    // Add Company Name and PAN (Center)
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(0, 128, 128); // Teal color for the company name
    $pdf->Cell(0, 10, 'Capistra', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 14);
    $pdf->SetTextColor(108, 117, 125); // Gray color for the address
    $pdf->Cell(0, 10, 'Company address (configure in Settings)', 0, 1, 'C');
    $pdf->SetFont('Arial', 'I', 14);
    $pdf->SetTextColor(255, 99, 71); // Coral color for the tax id
    $pdf->Cell(0, 10, 'Tax ID: (configure in Settings)', 0, 1, 'C');
    $pdf->Ln(15); // Add vertical space

    // Add Divider Line
    $pdf->SetDrawColor(200, 200, 200); // Light gray color for the line
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Draw a horizontal line
    $pdf->Ln(15); // Add vertical space

    // Add Title with Teal Background
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetFillColor(0, 128, 128); // Teal background for the title
    $pdf->SetTextColor(255, 255, 255); // White text color
    $pdf->Cell(0, 15, "Annual Financial Report for $currentYear", 0, 1, 'C', true);
    $pdf->Ln(15); // Add vertical space

    // Add Financial Data in a Modern Table
    $tableWidth = 150; // Width of the table
    $tableX = ($pdf->GetPageWidth() - $tableWidth) / 2; // Center the table horizontally
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(0, 128, 128); // Teal background for headers
    $pdf->SetTextColor(255, 255, 255); // White text color
    $pdf->SetXY($tableX, $pdf->GetY()); // Set table position
    $pdf->Cell(100, 10, 'Category', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Amount', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetFillColor(255, 255, 255); // White background for data rows
    $pdf->SetTextColor(0, 0, 0); // Black text color
    $pdf->SetXY($tableX, $pdf->GetY()); // Set table position
    $pdf->Cell(100, 10, 'Total Income', 1, 0, 'L', true);
    $pdf->Cell(50, 10, formatNepaliRupees($totalIncomeThisYear), 1, 1, 'R', true);

    $pdf->SetXY($tableX, $pdf->GetY()); // Set table position
    $pdf->Cell(100, 10, 'Total Expense', 1, 0, 'L', true);
    $pdf->Cell(50, 10, formatNepaliRupees($totalExpenseThisYear), 1, 1, 'R', true);

    $pdf->SetXY($tableX, $pdf->GetY()); // Set table position
    $pdf->Cell(100, 10, 'Net Profit', 1, 0, 'L', true);
    $pdf->Cell(50, 10, formatNepaliRupees($netProfitThisYear), 1, 1, 'R', true);
    $pdf->Ln(20); // Add vertical space

    // Add Summary Section with Coral Background
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(255, 99, 71); // Coral background for the summary
    $pdf->SetTextColor(255, 255, 255); // White text color
    $pdf->Cell(0, 10, 'Summary', 0, 1, 'L', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Black text color
    $pdf->SetFillColor(255, 255, 255); // White background for the summary text
    $pdf->MultiCell(0, 10, "The financial report for the year $currentYear highlights the company's performance. The total income for the year was " . formatNepaliRupees($totalIncomeThisYear) . ", while the total expenses amounted to " . formatNepaliRupees($totalExpenseThisYear) . ". This resulted in a net profit of " . formatNepaliRupees($netProfitThisYear) . ".", 0, 'L', true);
    $pdf->Ln(10); // Add vertical space

    // Add Divider Line
    $pdf->SetDrawColor(200, 200, 200); // Light gray color for the line
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Draw a horizontal line
    $pdf->Ln(10); // Add vertical space

    // Add Footer with Teal Background
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetFillColor(0, 128, 128); // Teal background for the footer
    $pdf->SetTextColor(255, 255, 255); // White text color
    $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C', true);
    $pdf->Cell(0, 10, 'Capistra', 0, 1, 'C', true);

    // Output PDF with dynamic file name
    $pdf->Output('D', 'financial_report_' . $currentYear . '.pdf'); // Download the PDF
    exit; // Stop further execution
}

// Include necessary files
include('includes/header.php');
include('includes/topbar.php');
include('includes/sidebar.php');
include('config/dbcon.php'); // Include DB connection

// Function to format amounts in Nepali Rupees format (with commas)
function formatNepaliRupees($amount) {
    return "Rs " . number_format($amount);
}

// Function to calculate total expense for a specific month and year
function getTotalExpense($conn, $month, $year) {
    $query = "SELECT SUM(amount) AS total_expense FROM expenses WHERE MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_expense'] ?: 0;
}

// Function to calculate total expense for a specific year
function getTotalExpenseForYear($conn, $year) {
    $query = "SELECT SUM(amount) AS total_expense FROM expenses WHERE YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_expense'] ?: 0;
}

// Function to calculate total income for a specific month and year
function getTotalIncome($conn, $month, $year) {
    $query = "SELECT SUM(amount) AS total_income FROM income WHERE MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_income'] ?: 0;
}

// Function to calculate total income for a specific year
function getTotalIncomeForYear($conn, $year) {
    $query = "SELECT SUM(amount) AS total_income FROM income WHERE YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_income'] ?: 0;
}

// Function to get stock investment summary
function getStockInvestmentSummary($conn) {
    $summary = [
        'total_investment' => 0,
        'total_value' => 0,
        'total_gain' => 0,
        'total_gain_percent' => 0,
        'count' => 0
    ];

    $query = "SELECT s.id, s.company_name, s.company_symbol, s.base_price, s.total_units, 
                     i.invested_amount, i.investment_date
              FROM stock_investments s
              JOIN investments i ON s.investment_id = i.id";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $investment = $row['base_price'] * $row['total_units'];
            $summary['total_investment'] += $investment;
            $summary['count']++;
        }
    }
    
    return $summary;
}

// Fetch the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Calculate financial totals
$totalExpenseThisMonth = getTotalExpense($conn, $currentMonth, $currentYear);
$totalExpenseThisYear = getTotalExpenseForYear($conn, $currentYear);
$totalIncomeThisMonth = getTotalIncome($conn, $currentMonth, $currentYear);
$totalIncomeThisYear = getTotalIncomeForYear($conn, $currentYear);

// Calculate net profit and gross profit
$netProfitThisMonth = $totalIncomeThisMonth - $totalExpenseThisMonth;
$netProfitThisYear = $totalIncomeThisYear - $totalExpenseThisYear;
$grossProfitThisMonth = $totalIncomeThisMonth;
$grossProfitThisYear = $totalIncomeThisYear;

// Get stock investment summary
$stockSummary = getStockInvestmentSummary($conn);
?>
 <?php
require_once('../protect/session_check.php');

// [Keep all your existing backend PHP code exactly as is]

// Only modifying the frontend HTML/CSS/JS part
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financial Dashboard | Capistra</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --dark: #212529;
            --light: #f8f9fa;
            --teal: #20c997;
            --purple: #7209b7;
            --cyan: #00b4d8;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #4a5568;
        }
        
        .content-wrapper {
            background-color: #f5f7fb;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            background-color: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background-color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0;
        }
        
        /* Gradient Cards */
        .card-gradient {
            color: white;
            border: none;
        }
        
        .card-gradient .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
        }
        
        .bg-gradient-danger {
            background: linear-gradient(135deg, #f72585 0%, #7209b7 100%);
        }
        
        .bg-gradient-info {
            background: linear-gradient(135deg, #4895ef 0%, #3f37c9 100%);
        }
        
        .bg-gradient-warning {
            background: linear-gradient(135deg, #f8961e 0%, #f72585 100%);
        }
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        }
        
        .bg-gradient-purple {
            background: linear-gradient(135deg, #7209b7 0%, #560bad 100%);
        }
        
        .bg-gradient-teal {
            background: linear-gradient(135deg, #20c997 0%, #4cc9f0 100%);
        }
        
        /* Info Box */
        .info-box {
            border-radius: 12px;
            color: white;
            padding: 1rem;
            min-height: 120px;
        }
        
        .info-box-icon {
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .info-box-content {
            padding-left: 1.5rem;
        }
        
        .info-box-text {
            font-size: 1rem;
            font-weight: 500;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .info-box-number {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        /* Quick Action Buttons */
        .btn-app {
            border-radius: 12px;
            padding: 1.5rem 1rem;
            margin: 0 0 1rem;
            min-width: 100%;
            height: auto;
            text-align: center;
            color: white;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-app:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            color: white;
        }
        
        .btn-app i {
            display: block;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        /* Chart Containers */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Content Header */
        .content-header {
            padding: 15px 0;
        }
        
        .content-header h1 {
            font-weight: 700;
            color: #2d3748;
            font-size: 1.8rem;
        }
        
        /* Custom Shadow */
        .shadow-lg {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .info-box {
                margin-bottom: 1rem;
            }
            
            .btn-app {
                margin-bottom: 1rem;
            }
        }
    </style>
    
    <?php include('includes/header.php'); ?>
   
    <?php include('includes/sidebar.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Financial Dashboard</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="?export_pdf" class="btn btn-primary shadow-lg">
                            <i class="fas fa-download fa-sm mr-2"></i> Generate Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Financial Overview Cards -->
                <div class="row">
                    <!-- Income Card -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-gradient bg-gradient-success shadow-lg">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-wallet mr-2"></i>Income Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-1">Monthly Income</h5>
                                        <p class="h3 mb-0"><?php echo formatNepaliRupees($totalIncomeThisMonth); ?></p>
                                    </div>
                                    <div class="icon-circle bg-white-10">
                                        <i class="fas fa-arrow-up text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">Annual Income</h5>
                                        <p class="h3 mb-0"><?php echo formatNepaliRupees($totalIncomeThisYear); ?></p>
                                    </div>
                                    <div class="icon-circle bg-white-10">
                                        <i class="fas fa-calendar-alt text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Card -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-gradient bg-gradient-danger shadow-lg">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Expense Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-1">Monthly Expense</h5>
                                        <p class="h3 mb-0"><?php echo formatNepaliRupees($totalExpenseThisMonth); ?></p>
                                    </div>
                                    <div class="icon-circle bg-white-10">
                                        <i class="fas fa-arrow-down text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">Annual Expense</h5>
                                        <p class="h3 mb-0"><?php echo formatNepaliRupees($totalExpenseThisYear); ?></p>
                                    </div>
                                    <div class="icon-circle bg-white-10">
                                        <i class="fas fa-calendar-alt text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Profit Card -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-gradient bg-gradient-<?php echo ($netProfitThisMonth >= 0) ? 'info' : 'warning'; ?> shadow-lg">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Net Profit/Loss</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-1">Monthly</h5>
                                        <p class="h3 mb-0"><?php echo formatNepaliRupees($netProfitThisMonth); ?></p>
                                    </div>
                                    <div class="icon-circle bg-white-10">
                                        <i class="fas <?php echo ($netProfitThisMonth >= 0) ? 'fa-arrow-up' : 'fa-arrow-down'; ?> text-white"></i>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">Annual</h5>
                                        <p class="h3 mb-0"><?php echo formatNepaliRupees($netProfitThisYear); ?></p>
                                    </div>
                                    <div class="icon-circle bg-white-10">
                                        <i class="fas fa-calendar-alt text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investment Summary Section -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-lg">
                            <div class="card-header bg-white">
                                <h3 class="card-title"><i class="fas fa-chart-pie mr-2 text-primary"></i>Investment Portfolio Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Stock Investment -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="info-box bg-gradient-info">
                                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Stock Investments</span>
                                                <span class="info-box-number"><?= formatNepaliRupees($stockSummary['total_investment']) ?></span>
                                                <div class="progress bg-white bg-opacity-25">
                                                    <div class="progress-bar bg-white" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    <?= $stockSummary['count'] ?> stock holdings
                                                </span>
                                                <a href="investment/dis_stock.php" class="small-box-footer text-white">
                                                    View details <i class="fas fa-arrow-circle-right ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Business Investment -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="info-box bg-gradient-success">
                                            <span class="info-box-icon"><i class="fas fa-business-time"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Business Investments</span>
                                                <span class="info-box-number">Rs. 0.00</span>
                                                <div class="progress bg-white bg-opacity-25">
                                                    <div class="progress-bar bg-white" style="width: 0%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    0 business investments
                                                </span>
                                                <a href="investment/dis_business.php" class="small-box-footer text-white">
                                                    View details <i class="fas fa-arrow-circle-right ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Total Investment -->
                                    <div class="col-lg-4 col-md-6">
                                        <div class="info-box bg-gradient-purple">
                                            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Investment</span>
                                                <span class="info-box-number"><?= formatNepaliRupees($stockSummary['total_investment']) ?></span>
                                                <div class="progress bg-white bg-opacity-25">
                                                    <div class="progress-bar bg-white" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    Across all investment types
                                                </span>
                                                <a href="investment_portfolio.php" class="small-box-footer text-white">
                                                    View details <i class="fas fa-arrow-circle-right ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-lg">
                            <div class="card-header bg-white">
                                <h3 class="card-title"><i class="fas fa-rocket mr-2 text-primary"></i>Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <a href="investment/stock.php" class="btn btn-app bg-gradient-primary">
                                            <i class="fas fa-plus-circle fa-2x"></i> 
                                            <span>Add Stock</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <a href="Income/income.php" class="btn btn-app bg-gradient-info">
                                            <i class="fas fa-money-bill-wave fa-2x"></i>
                                            <span>Record Expense</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <a href="Income/income.php" class="btn btn-app bg-gradient-danger">
                                            <i class="fas fa-receipt fa-2x"></i>
                                            <span>Record Income</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <a href="#" class="btn btn-app bg-gradient-teal">
                                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                                            <span>Generate Report</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row mt-4">
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-header bg-white">
                                <h4 class="card-title"><i class="fas fa-chart-bar mr-2 text-primary"></i>Profit/Loss Comparison</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="profitLossBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-header bg-white">
                                <h4 class="card-title"><i class="fas fa-chart-pie mr-2 text-primary"></i>Investment Distribution</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="investmentDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Enhanced Profit/Loss Bar Chart
        const profitLossBarChart = new Chart(document.getElementById('profitLossBarChart'), {
            type: 'bar',
            data: {
                labels: ['Income', 'Expense', 'Net Profit'],
                datasets: [{
                    label: 'Amount in Rs',
                    data: [<?php echo $totalIncomeThisYear; ?>, <?php echo $totalExpenseThisYear; ?>, <?php echo $netProfitThisYear; ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rs ' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Enhanced Investment Distribution Doughnut Chart
        const investmentDistributionChart = new Chart(document.getElementById('investmentDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Stocks', 'Business', 'Loans', 'Real Estate'],
                datasets: [{
                    data: [<?= $stockSummary['total_investment'] ?>, 0, 0, 0],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rs ' + context.raw.toLocaleString();
                                return label;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    </script>

    <?php include('includes/footer.php'); ?>
</div>
</body>
</html> 