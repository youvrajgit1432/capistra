<?php
 require_once('../../protect/session_check.php');
include('../config/dbcon.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $category = $_POST['category'];
    $date = $_POST['date'];
    $amount = $_POST['amount'];

    // Handle file upload (PDF or image)
    $file_name = '';
    if (isset($_FILES['bill_file'])) {
        $file_tmp = $_FILES['bill_file']['tmp_name'];
        $file_name = $_FILES['bill_file']['name'];
        $file_path = 'uploads/' . $file_name;
        
        // Move the uploaded file to the server
        move_uploaded_file($file_tmp, $file_path);
    }

    // Insert the data into the database
    $query = "INSERT INTO expenses (category, date, amount, bill_file) 
              VALUES ('$category', '$date', '$amount', '$file_name')";

    if (mysqli_query($conn, $query)) {
        // Redirect to the same page after submission
        header('Location: expense.php?success=Expense Added successfully.');
        exit; // Always call exit after header to stop further execution
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
