<?php
include('../../config/dbcon.php'); // Include your database connection file

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $id = $_POST['id'];
    $name = $_POST['name'];
    $contact_phone = $_POST['contact_phone'];
    $contact_email = $_POST['contact_email'];
    $address = $_POST['address'];
    $nationality = $_POST['nationality'];
    $citizenship_number = $_POST['citizenship_number'];
    $date_of_investment = $_POST['date_of_investment'];
    $investment_type = $_POST['investment_type'];
    $investment_amount = $_POST['investment_amount'];
    $kyc_status = $_POST['kyc_status'];
    $bank_account_number = $_POST['bank_account_number'];
    $bank_name = $_POST['bank_name'];
    $nominee_name = $_POST['nominee_name'];
    $relationship = $_POST['relationship'];
    $investment_risk = $_POST['investment_risk'];

    // Handle file upload for profile photo (if a new file is uploaded)
    if ($_FILES['profile_photo']['name']) {
        $profile_photo = uploadFile($_FILES['profile_photo'], '../../uploads/');
    } else {
        // If no new file is uploaded, keep the existing profile photo
        $sql = "SELECT profile_photo FROM investors WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $profile_photo = $row['profile_photo'];
    }

    // Handle file upload for agreement files (if new files are uploaded)
    if (!empty($_FILES['agreement_files']['name'][0])) {
        $agreement_files = uploadMultipleFiles($_FILES['agreement_files'], '../../agreements/');
    } else {
        // If no new files are uploaded, keep the existing agreement files
        $sql = "SELECT agreement_files FROM investors WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $agreement_files = $row['agreement_files'];
    }

    // Update the investor's data in the database
    $sql = "UPDATE investors SET 
            name = ?, 
            profile_photo = ?, 
            contact_phone = ?, 
            contact_email = ?, 
            address = ?, 
            nationality = ?, 
            citizenship_number = ?, 
            date_of_investment = ?, 
            investment_type = ?, 
            investment_amount = ?, 
            agreement_files = ?, 
            kyc_status = ?, 
            bank_account_number = ?, 
            bank_name = ?, 
            nominee_name = ?, 
            relationship = ?, 
            investment_risk = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssssssi", $name, $profile_photo, $contact_phone, $contact_email, $address, $nationality, $citizenship_number, $date_of_investment, $investment_type, $investment_amount, $agreement_files, $kyc_status, $bank_account_number, $bank_name, $nominee_name, $relationship, $investment_risk, $id);

    if ($stmt->execute()) {
        // Redirect back to the investor profiles page with a success message
        header("Location: ../investorprofile.php?status=success&message=Investor updated successfully");
        exit();
    } else {
        // Redirect back to the investor profiles page with an error message
        header("Location: ../investorprofile.php?status=error&message=Error updating investor: " . $stmt->error);
        exit();
    }
} else {
    // Redirect back to the investor profiles page if the form is not submitted
    header("Location: ../investorprofile.php");
    exit();
}

// Function to handle single file upload
function uploadFile($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    move_uploaded_file($file["tmp_name"], $target_file);
    return $target_file;
}

// Function to handle multiple file uploads
function uploadMultipleFiles($files, $target_dir) {
    $file_paths = [];
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        $target_file = $target_dir . basename($files['name'][$key]);
        move_uploaded_file($tmp_name, $target_file);
        $file_paths[] = $target_file;
    }
    return json_encode($file_paths);
}
?>