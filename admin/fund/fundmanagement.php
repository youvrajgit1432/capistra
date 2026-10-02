<?php
include('../head/header.php');
include('../config/dbcon.php');

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

// Fetch the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Calculate totals
$totalExpenseThisMonth = getTotalExpense($conn, $currentMonth, $currentYear);
$totalExpenseThisYear = getTotalExpenseForYear($conn, $currentYear);
$totalIncomeThisMonth = getTotalIncome($conn, $currentMonth, $currentYear);
$totalIncomeThisYear = getTotalIncomeForYear($conn, $currentYear);

// Calculate net profit and gross profit
$netProfitThisMonth = $totalIncomeThisMonth - $totalExpenseThisMonth;
$netProfitThisYear = $totalIncomeThisYear - $totalExpenseThisYear;
$grossProfitThisMonth = $totalIncomeThisMonth;
$grossProfitThisYear = $totalIncomeThisYear;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   <link rel="stylesheet" href="style.css">
</head>
<body><div class="content-wrapper">
    <div class="container">
        <h1 class="text-center">Fund Management</h1>

        <!-- Header (Summary Section) -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Capital Collected</h5>
                        <p class="card-text"><?php echo formatNepaliRupees($totalIncomeThisYear); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Allocated Funds</h5>
                        <p class="card-text"><?php echo formatNepaliRupees($totalExpenseThisYear); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Available Balance</h5>
                        <p class="card-text"><?php echo formatNepaliRupees($totalIncomeThisYear - $totalExpenseThisYear); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Overall Profit/Loss</h5>
                        <p class="card-text"><?php echo formatNepaliRupees($netProfitThisYear); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mt-4">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#investorDetails">Investor Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#fundAllocation">Fund Allocation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#transactions">Transactions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#reports">Reports</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
      <!-- Investor Details Tab -->
<div id="investorDetails" class="tab-pane active">
    <h3>Investor Details</h3>
    <div class="row">
        <div class="col-md-6">
            <input type="text" class="form-control" id="searchInvestor" placeholder="Search Investor">
        </div>
    </div>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>SN</th>
                <th>Investor ID</th>
                <th>Name</th>
                <th>Contact Phone</th>
                <th>Contact Email</th>
                <th>Investment Amount</th>
                <th>Investment Type</th>
                <th>Risk Level</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT id, name, contact_phone, contact_email, investment_amount, investment_type, investment_risk FROM investors";
            $result = mysqli_query($conn, $query);
            $sn = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>$sn</td>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['contact_phone']}</td>
                        <td>{$row['contact_email']}</td>
                        <td>{$row['investment_amount']}</td>
                        <td>{$row['investment_type']}</td>
                        <td>{$row['investment_risk']}</td>
                    <td>
                                        <a href='investor_details.php?investor_id={$row['id']}&investment_type={$row['investment_type']}' 
                                        class='btn btn-primary btn-sm'>
                                            Add Details
                                        </a>
                                          <a href='view_investor_details.php?investor_id={$row['id']}&investment_type={$row['investment_type']}' 
       class='btn btn-info btn-sm'>
        View
    </a>
                                    </td>
                      </tr>";
                $sn++;
            }
            ?>
        </tbody>
    </table>
</div>
 
            <!-- Fund Allocation Tab -->
            <div id="fundAllocation" class="tab-pane fade">
                <h3>Fund Allocation</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Investment Type</th>
                            <th>Allocated Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT investment_type, SUM(investment_amount) AS total_amount FROM investors GROUP BY investment_type";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                    <td>{$row['investment_type']}</td>
                                    <td>{$row['total_amount']}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Transactions Tab -->
            <div id="transactions" class="tab-pane fade">
                <h3>Transactions</h3>
                <!-- Filter Form -->
                <form method="GET" action="" class="mb-4" id="filterForm">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="startMonth">Start Month:</label>
                            <select name="startMonth" id="startMonth" class="form-control">
                                <?php
                                for ($m = 1; $m <= 12; $m++) {
                                    $monthName = date('F', mktime(0, 0, 0, $m, 10));
                                    $selected = (isset($_GET['startMonth']) && $_GET['startMonth'] == $m) ? 'selected' : '';
                                    echo "<option value='{$m}' {$selected}>{$monthName}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="startYear">Start Year:</label>
                            <select name="startYear" id="startYear" class="form-control">
                                <?php
                                $currentYear = date('Y');
                                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                    $selected = (isset($_GET['startYear']) && $_GET['startYear'] == $y) ? 'selected' : '';
                                    echo "<option value='{$y}' {$selected}>{$y}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="endMonth">End Month:</label>
                            <select name="endMonth" id="endMonth" class="form-control">
                                <?php
                                for ($m = 1; $m <= 12; $m++) {
                                    $monthName = date('F', mktime(0, 0, 0, $m, 10));
                                    $selected = (isset($_GET['endMonth']) && $_GET['endMonth'] == $m) ? 'selected' : '';
                                    echo "<option value='{$m}' {$selected}>{$monthName}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="endYear">End Year:</label>
                            <select name="endYear" id="endYear" class="form-control">
                                <?php
                                $currentYear = date('Y');
                                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                    $selected = (isset($_GET['endYear']) && $_GET['endYear'] == $y) ? 'selected' : '';
                                    echo "<option value='{$y}' {$selected}>{$y}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </form>
                <?php
                // Fetch transactions based on filters
                $startMonth = isset($_GET['startMonth']) ? intval($_GET['startMonth']) : date('n');
                $startYear = isset($_GET['startYear']) ? intval($_GET['startYear']) : date('Y');
                $endMonth = isset($_GET['endMonth']) ? intval($_GET['endMonth']) : date('n');
                $endYear = isset($_GET['endYear']) ? intval($_GET['endYear']) : date('Y');

                $startDate = "{$startYear}-{$startMonth}-01";
                $endDate = date('Y-m-t', strtotime("{$endYear}-{$endMonth}-01"));

                $query = "
                    SELECT date, category AS description, amount, 'Income' AS type 
                    FROM income 
                    WHERE date BETWEEN '{$startDate}' AND '{$endDate}'
                    UNION ALL
                    SELECT date, category AS description, amount, 'Expense' AS type 
                    FROM expenses 
                    WHERE date BETWEEN '{$startDate}' AND '{$endDate}'
                    ORDER BY date ASC";

                $result = mysqli_query($conn, $query);

                if (!$result) {
                    die("Error fetching transactions: " . mysqli_error($conn));
                }

                $totalDebit = 0;
                $totalCredit = 0;
                ?>

                <!-- Transactions Table -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $sn = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $amount = $row['amount'];
                                $type = $row['type'];
                                $debit = ($type == 'Expense') ? $amount : '-';
                                $credit = ($type == 'Income') ? $amount : '-';

                                if ($type == 'Expense') {
                                    $totalDebit += $amount;
                                } else {
                                    $totalCredit += $amount;
                                }

                                echo "<tr>
                                        <td>{$sn}</td>
                                        <td>{$row['date']}</td>
                                        <td>{$row['description']}</td>
                                        <td>{$type}</td>
                                        <td class='text-red'>{$debit}</td>
                                        <td class='text-green'>{$credit}</td>
                                      </tr>";
                                $sn++;
                            }

                            $profitLoss = $totalCredit - $totalDebit;
                            $profitLossColor = ($profitLoss >= 0) ? 'text-green' : 'text-red';
                        } else {
                            echo "<tr><td colspan='6'>No transactions found for the selected period.</td></tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Total</strong></td>
                            <td class="text-red"><strong><?php echo $totalDebit; ?></strong></td>
                            <td class="text-green"><strong><?php echo $totalCredit; ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Profit/Loss</strong></td>
                            <td colspan="2" class="<?php echo $profitLossColor ?? 'text-green'; ?>">
                                <strong><?php echo $profitLoss ?? 0; ?></strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <script>
        document.getElementById('startMonth').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('startYear').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('endMonth').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('endYear').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>
            <!-- Reports Tab -->
            <div id="reports" class="tab-pane fade">
                <h3>Reports</h3>
                <div class="row">
                    <div class="col-md-6">
                        <h4>Profit/Loss Reports</h4>
                        <table class="table table-bordered profit-loss-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Profit/Loss</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    $totalIncome = getTotalIncome($conn, $i, $currentYear);
                                    $totalExpense = getTotalExpense($conn, $i, $currentYear);
                                    $netProfit = $totalIncome - $totalExpense;
                                    $color = ($netProfit >= 0) ? 'text-green' : 'text-red';
                                    echo "<tr>
                                            <td>" . date('F', mktime(0, 0, 0, $i, 10)) . "</td>
                                            <td class='{$color}'>{$netProfit}</td>
                                          </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Investor-Wise Fund Contributions</h4>
                        <table class="table table-bordered investor-table">
                            <thead>
                                <tr>
                                    <th>Investor Name</th>
                                    <th>Total Contribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT name, SUM(investment_amount) AS total_contribution FROM investors GROUP BY name";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                            <td>{$row['name']}</td>
                                            <td>{$row['total_contribution']}</td>
                                          </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>   </div>
</body>
</html>

<?php
include('../head/footer.php');
?>