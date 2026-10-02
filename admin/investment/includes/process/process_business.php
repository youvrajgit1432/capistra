<?php
// Business-specific validation
$business_name = sanitizeInput($_POST['business_name']);
$business_type = sanitizeInput($_POST['business_type']);
$business_investment_model = sanitizeInput($_POST['business_investment_model']);

// Validate required business fields
if(empty($business_name) || empty($business_type) || empty($business_investment_model)) {
    throw new Exception("All business fields must be filled");
}

// Handle different investment models
$equity_percentage = null;
$shareholder_rights = null;
$loan_amount = null;
$interest_rate = null;
$repayment_period = null;
$profit_share_percentage = null;
$distribution_schedule = null;

if($business_investment_model === 'equity') {
    $equity_percentage = filter_var($_POST['equity_percentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $shareholder_rights = sanitizeInput($_POST['shareholder_rights']);
    
    if(empty($equity_percentage)) {
        throw new Exception("Equity percentage is required for equity investments");
    }
} elseif($business_investment_model === 'debt') {
    $loan_amount = filter_var($_POST['loan_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $interest_rate = filter_var($_POST['interest_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $repayment_period = filter_var($_POST['repayment_period'], FILTER_SANITIZE_NUMBER_INT);
    
    if(empty($loan_amount) || empty($interest_rate) || empty($repayment_period)) {
        throw new Exception("All debt investment fields must be filled");
    }
} elseif($business_investment_model === 'profit_sharing') {
    $profit_share_percentage = filter_var($_POST['profit_share_percentage'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $distribution_schedule = sanitizeInput($_POST['distribution_schedule']);
    
    if(empty($profit_share_percentage)) {
        throw new Exception("Profit share percentage is required for profit sharing investments");
    }
}

// File upload for business agreement
$fileUpload = uploadFile('agreement_pdf');
if(isset($fileUpload['error'])) {
    throw new Exception($fileUpload['error']);
}
$agreement_pdf = isset($fileUpload['success']) ? $fileUpload['success'] : null;

// Insert into business_investments table
$query = "INSERT INTO business_investments 
          (investment_id, business_name, business_type, investment_model, 
           equity_percentage, shareholder_rights, loan_amount, interest_rate, 
           repayment_period, profit_share_percentage, distribution_schedule, agreement_pdf) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'isssssddidss', $investment_id, $business_name, $business_type, 
                     $business_investment_model, $equity_percentage, $shareholder_rights, 
                     $loan_amount, $interest_rate, $repayment_period, 
                     $profit_share_percentage, $distribution_schedule, $agreement_pdf);

if(!mysqli_stmt_execute($stmt)) {
    throw new Exception("Failed to save business investment: " . mysqli_error($conn));
}