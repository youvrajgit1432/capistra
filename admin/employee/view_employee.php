<?php
require_once('../../protect/session_check.php');
include('../head/header.php');
include('../config/dbcon.php');

$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];

// Fetch employee data
$employee = [];
if ($employee_id > 0) {
    $query = "SELECT * FROM employees WHERE id = {$employee_id}";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $employee = mysqli_fetch_assoc($result);
    } else {
        $errors[] = "Employee not found";
    }
} else {
    $errors[] = "Invalid employee ID";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .profile-header h2 {
            color: #4361ee;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .profile-photo {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #e0e0e0;
            margin: 0 auto 20px;
            display: block;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #4361ee;
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e0e0e0;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }
        .info-value {
            margin-bottom: 15px;
        }
        .document-link {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 10px;
        }
        .json-data {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="profile-container">
        <div class="profile-header">
            <?php if (!empty($employee['photo_path'])): ?>
                <img src="<?php echo $employee['photo_path']; ?>" class="profile-photo" alt="Employee Photo">
            <?php else: ?>
                <div class="profile-photo d-flex align-items-center justify-content-center bg-light">
                    <i class="fas fa-user fa-4x text-secondary"></i>
                </div>
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($employee['full_name'] ?? ''); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($employee['position'] ?? ''); ?> - <?php echo htmlspecialchars($employee['department'] ?? ''); ?></p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Error:</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Basic Information -->
        <div class="row">
            <div class="col-md-6">
                <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                <div class="row">
                    <div class="col-5 info-label">Employee ID:</div>
                    <div class="col-7 info-value"><?php echo htmlspecialchars($employee['employee_id'] ?? 'N/A'); ?></div>
                    
                    <div class="col-5 info-label">Date of Birth:</div>
                    <div class="col-7 info-value"><?php echo !empty($employee['dob']) ? date('M d, Y', strtotime($employee['dob'])) : 'N/A'; ?></div>
                    
                    <div class="col-5 info-label">Marital Status:</div>
                    <div class="col-7 info-value"><?php echo htmlspecialchars($employee['marital_status'] ?? 'N/A'); ?></div>
                    
                    <div class="col-5 info-label">Nationality:</div>
                    <div class="col-7 info-value"><?php echo htmlspecialchars($employee['nationality'] ?? 'N/A'); ?></div>
                    
                    <div class="col-5 info-label">Join Date:</div>
                    <div class="col-7 info-value"><?php echo !empty($employee['join_date']) ? date('M d, Y', strtotime($employee['join_date'])) : 'N/A'; ?></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <h5 class="section-title"><i class="fas fa-address-card me-2"></i>Contact Information</h5>
                <div class="row">
                    <div class="col-5 info-label">Mobile:</div>
                    <div class="col-7 info-value"><?php echo htmlspecialchars($employee['mobile'] ?? 'N/A'); ?></div>
                    
                    <div class="col-5 info-label">Email:</div>
                    <div class="col-7 info-value"><?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?></div>
                    
                    <div class="col-5 info-label">Emergency Contact:</div>
                    <div class="col-7 info-value">
                        <?php echo htmlspecialchars($employee['emergency_contact'] ?? 'N/A'); ?>
                        <?php if (!empty($employee['emergency_number'])): ?>
                            (<?php echo htmlspecialchars($employee['emergency_number']); ?>)
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Citizenship Details -->
        <h5 class="section-title"><i class="fas fa-id-card me-2"></i>Citizenship Details</h5>
        <div class="row">
            <div class="col-md-4 info-label">Citizenship Number:</div>
            <div class="col-md-8 info-value"><?php echo htmlspecialchars($employee['citizenship_number'] ?? 'N/A'); ?></div>
            
            <div class="col-md-4 info-label">Issue Date:</div>
            <div class="col-md-8 info-value"><?php echo !empty($employee['citizenship_issue_date']) ? date('M d, Y', strtotime($employee['citizenship_issue_date'])) : 'N/A'; ?></div>
            
            <div class="col-md-4 info-label">Issue Place:</div>
            <div class="col-md-8 info-value"><?php echo htmlspecialchars($employee['citizenship_issue_place'] ?? 'N/A'); ?></div>
        </div>
        
        <!-- Address Information -->
        <h5 class="section-title"><i class="fas fa-home me-2"></i>Address Information</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="info-label">Permanent Address:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($employee['permanent_address'] ?? 'N/A')); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Current Address:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($employee['current_address'] ?? 'N/A')); ?></div>
            </div>
        </div>
        
        <!-- Financial Information -->
        <h5 class="section-title"><i class="fas fa-wallet me-2"></i>Financial Information</h5>
        <div class="row">
            <div class="col-md-6 info-label">Bank Account Number:</div>
            <div class="col-md-6 info-value"><?php echo htmlspecialchars($employee['bank_account_number'] ?? 'N/A'); ?></div>
            
            <div class="col-md-6 info-label">PAN Number:</div>
            <div class="col-md-6 info-value"><?php echo htmlspecialchars($employee['pan_number'] ?? 'N/A'); ?></div>
        </div>
        
        <!-- Professional Information -->
        <h5 class="section-title"><i class="fas fa-briefcase me-2"></i>Professional Information</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="info-label">Education:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($employee['education'] ?? 'N/A')); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Previous Experience:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($employee['previous_experience'] ?? 'N/A')); ?></div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-label">Assigned Assets:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($employee['assigned_assets'] ?? 'N/A')); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Reference Person:</div>
                <div class="info-value"><?php echo htmlspecialchars($employee['reference_person'] ?? 'N/A'); ?></div>
            </div>
        </div>
        
        <!-- Leave Records -->
        <?php if (!empty($employee['leave_records'])): ?>
        <h5 class="section-title"><i class="fas fa-calendar-alt me-2"></i>Leave Records</h5>
        <div class="json-data">
            <?php 
                $leave_records = json_decode($employee['leave_records'], true);
                if ($leave_records && json_last_error() === JSON_ERROR_NONE) {
                    echo "<pre>" . print_r($leave_records, true) . "</pre>";
                } else {
                    echo htmlspecialchars($employee['leave_records']);
                }
            ?>
        </div>
        <?php endif; ?>
        
        <!-- Salary Information -->
        <?php if (!empty($employee['salary_info'])): ?>
        <h5 class="section-title"><i class="fas fa-money-bill-wave me-2"></i>Salary Information</h5>
        <div class="json-data">
            <?php 
                $salary_info = json_decode($employee['salary_info'], true);
                if ($salary_info && json_last_error() === JSON_ERROR_NONE) {
                    echo "<pre>" . print_r($salary_info, true) . "</pre>";
                } else {
                    echo htmlspecialchars($employee['salary_info']);
                }
            ?>
        </div>
        <?php endif; ?>
        
        <!-- Documents -->
        <h5 class="section-title"><i class="fas fa-file me-2"></i>Documents</h5>
        <div class="info-value">
            <?php if (!empty($employee['resume_path'])): ?>
                <a href="<?php echo $employee['resume_path']; ?>" class="document-link" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>Download Resume
                </a>
            <?php else: ?>
                <span class="text-muted">No resume uploaded</span>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="employee_management.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Employee List
            </a>
            <a href="add_moredetails.php?id=<?php echo $employee_id; ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit Details
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include('../head/footer.php'); ?>