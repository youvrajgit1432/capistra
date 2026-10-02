<?php
require_once('../../protect/session_check.php');
include('../head/header.php');
include('../config/dbcon.php');

// Initialize variables
$errors = [];
$success = false;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Employee data structure
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
    'join_date' => '',
    'photo_path' => ''
];

// Options for dropdowns
$positionOptions = [
    'CEO', 'CTO', 'CFO', 'Manager', 'Supervisor', 
    'Developer', 'Designer', 'HR Specialist', 'Accountant',
    'Marketing Executive', 'Sales Representative', 'Support Staff'
];

$departmentOptions = [
    'Executive', 'Management', 'IT', 'Human Resources',
    'Finance', 'Marketing', 'Sales', 'Operations', 'Customer Support'
];

// Function to generate employee ID
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

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $employeeData['marital_status'] = mysqli_real_escape_string($conn, $_POST['marital_status']);
    $employeeData['nationality'] = mysqli_real_escape_string($conn, $_POST['nationality']);
    $employeeData['full_name'] = mysqli_real_escape_string($conn, $_POST['full_name']);
    $employeeData['dob'] = mysqli_real_escape_string($conn, $_POST['dob']);
    $employeeData['mobile'] = mysqli_real_escape_string($conn, $_POST['mobile']);
    $employeeData['email'] = mysqli_real_escape_string($conn, $_POST['email']);
    $employeeData['emergency_contact'] = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
    $employeeData['emergency_number'] = mysqli_real_escape_string($conn, $_POST['emergency_number']);
    $employeeData['position'] = mysqli_real_escape_string($conn, $_POST['position']);
    $employeeData['department'] = mysqli_real_escape_string($conn, $_POST['department']);
    $employeeData['join_date'] = mysqli_real_escape_string($conn, $_POST['join_date']);
    
    // Validation
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
    
    // Handle file upload
    $photoPath = '';
    if (isset($_FILES['employee_photo']) && $_FILES['employee_photo']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "../uploads/employee_photos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES['employee_photo']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array(strtolower($fileType), $allowTypes)) {
            // Generate unique filename
            $newFileName = uniqid() . '_' . $fileName;
            $targetFilePath = $targetDir . $newFileName;
            
            if (move_uploaded_file($_FILES['employee_photo']['tmp_name'], $targetFilePath)) {
                $photoPath = $targetFilePath;
                
                // Delete old photo if exists
                if (!empty($employeeData['photo_path']) && file_exists($employeeData['photo_path'])) {
                    unlink($employeeData['photo_path']);
                }
            } else {
                $errors[] = "Sorry, there was an error uploading your file.";
            }
        } else {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } elseif (isset($_POST['existing_photo'])) {
        $photoPath = $_POST['existing_photo'];
    }
    
    // Process form if no errors
    if (empty($errors)) {
        if ($action == 'edit' && $employee_id > 0) {
            // Update existing employee
            $query = "UPDATE employees SET 
                marital_status = '{$employeeData['marital_status']}',
                nationality = '{$employeeData['nationality']}',
                full_name = '{$employeeData['full_name']}',
                dob = '{$employeeData['dob']}',
                mobile = '{$employeeData['mobile']}',
                email = '{$employeeData['email']}',
                emergency_contact = '{$employeeData['emergency_contact']}',
                emergency_number = '{$employeeData['emergency_number']}',
                position = '{$employeeData['position']}',
                department = '{$employeeData['department']}',
                join_date = '{$employeeData['join_date']}',
                photo_path = '{$photoPath}',
                updated_at = NOW()
                WHERE id = {$employee_id}";
                
            if (mysqli_query($conn, $query)) {
                $success = true;
                $_SESSION['success_message'] = "Employee updated successfully!";
                header("Location: employee_management.php");
                exit();
            } else {
                $errors[] = "Error updating employee: " . mysqli_error($conn);
            }
        } else {
            // Add new employee
            $employeeData['employee_id'] = generateEmployeeID($conn);
            
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
                $_SESSION['success_message'] = "Employee added successfully!";
                header("Location: employee_management.php");
                exit();
            } else {
                $errors[] = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch employee data for editing
if ($action == 'edit' && $employee_id > 0) {
    $query = "SELECT * FROM employees WHERE id = {$employee_id}";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $employeeData = mysqli_fetch_assoc($result);
    } else {
        $errors[] = "Employee not found";
        $action = 'add'; // Fall back to add mode
    }
}

// Fetch all employees from database
$employees = [];
$query = "SELECT * FROM employees ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
}

// Display success message from session
if (isset($_SESSION['success_message'])) {
    $success = true;
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .photo-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .table-responsive {
            margin-top: 30px;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .no-photo {
            width: 50px;
            height: 50px;
            background-color: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-header {
            background-color: #4361ee;
            color: white;
        }
        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }
        .btn-primary:hover {
            background-color: #3a56d4;
            border-color: #3a56d4;
        }
    </style>
</head>
<body>
<div class="content-wrapper">
    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Employee Management</h2>
            <a href="register.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Employee
            </a>
        </div>
        
        <!-- Employee List Table -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Employee List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>SN</th>
                                <th>Photo</th>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Join Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($employees)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No employees found</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                      $sn = 1;
                                      foreach ($employees as $employee): ?>
                                    <tr>
                                    <td><?php echo $sn++; ?></td>


                                        <td>
                                            <?php if (!empty($employee['photo_path'])): ?>
                                                <img src="<?php echo $employee['photo_path']; ?>" alt="Employee Photo" class="photo-thumbnail">
                                            <?php else: ?>
                                                <div class="no-photo">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['department']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['mobile']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($employee['join_date'])); ?></td>
                                        <td class="action-buttons">
                                            <a href="add_moredetails.php?id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-info" title="Add Details">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="view_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="employee_management.php?action=edit&id=<?php echo $employee['id']; ?>"
                                             class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this employee?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
<?php include('../head/footer.php'); ?>