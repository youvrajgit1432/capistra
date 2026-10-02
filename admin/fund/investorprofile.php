<?php
require_once dirname(__DIR__, 2) . '/protect/session_check.php'; // Capistra auth guard
include('../head/header.php');
include('../config/dbcon.php'); // Include your database connection file

// Fetch all investors' data
$sql = "SELECT * FROM investors WHERE deleted = 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Profiles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .modal-body img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body> <div class="content-wrapper"><div class="container mt-5">
    <h1 class="mb-4">Investor Profiles</h1>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addInvestorModal">
        <i class="fas fa-plus"></i> Add Investor
    </button>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>    
                <th>SN</th> <!-- Added Serial Number Column -->
                <th>Investor ID</th>
                <th>Profile Photo</th>
                <th>Name</th>
                <th>Contact Info</th>
                <th>Investment Type</th>
                <th>KYC Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sn = 1; // Initialize the serial number counter
            while ($row = $result->fetch_assoc()): ?>
                <tr> 
                    <td><?= $sn++ ?></td> <!-- Display and increment the serial number -->
                    <td><?= $row['id'] ?></td>
                    <td>
                        <img src="<?= '../uploads' . $row['profile_photo'] ?>" alt="Profile Photo" class="profile-photo">
                    </td>
                    <td><?= $row['name'] ?></td>
                    <td>
                        <p>Phone: <?= $row['contact_phone'] ?></p>
                    </td>
                    <td><?= $row['investment_type'] ?></td>
                    <td><?= $row['kyc_status'] ?></td>
                    <td class="action-buttons">
                        <a href="view_investor.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <button class="btn btn-sm btn-secondary" onclick="viewAgreements(<?= $row['id'] ?>)">
                            <i class="fas fa-file-pdf"></i> Agreements
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editInvestor(<?= $row['id'] ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteInvestor(<?= $row['id'] ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

    <!-- Add Investor Modal -->
  <!-- Include FontAwesome CSS from CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div class="modal fade" id="addInvestorModal" tabindex="-1" aria-labelledby="addInvestorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #6a11cb, #2575fc); color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title" id="addInvestorModalLabel" style="font-weight: bold;"><i class="fas fa-user-plus"></i> Add Investor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="color: white;"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form id="addInvestorForm" action="process/save_investor.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label"><i class="fas fa-user"></i> Investor Name</label>
                        <input type="text" class="form-control" id="name" name="name" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label"><i class="fas fa-camera"></i> Profile Photo</label>
                        <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*" style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label"><i class="fas fa-phone"></i> Contact Phone</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="contact_email" class="form-label"><i class="fas fa-envelope"></i> Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label"><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea class="form-control" id="address" name="address" required style="border-radius: 10px; padding: 10px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nationality" class="form-label"><i class="fas fa-globe"></i> Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="citizenship_number" class="form-label"><i class="fas fa-id-card"></i> Citizenship Number</label>
                        <input type="text" class="form-control" id="citizenship_number" name="citizenship_number" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="date_of_investment" class="form-label"><i class="fas fa-calendar-alt"></i> Date of Investment</label>
                        <input type="date" class="form-control" id="date_of_investment" name="date_of_investment" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="investment_type" class="form-label"><i class="fas fa-chart-line"></i> Investment Type</label>
                        <select class="form-control" id="investment_type" name="investment_type" required style="border-radius: 10px; padding: 10px;">
                            <option value="Equity">Equity</option>
                            <option value="Debt">Debt</option>
                            <option value="Profit-sharing">Profit-sharing</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="investment_amount" class="form-label"><i class="fas fa-money-bill-wave"></i> Investment Amount</label>
                        <input type="number" class="form-control" id="investment_amount" name="investment_amount" step="0.01" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="agreement_files" class="form-label"><i class="fas fa-file-contract"></i> Agreement Files (Up to 5)</label>
                        <input type="file" class="form-control" id="agreement_files" name="agreement_files[]" multiple accept="application/pdf" style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="kyc_status" class="form-label"><i class="fas fa-check-circle"></i> KYC Status</label>
                        <select class="form-control" id="kyc_status" name="kyc_status" required style="border-radius: 10px; padding: 10px;">
                            <option value="Verified">Verified</option>
                            <option value="Pending">Pending</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bank_account_number" class="form-label"><i class="fas fa-university"></i> Bank Account Number</label>
                        <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="bank_name" class="form-label"><i class="fas fa-landmark"></i> Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" required style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="nominee_name" class="form-label"><i class="fas fa-user-friends"></i> Nominee Name</label>
                        <input type="text" class="form-control" id="nominee_name" name="nominee_name" style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="relationship" class="form-label"><i class="fas fa-handshake"></i> Relationship</label>
                        <input type="text" class="form-control" id="relationship" name="relationship" style="border-radius: 10px; padding: 10px;">
                    </div>
                    <div class="mb-3">
                        <label for="investment_risk" class="form-label"><i class="fas fa-exclamation-triangle"></i> Investment Risk</label>
                        <select class="form-control" id="investment_risk" name="investment_risk" required style="border-radius: 10px; padding: 10px;">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                           
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #6a11cb, #2575fc); border: none; border-radius: 10px; padding: 10px 20px; font-weight: bold;"><i class="fas fa-save"></i> Save Investor</button>
                </form>
            </div>
        </div>
    </div>
</div>

     <!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome CDN for Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<!-- Edit Investor Modal -->
<div class="modal fade" id="editInvestorModal" tabindex="-1" aria-labelledby="editInvestorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editInvestorModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit Investor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="editInvestorForm" action="process/update_investor.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id" name="id">

                    <!-- Investor Name -->
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">
                            <i class="fas fa-user me-2"></i>Investor Name
                        </label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>

                    <!-- Profile Photo -->
                    <div class="mb-3">
                        <label for="edit_profile_photo" class="form-label">
                            <i class="fas fa-camera me-2"></i>Profile Photo
                        </label>
                        <input type="file" class="form-control" id="edit_profile_photo" name="profile_photo" accept="image/*">
                    </div>

                    <!-- Contact Phone -->
                    <div class="mb-3">
                        <label for="edit_contact_phone" class="form-label">
                            <i class="fas fa-phone me-2"></i>Contact Phone
                        </label>
                        <input type="text" class="form-control" id="edit_contact_phone" name="contact_phone" required>
                    </div>

                    <!-- Contact Email -->
                    <div class="mb-3">
                        <label for="edit_contact_email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Contact Email
                        </label>
                        <input type="email" class="form-control" id="edit_contact_email" name="contact_email" required>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>Address
                        </label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                    </div>

                    <!-- Nationality -->
                    <div class="mb-3">
                        <label for="edit_nationality" class="form-label">
                            <i class="fas fa-globe me-2"></i>Nationality
                        </label>
                        <input type="text" class="form-control" id="edit_nationality" name="nationality" required>
                    </div>

                    <!-- Citizenship Number -->
                    <div class="mb-3">
                        <label for="edit_citizenship_number" class="form-label">
                            <i class="fas fa-id-card me-2"></i>Citizenship Number
                        </label>
                        <input type="text" class="form-control" id="edit_citizenship_number" name="citizenship_number" required>
                    </div>

                    <!-- Date of Investment -->
                    <div class="mb-3">
                        <label for="edit_date_of_investment" class="form-label">
                            <i class="fas fa-calendar-alt me-2"></i>Date of Investment
                        </label>
                        <input type="date" class="form-control" id="edit_date_of_investment" name="date_of_investment" required>
                    </div>

                    <!-- Investment Type -->
                    <div class="mb-3">
                        <label for="edit_investment_type" class="form-label">
                            <i class="fas fa-chart-line me-2"></i>Investment Type
                        </label>
                        <select class="form-control" id="edit_investment_type" name="investment_type" required>
                            <option value="Equity">Equity</option>
                            <option value="Debt">Debt</option>
                            <option value="Profit-sharing">Profit-sharing</option>
                        </select>
                    </div>

                    <!-- Investment Amount -->
                    <div class="mb-3">
                        <label for="edit_investment_amount" class="form-label">
                            <i class="fas fa-money-bill-wave me-2"></i>Investment Amount
                        </label>
                        <input type="number" class="form-control" id="edit_investment_amount" name="investment_amount" step="0.01" required>
                    </div>

                    <!-- KYC Status -->
                    <div class="mb-3">
                        <label for="edit_kyc_status" class="form-label">
                            <i class="fas fa-check-circle me-2"></i>KYC Status
                        </label>
                        <select class="form-control" id="edit_kyc_status" name="kyc_status" required>
                            <option value="Verified">Verified</option>
                            <option value="Pending">Pending</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Bank Account Number -->
                    <div class="mb-3">
                        <label for="edit_bank_account_number" class="form-label">
                            <i class="fas fa-university me-2"></i>Bank Account Number
                        </label>
                        <input type="text" class="form-control" id="edit_bank_account_number" name="bank_account_number" required>
                    </div>

                    <!-- Bank Name -->
                    <div class="mb-3">
                        <label for="edit_bank_name" class="form-label">
                            <i class="fas fa-building me-2"></i>Bank Name
                        </label>
                        <input type="text" class="form-control" id="edit_bank_name" name="bank_name" required>
                    </div>

                    <!-- Nominee Name -->
                    <div class="mb-3">
                        <label for="edit_nominee_name" class="form-label">
                            <i class="fas fa-users me-2"></i>Nominee Name
                        </label>
                        <input type="text" class="form-control" id="edit_nominee_name" name="nominee_name">
                    </div>

                    <!-- Relationship -->
                    <div class="mb-3">
                        <label for="edit_relationship" class="form-label">
                            <i class="fas fa-handshake me-2"></i>Relationship
                        </label>
                        <input type="text" class="form-control" id="edit_relationship" name="relationship">
                    </div>

                    <!-- Investment Risk -->
                    <div class="mb-3">
                        <label for="edit_investment_risk" class="form-label">
                            <i class="fas fa-exclamation-triangle me-2"></i>Investment Risk
                        </label>
                        <select class="form-control" id="edit_investment_risk" name="investment_risk" required>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Update Investor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and Popper.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    <!-- View Agreements Modal -->
    <div class="modal fade" id="viewAgreementsModal" tabindex="-1" aria-labelledby="viewAgreementsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAgreementsModalLabel">Investor Agreements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewAgreementsBody">
                    <!-- Agreement files will be dynamically populated here -->
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to view investor details
        
        // Function to view investor agreements
        async function viewAgreements(id) {
            try {
                const response = await fetch(`process/get_investor.php?id=${id}`);
                const investor = await response.json();
                const agreements = JSON.parse(investor.agreement_files);

                const agreementsBody = document.getElementById('viewAgreementsBody');
                agreementsBody.innerHTML = agreements.map(file => `
                    <div class="mb-3">
                        <embed src="../agreements${file}" width="100%" height="650px" type="application/pdf">
                    </div>
                `).join('');

                new bootstrap.Modal(document.getElementById('viewAgreementsModal')).show();
            } catch (error) {
                console.error("Error fetching agreements:", error);
            }
        }

        // Function to edit investor
        async function editInvestor(id) {
            const response = await fetch(`process/get_investor.php?id=${id}`);
            const investor = await response.json();

            document.getElementById('edit_id').value = investor.id;
            document.getElementById('edit_name').value = investor.name;
            document.getElementById('edit_contact_phone').value = investor.contact_phone;
            document.getElementById('edit_contact_email').value = investor.contact_email;
            document.getElementById('edit_address').value = investor.address;
            document.getElementById('edit_nationality').value = investor.nationality;
            document.getElementById('edit_citizenship_number').value = investor.citizenship_number;
            document.getElementById('edit_date_of_investment').value = investor.date_of_investment;
            document.getElementById('edit_investment_type').value = investor.investment_type;
            document.getElementById('edit_investment_amount').value = investor.investment_amount;
            document.getElementById('edit_kyc_status').value = investor.kyc_status;
            document.getElementById('edit_bank_account_number').value = investor.bank_account_number;
            document.getElementById('edit_bank_name').value = investor.bank_name;
            document.getElementById('edit_nominee_name').value = investor.nominee_name;
            document.getElementById('edit_relationship').value = investor.relationship;
            document.getElementById('edit_investment_risk').value = investor.investment_risk;

            new bootstrap.Modal(document.getElementById('editInvestorModal')).show();
        }

        // Function to delete investor
        function deleteInvestor(id) {
            if (confirm("Are you sure you want to delete this investor?")) {
                window.location.href = "process/delete_investor.php?id=" + id;
            }
        }  
        function exportPdf(id) {
        window.location.href = `process/export_pdf.php?id=${id}`;
    }
    </script>
</body>
</html>

<?php
include('../head/footer.php');
?>