<?php
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
include('../config/dbcon.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $investorId = $_POST['investorId'];
    $investmentType = $_POST['investmentType'];
    $investmentAmount = $_POST['investmentAmount'];

    // Validate required fields
    if (empty($investorId) || empty($investmentType) || empty($investmentAmount)) {
        die("Error: Required fields are missing.");
    }

    // Handle dynamic fields based on investment type
    if ($investmentType === 'Profit-sharing') {
        // Validate and retrieve profit-sharing fields
        $timeRange = $_POST['timeRange'] ?? '';
        $profitPercentage = $_POST['profitPercentage'] ?? '';
        $payoutFrequency = $_POST['payoutFrequency'] ?? '';
        $returnMethod = $_POST['returnMethod'] ?? '';

        if (empty($timeRange) || empty($profitPercentage) || empty($payoutFrequency) || empty($returnMethod)) {
            die("Error: All profit-sharing fields are required.");
        }

        // Save to database
        $query = "INSERT INTO profit_sharing_details (investor_id, time_range, profit_percentage, payout_frequency, return_method) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'issss', $investorId, $timeRange, $profitPercentage, $payoutFrequency, $returnMethod);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            die("Error: Failed to save profit-sharing details.");
        }
    } elseif ($investmentType === 'Debt') {
        // Validate and retrieve debt fields
        $debtDuration = $_POST['debtDuration'] ?? '';
        $interestRate = $_POST['interestRate'] ?? '';
        $repaymentSchedule = $_POST['repaymentSchedule'] ?? '';
        $collateral = $_POST['collateral'] ?? '';
        $totalInterest = $_POST['totalInterest'] ?? '';

        if (empty($debtDuration) || empty($interestRate) || empty($repaymentSchedule) || empty($totalInterest)) {
            die("Error: All debt fields are required.");
        }

        // Save to database
        $query = "INSERT INTO debt_details (investor_id, debt_duration, interest_rate, repayment_schedule, collateral, total_interest) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssss', $investorId, $debtDuration, $interestRate, $repaymentSchedule, $collateral, $totalInterest);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            die("Error: Failed to save debt details.");
        }
    } elseif ($investmentType === 'Equity') {
        // Validate and retrieve equity fields
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

        // Save to database
        $query = "INSERT INTO equity_details (investor_id, equity_percentage, share_price, total_shares, dividend_policy, voting_rights, resale_strategy, share_transfer) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssssss', $investorId, $equityPercentage, $sharePrice, $totalShares, $dividendPolicy, $votingRights, $resaleStrategy, $shareTransfer);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            die("Error: Failed to save equity details.");
        }
    } else {
        die("Error: Invalid investment type.");
    }

    // Handle file upload (optional)
    if (!empty($_FILES['agreementDocument']['name'])) {
        $file = $_FILES['agreementDocument'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileDestination = '../uploads/' . $fileName;

        // Validate file upload
        if (!move_uploaded_file($fileTmpName, $fileDestination)) {
            die("Error: Failed to upload agreement document.");
        }

        // Save file path to database
        $query = "UPDATE investors SET agreement_document = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $fileDestination, $investorId);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            die("Error: Failed to update agreement document.");
        }
    }

    echo "Details saved successfully!";
    header("Location: fundmanagement.php");
} else {
    die("Error: Invalid request method.");
}
?>