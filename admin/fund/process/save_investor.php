<?php
  include('../../config/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Handle file uploads
    $profile_photo = uploadFile($_FILES['profile_photo'], '../../uploads/');
    $agreement_files = uploadMultipleFiles($_FILES['agreement_files'], '../../agreements/');

    // Insert into database
    $sql = "INSERT INTO investors (name, profile_photo, contact_phone, contact_email, address, nationality, citizenship_number, date_of_investment, investment_type, investment_amount, agreement_files, kyc_status, bank_account_number, bank_name, nominee_name, relationship, investment_risk)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssssss", $name, $profile_photo, $contact_phone, $contact_email, $address, $nationality, $citizenship_number, $date_of_investment, $investment_type, $investment_amount, $agreement_files, $kyc_status, $bank_account_number, $bank_name, $nominee_name, $relationship, $investment_risk);
    $stmt->execute();

    header("Location: ../investorprofile.php");
    exit();
}

function uploadFile($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    move_uploaded_file($file["tmp_name"], $target_file);
    return $target_file;
}

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