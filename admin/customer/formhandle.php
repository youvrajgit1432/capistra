<?php
// formhandle.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "capistra";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables
$applicant_type = $marital_status = $title = $applicant_name_en = $father_name = $mother_name = "";
$grandfather_name = $grandmother_name = $phone_number = $email_address = $id_type = $id_number = "";
$issued_date = $issued_place = $permanent_country = $permanent_province = $permanent_district = "";
$ward_no = $permanent_tole = $temporary_country = $temporary_province = $temporary_district = "";
$temporary_ward_no = $temporary_tole = $bank_name = $bank_account_number = $demat_account_number = "";
$crn_number = $broker_name = $user_number = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $title = sanitizeInput($_POST["title"]);
    $applicant_type = sanitizeInput($_POST["applicant_type"]);
    $applicant_name_en = sanitizeInput($_POST["applicant_name_en"]);
    $father_name = sanitizeInput($_POST["father_name"]);
    $mother_name = sanitizeInput($_POST["mother_name"]);
    $grandfather_name = sanitizeInput($_POST["grandfather_name"]);
    $grandmother_name = sanitizeInput($_POST["grandmother_name"]);
    $phone_number = sanitizeInput($_POST["phone_number"]);
    $email_address = sanitizeInput($_POST["email_address"]);
    $marital_status = sanitizeInput($_POST["marital_status"]);
    $dob_bs = sanitizeInput($_POST["date1"]);
    $dob_ad = sanitizeInput($_POST["dob_ad"]);
    $id_type = sanitizeInput($_POST["id_type"]);
    $id_number = sanitizeInput($_POST["id_number"]);
    $issued_date = sanitizeInput($_POST["issued_date"]);
    $issued_place = sanitizeInput($_POST["issued_place"]);
    $permanent_country = sanitizeInput($_POST["permanent_country"]);
    $permanent_province = sanitizeInput($_POST["permanent_province"]);
    $permanent_district = sanitizeInput($_POST["permanent_district"]);
    $ward_no = sanitizeInput($_POST["ward_no"]);
    $permanent_tole = sanitizeInput($_POST["permanent_tole"]);
    $temporary_country = sanitizeInput($_POST["temporary_country"]);
    $temporary_province = sanitizeInput($_POST["temporary_province"]);
    $temporary_district = sanitizeInput($_POST["temporary_district"]);
    $temporary_ward_no = sanitizeInput($_POST["temporary_ward_no"]);
    $temporary_tole = sanitizeInput($_POST["temporary_tole"]);
    $bank_name = sanitizeInput($_POST["bank_name"]);
    $bank_account_number = sanitizeInput($_POST["bank_account_number"]);
    $demat_account_number = sanitizeInput($_POST["demat_account_number"]);
    $crn_number = sanitizeInput($_POST["crn_number"]);
    $broker_name = sanitizeInput($_POST["broker_name"]);
    $user_number = sanitizeInput($_POST["user_number"]);

    // Handle file uploads
    $upload_dir = "uploads/";
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Process document uploads
    $documents = [];
    
    // Citizenship
    if (!empty($_FILES["citizenship_front"]["name"])) {
        $citizenship_front = $upload_dir . basename($_FILES["citizenship_front"]["name"]);
        move_uploaded_file($_FILES["citizenship_front"]["tmp_name"], $citizenship_front);
        $documents[] = ["type" => "citizenship_front", "path" => $citizenship_front];
    }
    
    if (!empty($_FILES["citizenship_back"]["name"])) {
        $citizenship_back = $upload_dir . basename($_FILES["citizenship_back"]["name"]);
        move_uploaded_file($_FILES["citizenship_back"]["tmp_name"], $citizenship_back);
        $documents[] = ["type" => "citizenship_back", "path" => $citizenship_back];
    }
    
    // Birth Certificate
    if (!empty($_FILES["birth_certificate"]["name"])) {
        $birth_certificate = $upload_dir . basename($_FILES["birth_certificate"]["name"]);
        move_uploaded_file($_FILES["birth_certificate"]["tmp_name"], $birth_certificate);
        $documents[] = ["type" => "birth_certificate", "path" => $birth_certificate];
    }
    
    // License
    if (!empty($_FILES["license_front"]["name"])) {
        $license_front = $upload_dir . basename($_FILES["license_front"]["name"]);
        move_uploaded_file($_FILES["license_front"]["tmp_name"], $license_front);
        $documents[] = ["type" => "license_front", "path" => $license_front];
    }
    
    if (!empty($_FILES["license_back"]["name"])) {
        $license_back = $upload_dir . basename($_FILES["license_back"]["name"]);
        move_uploaded_file($_FILES["license_back"]["tmp_name"], $license_back);
        $documents[] = ["type" => "license_back", "path" => $license_back];
    }
    
    // National ID
    if (!empty($_FILES["national_id_front"]["name"])) {
        $national_id_front = $upload_dir . basename($_FILES["national_id_front"]["name"]);
        move_uploaded_file($_FILES["national_id_front"]["tmp_name"], $national_id_front);
        $documents[] = ["type" => "national_id_front", "path" => $national_id_front];
    }
    
    if (!empty($_FILES["national_id_back"]["name"])) {
        $national_id_back = $upload_dir . basename($_FILES["national_id_back"]["name"]);
        move_uploaded_file($_FILES["national_id_back"]["tmp_name"], $national_id_back);
        $documents[] = ["type" => "national_id_back", "path" => $national_id_back];
    }
    
    // Parents Citizenship (for minors)
    if (!empty($_FILES["father_citizenship_front"]["name"])) {
        $father_citizenship_front = $upload_dir . basename($_FILES["father_citizenship_front"]["name"]);
        move_uploaded_file($_FILES["father_citizenship_front"]["tmp_name"], $father_citizenship_front);
        $documents[] = ["type" => "father_citizenship_front", "path" => $father_citizenship_front];
    }
    
    if (!empty($_FILES["father_citizenship_back"]["name"])) {
        $father_citizenship_back = $upload_dir . basename($_FILES["father_citizenship_back"]["name"]);
        move_uploaded_file($_FILES["father_citizenship_back"]["tmp_name"], $father_citizenship_back);
        $documents[] = ["type" => "father_citizenship_back", "path" => $father_citizenship_back];
    }

    // Insert common data into the main applicants table
    $sql = "INSERT INTO applicants (
        title, applicant_type, applicant_name_en, father_name, mother_name, 
        grandfather_name, grandmother_name, phone_number, email_address, 
        marital_status, dob_bs, dob_ad, id_type, id_number, issued_date, 
        issued_place, permanent_country, permanent_province, permanent_district, 
        ward_no, permanent_tole, temporary_country, temporary_province, 
        temporary_district, temporary_ward_no, temporary_tole, bank_name, 
        bank_account_number, demat_account_number, crn_number, broker_name, 
        user_number, registration_date
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssssssssssssssssss", 
        $title, $applicant_type, $applicant_name_en, $father_name, $mother_name, 
        $grandfather_name, $grandmother_name, $phone_number, $email_address, 
        $marital_status, $dob_bs, $dob_ad, $id_type, $id_number, $issued_date, 
        $issued_place, $permanent_country, $permanent_province, $permanent_district, 
        $ward_no, $permanent_tole, $temporary_country, $temporary_province, 
        $temporary_district, $temporary_ward_no, $temporary_tole, $bank_name, 
        $bank_account_number, $demat_account_number, $crn_number, $broker_name, 
        $user_number
    );

    if ($stmt->execute()) {
        $applicant_id = $conn->insert_id;
        
        // Insert documents into documents table
        foreach ($documents as $doc) {
            $doc_sql = "INSERT INTO applicant_documents (applicant_id, document_type, document_path) VALUES (?, ?, ?)";
            $doc_stmt = $conn->prepare($doc_sql);
            $doc_stmt->bind_param("iss", $applicant_id, $doc["type"], $doc["path"]);
            $doc_stmt->execute();
            $doc_stmt->close();
        }
        
        // Handle different applicant types
        if ($applicant_type == "Minor") {
            // Insert into minors table
            $guardian_name = sanitizeInput($_POST["guardian_name"]);
            $guardian_relationship = sanitizeInput($_POST["guardian_relationship"]);
            $guardian_phone = sanitizeInput($_POST["guardian_phone"]);
            $guardian_email = sanitizeInput($_POST["guardian_email"]);
            $guardian_address = sanitizeInput($_POST["guardian_address"]);
            $iid_type = sanitizeInput($_POST["iid_type"]);
            $iiissued_number = sanitizeInput($_POST["iiissued_number"]);
            $iiissued_date = sanitizeInput($_POST["date1"]);
            $iiissued_place = sanitizeInput($_POST["issued_place"]);
            
            $minor_sql = "INSERT INTO minors (
                applicant_id, guardian_name, guardian_relationship, guardian_phone, 
                guardian_email, guardian_address, guardian_id_type, guardian_id_number, 
                guardian_id_issued_date, guardian_id_issued_place
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $minor_stmt = $conn->prepare($minor_sql);
            $minor_stmt->bind_param(
                "isssssssss", 
                $applicant_id, $guardian_name, $guardian_relationship, $guardian_phone, 
                $guardian_email, $guardian_address, $iid_type, $iiissued_number, 
                $iiissued_date, $iiissued_place
            );
            $minor_stmt->execute();
            $minor_stmt->close();
            
        } elseif ($marital_status == "Married") {
            // Insert into married table
            $spouse_name = sanitizeInput($_POST["spouse_name"]);
            
            $married_sql = "INSERT INTO married (applicant_id, spouse_name) VALUES (?, ?)";
            $married_stmt = $conn->prepare($married_sql);
            $married_stmt->bind_param("is", $applicant_id, $spouse_name);
            $married_stmt->execute();
            $married_id = $conn->insert_id;
            $married_stmt->close();
            
            // Handle children if any
            if (isset($_POST["son_name"])) {
                foreach ($_POST["son_name"] as $son_name) {
                    if (!empty($son_name)) {
                        $son_sql = "INSERT INTO children (married_id, child_name, child_type) VALUES (?, ?, 'son')";
                        $son_stmt = $conn->prepare($son_sql);
                        $son_stmt->bind_param("is", $married_id, $son_name);
                        $son_stmt->execute();
                        $son_stmt->close();
                    }
                }
            }
            
            if (isset($_POST["daughter_name"])) {
                foreach ($_POST["daughter_name"] as $daughter_name) {
                    if (!empty($daughter_name)) {
                        $daughter_sql = "INSERT INTO children (married_id, child_name, child_type) VALUES (?, ?, 'daughter')";
                        $daughter_stmt = $conn->prepare($daughter_sql);
                        $daughter_stmt->bind_param("is", $married_id, $daughter_name);
                        $daughter_stmt->execute();
                        $daughter_stmt->close();
                    }
                }
            }
            
        } else {
            // Insert into singles table
            $single_sql = "INSERT INTO singles (applicant_id) VALUES (?)";
            $single_stmt = $conn->prepare($single_sql);
            $single_stmt->bind_param("i", $applicant_id);
            $single_stmt->execute();
            $single_stmt->close();
        }
        
        // Success message
        echo "<script>alert('Registration successful!'); window.location.href='index.php';</script>";
        
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>