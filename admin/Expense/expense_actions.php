<?php
 require_once('../../protect/session_check.php');
include('../config/dbcon.php'); // Include DB connection

// Handle delete action
if (isset($_GET['delete_id'])) {
    $expense_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    // Fetch the data to move to recycle bin
    $delete_query = "SELECT * FROM expenses WHERE id = $expense_id";
    $delete_result = mysqli_query($conn, $delete_query);
    $expense_data = mysqli_fetch_assoc($delete_result);

    if ($expense_data) {
        // Insert into recycle_bin table
        $recycle_query = "INSERT INTO recycle_bin (category, date, amount, bill_file) 
                          VALUES ('" . mysqli_real_escape_string($conn, $expense_data['category']) . "', 
                                  '" . mysqli_real_escape_string($conn, $expense_data['date']) . "', 
                                  '" . mysqli_real_escape_string($conn, $expense_data['amount']) . "', 
                                  '" . mysqli_real_escape_string($conn, $expense_data['bill_file']) . "')";
        mysqli_query($conn, $recycle_query);

        // Delete the record from expenses table
        $delete_expense_query = "DELETE FROM expenses WHERE id = $expense_id";
        mysqli_query($conn, $delete_expense_query);

        header("Location: expense.php?success=Expense deleted successfully.");
    } else {
        header("Location: expense.php?error=Expense not found.");
    }
    exit();
}

// Handle edit action
if (isset($_POST['update_expense'])) {
    $expense_id = mysqli_real_escape_string($conn, $_POST['expense_id']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);

    // Handle file upload
    if (isset($_FILES['bill_file']) && $_FILES['bill_file']['error'] == 0) {
        // Upload the new file
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['bill_file']['name']);
        move_uploaded_file($_FILES['bill_file']['tmp_name'], $target_file);
        $bill_file = $_FILES['bill_file']['name'];
    } else {
        // If no new file, keep the existing file
        $bill_file = $_POST['existing_bill_file'];
    }

    // Update the database with the new data
    $update_query = "UPDATE expenses SET category='$category', date='$date', amount='$amount', bill_file='$bill_file' WHERE id=$expense_id";
    if (mysqli_query($conn, $update_query)) {
        header("Location: expense.php?success=Expense updated successfully.");
    } else {
        header("Location: expense.php?error=Failed to update expense.");
    }
    exit();
}

// Fetch expense for editing
if (isset($_GET['edit_id'])) {
    $expense_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $query = "SELECT * FROM expenses WHERE id=$expense_id";
    $result = mysqli_query($conn, $query);
    $expense = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

<!-- Modal for Editing Expense -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExpenseModalLabel"><i class="fas fa-edit me-2"></i>Edit Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="expense_actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                    <input type="hidden" name="existing_bill_file" value="<?php echo $expense['bill_file']; ?>">
                    
                    <!-- Category Dropdown -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                        <option value="Office Rent" <?php echo ($expense['category'] == 'Office Rent') ? 'selected' : ''; ?>>Office Rent</option>
        <option value="Utilities" <?php echo ($expense['category'] == 'Utilities') ? 'selected' : ''; ?>>Utilities</option>
        <option value="Employee Salaries" <?php echo ($expense['category'] == 'Employee Salaries') ? 'selected' : ''; ?>>Employee Salaries</option>
        <option value="Marketing and Advertising" <?php echo ($expense['category'] == 'Marketing and Advertising') ? 'selected' : ''; ?>>Marketing and Advertising</option>
        <option value="Travel and Accommodation" <?php echo ($expense['category'] == 'Travel and Accommodation') ? 'selected' : ''; ?>>Travel and Accommodation</option>
        <option value="Office Supplies" <?php echo ($expense['category'] == 'Office Supplies') ? 'selected' : ''; ?>>Office Supplies</option>
        <option value="Software Subscriptions" <?php echo ($expense['category'] == 'Software Subscriptions') ? 'selected' : ''; ?>>Software Subscriptions</option>
        <option value="Professional Services" <?php echo ($expense['category'] == 'Professional Services') ? 'selected' : ''; ?>>Professional Services</option>
        <option value="Insurance" <?php echo ($expense['category'] == 'Insurance') ? 'selected' : ''; ?>>Insurance</option>
        <option value="Maintenance and Repairs" <?php echo ($expense['category'] == 'Maintenance and Repairs') ? 'selected' : ''; ?>>Maintenance and Repairs</option>
        <option value="Bank Fees" <?php echo ($expense['category'] == 'Bank Fees') ? 'selected' : ''; ?>>Bank Fees</option>
        <option value="Investment Costs" <?php echo ($expense['category'] == 'Investment Costs') ? 'selected' : ''; ?>>Investment Costs</option>
        <option value="Training and Development" <?php echo ($expense['category'] == 'Training and Development') ? 'selected' : ''; ?>>Training and Development</option>
        <option value="Client Entertainment" <?php echo ($expense['category'] == 'Client Entertainment') ? 'selected' : ''; ?>>Client Entertainment</option>
        <option value="Technology Upgrades" <?php echo ($expense['category'] == 'Technology Upgrades') ? 'selected' : ''; ?>>Technology Upgrades</option>
        <option value="Data and Research" <?php echo ($expense['category'] == 'Data and Research') ? 'selected' : ''; ?>>Data and Research</option>
        <option value="Compliance and Regulatory Fees" <?php echo ($expense['category'] == 'Compliance and Regulatory Fees') ? 'selected' : ''; ?>>Compliance and Regulatory Fees</option>
        <option value="Taxes" <?php echo ($expense['category'] == 'Taxes') ? 'selected' : ''; ?>>Taxes</option>
        <option value="Employee Benefits" <?php echo ($expense['category'] == 'Employee Benefits') ? 'selected' : ''; ?>>Employee Benefits</option>
        <option value="Office Refreshments" <?php echo ($expense['category'] == 'Office Refreshments') ? 'selected' : ''; ?>>Office Refreshments</option>
        <option value="Event Sponsorships" <?php echo ($expense['category'] == 'Event Sponsorships') ? 'selected' : ''; ?>>Event Sponsorships</option>
        <option value="Charitable Donations" <?php echo ($expense['category'] == 'Charitable Donations') ? 'selected' : ''; ?>>Charitable Donations</option>
        <option value="Security Services" <?php echo ($expense['category'] == 'Security Services') ? 'selected' : ''; ?>>Security Services</option>
        <option value="Telecommunications" <?php echo ($expense['category'] == 'Telecommunications') ? 'selected' : ''; ?>>Telecommunications</option>
        <option value="Printing and Stationery" <?php echo ($expense['category'] == 'Printing and Stationery') ? 'selected' : ''; ?>>Printing and Stationery</option>
        <option value="Vehicle Expenses" <?php echo ($expense['category'] == 'Vehicle Expenses') ? 'selected' : ''; ?>>Vehicle Expenses</option>
        <option value="Consultancy Fees" <?php echo ($expense['category'] == 'Consultancy Fees') ? 'selected' : ''; ?>>Consultancy Fees</option>
        <option value="Miscellaneous" <?php echo ($expense['category'] == 'Miscellaneous') ? 'selected' : ''; ?>>Miscellaneous</option>    <!-- Add more options as needed -->
                        </select>
                    </div>

                    <!-- Date Input -->
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($expense['date']); ?>" required>
                    </div>

                    <!-- Amount Input -->
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" value="<?php echo htmlspecialchars($expense['amount']); ?>" required>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="bill_file" class="form-label">Upload New Bill (optional)</label>
                        <input type="file" class="form-control" name="bill_file" accept="image/*,application/pdf">
                        <small class="form-text text-muted">
                            Current file: <a href="uploads/<?php echo $expense['bill_file']; ?>" target="_blank">View File</a>
                        </small>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary" name="update_expense"><i class="fas fa-save me-2"></i>Update</button>
                        <button type="button" class="btn btn-secondary" id="cancelButton"><i class="fas fa-times me-2"></i>Cancel</button>
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
        var editModal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
        editModal.show();
    };

    // Handle Cancel button click
    document.getElementById('cancelButton').addEventListener('click', function() {
        window.location.href = 'expense.php'; // Redirect to expense.php
    });

    // Handle modal close event (cross button or clicking outside the modal)
    var editExpenseModal = document.getElementById('editExpenseModal');
    editExpenseModal.addEventListener('hidden.bs.modal', function () {
        window.location.href = 'expense.php'; // Redirect to expense.php
    });
</script>
</body>
</html>