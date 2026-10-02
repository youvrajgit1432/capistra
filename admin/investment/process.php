<?php
include('../config/dbcon.php');
include('includes/functions.php');
require_once('../../protect/session_check.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Common validation
        $investment_type = sanitizeInput($_POST['investment_type']);
        $invested_amount = filter_var($_POST['invested_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $investment_date = sanitizeInput($_POST['investment_date']);
        $remarks = isset($_POST['remarks']) ? sanitizeInput($_POST['remarks']) : null;

        // Validate required fields
        if(empty($investment_type) || empty($invested_amount) || empty($investment_date)) {
            throw new Exception("All required fields must be filled");
        }

        // Start transaction
        mysqli_begin_transaction($conn);

        // Insert into investments table
        $query = "INSERT INTO investments (investment_type, invested_amount, investment_date, remarks) 
                  VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sdss', $investment_type, $invested_amount, $investment_date, $remarks);
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to save investment: " . mysqli_error($conn));
        }
        
        $investment_id = mysqli_insert_id($conn);

        // Handle specific investment types
        switch ($investment_type) {
            case 'stock':
                require_once('includes/process/process_stock.php');
                break;
                
            case 'business':
                require_once('includes/process/process_business.php');
                break;
                
            case 'loan':
                require_once('includes/process/process_loan.php');
                break;
                
            case 'real_estate':
                require_once('includes/process/process_real_estate.php');
                break;
                
            default:
                throw new Exception("Invalid investment type");
        }

        // Commit transaction
        mysqli_commit($conn);
        
        // Success - redirect to listing page
        $_SESSION['success_message'] = ucfirst(str_replace('_', ' ', $investment_type)) . " investment added successfully!";
        header('Location: dis_stock.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}