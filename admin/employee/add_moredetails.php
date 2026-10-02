<?php
require_once('../../protect/session_check.php');
include('../config/dbcon.php');

$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$success = false;

// Fetch basic employee data
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $employee_id > 0) {
    // Basic information
    $citizenship_number = mysqli_real_escape_string($conn, $_POST['citizenship_number']);
    $citizenship_issue_date = mysqli_real_escape_string($conn, $_POST['citizenship_issue_date']);
    $citizenship_issue_place = mysqli_real_escape_string($conn, $_POST['citizenship_issue_place']);
    $permanent_address = mysqli_real_escape_string($conn, $_POST['permanent_address']);
    $current_address = mysqli_real_escape_string($conn, $_POST['current_address']);
    $assigned_assets = mysqli_real_escape_string($conn, $_POST['assigned_assets']);
    $bank_account_number = mysqli_real_escape_string($conn, $_POST['bank_account_number']);
    $pan_number = mysqli_real_escape_string($conn, $_POST['pan_number']);
    $education = mysqli_real_escape_string($conn, $_POST['education']);
    $previous_experience = mysqli_real_escape_string($conn, $_POST['previous_experience']);
    $reference_person = mysqli_real_escape_string($conn, $_POST['reference_person']);
    
    // New fields for leave and salary records
    $leave_records = mysqli_real_escape_string($conn, $_POST['leave_records']);
    $salary_info = mysqli_real_escape_string($conn, $_POST['salary_info']);
    
    // Handle file uploads
    $resume_path = $employee['resume_path'] ?? '';
    $photo_path = $employee['photo_path'] ?? '';
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "../uploads/resumes/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES['resume']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        
        $allowTypes = array('pdf', 'doc', 'docx');
        if (in_array(strtolower($fileType), $allowTypes)) {
            $newFileName = uniqid() . '_' . $fileName;
            $targetFilePath = $targetDir . $newFileName;
            
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetFilePath)) {
                if (!empty($resume_path) && file_exists($resume_path)) {
                    unlink($resume_path);
                }
                $resume_path = $targetFilePath;
            } else {
                $errors[] = "Error uploading resume file.";
            }
        } else {
            $errors[] = "Only PDF, DOC & DOCX files are allowed for resume.";
        }
    }
    
    if (isset($_FILES['employee_photo']) && $_FILES['employee_photo']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "../uploads/employee_photos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES['employee_photo']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array(strtolower($fileType), $allowTypes)) {
            $newFileName = uniqid() . '_' . $fileName;
            $targetFilePath = $targetDir . $newFileName;
            
            if (move_uploaded_file($_FILES['employee_photo']['tmp_name'], $targetFilePath)) {
                if (!empty($photo_path) && file_exists($photo_path)) {
                    unlink($photo_path);
                }
                $photo_path = $targetFilePath;
            } else {
                $errors[] = "Error uploading employee photo.";
            }
        } else {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed for photos.";
        }
    }
    
    if (empty($errors)) {
        $query = "UPDATE employees SET 
            citizenship_number = '{$citizenship_number}',
            citizenship_issue_date = " . ($citizenship_issue_date ? "'{$citizenship_issue_date}'" : "NULL") . ",
            citizenship_issue_place = '{$citizenship_issue_place}',
            permanent_address = '{$permanent_address}',
            current_address = '{$current_address}',
            assigned_assets = '{$assigned_assets}',
            bank_account_number = '{$bank_account_number}',
            pan_number = '{$pan_number}',
            education = '{$education}',
            previous_experience = '{$previous_experience}',
            resume_path = '{$resume_path}',
            reference_person = '{$reference_person}',
            photo_path = '{$photo_path}',
            leave_records = '{$leave_records}',
            salary_info = '{$salary_info}',
            updated_at = NOW()
            WHERE id = {$employee_id}";
            
        if (mysqli_query($conn, $query)) {
            $success = true;
            $_SESSION['success_message'] = "Employee details updated successfully!";
            header("Location: employee_management.php");
            exit();
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}

include('../head/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .form-header h2 {
            color: #4361ee;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .photo-upload {
            text-align: center;
            position: relative;
            margin-bottom: 20px;
        }
        .photo-preview {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e0e0e0;
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #4361ee;
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e0e0e0;
        }
        .required-field::after {
            content: '*';
            color: #f72585;
            margin-left: 4px;
        }
        .tab-content {
            padding: 20px 0;
        }
        .nav-tabs .nav-link {
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h2><i class="fas fa-user-edit me-2"></i>Employee Details Management</h2>
            <p class="text-muted">Complete record for <?php echo htmlspecialchars($employee['full_name'] ?? ''); ?></p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Errors found:</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Personal</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="professional-tab" data-bs-toggle="tab" data-bs-target="#professional" type="button" role="tab">Professional</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab">Financial</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">Documents</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave" type="button" role="tab">Leave Records</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab">Salary Info</button>
                </li>
            </ul>
            
            <div class="tab-content" id="employeeTabsContent">
                <!-- Personal Information Tab -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="citizenship_number" class="form-label">Citizenship Number</label>
                            <input type="text" class="form-control" id="citizenship_number" name="citizenship_number" 
                                   value="<?php echo htmlspecialchars($employee['citizenship_number'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="citizenship_issue_date" class="form-label">Issue Date</label>
                            <input type="date" class="form-control" id="citizenship_issue_date" name="citizenship_issue_date" 
                                   value="<?php echo htmlspecialchars($employee['citizenship_issue_date'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="citizenship_issue_place" class="form-label">Issue Place</label>
                            <input type="text" class="form-control" id="citizenship_issue_place" name="citizenship_issue_place" 
                                   value="<?php echo htmlspecialchars($employee['citizenship_issue_place'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="permanent_address" class="form-label">Permanent Address</label>
                            <textarea class="form-control" id="permanent_address" name="permanent_address" rows="3"><?php echo htmlspecialchars($employee['permanent_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="current_address" class="form-label">Current Address</label>
                            <textarea class="form-control" id="current_address" name="current_address" rows="3"><?php echo htmlspecialchars($employee['current_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Professional Information Tab -->
                <div class="tab-pane fade" id="professional" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="education" class="form-label">Education</label>
                            <textarea class="form-control" id="education" name="education" rows="3"><?php echo htmlspecialchars($employee['education'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="previous_experience" class="form-label">Previous Experience</label>
                            <textarea class="form-control" id="previous_experience" name="previous_experience" rows="3"><?php echo htmlspecialchars($employee['previous_experience'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assigned_assets" class="form-label">Assigned Assets</label>
                            <textarea class="form-control" id="assigned_assets" name="assigned_assets" rows="2"><?php echo htmlspecialchars($employee['assigned_assets'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reference_person" class="form-label">Reference Person</label>
                            <input type="text" class="form-control" id="reference_person" name="reference_person" 
                                   value="<?php echo htmlspecialchars($employee['reference_person'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Financial Information Tab -->
                <div class="tab-pane fade" id="financial" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_account_number" class="form-label">Bank Account Number</label>
                            <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" 
                                   value="<?php echo htmlspecialchars($employee['bank_account_number'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pan_number" class="form-label">PAN Number</label>
                            <input type="text" class="form-control" id="pan_number" name="pan_number" 
                                   value="<?php echo htmlspecialchars($employee['pan_number'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Documents Tab -->
                <div class="tab-pane fade" id="documents" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="resume" class="form-label">Upload Resume (PDF/DOC)</label>
                            <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                            <?php if (!empty($employee['resume_path'])): ?>
                                <small class="text-muted">Current file: <?php echo basename($employee['resume_path']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="photo-upload">
                                <label for="employee_photo" class="form-label">Update Profile Photo</label>
                                <input type="file" id="employee_photo" name="employee_photo" accept="image/*" onchange="previewPhoto(event)">
                                <?php if (!empty($employee['photo_path'])): ?>
                                    <img id="photoPreview" src="<?php echo $employee['photo_path']; ?>" class="photo-preview" alt="Profile Preview">
                                <?php else: ?>
                                    <img id="photoPreview" src="../assets/default-profile.png" class="photo-preview" alt="Profile Preview">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Leave Records Tab -->
                <div class="tab-pane fade" id="leave" role="tabpanel">
                    <div class="mb-3">
                        <label for="leave_records" class="form-label">Leave Records (JSON format recommended)</label>
                        <textarea class="form-control" id="leave_records" name="leave_records" rows="8"><?php echo htmlspecialchars($employee['leave_records'] ?? ''); ?></textarea>
                        <small class="text-muted">Example format: {"2023": {"sick_leave": 5, "annual_leave": 12}, "2024": {"sick_leave": 2, "annual_leave": 8}}</small>
                    </div>
                </div>
                
                <!-- Salary Information Tab -->
                <div class="tab-pane fade" id="salary" role="tabpanel">
                    <div class="mb-3">
                        <label for="salary_info" class="form-label">Salary Information (JSON format recommended)</label>
                        <textarea class="form-control" id="salary_info" name="salary_info" rows="8"><?php echo htmlspecialchars($employee['salary_info'] ?? ''); ?></textarea>
                        <small class="text-muted">Example format: {"basic_salary": 50000, "allowances": {"housing": 10000, "transport": 5000}, "deductions": {"tax": 5000, "pf": 3000}}</small>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary me-3">
                    <i class="fas fa-save me-2"></i>Save All Details
                </button>
                <a href="employee_management.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function previewPhoto(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('photoPreview');
            preview.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
    
    // Initialize tabs
    const tabElms = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabElms.forEach(tabEl => {
        tabEl.addEventListener('click', function (event) {
            event.preventDefault();
            const tab = new bootstrap.Tab(this);
            tab.show();
        });
    });
</script>
</body>
</html>
<?php include('../head/footer.php'); ?>