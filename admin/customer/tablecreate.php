<?php
// Database connection via the central Capistra bootstrap.
require_once dirname(__DIR__, 2) . '/config/app.php';
$conn = capistra_mysqli();

// SQL Queries to create tables
$sql1 = "
CREATE TABLE IF NOT EXISTS minordet (
    user_id VARCHAR(10) PRIMARY KEY,
    title VARCHAR(50),
    applicant_name VARCHAR(100),
    father_name VARCHAR(100),
    grandfather_name VARCHAR(100),
    dob_bs VARCHAR(20),
    dob_ad DATE,
    id_type VARCHAR(50),
    id_number VARCHAR(50),
    issued_date DATE,
    issued_place VARCHAR(100),
    permanent_country VARCHAR(100),
    permanent_province VARCHAR(100),
    permanent_district VARCHAR(100),
    ward_no INT,
    permanent_tole VARCHAR(100),
    temporary_district VARCHAR(100),
    temporary_province VARCHAR(100),
    temporary_ward_no INT,
    temporary_tole VARCHAR(100),
    bank_name VARCHAR(100),
    bank_account_number VARCHAR(100),
    demat_account_number VARCHAR(100),
    crn_number VARCHAR(100),
    broker_name VARCHAR(100),
    user_number VARCHAR(100),
    terms_accepted BOOLEAN,
    guardian_name VARCHAR(100),
    guardian_relationship VARCHAR(50),
    guardian_address VARCHAR(255),
    guardian_id_type VARCHAR(50),
    guardian_id_number VARCHAR(50),
    guardian_issued_date DATE,
    guardian_issued_place VARCHAR(100)
);
";

$sql2 = "
CREATE TABLE IF NOT EXISTS mariadult (
    user_id VARCHAR(10) PRIMARY KEY,
    title VARCHAR(50),
    applicant_name VARCHAR(100),
    father_name VARCHAR(100),
    grandfather_name VARCHAR(100),
    dob_bs VARCHAR(20),
    dob_ad DATE,
    id_type VARCHAR(50),
    id_number VARCHAR(50),
    issued_date DATE,
    issued_place VARCHAR(100),
    permanent_country VARCHAR(100),
    permanent_province VARCHAR(100),
    permanent_district VARCHAR(100),
    ward_no INT,
    permanent_tole VARCHAR(100),
    temporary_district VARCHAR(100),
    temporary_province VARCHAR(100),
    temporary_ward_no INT,
    temporary_tole VARCHAR(100),
    bank_name VARCHAR(100),
    bank_account_number VARCHAR(100),
    demat_account_number VARCHAR(100),
    crn_number VARCHAR(100),
    broker_name VARCHAR(100),
    user_number VARCHAR(100),
    terms_accepted BOOLEAN,
    spouse_name VARCHAR(100),
    son_name VARCHAR(100),
    daughter_name VARCHAR(100)
);
";

$sql3 = "
CREATE TABLE IF NOT EXISTS nomarriadl (
    user_id VARCHAR(10) PRIMARY KEY,
    title VARCHAR(50),
    applicant_name VARCHAR(100),
    father_name VARCHAR(100),
    grandfather_name VARCHAR(100),
    dob_bs VARCHAR(20),
    dob_ad DATE,
    id_type VARCHAR(50),
    id_number VARCHAR(50),
    issued_date DATE,
    issued_place VARCHAR(100),
    permanent_country VARCHAR(100),
    permanent_province VARCHAR(100),
    permanent_district VARCHAR(100),
    ward_no INT,
    permanent_tole VARCHAR(100),
    temporary_district VARCHAR(100),
    temporary_province VARCHAR(100),
    temporary_ward_no INT,
    temporary_tole VARCHAR(100),
    bank_name VARCHAR(100),
    bank_account_number VARCHAR(100),
    demat_account_number VARCHAR(100),
    crn_number VARCHAR(100),
    broker_name VARCHAR(100),
    user_number VARCHAR(100),
    terms_accepted BOOLEAN
);
";

// Execute the SQL queries
if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE && $conn->query($sql3) === TRUE) {
    echo "Tables created successfully!";
} else {
    echo "Error creating tables: " . $conn->error;
}

// Close the connection
$conn->close();
?>
