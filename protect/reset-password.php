<?php
session_start();
include('db_connection.php');

// Redirect if OTP not verified
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header('Location: forgot-password.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Both password fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        // Update password in database
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE adminusers SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $_SESSION['reset_user_id']);
        
        if ($stmt->execute()) {
            $success = 'Password updated successfully!';
            
            // Clear reset session variables
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['otp_expiration']);
            
            // Redirect to login after 3 seconds
            header("Refresh: 3; url=../index.php");
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Capistra</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="logo">
                <div style="background-color: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.8rem; font-weight: bold;">GWI</div>
                <h2>Reset Password</h2>
            </div>
            
            <h3>Create New Password</h3>
            <p class="subtitle">Please create a new secure password</p>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="reset-password.php">
                <div class="input-container">
                    <label for="new_password">New Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required minlength="8">
                </div>
                
                <div class="input-container">
                    <label for="confirm_password">Confirm Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8">
                </div>
                
                <button type="submit">Reset Password</button>
            </form>
            
            <div class="security-info">
                <i class="fas fa-lock"></i>
                <span>256-bit SSL encryption secured connection</span>
            </div>
        </div>
    </div>
</body>
</html>