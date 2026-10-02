<?php
  require_once('../../protect/session_check.php');
require_once('../config/dbcon.php');
require_once('includes/functions.php');
 

$stock = null;
$investment = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stock_id = (int)$_GET['id'];
    
    try {
        // Fetch stock investment data
        $query = "SELECT s.*, i.investment_date, i.invested_amount, i.remarks 
                  FROM stock_investments s
                  JOIN investments i ON s.investment_id = i.id
                  WHERE s.id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $stock_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $stock = $row;
            $investment = [
                'date' => $row['investment_date'],
                'amount' => $row['invested_amount'],
                'remarks' => $row['remarks']
            ];
        } else {
            throw new Exception("Stock investment not found");
        }
    } catch (Exception $e) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => $e->getMessage()];
        header("Location: dis_stock.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stock_id = (int)$_POST['stock_id'];
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
        $company_symbol = mysqli_real_escape_string($conn, $_POST['company_symbol']);
        $investment_type = mysqli_real_escape_string($conn, $_POST['investment_type']);
        $base_price = (float)$_POST['base_price'];
        $total_units = (float)$_POST['total_units'];
        $investment_date = mysqli_real_escape_string($conn, $_POST['investment_date']);
        $invested_amount = (float)$_POST['invested_amount'];
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
        
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        // Update investment record
        $investment_query = "UPDATE investments i
                            JOIN stock_investments s ON i.id = s.investment_id
                            SET i.investment_date = ?,
                                i.invested_amount = ?,
                                i.remarks = ?
                            WHERE s.id = ?";
        $stmt = mysqli_prepare($conn, $investment_query);
        mysqli_stmt_bind_param($stmt, 'sdsi', $investment_date, $invested_amount, $remarks, $stock_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update investment: " . mysqli_error($conn));
        }
        
        // Update stock record
        $stock_query = "UPDATE stock_investments 
                        SET company_name = ?,
                            company_symbol = ?,
                            investment_type = ?,
                            base_price = ?,
                            total_units = ?
                        WHERE id = ?";
        $stmt = mysqli_prepare($conn, $stock_query);
        mysqli_stmt_bind_param($stmt, 'sssddi', $company_name, $company_symbol, $investment_type, $base_price, $total_units, $stock_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update stock: " . mysqli_error($conn));
        }
        
        // Handle file upload if provided
        if (!empty($_FILES['agreement_pdf']['name'])) {
            $target_dir = "../uploads/agreements/";
            $file_name = basename($_FILES["agreement_pdf"]["name"]);
            $target_file = $target_dir . uniqid() . '_' . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check file size (5MB max)
            if ($_FILES["agreement_pdf"]["size"] > 5000000) {
                throw new Exception("File is too large. Maximum size is 5MB.");
            }
            
            // Allow certain file formats
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Only PDF, JPG, JPEG, PNG files are allowed.");
            }
            
            // Upload file
            if (move_uploaded_file($_FILES["agreement_pdf"]["tmp_name"], $target_file)) {
                // Update agreement path in database
                $file_query = "UPDATE stock_investments SET agreement_pdf = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $file_query);
                mysqli_stmt_bind_param($stmt, 'si', $target_file, $stock_id);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to update agreement: " . mysqli_error($conn));
                }
            } else {
                throw new Exception("Error uploading file.");
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Stock investment updated successfully'];
        header("Location: dis_stock.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = ['type' => 'danger', 'text' => $e->getMessage()];
        header("Location: edit_stock.php?id=".$stock_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stock Investment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .sidebar {
            background: rgba(3, 6, 13, 0.73);
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(52, 49, 49, 0.1);
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 5px;
            margin: 5px 10px;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .logo h4 {
            color: white;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .sidebar .user-info {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .user-info img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }
        
        .main-content {
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-secondary {
            color: var(--dark-color);
            border-color: var(--dark-color);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--dark-color);
            color: white;
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .alert {
            border-radius: 5px;
        }
        
        .page-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .logout-btn {
            color: rgba(255, 255, 255, 0.8);
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger-color);
        }
        
        .logout-btn i {
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body><div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="logo">
                    <h4><i class="fas fa-chart-line"></i> Capistra</h4>
                </div>
                
          
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <!-- Income Menu Item -->
<li class="nav-item">
    <a class="nav-link" href="../Income/income.php">
        <i class="fas fa-dollar-sign"></i> Income
    </a>
</li>

<!-- Expense Menu Item -->
<li class="nav-item">
    <a class="nav-link" href="../Expense/expense.php">
        <i class="fas fa-money-bill-wave"></i> Expense
    </a>
</li>

<!-- Investment Menu Item -->
<li class="nav-item">
    <a class="nav-link" href="../investment/index.php">
        <i class="fas fa-chart-line"></i> Investment
    </a>
</li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dis_stock.php">
                            <i class="fas fa-chart-line"></i> Stock Portfolio
                        </a>
                    </li>
         
                
                <div class="mt-auto p-3">
                    <button class="logout-btn" onclick="location.href='../logout.php'">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-edit me-2"></i>Edit Stock Investment</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="dis_stock.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Portfolio
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show">
                        <?= $_SESSION['message']['text'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if ($stock): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="stock_id" value="<?= $stock['id'] ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" 
                                               value="<?= htmlspecialchars($stock['company_name']) ?>" required readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="company_symbol" class="form-label">Company Symbol</label>
                                        <input type="text" class="form-control" id="company_symbol" name="company_symbol" 
                                               value="<?= htmlspecialchars($stock['company_symbol']) ?>" required readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="investment_type" class="form-label">Investment Type</label>
                                        <select class="form-select" id="investment_type" name="investment_type" required>
                                        <option value="Long Term" <?= $stock['investment_type'] === 'Long Term' ? 'selected' : '' ?>>Long Term</option>
                                        <option value="Secondary" <?= $stock['investment_type'] === 'Secondary' ? 'selected' : '' ?>>Secondary Market</option>
                                           <option value="Short Term" <?= $stock['investment_type'] === 'Short Term' ? 'selected' : '' ?>>Short Term</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="base_price" class="form-label">Base Price (₹)</label>
                                        <input type="number" step="0.01" class="form-control" id="base_price" name="base_price" 
                                               value="<?= htmlspecialchars($stock['base_price']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="total_units" class="form-label">Total Units</label>
                                        <input type="number" step="0.0001" class="form-control" id="total_units" name="total_units" 
                                               value="<?= htmlspecialchars($stock['total_units']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="investment_date" class="form-label">Investment Date</label>
                                        <input type="date" class="form-control" id="investment_date" name="investment_date" 
                                               value="<?= htmlspecialchars($investment['date']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="invested_amount" class="form-label">Invested Amount (₹)</label>
                                        <input type="number" step="0.01" class="form-control" id="invested_amount" name="invested_amount" 
                                               value="<?= htmlspecialchars($investment['amount']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-group">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3"><?= htmlspecialchars($investment['remarks']) ?></textarea>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-group">
                                    <label for="agreement_pdf" class="form-label">Investment Proof (Voucher/Receipt)</label>
                                    <input type="file" class="form-control" id="agreement_pdf" name="agreement_pdf" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Leave blank to keep existing file</small>
                                    <?php if ($stock['agreement_pdf']): ?>
                                        <div class="mt-2">
                                            <a href="<?= htmlspecialchars($stock['agreement_pdf']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-pdf me-1"></i> View Current Agreement
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="dis_stock.php" class="btn btn-outline-secondary px-4 py-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        No stock investment found to edit.
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate units when base price or invested amount changes
        const basePriceInput = document.getElementById('base_price');
        const investedAmountInput = document.getElementById('invested_amount');
        const totalUnitsInput = document.getElementById('total_units');
        
        function calculateUnits() {
            const basePrice = parseFloat(basePriceInput.value) || 0;
            const investedAmount = parseFloat(investedAmountInput.value) || 0;
            
            if (basePrice > 0) {
                totalUnitsInput.value = (investedAmount / basePrice).toFixed(4);
            }
        }
        
        basePriceInput.addEventListener('input', calculateUnits);
        investedAmountInput.addEventListener('input', calculateUnits);
        
        // Auto-calculate when page loads
        calculateUnits();
    });
    </script>
</body>
</html>