<?php
require_once dirname(__DIR__, 3) . '/protect/session_check.php'; // Capistra auth guard
session_start(); // Start the session

// Include Composer autoloader
require_once __DIR__ . '/../../../config/autoload.php';

// Include DB connection
include('../../config/dbcon.php');

// Check if investor ID is provided
if (isset($_GET['id'])) {
    $investorId = $_GET['id'];

    // Fetch investor details from the database
    $sql = "SELECT * FROM investors WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $investorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $investor = $result->fetch_assoc();

    if ($investor) {
        // Generate PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Add Background Gradient (Optional)
        $pdf->SetFillColor(245, 248, 250); // Very light blue-gray background
        $pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight(), 'F');

        // Add Logo (Left Side)
        $pdf->Image('../assets/dist/img/middlelogo.png', 10, 10, 30); // Replace with your logo path

        // Add Company Name and PAN (Center)
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(0, 102, 102); // Dark teal color for the company name
        $pdf->Cell(0, 10, 'Capistra', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(85, 85, 85); // Dark gray color for the address
        $pdf->Cell(0, 10, 'Company address (configure in Settings)', 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 14);
        $pdf->SetTextColor(220, 53, 69); // Red color for the tax id
        $pdf->Cell(0, 10, 'Tax ID: (configure in Settings)', 0, 1, 'C');
        $pdf->Ln(15); // Add vertical space

        // Add Divider Line
        $pdf->SetDrawColor(200, 200, 200); // Light gray color for the line
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Draw a horizontal line
        $pdf->Ln(15); // Add vertical space

        // Add Title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetFillColor(0, 102, 102); // Dark teal background for the title
        $pdf->SetTextColor(255, 255, 255); // White text color
        $pdf->Cell(0, 15, 'Investor Details', 0, 1, 'C', true);
        $pdf->Ln(15); // Add vertical space

        // Add Investor Details
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Black text color

        $pdf->Cell(50, 10, 'Investor ID:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['id'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Name:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['name'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Contact Phone:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['contact_phone'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Contact Email:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['contact_email'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Address:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['address'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Nationality:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['nationality'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Citizenship Number:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['citizenship_number'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Date of Investment:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['date_of_investment'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Investment Type:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['investment_type'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Investment Amount:', 0, 0, 'L');
        $pdf->Cell(0, 10, '$' . $investor['investment_amount'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'KYC Status:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['kyc_status'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Bank Account Number:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['bank_account_number'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Bank Name:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['bank_name'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Nominee Name:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['nominee_name'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Relationship:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['relationship'], 0, 1, 'L');
        $pdf->Cell(50, 10, 'Investment Risk:', 0, 0, 'L');
        $pdf->Cell(0, 10, $investor['investment_risk'], 0, 1, 'L');

        // Output PDF
        $pdf->Output('D', 'investor_details_' . $investor['id'] . '.pdf'); // Download the PDF
        exit;
    } else {
        echo "Investor not found.";
    }
} else {
    echo "Invalid request.";
}
?>