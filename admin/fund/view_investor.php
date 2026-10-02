<?php
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
include('../head/header.php');
include('../config/dbcon.php'); // Include your database connection file

// Check if the investor ID is provided in the query string
if (!isset($_GET['id'])) {
    header("Location: ../investorprofile.php");
    exit();
}

$id = $_GET['id'];

// Fetch investor details from the database
$sql = "SELECT * FROM investors WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // If no investor is found, redirect to the investor profiles page
    header("Location: ../investorprofile.php");
    exit();
}

$investor = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            text-align: center;
            padding: 20px;
            background-color: #ffffff;
            border-bottom: 2px solid #eeeeee;
        }
        .header img {
            width: 80px;
            height: auto;
        }
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #333333;
            margin: 10px 0 5px 0;
        }
        .header p {
            font-size: 14px;
            color: #555555;
            margin: 0;
        }
        .investor-details-container {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 25px;
            max-width: 800px;
            margin: 20px auto;
        }
        .investor-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .investor-header h2 {
            color: #333333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #555555;
            font-weight: bold;
            flex: 1;
        }
        .info-value {
            color: #333333;
            flex: 2;
            text-align: right;
        }
        .info-row:hover {
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .btn-custom {
            margin: 5px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
        }
        .btn-edit {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
        }
        .btn-export {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white;
        }
        .btn-back {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #ffffff;
            border-top: 2px solid #eeeeee;
            margin-top: 20px;
        }
        .footer p {
            font-size: 14px;
            color: #555555;
            margin: 0;
        }
    </style>
</head>
<body><div class="content-wrapper">
    <!-- Header Section -->
    <div class="header">
       
        <h1>Capistra</h1>
        <p>Company address (configure in Settings)</p>
        <hr>
    </div>

    <!-- Investor Details Section -->
    <div class="container mt-5">
        <div class="investor-details-container">
            <div class="investor-header">
                <h2>Investor Details</h2>
                <div class="d-flex justify-content-center gap-3">
                    <a href="edit_investor.php?id=<?= $investor['id'] ?>" class="btn btn-edit btn-custom">
                        <i class="fas fa-edit"></i> Edit Investor
                    </a>
                    <button onclick="exportToPDF()" class="btn btn-export btn-custom">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
            <div class="investor-info">
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?= $investor['name'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Investor ID:</span>
                    <span class="info-value"><?= $investor['id'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact Phone:</span>
                    <span class="info-value"><?= $investor['contact_phone'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact Email:</span>
                    <span class="info-value"><?= $investor['contact_email'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?= $investor['address'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nationality:</span>
                    <span class="info-value"><?= $investor['nationality'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Citizenship Number:</span>
                    <span class="info-value"><?= $investor['citizenship_number'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Investment:</span>
                    <span class="info-value"><?= $investor['date_of_investment'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Investment Type:</span>
                    <span class="info-value"><?= $investor['investment_type'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Investment Amount:</span>
                    <span class="info-value">$<?= $investor['investment_amount'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">KYC Status:</span>
                    <span class="info-value"><?= $investor['kyc_status'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Bank Account Number:</span>
                    <span class="info-value"><?= $investor['bank_account_number'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Bank Name:</span>
                    <span class="info-value"><?= $investor['bank_name'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nominee Name:</span>
                    <span class="info-value"><?= $investor['nominee_name'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Relationship:</span>
                    <span class="info-value"><?= $investor['relationship'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Investment Risk:</span>
                    <span class="info-value"><?= $investor['investment_risk'] ?></span>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="investorprofile.php" class="btn btn-back btn-custom">
                <i class="fas fa-arrow-left"></i> Back to Investor Profiles
            </a>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <hr>
        <p>Your Trust, Our Commitment - Capistra</p>
    </div>
    </div>
    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Function to export investor details as PDF
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Add header
            doc.setFontSize(20);
            doc.text("Capistra", 10, 15);
            doc.setFontSize(12);
            doc.text("Tax ID: (configure in Settings)", 10, 25);
            doc.line(10, 30, 200, 30); // Horizontal line

            // Add title
            doc.setFontSize(18);
            doc.text("Investor Details", 10, 40);

            // Add investor details
            doc.setFontSize(12);
            let y = 50;
            
            const investorData = [
                ["Name", "<?= $investor['name'] ?>"],
                ["Investor ID", "<?= $investor['id'] ?>"],
                ["Contact Phone", "<?= $investor['contact_phone'] ?>"],
                ["Contact Email", "<?= $investor['contact_email'] ?>"],
                ["Address", "<?= $investor['address'] ?>"],
                ["Nationality", "<?= $investor['nationality'] ?>"],
                ["Citizenship Number", "<?= $investor['citizenship_number'] ?>"],
                ["Date of Investment", "<?= $investor['date_of_investment'] ?>"],
                ["Investment Type", "<?= $investor['investment_type'] ?>"],
                ["Investment Amount", "$<?= $investor['investment_amount'] ?>"],
                ["KYC Status", "<?= $investor['kyc_status'] ?>"],
                ["Bank Account Number", "<?= $investor['bank_account_number'] ?>"],
                ["Bank Name", "<?= $investor['bank_name'] ?>"],
                ["Nominee Name", "<?= $investor['nominee_name'] ?>"],
                ["Relationship", "<?= $investor['relationship'] ?>"],
                ["Investment Risk", "<?= $investor['investment_risk'] ?>"]
            ];

            investorData.forEach(([label, value]) => {
                doc.text(`${label}: ${value}`, 10, y);
                y += 10;
            });

            // Add footer
            doc.line(10, y + 10, 200, y + 10); // Horizontal line
            doc.setFontSize(12);
            doc.text("Your Trust, Our Commitment - Capistra", 10, y + 20);

            // Save the PDF
            doc.save(`Investor_Details_<?= $investor['name'] ?>.pdf`);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include('../head/footer.php');
?>