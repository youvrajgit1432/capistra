<?php
 require_once('../../protect/session_check.php');

// Inclsession_start(); // Start the sessionude Composer autoloader
require_once __DIR__ . '/../../config/autoload.php';
require_once __DIR__ . '/../lib/admin.php';

// Include DB connection
include('../config/dbcon.php');

// Function to calculate total income for a specific year (ignoring month filter)
function getTotalIncomeForYear($conn, $year) {
    $query = "SELECT SUM(amount) AS total_income FROM income WHERE YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_income'] ?: 0; // Return 0 if no income found
}

// Function to calculate total income for a specific month and year
function getTotalIncome($conn, $month, $year) {
    $query = "SELECT SUM(amount) AS total_income FROM income WHERE MONTH(date) = ? AND YEAR(date) = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total_income'] ?: 0; // Return 0 if no income found
}

// Function to format amount with commas in Nepali currency style
function formatNepaliCurrency($amount) {
    return "Rs " . number_format($amount);
}

// Handle PDF Export
if (isset($_GET['export_pdf'])) {
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

    // Fetch filtered data for the selected month range
    $startDate = "$startYear-$startMonth-01";
    $endDate = "$endYear-$endMonth-31";
    $query = "SELECT * FROM income WHERE date BETWEEN ? AND ? ORDER BY date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $incomes = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Group incomes by month and year
    $monthlyIncomes = [];
    foreach ($incomes as $income) {
        $monthYear = date('F Y', strtotime($income['date']));
        if (!isset($monthlyIncomes[$monthYear])) {
            $monthlyIncomes[$monthYear] = [];
        }
        $monthlyIncomes[$monthYear][] = $income;
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
    $pdf->Cell(0, 15, 'Income Report', 0, 1, 'C', true);
    $pdf->Ln(15); // Add vertical space

    // Loop through monthly incomes
    foreach ($monthlyIncomes as $monthYear => $incomes) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 102, 102); // Dark teal color for the section title
        $pdf->Cell(0, 10, "Income Records for $monthYear", 0, 1, 'C');
        $pdf->Ln(5);

        // Table Header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(0, 102, 102); // Dark teal background for headers
        $pdf->SetTextColor(255, 255, 255); // White text color

        // Calculate table width and X position to center the table
        $tableWidth = 150; // Total width of the table (20 + 50 + 40 + 40)
        $tableX = ($pdf->GetPageWidth() - $tableWidth) / 2; // Center the table horizontally
        $pdf->SetX($tableX); // Set X position for the table

        $pdf->Cell(20, 10, 'SN', 1, 0, 'C', true);
        $pdf->Cell(50, 10, 'Category', 1, 0, 'C', true);
        $pdf->Cell(40, 10, 'Date', 1, 0, 'C', true);
        $pdf->Cell(40, 10, 'Amount', 1, 1, 'C', true);

        // Table Data
        $sn = 1;
        foreach ($incomes as $income) {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(255, 255, 255); // White background for data rows
            $pdf->SetTextColor(0, 0, 0); // Black text color

            // Set X position for the table data rows
            $pdf->SetX($tableX); // Center the table horizontally

            $pdf->Cell(20, 10, $sn, 1, 0, 'C', true);
            $pdf->Cell(50, 10, $income['category'], 1, 0, 'L', true);
            $pdf->Cell(40, 10, $income['date'], 1, 0, 'C', true);
            $pdf->Cell(40, 10, formatNepaliCurrency($income['amount']), 1, 1, 'R', true);
            $sn++;
        }
        $pdf->Ln(10);
    }

    // Add Total Income for the Year (based on the selected year, ignoring month filter)
    $totalIncomeForYear = getTotalIncomeForYear($conn, $startYear);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 102, 102); // Dark teal color for the total income
    $pdf->Cell(0, 10, "Total Income for the Year ($startYear): " . formatNepaliCurrency($totalIncomeForYear), 0, 1, 'C');
    $pdf->Ln(10);

    // Add Footer with Dark Teal Background
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetFillColor(0, 102, 102); // Dark teal background for the footer
    $pdf->SetTextColor(255, 255, 255); // White text color
    $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C', true);
    $pdf->Cell(0, 10, 'Capistra | PAN No: (configure in Settings)', 0, 1, 'C', true);

    // Output PDF
    $pdf->Output('D', 'income_report.pdf'); // Download the PDF
    exit;
}

// Include header after handling PDF export
include('../head/header.php');
include('../head/topbar.php');
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
        // Update end_month and end_year to match start_month and start_year
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

// Handle the submission of the "Add Income" form
if (isset($_POST['add_income'])) {
    $category = $_POST['category'];
    $date = $_POST['date'];
    $amount = $_POST['amount'];
    $bill_file = $_FILES['bill_file']['name'];
    $remarks = $_POST['remarks'];
    $financialAccountId = (int) ($_POST['financial_account_id'] ?? 0);

    // Validate file upload
    if ($_FILES['bill_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($bill_file);

        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['bill_file']['tmp_name'], $target_file)) {
            // Insert into the database
            $query = "INSERT INTO income (category, date, amount, bill_file, remarks, financial_account_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            $accountParam = $financialAccountId > 0 ? $financialAccountId : null;
            mysqli_stmt_bind_param($stmt, 'ssdssi', $category, $date, $amount, $bill_file, $remarks, $accountParam);
            if (mysqli_stmt_execute($stmt)) {
                // Set success message in session
                $_SESSION['message'] = "Income added successfully!";
                // Redirect to avoid form resubmission
           
            } else {
                $_SESSION['message'] = "Error adding income: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['message'] = "Error uploading file.";
        }
    } else {
        $_SESSION['message'] = "File upload error.";
    }
}

// Display success/error message from session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- For Charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> <!-- For PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script> <!-- For Excel Export -->
    <style>
        .modal-body{
            border-radius: 30px;
        }
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
<body><div class="content-wrapper">
<div class="container mt-5">
    <!-- Success/Error Message -->
    <?php if (isset($message)) { ?>
        <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <!-- Add Income Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Income Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#incomeModal">
            <i class="fas fa-plus"></i> Add Income
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

    <!-- Income Records by Month -->
    <div id="pdfContent">
        <?php
        $startDate = "$startYear-$startMonth-01";
        $endDate = "$endYear-$endMonth-31";
        $query = "SELECT * FROM income WHERE date BETWEEN ? AND ? ORDER BY date DESC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Group income records by month and year
        $monthlyIncomes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $monthYear = date('F Y', strtotime($row['date']));
            if (!isset($monthlyIncomes[$monthYear])) {
                $monthlyIncomes[$monthYear] = [];
            }
            $monthlyIncomes[$monthYear][] = $row;
        }

        // Display income records for each month
        $totalIncomeForYear = 0; // Initialize total income for the year
        if (empty($monthlyIncomes)) {
            echo "<div class='alert alert-warning'>No data available for the selected month.</div>";
        } else {
            foreach ($monthlyIncomes as $monthYear => $incomes) {
                $totalIncomeForMonth = getTotalIncome($conn, date('m', strtotime($incomes[0]['date'])), date('Y', strtotime($incomes[0]['date'])));
                $totalIncomeForYear += $totalIncomeForMonth; // Add to yearly total

                echo "<div class='card mb-4'>
                        <div class='card-header'>
                            <h4>Income Records for $monthYear</h4>
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
                foreach ($incomes as $income) {
                    echo "<tr>
                            <td>$sn</td>
                            <td>{$income['category']}</td>
                            <td>{$income['date']}</td>
                            <td>" . formatNepaliCurrency($income['amount']) . "</td>
                            <td><a href='uploads/{$income['bill_file']}' target='_blank'>View Bill</a></td>
                                  <td>{$income['remarks']}</td>
                            <td>
                                <a href='income_actions.php?edit_id={$income['id']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a>
                                <a href='income_actions.php?delete_id={$income['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this income?\")'><i class='fas fa-trash'></i></a>
                            </td>
                          </tr>";
                    $sn++;
                }
                echo "<tr>
                        <td colspan='3'><strong>Total Income for $monthYear</strong></td>
                        <td colspan='3'>" . formatNepaliCurrency($totalIncomeForMonth) . "</td>
                      </tr>";
                echo "</tbody>
                      </table>
                      </div>
                      </div>";
            }

            // Display total income for the year
            echo "<div class='card mb-4'>
                    <div class='card-header'>
                        <h4>Total Income for the Selected Time frame is ($startYear - $endYear)</h4>
                    </div>
                    <div class='card-body'>
                        <p class='h4'>" . formatNepaliCurrency($totalIncomeForYear) . "</p>
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
</div> <!-- Add Income Modal -->
<div class="modal fade" id="incomeModal" tabindex="-1" aria-labelledby="incomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <!-- Modal Header with Gradient Background -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="incomeModalLabel">
                    <i class="fas fa-coins me-2"></i> <!-- Font Awesome Icon -->
                    Add Income
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="incomeForm">
                    <!-- Category Dropdown -->
                    <div class="mb-3">
                        <label for="category" class="form-label fw-bold">
                            <i class="fas fa-list-alt me-2"></i> <!-- Font Awesome Icon -->
                            Category
                        </label>
                        <select class="form-select" name="category" required>
                            <?php foreach (capistra_categories('income', true) as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-text">Categories are managed in Settings &rarr; Categories.</p>
                    </div>
                    <!-- Date Input -->
                    <div class="mb-3">
                        <label for="date" class="form-label fw-bold">
                            <i class="fas fa-calendar-alt me-2"></i> <!-- Font Awesome Icon -->
                            Date
                        </label>
                        <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <!-- Amount Input -->
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-bold">
                            <i class="fas fa-money-bill-wave me-2"></i> <!-- Font Awesome Icon -->
                            Amount
                        </label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="bill_file" class="form-label fw-bold">
                            <i class="fas fa-file-upload me-2"></i> <!-- Font Awesome Icon -->
                            Upload Bill
                        </label>
                        <input type="file" class="form-control" name="bill_file" accept="image/*,application/pdf">
                    </div>
                    <div class="mb-4">
        <label for="remarks" class="form-label fw-bold"><i class="fas fa-comment me-2"></i>Remarks</label>
        <textarea class="form-control form-control-lg" name="remarks" rows="3" placeholder="Enter any additional remarks or comments"></textarea>
    </div>
    <div class="mb-3">
        <label for="financial_account_id" class="form-label fw-bold"><i class="fas fa-wallet me-2"></i>Received Into</label>
        <select class="form-select" name="financial_account_id">
            <option value="0">Default cash account</option>
            <?php foreach (capistra_financial_accounts(true) as $fa): ?>
                <option value="<?php echo (int) $fa['id']; ?>" <?php echo (int) $fa['id'] === (int) capistra_financial_account_default() ? 'selected' : ''; ?>><?php echo htmlspecialchars($fa['name'], ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" name="add_income" class="btn btn-success btn-lg">
                            <i class="fas fa-plus-circle me-2"></i> <!-- Font Awesome Icon -->
                            Add Income
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>   </div>
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
        const workbook = XLSX.utils.book_new();

        <?php foreach ($monthlyIncomes as $monthYear => $incomes) { ?>
            const columns = ["SN", "Category", "Date", "Amount"];
            const rows = [];
            <?php $sn = 1; foreach ($incomes as $income) { ?>
                rows.push([
                    <?php echo $sn; ?>,
                    "<?php echo $income['category']; ?>",
                    "<?php echo $income['date']; ?>",
                    "<?php echo formatNepaliCurrency($income['amount']); ?>"
                ]);
                <?php $sn++; }
            ?>

            const title = "Income Statement for <?php echo $monthYear; ?> - Capistra";
            const titleRow = [title];
            const emptyRow = [""];
            const totalIncomeForMonth = "<?php echo formatNepaliCurrency(getTotalIncome($conn, date('m', strtotime($incomes[0]['date']))
            , date('Y', strtotime($incomes[0]['date'])))); ?>";
            const totalRow = ["Total Income for <?php echo $monthYear; ?>", "", "", totalIncomeForMonth];

            const worksheetData = [
                titleRow,
                emptyRow,
                columns,
                ...rows,
                emptyRow,
                totalRow
            ];

            const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

            const mergeRange = { s: { r: 0, c: 0 }, e: { r: 0, c: columns.length - 1 } };
            if (!worksheet['!merges']) worksheet['!merges'] = [];
            worksheet['!merges'].push(mergeRange);

            worksheet['A1'].s = {
                font: { bold: true, color: { rgb: "FFFFFF" } },
                fill: { fgColor: { rgb: "4F81BD" } },
                alignment: { horizontal: "center" }
            };

            const totalRowIndex = worksheetData.length - 1;
            worksheet[`A${totalRowIndex}`].s = {
                font: { bold: true, color: { rgb: "FFFFFF" } },
                fill: { fgColor: { rgb: "FF0000" } },
                alignment: { horizontal: "right" }
            };

            XLSX.utils.book_append_sheet(workbook, worksheet, "<?php echo $monthYear; ?>");
        <?php } ?>

        const summarySheetData = [
            ["Capistra - Income Statement Summary"],
            [""],
            ["Total Income for the Year (<?php echo $startYear; ?> - <?php echo $endYear; ?>)"],
            ["<?php echo formatNepaliCurrency($totalIncomeForYear); ?>"]
        ];

        const summarySheet = XLSX.utils.aoa_to_sheet(summarySheetData);

        summarySheet['A1'].s = {
            font: { bold: true, color: { rgb: "FFFFFF" } },
            fill: { fgColor: { rgb: "4F81BD" } },
            alignment: { horizontal: "center" }
        };

        summarySheet['A3'].s = {
            font: { bold: true, color: { rgb: "FFFFFF" } },
            fill: { fgColor: { rgb: "00B050" } },
            alignment: { horizontal: "center" }
        };

        summarySheet['A4'].s = {
            font: { bold: true, color: { rgb: "000000" } },
            fill: { fgColor: { rgb: "FFFF00" } },
            alignment: { horizontal: "center" }
        };

        XLSX.utils.book_append_sheet(workbook, summarySheet, "Summary");

        XLSX.writeFile(workbook, 'income_report.xlsx');
    }

    // Charts
    const monthComparisonCtx = document.getElementById('monthComparisonChart').getContext('2d');
    const yearComparisonCtx = document.getElementById('yearComparisonChart').getContext('2d');

    new Chart(monthComparisonCtx, {
        type: 'bar',
        data: {
            labels: ['Current Month', 'Last Month'],
            datasets: [{
                label: 'Income',
                data: [<?php echo getTotalIncome($conn, $currentMonth, $currentYear); ?>, <?php echo getTotalIncome($conn, $currentMonth - 1, $currentYear); ?>],
                backgroundColor: ['#6a11cb', '#2575fc']
            }]
        }
    });

    new Chart(yearComparisonCtx, {
        type: 'bar',
        data: {
            labels: ['This Year', 'Last Year'],
            datasets: [{
                label: 'Income',
                data: [<?php echo getTotalIncome($conn, null, $currentYear); ?>, <?php echo getTotalIncome($conn, null, $currentYear - 1); ?>],
                backgroundColor: ['#f9d423', '#ff4e50']
            }]
        }
    });

    // Clear form after successful submission
    document.getElementById('incomeForm').addEventListener('submit', function() {
        setTimeout(function() {
            document.getElementById('incomeForm').reset();
        }, 1000); // Clear form after 1 second
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</body>
</html>

<?php
include('../head/footer.php');
?>