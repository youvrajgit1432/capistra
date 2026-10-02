<?php
 require_once('../protect/session_check.php');
// Database connection uses the central Capistra bootstrap.
$conn = capistra_mysqli();

// SQL to create the employees table with extended fields
$sql = "CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marital_status VARCHAR(20),
    nationality VARCHAR(50),
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    emergency_contact VARCHAR(100),
    emergency_number VARCHAR(15),
    position VARCHAR(50) NOT NULL,
    department VARCHAR(50) NOT NULL,
    join_date DATE NOT NULL,
    citizenship_number VARCHAR(50),
    citizenship_issue_date DATE,
    citizenship_issue_place VARCHAR(100),
    permanent_address TEXT,
    current_address TEXT,
    assigned_assets TEXT,
    bank_account_number VARCHAR(50),
    pan_number VARCHAR(50),
    education TEXT,
    previous_experience TEXT,
    resume_path VARCHAR(255),
    reference_person VARCHAR(100),
    leave_records TEXT,
    salary_info TEXT,
    photo_path VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "Table 'employees' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

// Close connection
$conn->close();
?>
