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
    <title>Edit Investor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-investor-container {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 25px;
            max-width: 800px;
            margin: 20px auto;
            font-family: 'Arial', sans-serif;
        }
        .edit-investor-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .edit-investor-header h2 {
            color: #333333;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: bold;
            color: #555555;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #dddddd;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2575fc, #6a11cb);
        }
    </style>
</head>
<body><div class="content-wrapper">
    <div class="container mt-5">
        <div class="edit-investor-container">
            <div class="edit-investor-header">
                <h2>Edit Investor</h2>
            </div>
            <form id="editInvestorForm" action="process/update_investor.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_id" name="id" value="<?= $investor['id'] ?>">
                <div class="mb-3">
                    <label for="edit_name" class="form-label">Investor Name</label>
                    <input type="text" class="form-control" id="edit_name" name="name" value="<?= $investor['name'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_profile_photo" class="form-label">Profile Photo</label>
                    <input type="file" class="form-control" id="edit_profile_photo" name="profile_photo" accept="image/*">
                    <?php if ($investor['profile_photo']) { ?>
                        <small class="form-text text-muted">Current Photo: <a href="<?= $investor['profile_photo'] ?>" target="_blank">View</a></small>
                    <?php } ?>
                </div>
                <div class="mb-3">
                    <label for="edit_contact_phone" class="form-label">Contact Phone</label>
                    <input type="text" class="form-control" id="edit_contact_phone" name="contact_phone" value="<?= $investor['contact_phone'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_contact_email" class="form-label">Contact Email</label>
                    <input type="email" class="form-control" id="edit_contact_email" name="contact_email" value="<?= $investor['contact_email'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_address" class="form-label">Address</label>
                    <textarea class="form-control" id="edit_address" name="address" required><?= $investor['address'] ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="edit_nationality" class="form-label">Nationality</label>
                    <input type="text" class="form-control" id="edit_nationality" name="nationality" value="<?= $investor['nationality'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_citizenship_number" class="form-label">Citizenship Number</label>
                    <input type="text" class="form-control" id="edit_citizenship_number" name="citizenship_number" value="<?= $investor['citizenship_number'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_date_of_investment" class="form-label">Date of Investment</label>
                    <input type="date" class="form-control" id="edit_date_of_investment" name="date_of_investment" value="<?= $investor['date_of_investment'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_investment_type" class="form-label">Investment Type</label>
                    <select class="form-control" id="edit_investment_type" name="investment_type" required>
                        <option value="Equity" <?= $investor['investment_type'] === 'Equity' ? 'selected' : '' ?>>Equity</option>
                        <option value="Debt" <?= $investor['investment_type'] === 'Debt' ? 'selected' : '' ?>>Debt</option>
                        <option value="Profit-sharing" <?= $investor['investment_type'] === 'Profit-sharing' ? 'selected' : '' ?>>Profit-sharing</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit_investment_amount" class="form-label">Investment Amount</label>
                    <input type="number" class="form-control" id="edit_investment_amount" name="investment_amount" value="<?= $investor['investment_amount'] ?>" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label for="edit_kyc_status" class="form-label">KYC Status</label>
                    <select class="form-control" id="edit_kyc_status" name="kyc_status" required>
                        <option value="Verified" <?= $investor['kyc_status'] === 'Verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="Pending" <?= $investor['kyc_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Rejected" <?= $investor['kyc_status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit_bank_account_number" class="form-label">Bank Account Number</label>
                    <input type="text" class="form-control" id="edit_bank_account_number" name="bank_account_number" value="<?= $investor['bank_account_number'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_bank_name" class="form-label">Bank Name</label>
                    <input type="text" class="form-control" id="edit_bank_name" name="bank_name" value="<?= $investor['bank_name'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="edit_nominee_name" class="form-label">Nominee Name</label>
                    <input type="text" class="form-control" id="edit_nominee_name" name="nominee_name" value="<?= $investor['nominee_name'] ?>">
                </div>
                <div class="mb-3">
                    <label for="edit_relationship" class="form-label">Relationship</label>
                    <input type="text" class="form-control" id="edit_relationship" name="relationship" value="<?= $investor['relationship'] ?>">
                </div>
                <div class="mb-3">
                    <label for="edit_investment_risk" class="form-label">Investment Risk</label>
                    <select class="form-control" id="edit_investment_risk" name="investment_risk" required>
                        <option value="Low" <?= $investor['investment_risk'] === 'Low' ? 'selected' : '' ?>>Low</option>
                        <option value="Medium" <?= $investor['investment_risk'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="High" <?= $investor['investment_risk'] === 'High' ? 'selected' : '' ?>>High</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Investor</button>
            </form>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include('../head/footer.php');
?>