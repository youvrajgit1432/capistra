<?php
  require_once('../../protect/session_check.php');

// Include Composer autoloader
require __DIR__ . '/../../vendor/autoload.php';

// Handle PDF Export
if (isset($_GET['export_pdf'])) {
    // Include DB connection
    include('../config/dbcon.php');

    // Fetch the current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');

    // Initialize session variables if not set
    if (!isset($_SESSION['startMonth'])) {
        $_SESSION['startMonth'] = $currentMonth;
        $_SESSION['startYear'] = $currentYear;
        $_SESSION['endMonth'] = $currentMonth;
        $_SESSION['endYear'] = $currentYear;
    }

    // Set filter values from session
    $startMonth = $_SESSION['startMonth'];
    $startYear = $_SESSION['startYear'];
    $endMonth = $_SESSION['endMonth'];
    $endYear = $_SESSION['endYear'];

    // Fetch filtered data
    $startDate = "$startYear-$startMonth-01";
    $endDate = "$endYear-$endMonth-31";
    $query = "SELECT * FROM expenses WHERE date BETWEEN ? AND ? ORDER BY date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $expenses = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Group expenses by month and year
    $monthlyExpenses = [];
    foreach ($expenses as $expense) {
        $monthYear = date('F Y', strtotime($expense['date']));
        if (!isset($monthlyExpenses[$monthYear])) {
            $monthlyExpenses[$monthYear] = [];
        }
        $monthlyExpenses[$monthYear][] = $expense;
    }

    // Generate PDF
    $pdf = new FPDF(); // Correctly instantiate FPDF
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
    $pdf->SetTextColor(220, 53, 69); // Red color for the PAN
    $pdf->Cell(0, 10, 'PAN No: (configure in Settings)', 0, 1, 'C');
    $pdf->Ln(15); // Add vertical space

    // Add Divider Line
    $pdf->SetDrawColor(200, 200, 200); // Light gray color for the line
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Draw a horizontal line
    $pdf->Ln(15); // Add vertical space

    // Add Title
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetFillColor(0, 102, 102); // Dark teal background for the title
    $pdf->SetTextColor(255, 255, 255); // White text color
    $pdf->Cell(0, 15, 'Expense Report', 0, 1, 'C', true);
    $pdf->Ln(15); // Add vertical space

    // Loop through monthly expenses
    foreach ($monthlyExpenses as $monthYear => $expenses) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 102, 102); // Dark teal color for the section title
        $pdf->Cell(0, 10, "Expense Records for $monthYear", 0, 1, 'C');
        $pdf->Ln(5);

        // Table Header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(0, 102, 102); // Dark teal background for headers
        $pdf->SetTextColor(255, 255, 255); // White text color

        // Center the table
        $tableWidth = 150; // Total width of the table
        $tableX = ($pdf->GetPageWidth() - $tableWidth) / 2; // Calculate X position to center the table
        $pdf->SetX($tableX); // Set X position for the table

        $pdf->Cell(20, 10, 'SN', 1, 0, 'C', true);
        $pdf->Cell(50, 10, 'Category', 1, 0, 'C', true);
        $pdf->Cell(40, 10, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 10, 'Amount', 1, 1, 'C', true);

        // Table Data
        $sn = 1;
        $totalMonthlyExpense = 0; // Initialize total expense for the month
        foreach ($expenses as $expense) {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(255, 255, 255); // White background for data rows
            $pdf->SetTextColor(0, 0, 0); // Black text color

            // Center the table
            $pdf->SetX($tableX); // Set X position for the table

            $pdf->Cell(20, 10, $sn, 1, 0, 'C', true);
            $pdf->Cell(50, 10, $expense['category'], 1, 0, 'L', true);
            $pdf->Cell(40, 10, $expense['date'], 1, 0, 'C', true);
            $pdf->Cell(40, 10, formatNepaliRupees($expense['amount']), 1, 1, 'R', true);
            $sn++;

            // Add to total monthly expense
            $totalMonthlyExpense += $expense['amount'];
        }

        // Display total expense for the month
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(245, 245, 245); // Light gray background for the total row
        $pdf->SetTextColor(0, 102, 102); // Dark teal color for the total text
        $pdf->SetX($tableX); // Center the table
        $pdf->Cell(110, 10, 'Total Expense for ' . $monthYear, 1, 0, 'R', true);
        $pdf->Cell(40, 10, formatNepaliRupees($totalMonthlyExpense), 1, 1, 'R', true);
        $pdf->Ln(10);
    }

    // Add Total Expense for the Year
    $totalExpenseForYear = 0;
    foreach ($monthlyExpenses as $monthYear => $expenses) {
        $totalExpenseForYear += getTotalExpense($conn, date('m', strtotime($expenses[0]['date'])), date('Y', strtotime($expenses[0]['date'])));
    }
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 102, 102); // Dark teal color for the total expense
    $pdf->Cell(0, 10, "Total Expense for the Year ($startYear - $endYear): " . formatNepaliRupees($totalExpenseForYear), 0, 1, 'C');
    $pdf->Ln(10);

    // Add Footer with Dark Teal Background
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetFillColor(0, 102, 102); // Dark teal background for the footer
    $pdf->SetTextColor(255, 255, 255); // White text color
    $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C', true);
    $pdf->Cell(0, 10, 'Capistra | PAN No: (configure in Settings)', 0, 1, 'C', true);

    // Output PDF
    $pdf->Output('D', 'expense_report.pdf'); // Download the PDF
    exit;
}

// Include header after handling PDF export
include('../head/header.php');
include('../config/dbcon.php'); // Include DB connection

// Function to format amounts in Nepali Rupees format (with commas)
function formatNepaliRupees($amount) {
    return "Rs " . number_format($amount);
}

// Function to calculate total expense for a specific month and year
function getTotalExpense($conn, $month, $year) {
    $query = "SELECT SUM(amount) AS total_expense FROM expenses WHERE MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_expense'] ?: 0;
}

// Fetch the current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Initialize session variables if not set
if (!isset($_SESSION['startMonth'])) {
    $_SESSION['startMonth'] = $currentMonth;
    $_SESSION['startYear'] = $currentYear;
    $_SESSION['endMonth'] = $currentMonth;
    $_SESSION['endYear'] = $currentYear;
}

// Handle filter submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['previous_month'])) {
        // If "Previous Month" button is clicked, set filter to previous month
        if ($_SESSION['startMonth'] == 1) {
            $_SESSION['startMonth'] = 12;
            $_SESSION['startYear']--;
        } else {
            $_SESSION['startMonth']--;
        }
        $_SESSION['endMonth'] = $_SESSION['startMonth'];
        $_SESSION['endYear'] = $_SESSION['startYear'];
    } elseif (isset($_POST['reset'])) {
        // If "Reset" button is clicked, reset to current month and year
        $_SESSION['startMonth'] = $currentMonth;
        $_SESSION['startYear'] = $currentYear;
        $_SESSION['endMonth'] = $currentMonth;
        $_SESSION['endYear'] = $currentYear;
    } else {
        // Otherwise, use the selected filter values
        $_SESSION['startMonth'] = isset($_POST['start_month']) ? $_POST['start_month'] : $currentMonth;
        $_SESSION['startYear'] = isset($_POST['start_year']) ? $_POST['start_year'] : $currentYear;
        $_SESSION['endMonth'] = isset($_POST['end_month']) ? $_POST['end_month'] : $currentMonth;
        $_SESSION['endYear'] = isset($_POST['end_year']) ? $_POST['end_year'] : $currentYear;
    }
}

// Set filter values from session
$startMonth = $_SESSION['startMonth'];
$startYear = $_SESSION['startYear'];
$endMonth = $_SESSION['endMonth'];
$endYear = $_SESSION['endYear'];
 // Handle the submission of the "Add Expense" form
if (isset($_POST['add_expense'])) {
    $category = $_POST['category'];
    $date = $_POST['date'];
    $amount = $_POST['amount'];
    $remarks = $_POST['remarks'];

    // File upload handling
    if ($_FILES['bill_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES['bill_file']['name']);
        $file_tmp = $_FILES['bill_file']['tmp_name'];
        $file_size = $_FILES['bill_file']['size'];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $message = "Error: Only JPG, JPEG, PNG, GIF, and PDF files are allowed.";
        }
        // Validate file size
        elseif ($file_size > $max_file_size) {
            $message = "Error: File size must be less than 5MB.";
        }
        // Sanitize file name
        else {
            $sanitized_file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file_name);
            $target_file = $target_dir . $sanitized_file_name;

            // Ensure the uploads directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            // Move the uploaded file
            if (move_uploaded_file($file_tmp, $target_file)) {
                // Insert into the database
                $query = "INSERT INTO expenses (category, date, amount, bill_file,remarks) VALUES (?, ?, ?, ?,?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssiss', $category, $date, $amount, $sanitized_file_name,$remarks);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Expense added successfully!";
                } else {
                    $message = "Error adding expense: " . mysqli_error($conn);
                }
            } else {
                $message = "Error uploading file.";
            }
        }
    } else {
        $message = "File upload error: " . $_FILES['bill_file']['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- For Charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> <!-- For PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script> <!-- For Excel Export -->
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border: none;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            border: none;
        }
        .btn-warning {
            background: linear-gradient(135deg, #f9d423, #ff4e50);
            border: none;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
<div class="content-wrapper">
<div class="container mt-5">
    <!-- Success/Error Message -->
    <?php if (isset($message)) { ?>
        <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <!-- Add Expense Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Expense Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expenseModal">
            <i class="fas fa-plus"></i> Add Expense
        </button>
    </div>

    <!-- Filter Section -->
    <form method="POST" class="mb-4 card p-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Start Month</label>
                <select class="form-select" name="start_month">
                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $startMonth ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $i, 10)); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Start Year</label>
                <select class="form-select" name="start_year">
                    <?php for ($i = 2024; $i <= 2025; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $startYear ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">End Month</label>
                <select class="form-select" name="end_month">
                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $endMonth ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $i, 10)); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">End Year</label>
                <select class="form-select" name="end_year">
                    <?php for ($i = 2024; $i <= 2025; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $endYear ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-12 text-center mt-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <button type="submit" name="previous_month" class="btn btn-secondary">View Previous Month</button>
                <button type="submit" name="reset" class="btn btn-info">Reset to Current Month</button>
            </div>
        </div>
    </form>

    <!-- Search Bar -->
    <div class="mb-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by category, date, or amount...">
    </div>

    <!-- Export Buttons -->
    <div class="mb-4">
        <a href="?export_pdf=1" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Export to PDF
        </a>
        <button class="btn btn-success" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export to Excel</button>
    </div>

    <!-- Expense Records by Month -->
    <div id="pdfContent">
        <?php
        $startDate = "$startYear-$startMonth-01";
        $endDate = "$endYear-$endMonth-31";
        $query = "SELECT * FROM expenses WHERE date BETWEEN ? AND ? ORDER BY date DESC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Group expense records by month and year
        $monthlyExpenses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $monthYear = date('F Y', strtotime($row['date']));
            if (!isset($monthlyExpenses[$monthYear])) {
                $monthlyExpenses[$monthYear] = [];
            }
            $monthlyExpenses[$monthYear][] = $row;
        }

        // Display expense records for each month
        $totalExpenseForYear = 0; // Initialize total expense for the year
        if (empty($monthlyExpenses)) {
            echo "<div class='alert alert-warning'>No data available for the selected month.</div>";
        } else {
            foreach ($monthlyExpenses as $monthYear => $expenses) {
                $totalExpenseForMonth = getTotalExpense($conn, date('m', strtotime($expenses[0]['date'])), date('Y', 
                strtotime($expenses[0]['date'])));
                $totalExpenseForYear += $totalExpenseForMonth; // Add to yearly total

                echo "<div class='card mb-4'>
                        <div class='card-header'>
                            <h4>Expense Records for $monthYear</h4>
                        </div>
                        <div class='card-body'>
                            <table class='table table-hover'>
                                <thead>
                                    <tr>
                                        <th>SN</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Bill File</th>
                                           <th>Remarks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>";
                $sn = 1;
                foreach ($expenses as $expense) {
                    echo "<tr>
                            <td>$sn</td>
                            <td>{$expense['category']}</td>
                            <td>{$expense['date']}</td>
                            <td>" . formatNepaliRupees($expense['amount']) . "</td>
                            <td><a href='uploads/{$expense['bill_file']}' target='_blank'>View Bill</a></td>
                            <td>{$expense['remarks']}</td>
                            <td>
                                <a href='expense_actions.php?edit_id={$expense['id']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a>
                                <a href='expense_actions.php?delete_id={$expense['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this expense?\")'><i class='fas fa-trash'></i></a>
                            </td>
                          </tr>";
                    $sn++;
                }
                echo "<tr>
                        <td colspan='3'><strong>Total Expense for $monthYear</strong></td>
                        <td colspan='3'>" . formatNepaliRupees($totalExpenseForMonth) . "</td>
                      </tr>";
                echo "</tbody>
                      </table>
                      </div>
                      </div>";
            }

            // Display total expense for the year
            echo "<div class='card mb-4'>
                    <div class='card-header'>
                        <h4>Total Expense for the Selected Time frame is  ($startYear - $endYear)</h4>
                    </div>
                    <div class='card-body'>
                        <p class='h4'>" . formatNepaliRupees($totalExpenseForYear) . "</p>
                    </div>
                  </div>";
        }
        ?>
    </div>

    <!-- Comparison Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Current Month vs Last Month</h4>
                </div>
                <div class="card-body">
                    <canvas id="monthComparisonChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>This Year vs Last Year</h4>
                </div>
                <div class="card-body">
                    <canvas id="yearComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
 <!-- Add Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-lg shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="expenseModalLabel"><i class="fas fa-plus-circle me-2"></i>Add Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
            <form method="POST" enctype="multipart/form-data">
    <div class="mb-4">
        <label for="category" class="form-label fw-bold"><i class="fas fa-folder-open me-2"></i>Category</label>
        <select class="form-select form-select-lg" name="category" required>
            <option value="Office Rent">Office Rent</option>
            <option value="Internet Bills">Internet Bills</option>
            <option value="Utilities">Utilities</option>
            <option value="Employee Salaries">Employee Salaries</option>
            <option value="Marketing and Advertising">Marketing and Advertising</option>
            <option value="Travel and Accommodation">Travel and Accommodation</option>
            <option value="Office Supplies">Office Supplies</option>
            <option value="Software Subscriptions">Software Subscriptions</option>
            <option value="Professional Services">Professional Services (Legal, Accounting, etc.)</option>
            <option value="Insurance">Insurance</option>
            <option value="Maintenance and Repairs">Maintenance and Repairs</option>
            <option value="Bank Fees">Bank Fees</option>
            <option value="Investment Costs">Investment Costs</option>
            <option value="Training and Development">Training and Development</option>
            <option value="Client Entertainment">Client Entertainment</option>
            <option value="Technology Upgrades">Technology Upgrades</option>
            <option value="Data and Research">Data and Research</option>
            <option value="Compliance and Regulatory Fees">Compliance and Regulatory Fees</option>
            <option value="Taxes">Taxes</option>
            <option value="Employee Benefits">Employee Benefits</option>
            <option value="Office Refreshments">Office Refreshments</option>
            <option value="Event Sponsorships">Event Sponsorships</option>
            <option value="Charitable Donations">Charitable Donations</option>
            <option value="Security Services">Security Services</option>
            <option value="Telecommunications">Telecommunications</option>
            <option value="Printing and Stationery">Printing and Stationery</option>
            <option value="Vehicle Expenses">Vehicle Expenses</option>
            <option value="Consultancy Fees">Consultancy Fees</option>
            <option value="Miscellaneous">Miscellaneous</option>
        </select>
    </div>
    <div class="mb-4">
        <label for="date" class="form-label fw-bold"><i class="fas fa-calendar-alt me-2"></i>Date</label>
        <input type="date" class="form-control form-control-lg" name="date" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    <div class="mb-4">
        <label for="amount" class="form-label fw-bold">  <i class="fas fa-money-bill-wave me-2"></i>Amount</label>
        <input type="number" class="form-control form-control-lg" name="amount" step="0.01" required>
    </div>
    <div class="mb-3">
        <label for="bill_file" class="form-label">
            <i class="fas fa-file-upload me-2"></i>Upload Bill (PDF/Image)
        </label>
        <input type="file" class="form-control form-control-sm" name="bill_file" accept="image/*,application/pdf">
        <small class="form-text text-muted">
            Current file: <a href="uploads/<?php echo $expense['bill_file']; ?>" target="_blank">View File</a>
        </small>
    </div>
    <div class="mb-4">
        <label for="remarks" class="form-label fw-bold"><i class="fas fa-comment me-2"></i>Remarks</label>
        <textarea class="form-control form-control-lg" name="remarks" rows="3" placeholder="Enter any additional remarks or comments"></textarea>
    </div>
    <div class="d-grid">
        <button type="submit" name="add_expense" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Add Expense</button>
    </div>
</form>
            </div>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search Functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.table-hover tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Export to Excel
    function exportToExcel() {
        const table = document.querySelector('.table');
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Expense Data" });

        // Create a new worksheet with additional formatting
        const ws = workbook.Sheets["Expense Data"];
        ws['!cols'] = [{ wch: 10 }, { wch: 20 }, { wch: 15 }, { wch: 15 }, { wch: 20 }, { wch: 15 }];

        // Add header styling
        const headerRange = XLSX.utils.decode_range(ws['!ref']);
        for (let C = headerRange.s.c; C <= headerRange.e.c; ++C) {
            const cellAddress = XLSX.utils.encode_cell({ r: headerRange.s.r, c: C });
            if (!ws[cellAddress]) continue;
            ws[cellAddress].s = { font: { bold: true }, fill: { fgColor: { rgb: "2575fc" } } };
        }

        // Save the Excel file
        XLSX.writeFile(workbook, 'expense_report.xlsx');
    }

    // Charts
    const monthComparisonCtx = document.getElementById('monthComparisonChart').getContext('2d');
    const yearComparisonCtx = document.getElementById('yearComparisonChart').getContext('2d');

    new Chart(monthComparisonCtx, {
        type: 'bar',
        data: {
            labels: ['Current Month', 'Last Month'],
            datasets: [{
                label: 'Expense',
                data: [<?php echo getTotalExpense($conn, $currentMonth, $currentYear); ?>, <?php echo getTotalExpense($conn, $currentMonth - 1, $currentYear); ?>],
                backgroundColor: ['#6a11cb', '#2575fc']
            }]
        }
    });

    new Chart(yearComparisonCtx, {
        type: 'bar',
        data: {
            labels: ['This Year', 'Last Year'],
            datasets: [{
                label: 'Expense',
                data: [<?php echo getTotalExpense($conn, null, $currentYear); ?>, <?php echo getTotalExpense($conn, null, $currentYear - 1); ?>],
                backgroundColor: ['#f9d423', '#ff4e50']
            }]
        }
    });
</script>
</body>
</html>

<?php
include('../head/footer.php');
?>