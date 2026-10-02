<?php
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
include('../head/header.php');
include('../config/dbcon.php');

// Get investor ID and investment type from the URL
$investorId = $_GET['investor_id'] ?? null;
$investmentType = $_GET['investment_type'] ?? null;

if (!$investorId || !$investmentType) {
    die("Invalid request.");
}

// Fetch investor details
$query = "SELECT * FROM investors WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $investorId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$investor = mysqli_fetch_assoc($result);

if (!$investor) {
    die("Investor not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body><div class="content-wrapper">
    <div class="container">
        <h1 class="text-center">Investor Details</h1>
        <form id="detailsForm" method="POST" action="save_investment_details.php">
            <input type="hidden" id="investorId" name="investorId" value="<?php echo $investorId; ?>">
            <div class="form-group">
                <label for="investmentType">Investment Type</label>
                <input type="text" class="form-control" id="investmentType" name="investmentType" value="<?php echo $investmentType; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="investmentAmount">Investment Amount</label>
                <input type="text" class="form-control" id="investmentAmount" name="investmentAmount" value="<?php echo $investor['investment_amount']; ?>" readonly>
            </div>

            <!-- Dynamic Fields Based on Investment Type -->
            <div id="dynamicFields">
                <?php
                if ($investmentType === 'Profit-sharing') {
                    echo '
                    <div class="form-group">
                        <label for="timeRange">Investment Time Range</label>
                        <input type="text" class="form-control" id="timeRange" name="timeRange">
                    </div>
                    <div class="form-group">
                        <label for="profitPercentage">Profit Sharing Percentage</label>
                        <input type="number" class="form-control" id="profitPercentage" name="profitPercentage">
                    </div>
                    <div class="form-group">
                        <label for="payoutFrequency">Payout Frequency</label>
                        <select class="form-control" id="payoutFrequency" name="payoutFrequency">
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="returnMethod">Return Method</label>
                        <select class="form-control" id="returnMethod" name="returnMethod">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Digital Wallet">Digital Wallet</option>
                            <option value="Reinvestment">Reinvestment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="agreementDocument">Agreement Document</label>
                        <input type="file" class="form-control" id="agreementDocument" name="agreementDocument">
                    </div>';
                } elseif ($investmentType === 'Debt') {
                    echo '
                    <div class="form-group">
                        <label for="debtDuration">Debt Duration (Months/Years)</label>
                        <input type="text" class="form-control" id="debtDuration" name="debtDuration">
                    </div>
                    <div class="form-group">
                        <label for="interestRate">Interest Rate (%)</label>
                        <input type="number" class="form-control" id="interestRate" name="interestRate">
                    </div>
                    <div class="form-group">
                        <label for="repaymentSchedule">Repayment Schedule</label>
                        <select class="form-control" id="repaymentSchedule" name="repaymentSchedule">
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                            <option value="One-Time Payment">One-Time Payment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="collateral">Collateral (if any)</label>
                        <input type="text" class="form-control" id="collateral" name="collateral">
                    </div>
                    <div class="form-group">
                        <label for="totalInterest">Total Interest Earned at Maturity</label>
                        <input type="text" class="form-control" id="totalInterest" name="totalInterest">
                    </div>
                    <div class="form-group">
                        <label for="agreementDocument">Agreement Document</label>
                        <input type="file" class="form-control" id="agreementDocument" name="agreementDocument">
                    </div>';
                } elseif ($investmentType === 'Equity') {
                    echo '
                    <div class="form-group">
                        <label for="equityPercentage">Equity Percentage (%)</label>
                        <input type="number" class="form-control" id="equityPercentage" name="equityPercentage">
                    </div>
                    <div class="form-group">
                        <label for="sharePrice">Share Price at Buy Time</label>
                        <input type="text" class="form-control" id="sharePrice" name="sharePrice">
                    </div>
                    <div class="form-group">
                        <label for="totalShares">Total Shares Purchased</label>
                        <input type="number" class="form-control" id="totalShares" name="totalShares">
                    </div>
                    <div class="form-group">
                        <label for="dividendPolicy">Dividend Policy</label>
                        <input type="text" class="form-control" id="dividendPolicy" name="dividendPolicy">
                    </div>
                    <div class="form-group">
                        <label for="votingRights">Voting Rights</label>
                        <select class="form-control" id="votingRights" name="votingRights">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="resaleStrategy">Resale/Exit Strategy</label>
                        <input type="text" class="form-control" id="resaleStrategy" name="resaleStrategy">
                    </div>
                    <div class="form-group">
                        <label for="shareTransfer">Share Transfer Options</label>
                        <select class="form-control" id="shareTransfer" name="shareTransfer">
                            <option value="Allowed">Allowed</option>
                            <option value="Not Allowed">Not Allowed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="agreementDocument">Agreement Document</label>
                        <input type="file" class="form-control" id="agreementDocument" name="agreementDocument">
                    </div>';
                }
                ?>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>   </div>
</body>
</html>

<?php
include('../head/footer.php');
?>