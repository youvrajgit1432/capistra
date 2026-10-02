<?php
 require_once('../../protect/session_check.php');
include('../config/dbcon.php'); // Include DB connection

// Edit income logic
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];

    // Fetch existing data for the given income ID
    $query = "SELECT * FROM income WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $income = mysqli_fetch_assoc($result);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Capture the submitted form data
        $category = $_POST['category'];
        $date = $_POST['date'];
        $amount = $_POST['amount'];

        // Handle file upload
        if (isset($_FILES['bill_file']) && $_FILES['bill_file']['error'] == 0) {
            // Upload the new file
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['bill_file']['name']);
            move_uploaded_file($_FILES['bill_file']['tmp_name'], $target_file);
            $bill_file = $_FILES['bill_file']['name'];
        } else {
            // If no new file, keep the existing file
            $bill_file = $income['bill_file'];
        }

        // Update the database with the new data
        $query = "UPDATE income SET category = ?, date = ?, amount = ?, bill_file = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssisi', $category, $date, $amount, $bill_file, $id);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: income.php?success=Income updated successfully.');
        } else {
            header('Location: income.php?error=Error updating income');
        }
    }
}
// Delete income logic
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Delete the income entry from the database
    $query = "DELETE FROM income WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        header('Location: income.php?success=Income deleted successfully');
    } else {
        header('Location: income.php?error=Error deleting income');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Income</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom CSS for a modern look */
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .modal-header h5 {
            font-weight: 600;
        }
        .modal-body {
            padding: 20px;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }
        .btn-primary:hover, .btn-secondary:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<!-- Modal for Editing Income -->
<div class="modal fade" id="editIncomeModal" tabindex="-1" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIncomeModalLabel"><i class="fas fa-edit me-2"></i>Edit Income</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Display success or error message -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form action="income_actions.php?edit_id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                    <label for="category" class="form-label fw-bold">
    <i class="fas fa-list-alt me-2"></i> <!-- Font Awesome Icon -->
    Category
</label>
<select class="form-select" name="category" required>
    <option value="Sales" <?php echo ($income['category'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
    <option value="Investment" <?php echo ($income['category'] == 'Investment') ? 'selected' : ''; ?>>Investment</option>
    <option value="Investment Returns" <?php echo ($income['category'] == 'Investment Returns') ? 'selected' : ''; ?>>Investment Returns</option>
    <option value="Stock Market Profits" <?php echo ($income['category'] == 'Stock Market Profits') ? 'selected' : ''; ?>>Stock Market Profits</option>
    <option value="Real Estate Income" <?php echo ($income['category'] == 'Real Estate Income') ? 'selected' : ''; ?>>Real Estate Income</option>
    <option value="Consultation Fees" <?php echo ($income['category'] == 'Consultation Fees') ? 'selected' : ''; ?>>Consultation Fees</option>
    <option value="Service Charges" <?php echo ($income['category'] == 'Service Charges') ? 'selected' : ''; ?>>Service Charges</option>
    <option value="Loan Interest" <?php echo ($income['category'] == 'Loan Interest') ? 'selected' : ''; ?>>Loan Interest</option>
    <option value="Software Sales" <?php echo ($income['category'] == 'Software Sales') ? 'selected' : ''; ?>>Software Sales</option>
    <option value="Commission Income" <?php echo ($income['category'] == 'Commission Income') ? 'selected' : ''; ?>>Commission Income</option>
    <option value="Fund Management Fees" <?php echo ($income['category'] == 'Fund Management Fees') ? 'selected' : ''; ?>>Fund Management Fees</option>
    <option value="Trading Profits" <?php echo ($income['category'] == 'Trading Profits') ? 'selected' : ''; ?>>Trading Profits</option>
    <option value="Membership Fees" <?php echo ($income['category'] == 'Membership Fees') ? 'selected' : ''; ?>>Membership Fees</option>
    <option value="IPO Service Charges" <?php echo ($income['category'] == 'IPO Service Charges') ? 'selected' : ''; ?>>IPO Service Charges</option>
    <option value="Portfolio Management Fees" <?php echo ($income['category'] == 'Portfolio Management Fees') ? 'selected' : ''; ?>>Portfolio Management Fees</option>
    <option value="Training & Seminar Fees" <?php echo ($income['category'] == 'Training & Seminar Fees') ? 'selected' : ''; ?>>Training & Seminar Fees</option>
    <option value="Referral Bonuses" <?php echo ($income['category'] == 'Referral Bonuses') ? 'selected' : ''; ?>>Referral Bonuses</option>
    <option value="Government Grants/Subsidies" <?php echo ($income['category'] == 'Government Grants/Subsidies') ? 'selected' : ''; ?>>Government Grants/Subsidies</option>
    <option value="Dividend Income" <?php echo ($income['category'] == 'Dividend Income') ? 'selected' : ''; ?>>Dividend Income</option>
    <option value="Crowdfunding Contributions" <?php echo ($income['category'] == 'Crowdfunding Contributions') ? 'selected' : ''; ?>>Crowdfunding Contributions</option>
    <option value="Interest from Fixed Deposits" <?php echo ($income['category'] == 'Interest from Fixed Deposits') ? 'selected' : ''; ?>>Interest from Fixed Deposits</option>
    <option value="Other" <?php echo ($income['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
</select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" value="<?php echo $income['date']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" value="<?php echo $income['amount']; ?>" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="bill_file" class="form-label">Upload New Bill (optional)</label>
                        <input type="file" class="form-control" name="bill_file" accept="image/*,application/pdf">
                        <small class="form-text text-muted">Current file: <a href="uploads/<?php echo $income['bill_file']; ?>" target="_blank">View File</a></small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Income</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
<script>
    // Automatically show the modal when the page loads
    window.onload = function() {
        var editModal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
        editModal.show();
    };

    // Redirect to income.php when the modal is closed
    document.getElementById('editIncomeModal').addEventListener('hidden.bs.modal', function () {
        window.location.href = 'income.php';
    });
</script>
</body>
</html>