<?php
// Loan-specific validation
$borrower_name = sanitizeInput($_POST['borrower_name']);
$loan_type = sanitizeInput($_POST['loan_type']);
$interest_rate = filter_var($_POST['interest_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$loan_duration = filter_var($_POST['loan_duration'], FILTER_SANITIZE_NUMBER_INT);
$repayment_schedule = sanitizeInput($_POST['repayment_schedule']);
$collateral = sanitizeInput($_POST['collateral']);
$collateral_details = isset($_POST['collateral_details']) ? sanitizeInput($_POST['collateral_details']) : null;

// Validate required loan fields
if(empty($borrower_name) || empty($loan_type) || empty($interest_rate) || empty($loan_duration) || empty($repayment_schedule)) {
    throw new Exception("All loan fields must be filled");
}

// Validate interest rate range
if($interest_rate < 0 || $interest_rate > 50) {
    throw new Exception("Interest rate must be between 0% and 50%");
}

// Validate loan duration
if($loan_duration < 1 || $loan_duration > 360) {
    throw new Exception("Loan duration must be between 1 and 360 months");
}

// File upload for loan agreement
$fileUpload = uploadFile('agreement_pdf');
if(isset($fileUpload['error'])) {
    throw new Exception($fileUpload['error']);
}
$agreement_pdf = isset($fileUpload['success']) ? $fileUpload['success'] : null;
$query = "INSERT INTO loan_investments 
(investment_id, borrower_name, loan_type, interest_rate, 
 loan_duration, repayment_schedule, collateral, collateral_details, agreement_pdf) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'issdissds', $investment_id, $borrower_name, $loan_type, 
           $interest_rate, $loan_duration, $repayment_schedule, 
           $collateral, $collateral_details, $agreement_pdf);

if(!mysqli_stmt_execute($stmt)) {
throw new Exception("Failed to save loan investment: " . mysqli_error($conn));
}