<?php
require_once('../../protect/session_check.php');
include('../head/header.php');
include('../config/dbcon.php');

// Initialize variables (backend code remains the same)
$errors = [];
$success = false;
$employeeData = [
    'marital_status' => '',
    'nationality' => '',
    'employee_id' => '',
    'full_name' => '',
    'dob' => '',
    'mobile' => '',
    'email' => '',
    'emergency_contact' => '',
    'emergency_number' => '',
    'position' => '',
    'department' => '',
    'join_date' => ''
];

$positionOptions = [
    'CEO', 'CTO', 'CFO', 'Manager', 'Supervisor', 
    'Developer', 'Designer', 'HR Specialist', 'Accountant',
    'Marketing Executive', 'Sales Representative', 'Support Staff'
];

$departmentOptions = [
    'Executive', 'Management', 'IT', 'Human Resources',
    'Finance', 'Marketing', 'Sales', 'Operations', 'Customer Support'
];

function generateEmployeeID($conn) {
    $prefix = 'EMP';
    $year = date('y');
    $month = date('m');
    
    $query = "SELECT employee_id FROM employees ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastID = $row['employee_id'];
        $lastNum = (int)substr($lastID, -4);
        $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNum = '0001';
    }
    
    return $prefix . $year . $month . $newNum;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employeeData['marital_status'] = mysqli_real_escape_string($conn, $_POST['marital_status']);
    $employeeData['nationality'] = mysqli_real_escape_string($conn, $_POST['nationality']);
    $employeeData['employee_id'] = generateEmployeeID($conn);
    $employeeData['full_name'] = mysqli_real_escape_string($conn, $_POST['full_name']);
    $employeeData['dob'] = mysqli_real_escape_string($conn, $_POST['dob']);
    $employeeData['mobile'] = mysqli_real_escape_string($conn, $_POST['mobile']);
    $employeeData['email'] = mysqli_real_escape_string($conn, $_POST['email']);
    $employeeData['emergency_contact'] = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
    $employeeData['emergency_number'] = mysqli_real_escape_string($conn, $_POST['emergency_number']);
    $employeeData['position'] = mysqli_real_escape_string($conn, $_POST['position']);
    $employeeData['department'] = mysqli_real_escape_string($conn, $_POST['department']);
    $employeeData['join_date'] = mysqli_real_escape_string($conn, $_POST['join_date']);
    
    if (empty($employeeData['full_name'])) $errors[] = "Full name is required";
    if (empty($employeeData['dob'])) $errors[] = "Date of birth is required";
    if (empty($employeeData['mobile'])) $errors[] = "Mobile number is required";
    if (empty($employeeData['email'])) $errors[] = "Email is required";
    if (empty($employeeData['position'])) $errors[] = "Position is required";
    if (empty($employeeData['department'])) $errors[] = "Department is required";
    if (empty($employeeData['join_date'])) $errors[] = "Join date is required";
    
    if (!filter_var($employeeData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    $photoPath = '';
    if (isset($_FILES['employee_photo']) && $_FILES['employee_photo']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "../uploads/employee_photos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES['employee_photo']['name']);
        $targetFilePath = $targetDir . $employeeData['employee_id'] . '_' . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES['employee_photo']['tmp_name'], $targetFilePath)) {
                $photoPath = $targetFilePath;
            } else {
                $errors[] = "Sorry, there was an error uploading your file.";
            }
        } else {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO employees (
            marital_status, nationality, employee_id, full_name, 
            dob, mobile, email, emergency_contact, emergency_number, position, 
            department, join_date, photo_path, created_at
        ) VALUES (
            '{$employeeData['marital_status']}', '{$employeeData['nationality']}', '{$employeeData['employee_id']}', 
            '{$employeeData['full_name']}', '{$employeeData['dob']}', '{$employeeData['mobile']}', 
            '{$employeeData['email']}', '{$employeeData['emergency_contact']}', '{$employeeData['emergency_number']}', 
            '{$employeeData['position']}', '{$employeeData['department']}', '{$employeeData['join_date']}', 
            '{$photoPath}', NOW()
        )";
        
        if (mysqli_query($conn, $query)) {
            $success = true;
            $employeeData = array_fill_keys(array_keys($employeeData), '');
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .form-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .form-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .form-header::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            margin: 15px auto;
            border-radius: 2px;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
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
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .photo-upload:hover .photo-preview {
            border-color: var(--accent-color);
        }
        
        .photo-upload label {
            display: block;
            cursor: pointer;
            color: var(--primary-color);
            font-weight: 500;
            transition: var(--transition);
        }
        
        .photo-upload label:hover {
            color: var(--secondary-color);
        }
        
        .photo-upload input[type="file"] {
            display: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }
        
        .btn-secondary {
            background-color: white;
            color: var(--dark-color);
            border: 1px solid #e0e0e0;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }
        
        .btn-secondary:hover {
            background-color: #f8f9fa;
            border-color: #d0d0d0;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            border-color: rgba(76, 201, 240, 0.3);
            color: #0d6efd;
        }
        
        .alert-danger {
            background-color: rgba(247, 37, 133, 0.1);
            border-color: rgba(247, 37, 133, 0.3);
            color: var(--warning-color);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e0e0e0;
        }
        
        .required-field::after {
            content: '*';
            color: var(--warning-color);
            margin-left: 4px;
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 20px;
        }
        
        .floating-label .form-control {
            height: 50px;
        }
        
        .floating-label label {
            position: absolute;
            top: 15px;
            left: 15px;
            color: #999;
            transition: var(--transition);
            pointer-events: none;
            background-color: white;
            padding: 0 5px;
        }
        
        .floating-label .form-control:focus + label,
        .floating-label .form-control:not(:placeholder-shown) + label {
            top: -10px;
            font-size: 12px;
            color: var(--accent-color);
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #999;
        }
        
        .input-icon .form-control {
            padding-left: 40px;
        }
        
        .input-icon .form-control:focus + i {
            color: var(--accent-color);
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-user-plus me-2"></i>Employee Registration</h2>
                <p class="text-muted">Fill in the details to register a new employee</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Success!</h5>
                    <p class="mb-0">Employee registered successfully! Employee ID: <strong><?php echo $employeeData['employee_id']; ?></strong></p>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Personal Information Section -->
                <h5 class="section-title"><i class="fas fa-user me-2"></i>Personal Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="floating-label" hidden>
                            <input type="text" class="form-control" id="employee_id" 
                                   value="<?php echo $employeeData['employee_id'] ?: 'Will be generated after submission'; ?>" readonly>
                            <label for="employee_id">Employee ID</label>
                        </div>
                        
                        <div class="floating-label">
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($employeeData['full_name']); ?>" 
                                   placeholder=" " required>
                            <label for="full_name" class="required-field">Full Name</label>
                        </div>
                        
                        <div class="floating-label">
                            <input type="date" class="form-control" id="dob" name="dob" 
                                   value="<?php echo $employeeData['dob']; ?>" required>
                            <label for="dob" class="required-field">Date of Birth</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="marital_status" class="form-label">Marital Status</label>
                            <select class="form-select" id="marital_status" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="Single" <?php echo ($employeeData['marital_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo ($employeeData['marital_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                                <option value="Divorced" <?php echo ($employeeData['marital_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                <option value="Widowed" <?php echo ($employeeData['marital_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="photo-upload">
                            <img id="photoPreview" src="../assets/default-profile.png" class="photo-preview" alt="Profile Preview">
                            <input type="file" id="employee_photo" name="employee_photo" accept="image/*" onchange="previewPhoto(event)">
                            <label for="employee_photo"><i class="fas fa-camera me-2"></i>Upload Photo</label>
                        </div>
                        
                        <div class="floating-label">
                            <input type="text" class="form-control" id="nationality" name="nationality" 
                                   value="<?php echo htmlspecialchars($employeeData['nationality']); ?>" placeholder=" ">
                            <label for="nationality">Nationality</label>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information Section -->
                <h5 class="section-title"><i class="fas fa-address-book me-2"></i>Contact Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-icon mb-3">
                            <i class="fas fa-mobile-alt"></i>
                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                   value="<?php echo htmlspecialchars($employeeData['mobile']); ?>" 
                                   placeholder="Mobile Number" required>
                        </div>
                        
                        <div class="input-icon mb-3">
                            <i class="fas fa-envelope"></i>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($employeeData['email']); ?>" 
                                   placeholder="Email Address" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="input-icon mb-3">
                            <i class="fas fa-user-friends"></i>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                                   value="<?php echo htmlspecialchars($employeeData['emergency_contact']); ?>" 
                                   placeholder="Emergency Contact Person">
                        </div>
                        
                        <div class="input-icon mb-3">
                            <i class="fas fa-phone-alt"></i>
                            <input type="tel" class="form-control" id="emergency_number" name="emergency_number" 
                                   value="<?php echo htmlspecialchars($employeeData['emergency_number']); ?>" 
                                   placeholder="Emergency Contact Number">
                        </div>
                    </div>
                </div>
                
                <!-- Employment Information Section -->
                <h5 class="section-title"><i class="fas fa-briefcase me-2"></i>Employment Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="position" class="form-label required-field">Job Position</label>
                            <select class="form-select" id="position" name="position" required>
                                <option value="">Select Position</option>
                                <?php foreach ($positionOptions as $option): ?>
                                    <option value="<?php echo $option; ?>" <?php echo ($employeeData['position'] == $option) ? 'selected' : ''; ?>>
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Other">Other (Please specify)</option>
                            </select>
                            <input type="text" class="form-control mt-2 d-none" id="other_position" name="other_position" placeholder="Please specify position">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="department" class="form-label required-field">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departmentOptions as $option): ?>
                                    <option value="<?php echo $option; ?>" <?php echo ($employeeData['department'] == $option) ? 'selected' : ''; ?>>
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Other">Other (Please specify)</option>
                            </select>
                            <input type="text" class="form-control mt-2 d-none" id="other_department" name="other_department" placeholder="Please specify department">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="floating-label">
                            <input type="date" class="form-control" id="join_date" name="join_date" 
                                   value="<?php echo $employeeData['join_date']; ?>" required>
                            <label for="join_date" class="required-field">Date of Joining</label>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-primary me-3">
                        <i class="fas fa-save me-2"></i>Register Employee
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Reset Form
                    </button>
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

        // Show other field when "Other" is selected
        document.getElementById('position').addEventListener('change', function() {
            const otherField = document.getElementById('other_position');
            if (this.value === 'Other') {
                otherField.classList.remove('d-none');
                otherField.required = true;
            } else {
                otherField.classList.add('d-none');
                otherField.required = false;
            }
        });

        document.getElementById('department').addEventListener('change', function() {
            const otherField = document.getElementById('other_department');
            if (this.value === 'Other') {
                otherField.classList.remove('d-none');
                otherField.required = true;
            } else {
                otherField.classList.add('d-none');
                otherField.required = false;
            }
        });

        // Initialize floating labels for inputs with values
        document.addEventListener('DOMContentLoaded', function() {
            const floatingInputs = document.querySelectorAll('.floating-label .form-control');
            floatingInputs.forEach(input => {
                if (input.value) {
                    input.nextElementSibling.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
include('../head/footer.php');
?>