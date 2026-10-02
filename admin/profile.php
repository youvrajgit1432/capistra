<?php
// Start session securely
require_once('../protect/session_check.php');

// Include database connection
require_once('../protect/db_connection.php');

// Initialize variables
$profileError = '';
$profileSuccess = '';
$showPasswordForm = false;
$showDetailsForm = false;
$showImageForm = false;
$userDetails = [];

// Constants
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('DEFAULT_PROFILE_IMAGE', 'assets/dist/img/undraw_profile.svg');

// Check if we should show a form based on previous submission
if (isset($_SESSION['show_form'])) {
    $showDetailsForm = $_SESSION['show_form'] === 'details';
    $showPasswordForm = $_SESSION['show_form'] === 'password';
    $showImageForm = $_SESSION['show_form'] === 'image';
    unset($_SESSION['show_form']);
}

// Check for success messages from redirects
if (isset($_SESSION['profile_success'])) {
    $profileSuccess = $_SESSION['profile_success'];
    unset($_SESSION['profile_success']);
}

// Fetch user details
if (isset($_SESSION['user_id'])) {
    $query = "SELECT username, email, full_name, phone, address, profile_image FROM adminusers WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        $profileError = "Database error: " . htmlspecialchars($conn->error);
    } else {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->errno) {
            $profileError = "Database error: " . htmlspecialchars($stmt->error);
        } else {
            $result = $stmt->get_result();
            $userDetails = $result->fetch_assoc();
        }
    }
}

// Handle form actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'change_password':
            $showPasswordForm = true;
            break;
        case 'edit_details':
            $showDetailsForm = true;
            break;
        case 'edit_image':
        case 'add_image':
        case 'change_image':
        case 'upload_image':
        case 'remove_image':
        case 'delete_image':
        case 'update_image':
        case 'modify_image':
        case 'replace_image':
        case 'edit_profile_image':
        case 'manage_image':
        case 'profile_picture':
        case 'avatar':
            $showImageForm = true;
            break;
    }
}

// Handle Password Change
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $profileError = "All password fields are required";
        $_SESSION['show_form'] = 'password';
    } elseif ($newPassword !== $confirmPassword) {
        $profileError = "New passwords do not match";
        $_SESSION['show_form'] = 'password';
    } elseif (!isStrongPassword($newPassword)) {
        $profileError = "Password must be at least 8 characters with uppercase, lowercase, number, and special character";
        $_SESSION['show_form'] = 'password';
    } else {
        // Verify current password
        $query = "SELECT password FROM adminusers WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            $profileError = "Database error: " . htmlspecialchars($conn->error);
            $_SESSION['show_form'] = 'password';
        } else {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            
            if ($stmt->errno) {
                $profileError = "Database error: " . htmlspecialchars($stmt->error);
                $_SESSION['show_form'] = 'password';
            } else {
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if (password_verify($currentPassword, $user['password'])) {
                    // Update password
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    $updateQuery = "UPDATE adminusers SET password = ? WHERE id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    
                    if ($updateStmt === false) {
                        $profileError = "Database error: " . htmlspecialchars($conn->error);
                        $_SESSION['show_form'] = 'password';
                    } else {
                        $updateStmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);
                        if ($updateStmt->execute()) {
                            $_SESSION['profile_success'] = "Password changed successfully!";
                        } else {
                            $profileError = "Failed to update password. Please try again.";
                            $_SESSION['show_form'] = 'password';
                        }
                    }
                } else {
                    $profileError = "Current password is incorrect";
                    $_SESSION['show_form'] = 'password';
                }
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: profile.php");
    exit();
}

// Handle Profile Image Upload
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {
    $targetDir = "assets/uploads/profile_images/";
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            $profileError = "Failed to create upload directory";
            $_SESSION['show_form'] = 'image';
        }
    }
    
    if (empty($profileError)) {
        $fileName = basename($_FILES["profile_image"]["name"]);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $fileExt;
        $targetFile = $targetDir . $newFileName;
        
        // Validate image
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check === false) {
            $profileError = "File is not an image.";
            $_SESSION['show_form'] = 'image';
        } elseif ($_FILES["profile_image"]["size"] > MAX_FILE_SIZE) {
            $profileError = "File is too large (max " . (MAX_FILE_SIZE / 1024 / 1024) . "MB).";
            $_SESSION['show_form'] = 'image';
        } elseif (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
            $profileError = "Only " . implode(', ', ALLOWED_FILE_TYPES) . " files are allowed.";
            $_SESSION['show_form'] = 'image';
        }
        
        if (empty($profileError)) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                // Delete old image if it exists and is not the default
                if (!empty($userDetails['profile_image']) && 
                    $userDetails['profile_image'] !== DEFAULT_PROFILE_IMAGE &&
                    file_exists($userDetails['profile_image'])) {
                    unlink($userDetails['profile_image']);
                }
                
                // Update database
                $query = "UPDATE adminusers SET profile_image = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                
                if ($stmt === false) {
                    $profileError = "Database error: " . htmlspecialchars($conn->error);
                    unlink($targetFile); // Clean up uploaded file
                    $_SESSION['show_form'] = 'image';
                } else {
                    $stmt->bind_param("si", $targetFile, $_SESSION['user_id']);
                    if ($stmt->execute()) {
                        $_SESSION['profile_success'] = "Profile image updated successfully!";
                        $userDetails['profile_image'] = $targetFile;
                    } else {
                        $profileError = "Failed to update profile image in database.";
                        unlink($targetFile); // Clean up uploaded file
                        $_SESSION['show_form'] = 'image';
                    }
                }
            } else {
                $profileError = "Sorry, there was an error uploading your file.";
                $_SESSION['show_form'] = 'image';
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: profile.php");
    exit();
}

// Handle Remove Profile Image
if (isset($_POST['remove_image'])) {
    if (!empty($userDetails['profile_image']) && $userDetails['profile_image'] !== DEFAULT_PROFILE_IMAGE) {
        if (file_exists($userDetails['profile_image'])) {
            unlink($userDetails['profile_image']);
        }
        
        // Update database to default image
        $query = "UPDATE adminusers SET profile_image = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            $profileError = "Database error: " . htmlspecialchars($conn->error);
        } else {
            $stmt->bind_param("si", DEFAULT_PROFILE_IMAGE, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $_SESSION['profile_success'] = "Profile image removed successfully!";
                $userDetails['profile_image'] = DEFAULT_PROFILE_IMAGE;
            } else {
                $profileError = "Failed to remove profile image.";
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: profile.php");
    exit();
}

// Handle Update Profile Details
if (isset($_POST['update_details'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    $query = "UPDATE adminusers SET full_name = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        $profileError = "Database error: " . htmlspecialchars($conn->error);
        $_SESSION['show_form'] = 'details';
    } else {
        $stmt->bind_param("sssi", $fullName, $phone, $address, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['profile_success'] = "Profile details updated successfully!";
            // Update local user details
            $userDetails['full_name'] = $fullName;
            $userDetails['phone'] = $phone;
            $userDetails['address'] = $address;
        } else {
            $profileError = "Failed to update profile details. Please try again.";
            $_SESSION['show_form'] = 'details';
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: profile.php");
    exit();
}

function isStrongPassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $password);
}

// Check if profile is complete (has at least one additional detail)
$isProfileComplete = !empty($userDetails['full_name']) || !empty($userDetails['phone']) || !empty($userDetails['address']);
$hasCustomImage = !empty($userDetails['profile_image']) && $userDetails['profile_image'] !== DEFAULT_PROFILE_IMAGE;
include('includes/header.php');
include('includes/topbar.php');
include('includes/sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Profile</title>
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        .profile-card {
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .detail-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .image-upload-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .profile-actions {
            margin-top: 20px;
        }
        .custom-file-label::after {
            content: "Browse";
        }
        .image-preview-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .success-toast {
            position: fixed;
            top: 80px;
            right: 820px;
            z-index: 9999;
            min-width: 250px;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        @keyframes fadeOut {
            from {opacity: 1;}
            to {opacity: 0;}
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

 

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>User Profile</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card profile-card">
                            <div class="card-header">
                                    <!-- Success Toast Notification -->
    <?php if (!empty($profileSuccess)): ?>
    <div class="success-toast">
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-check"></i> <?= htmlspecialchars($profileSuccess) ?>
        </div>
    </div>
    <?php endif; ?>
                                <h3 class="card-title">Profile Information</h3>
                                <div class="card-tools">
                                    <?php if (!$showPasswordForm && !$showDetailsForm && !$showImageForm): ?>
                                        <?php if ($isProfileComplete): ?>
                                            <a href="?action=edit_details" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit Details
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=edit_details" class="btn btn-sm btn-success">
                                                <i class="fas fa-plus"></i> Add Details
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=change_password" class="btn btn-sm btn-info">
                                            <i class="fas fa-key"></i> Change Password
                                        </a>
                                        <?php if ($hasCustomImage): ?>
                                            <a href="?action=edit_image" class="btn btn-sm btn-warning">
                                                <i class="fas fa-image"></i> Change Profile Picture
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=add_image" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-image"></i> Add Image
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($profileError)): ?>
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <?= htmlspecialchars($profileError) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center mb-4">
                                    <img src="<?= !empty($userDetails['profile_image']) ? htmlspecialchars($userDetails['profile_image']) : DEFAULT_PROFILE_IMAGE ?>" 
                                         class="profile-img rounded-circle" alt="Profile picture">
                                </div>
                                
                                <!-- Profile Image Form -->
                                <?php if ($showImageForm): ?>
                                    <div class="form-section">
                                        <h4><?= $hasCustomImage ? 'Change' : 'Add' ?> Profile Image</h4>
                                        
                                        <?php if ($hasCustomImage): ?>
                                            <div class="image-preview-container">
                                                <img src="<?= htmlspecialchars($userDetails['profile_image']) ?>" class="image-preview" alt="Current profile image">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="profile_image" name="profile_image" accept="image/*">
                                                    <label class="custom-file-label" for="profile_image">Choose new profile image</label>
                                                </div>
                                                <small class="form-text text-muted">Max <?= (MAX_FILE_SIZE / 1024 / 1024) ?>MB (<?= strtoupper(implode(', ', ALLOWED_FILE_TYPES)) ?>)</small>
                                            </div>
                                            
                                            <div class="profile-actions">
                                                <button type="submit" name="upload_image" class="btn btn-primary">
                                                    <i class="fas fa-upload"></i> Upload Image
                                                </button>
                                                <?php if ($hasCustomImage): ?>
                                                    <button type="submit" name="remove_image" class="btn btn-danger">
                                                        <i class="fas fa-trash"></i> Remove Image
                                                    </button>
                                                <?php endif; ?>
                                                <a href="profile.php" class="btn btn-default">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Profile Details Display -->
                                <?php if (!$showDetailsForm && !$showPasswordForm && !$showImageForm): ?>
                                    <div class="detail-item">
                                        <strong>Username:</strong> <?= htmlspecialchars($userDetails['username'] ?? 'N/A') ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Email:</strong> <?= htmlspecialchars($userDetails['email'] ?? 'N/A') ?>
                                    </div>
                                    <?php if (!empty($userDetails['full_name'])): ?>
                                        <div class="detail-item">
                                            <strong>Full Name:</strong> <?= htmlspecialchars($userDetails['full_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($userDetails['phone'])): ?>
                                        <div class="detail-item">
                                            <strong>Phone:</strong> <?= htmlspecialchars($userDetails['phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($userDetails['address'])): ?>
                                        <div class="detail-item">
                                            <strong>Address:</strong> <?= nl2br(htmlspecialchars($userDetails['address'])) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- Profile Details Form -->
                                <?php if ($showDetailsForm): ?>
                                    <div class="form-section">
                                        <h4><?= $isProfileComplete ? 'Edit' : 'Add' ?> Profile Details</h4>
                                        <form method="POST" action="">
                                            <div class="form-group">
                                                <label for="full_name">Full Name</label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                                       value="<?= htmlspecialchars($userDetails['full_name'] ?? '') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="phone">Phone Number</label>
                                                <input type="text" class="form-control" id="phone" name="phone" 
                                                       value="<?= htmlspecialchars($userDetails['phone'] ?? '') ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                                <textarea class="form-control" id="address" name="address" rows="3"><?= 
                                                    htmlspecialchars($userDetails['address'] ?? '') ?></textarea>
                                            </div>
                                            <div class="profile-actions">
                                                <button type="submit" name="update_details" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Save Changes
                                                </button>
                                                <a href="profile.php" class="btn btn-default">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Password Change Form -->
                                <?php if ($showPasswordForm): ?>
                                    <div class="form-section">
                                        <h4>Change Password</h4>
                                        <form method="POST" action="">
                                            <div class="form-group">
                                                <label for="current_password">Current Password</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="new_password">New Password</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required
                                                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}" 
                                                       title="Must contain at least 8 characters, including uppercase, lowercase, number and special character">
                                                <small class="form-text text-muted">
                                                    Must be at least 8 characters with uppercase, lowercase, number, and special character
                                                </small>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirm_password">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            <div class="profile-actions">
                                                <button type="submit" name="change_password" class="btn btn-primary">
                                                    <i class="fas fa-key"></i> Change Password
                                                </button>
                                                <a href="profile.php" class="btn btn-default">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
<script>
    $(document).ready(function() {
        // Show the selected filename in the file input
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
            
            // Preview image before upload
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.image-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Add client-side validation for password match
        $('form').on('submit', function() {
            if ($('#new_password').length && $('#confirm_password').length) {
                if ($('#new_password').val() !== $('#confirm_password').val()) {
                    alert('New passwords do not match!');
                    return false;
                }
            }
            return true;
        });
        
        // Auto-hide success toast after 3 seconds
        setTimeout(function() {
            $('.success-toast').fadeOut();
        }, 3000);
        
        // Prevent back button from resubmitting forms
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
</script>
</body>
</html>