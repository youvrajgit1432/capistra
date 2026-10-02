<?php
require_once dirname(__DIR__, 3) . '/protect/session_check.php'; // Capistra auth guard
include('../../config/dbcon.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $investor_id = $_POST['investor_id'];
    $investment_type = $_POST['investment_type'];

    // Validate required fields
    if (empty($investor_id) || empty($investment_type)) {
        die("Error: Required fields are missing.");
    }

    // Handle updates based on investment type
    if ($investment_type === 'Profit-sharing') {
        // Retrieve and validate profit-sharing fields
        $timeRange = $_POST['timeRange'] ?? '';
        $profitPercentage = $_POST['profitPercentage'] ?? '';
        $payoutFrequency = $_POST['payoutFrequency'] ?? '';
        $returnMethod = $_POST['returnMethod'] ?? '';

        if (empty($timeRange) || empty($profitPercentage) || empty($payoutFrequency) || empty($returnMethod)) {
            die("Error: All profit-sharing fields are required.");
        }

        // Update profit-sharing details
        $query = "UPDATE profit_sharing_details SET 
                  time_range = ?, 
                  profit_percentage = ?, 
                  payout_frequency = ?, 
                  return_method = ? 
                  WHERE investor_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $timeRange, $profitPercentage, $payoutFrequency, $returnMethod, $investor_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            die("Error: Failed to update profit-sharing details.");
        }

    } elseif ($investment_type === 'Debt') {
        // Retrieve and validate debt fields
        $debtDuration = $_POST['debtDuration'] ?? '';
        $interestRate = $_POST['interestRate'] ?? '';
        $repaymentSchedule = $_POST['repaymentSchedule'] ?? '';
        $collateral = $_POST['collateral'] ?? '';
        $totalInterest = $_POST['totalInterest'] ?? '';

        if (empty($debtDuration) || empty($interestRate) || empty($repaymentSchedule) || empty($totalInterest)) {
            die("Error: All debt fields are required.");
        }

        // Update debt details
        $query = "UPDATE debt_details SET 
                  debt_duration = ?, 
                  interest_rate = ?, 
                  repayment_schedule = ?, 
                  collateral = ?, 
                  total_interest = ? 
                  WHERE investor_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssi', $debtDuration, $interestRate, $repaymentSchedule, $collateral, $totalInterest, $investor_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            die("Error: Failed to update debt details.");
        }

    } elseif ($investment_type === 'Equity') {
        // Retrieve and validate equity fields
        $equityPercentage = $_POST['equityPercentage'] ?? '';
        $sharePrice = $_POST['sharePrice'] ?? '';
        $totalShares = $_POST['totalShares'] ?? '';
        $dividendPolicy = $_POST['dividendPolicy'] ?? '';
        $votingRights = $_POST['votingRights'] ?? '';
        $resaleStrategy = $_POST['resaleStrategy'] ?? '';
        $shareTransfer = $_POST['shareTransfer'] ?? '';

        if (empty($equityPercentage) || empty($sharePrice) || empty($totalShares) || empty($dividendPolicy) || empty($votingRights) || empty($resaleStrategy) || empty($shareTransfer)) {
            die("Error: All equity fields are required.");
        }

        // Update equity details
        $query = "UPDATE equity_details SET 
                  equity_percentage = ?, 
                  share_price = ?, 
                  total_shares = ?, 
                  dividend_policy = ?, 
                  voting_rights = ?, 
                  resale_strategy = ?, 
                  share_transfer = ? 
                  WHERE investor_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssssi', $equityPercentage, $sharePrice, $totalShares, $dividendPolicy, $votingRights, $resaleStrategy, $shareTransfer, $investor_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            die("Error: Failed to update equity details.");
        }

    } else {
        die("Error: Invalid investment type.");
    }

    // Redirect back to the view page with a success message
    header("Location: ../view_investor_details.php?investor_id=$investor_id&investment_type=$investment_type&status=success");
    exit();
} else {
    die("Error: Invalid request method.");
}
?>