<?php
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
include('../head/header.php');
include('../config/dbcon.php'); // Include database connection

if (isset($_GET['investor_id']) && isset($_GET['investment_type'])) {
    $investor_id = $_GET['investor_id'];
    $investment_type = $_GET['investment_type'];

    // Fetch investor details
    $query = "SELECT * FROM investors WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $investor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $investor = mysqli_fetch_assoc($result);

    if (!$investor) {
        die("Investor not found.");
    }

    // Fetch investment details based on investment type
    if ($investment_type === 'Profit-sharing') {
        $query = "SELECT * FROM profit_sharing_details WHERE investor_id = ?";
    } elseif ($investment_type === 'Debt') {
        $query = "SELECT * FROM debt_details WHERE investor_id = ?";
    } elseif ($investment_type === 'Equity') {
        $query = "SELECT * FROM equity_details WHERE investor_id = ?";
    } else {
        die("Invalid investment type.");
    }

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $investor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $investment_details = mysqli_fetch_assoc($result);

    if (!$investment_details) {
        die("Investment details not found.");
    }
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Investor Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body><div class="content-wrapper">
    <div class="container mt-5">
        <h2>Investor Details</h2>
        <table class="table table-bordered">
            <tr>
                <th>Investor ID</th>
                <td><?php echo $investor['id']; ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><?php echo $investor['name']; ?></td>
            </tr>
            <tr>
                <th>Contact Phone</th>
                <td><?php echo $investor['contact_phone']; ?></td>
            </tr>
            <tr>
                <th>Contact Email</th>
                <td><?php echo $investor['contact_email']; ?></td>
            </tr>
            <tr>
                <th>Investment Amount</th>
                <td><?php echo $investor['investment_amount']; ?></td>
            </tr>
            <tr>
                <th>Investment Type</th>
                <td><?php echo $investor['investment_type']; ?></td>
            </tr>
            <tr>
                <th>Risk Level</th>
                <td><?php echo $investor['investment_risk']; ?></td>
            </tr>
        </table>

        <h2>Investment Details</h2>
        <table class="table table-bordered">
            <?php foreach ($investment_details as $key => $value): ?>
                <tr>
                    <th><?php echo ucfirst(str_replace('_', ' ', $key)); ?></th>
                    <td><?php echo $value; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Edit Button -->
        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal">
            Edit Investment Details
        </button>

        <!-- Edit Modal -->
     <!-- Include FontAwesome CSS from CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);">
            <div class="modal-header" style="background-color: #4CAF50; color: white; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h5 class="modal-title" id="editModalLabel"><i class="fas fa-edit"></i> Edit Investment Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form action="process/update_investment_details.php" method="POST">
                    <input type="hidden" name="investor_id" value="<?php echo $investor_id; ?>">
                    <input type="hidden" name="investment_type" value="<?php echo $investment_type; ?>">

                    <?php if ($investment_type === 'Profit-sharing'): ?>
                        <div class="form-group">
                            <label><i class="fas fa-clock"></i> Time Range</label>
                            <input type="text" name="timeRange" class="form-control" value="<?php echo $investment_details['time_range']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-percentage"></i> Profit Percentage</label>
                            <input type="text" name="profitPercentage" class="form-control" value="<?php echo $investment_details['profit_percentage']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Payout Frequency</label>
                            <input type="text" name="payoutFrequency" class="form-control" value="<?php echo $investment_details['payout_frequency']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-hand-holding-usd"></i> Return Method</label>
                            <input type="text" name="returnMethod" class="form-control" value="<?php echo $investment_details['return_method']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>

                    <?php elseif ($investment_type === 'Debt'): ?>
                        <div class="form-group">
                            <label><i class="fas fa-hourglass-half"></i> Debt Duration</label>
                            <input type="text" name="debtDuration" class="form-control" value="<?php echo $investment_details['debt_duration']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-percentage"></i> Interest Rate</label>
                            <input type="text" name="interestRate" class="form-control" value="<?php echo $investment_details['interest_rate']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Repayment Schedule</label>
                            <input type="text" name="repaymentSchedule" class="form-control" value="<?php echo $investment_details['repayment_schedule']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-shield-alt"></i> Collateral</label>
                            <input type="text" name="collateral" class="form-control" value="<?php echo $investment_details['collateral']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-coins"></i> Total Interest</label>
                            <input type="text" name="totalInterest" class="form-control" value="<?php echo $investment_details['total_interest']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>

                    <?php elseif ($investment_type === 'Equity'): ?>
                        <div class="form-group">
                            <label><i class="fas fa-chart-pie"></i> Equity Percentage</label>
                            <input type="text" name="equityPercentage" class="form-control" value="<?php echo $investment_details['equity_percentage']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-dollar-sign"></i> Share Price</label>
                            <input type="text" name="sharePrice" class="form-control" value="<?php echo $investment_details['share_price']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-sort-amount-up"></i> Total Shares</label>
                            <input type="text" name="totalShares" class="form-control" value="<?php echo $investment_details['total_shares']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-file-invoice-dollar"></i> Dividend Policy</label>
                            <input type="text" name="dividendPolicy" class="form-control" value="<?php echo $investment_details['dividend_policy']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-vote-yea"></i> Voting Rights</label>
                            <input type="text" name="votingRights" class="form-control" value="<?php echo $investment_details['voting_rights']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-exchange-alt"></i> Resale Strategy</label>
                            <input type="text" name="resaleStrategy" class="form-control" value="<?php echo $investment_details['resale_strategy']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-share-alt"></i> Share Transfer</label>
                            <input type="text" name="shareTransfer" class="form-control" value="<?php echo $investment_details['share_transfer']; ?>" style="border-radius: 5px; border: 1px solid #ddd; padding: 10px;">
                        </div>
                    <?php endif; ?>

                    <div class="modal-footer" style="border-top: 1px solid #ddd; padding-top: 15px;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background-color: #6c757d; border: none; border-radius: 5px; padding: 10px 20px;"><i class="fas fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #4CAF50; border: none; border-radius: 5px; padding: 10px 20px;"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
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