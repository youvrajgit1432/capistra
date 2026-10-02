<?php
// Stock-specific validation
$company_name = sanitizeInput($_POST['company_name']);
$company_symbol = sanitizeInput($_POST['company_symbol']);
$stock_investment_type = sanitizeInput($_POST['stock_investment_type']);
$stock_base_price = filter_var($_POST['stock_base_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$stock_total_units = filter_var($_POST['stock_total_units'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

// Validate stock fields
if(empty($company_name) || empty($company_symbol) || empty($stock_base_price) || empty($stock_total_units)) {
    throw new Exception("All stock fields must be filled");
}

// File upload for stock
$fileUpload = uploadFile('agreement_pdf');
if(isset($fileUpload['error'])) {
    throw new Exception($fileUpload['error']);
}
$agreement_pdf = isset($fileUpload['success']) ? $fileUpload['success'] : null;

// Insert into stock_investments table
$query = "INSERT INTO stock_investments 
          (investment_id, company_name, company_symbol, investment_type, base_price, total_units, agreement_pdf) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'isssdds', $investment_id, $company_name, $company_symbol, 
                     $stock_investment_type, $stock_base_price, $stock_total_units, $agreement_pdf);

if(!mysqli_stmt_execute($stmt)) {
    throw new Exception("Failed to save stock investment: " . mysqli_error($conn));
}