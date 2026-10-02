<?php
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
include('../config/dbcon.php'); // Include database connection
include('../head/header.php');

// Function to calculate total expense for a specific month and year
function getTotalExpense($conn, $month, $year) {
    $query = "SELECT SUM(amount) AS total_expense FROM expenses WHERE MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_expense'] ? (float)$row['total_expense'] : 0;
}

// Function to calculate total income for a specific month and year
function getTotalIncome($conn, $month, $year) {
    $query = "SELECT SUM(amount) AS total_income FROM income WHERE MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_income'] ? (float)$row['total_income'] : 0;
}

// Function to calculate returns based on investment type
function calculateReturns($investmentType, $investmentAmount, $details) {
    $returns = 0;

    // Ensure investment amount is a float
    $investmentAmount = (float)$investmentAmount;

    switch ($investmentType) {
        case 'Profit-sharing':
            $profitPercentage = (float)$details['profit_percentage'];
            $timeRange = (float)$details['time_range'];
            $returns = $investmentAmount * ($profitPercentage / 100) * ($timeRange / 12);
            break;

        case 'Debt':
            $interestRate = (float)$details['interest_rate'];
            $debtDuration = (float)$details['debt_duration'];
            $returns = $investmentAmount * ($interestRate / 100) * $debtDuration;
            break;

        case 'Equity':
            $equityPercentage = (float)$details['equity_percentage'];
            $totalShares = (float)$details['total_shares'];
            $sharePrice = (float)$details['share_price'];
            $dividendPolicy = (float)$details['dividend_policy'];
            $returns = ($equityPercentage / 100) * $totalShares * $sharePrice * ($dividendPolicy / 100);
            break;

        default:
            $returns = 0;
            break;
    }

    return $returns;
}

// Fetch all investors' details for further calculations
$allInvestorsQuery = "SELECT * FROM investors";
$allInvestorsResult = mysqli_query($conn, $allInvestorsQuery);
$allInvestors = mysqli_fetch_all($allInvestorsResult, MYSQLI_ASSOC);

// Calculate total expenses and income
$currentMonth = date('m');
$currentYear = date('Y');

$totalExpenseThisMonth = getTotalExpense($conn, $currentMonth, $currentYear);
$totalIncomeThisMonth = getTotalIncome($conn, $currentMonth, $currentYear);
$netProfitThisMonth = $totalIncomeThisMonth - $totalExpenseThisMonth;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Returns Calculation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body><div class="content-wrapper">
    <div class="container">
        <h1 class="text-center mt-4">Investor Returns Calculation</h1>

        <!-- Display Financial Summary -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Financial Summary</h5>
                <p><strong>Total Income This Month:</strong> <?php echo $totalIncomeThisMonth; ?></p>
                <p><strong>Total Expense This Month:</strong> <?php echo $totalExpenseThisMonth; ?></p>
                <p><strong>Net Profit This Month:</strong> <?php echo $netProfitThisMonth; ?></p>
            </div>
        </div>

        <!-- Display All Investors with Returns Calculation -->
        <h2 class="mt-5">All Investors</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Investment Type</th>
                    <th>Investment Amount</th>
                    <th>Returns</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allInvestors as $investor): ?>
                    <?php
                    $investmentType = $investor['investment_type'];
                    $investmentAmount = (float)$investor['investment_amount'];
                    $details = [];

                    // Fetch investment-specific details based on investment type
                    switch ($investmentType) {
                        case 'Profit-sharing':
                            $query = "SELECT * FROM profit_sharing_details WHERE investor_id = ?";
                            break;
                        case 'Debt':
                            $query = "SELECT * FROM debt_details WHERE investor_id = ?";
                            break;
                        case 'Equity':
                            $query = "SELECT * FROM equity_details WHERE investor_id = ?";
                            break;
                        default:
                            $query = null;
                            break;
                    }

                    if ($query) {
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, 'i', $investor['id']);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $details = mysqli_fetch_assoc($result);
                    }

                    // Calculate returns if details are available
                    $returns = $details ? calculateReturns($investmentType, $investmentAmount, $details) : 0;
                    ?>
                    <tr>
                        <td><?php echo $investor['id']; ?></td>
                        <td><?php echo $investor['name']; ?></td>
                        <td><?php echo $investmentType; ?></td>
                        <td><?php echo $investmentAmount; ?></td>
                        <td><?php echo $returns; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>   </div>
</body>
</html>

<?php
include('../head/footer.php');
?>