<?php
// Establish database connection via the central Capistra bootstrap.
require_once dirname(__DIR__, 2) . '/config/app.php';
$conn = capistra_mysqli();

if ($conn) {
    echo "<h1>Database connection success........ </h1>";

    // SQL to create tables
    $sql = [
        // Table for Investors
    
        // Table for Profit-Sharing Details
        "CREATE TABLE IF NOT EXISTS profit_sharing_details (
            id INT AUTO_INCREMENT PRIMARY KEY,
            investor_id INT NOT NULL,
            time_range VARCHAR(50) NOT NULL,
            profit_percentage DECIMAL(5, 2) NOT NULL,
            payout_frequency ENUM('Monthly', 'Quarterly', 'Yearly') NOT NULL,
            return_method ENUM('Bank Transfer', 'Digital Wallet', 'Reinvestment') NOT NULL,
            FOREIGN KEY (investor_id) REFERENCES investors(id) ON DELETE CASCADE
        )",

        // Table for Debt Details
        "CREATE TABLE IF NOT EXISTS debt_details (
            id INT AUTO_INCREMENT PRIMARY KEY,
            investor_id INT NOT NULL,
            debt_duration VARCHAR(50) NOT NULL,
            interest_rate DECIMAL(5, 2) NOT NULL,
            repayment_schedule ENUM('Monthly', 'Yearly', 'One-Time Payment') NOT NULL,
            collateral VARCHAR(255),
            total_interest DECIMAL(15, 2) NOT NULL,
            FOREIGN KEY (investor_id) REFERENCES investors(id) ON DELETE CASCADE
        )",

        // Table for Equity Details
        "CREATE TABLE IF NOT EXISTS equity_details (
            id INT AUTO_INCREMENT PRIMARY KEY,
            investor_id INT NOT NULL,
            equity_percentage DECIMAL(5, 2) NOT NULL,
            share_price DECIMAL(15, 2) NOT NULL,
            total_shares INT NOT NULL,
            dividend_policy VARCHAR(255) NOT NULL,
            voting_rights ENUM('Yes', 'No') NOT NULL,
            resale_strategy VARCHAR(255) NOT NULL,
            share_transfer ENUM('Allowed', 'Not Allowed') NOT NULL,
            FOREIGN KEY (investor_id) REFERENCES investors(id) ON DELETE CASCADE
        )"
    ];

    // Execute SQL queries to create tables
    foreach ($sql as $query) {
        if (mysqli_query($conn, $query)) {
            echo "<p>Table created successfully.</p>";
        } else {
            echo "<p>Error creating table: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    // Redirect to error page if the connection fails
    header("location: ../errors/db.php");
    exit(0);
}
?>