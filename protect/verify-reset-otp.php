<?php
session_start();
include('db_connection.php');

// Redirect if email or OTP not set
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_otp'])) {
    header('Location: forgot-password.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $userOtp = trim($_POST['otp']);
    
    // Check if OTP matches and isn't expired
    if (empty($userOtp)) {
        $error = 'OTP is required';
    } elseif (!is_numeric($userOtp) || strlen($userOtp) !== 6) {
        $error = 'Invalid OTP format';
    } elseif ($userOtp != $_SESSION['reset_otp']) {
        $error = 'Invalid OTP code';
    } elseif (time() > $_SESSION['otp_expiration']) {
        $error = 'OTP has expired. Please request a new one.';
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_expiration']);
    } else {
        // OTP is valid, redirect to password reset page
        $_SESSION['otp_verified'] = true;
        header('Location: reset-password.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | Capistra</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="logo">
                <div style="background-color: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.8rem; font-weight: bold;">GWI</div>
                <h2>OTP Verification</h2>
            </div>
            
            <h3>Enter Verification Code</h3>
            <p class="subtitle">We sent a 6-digit code to <?php echo htmlspecialchars($_SESSION['reset_email']); ?></p>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="verify-reset-otp.php">
                <div class="input-container">
                    <label for="otp">6-digit OTP Code</label>
                    <i class="fas fa-shield-alt"></i>
                    <input type="text" id="otp" name="otp" placeholder="Enter OTP code" required maxlength="6" pattern="\d{6}">
                </div>
                
                <button type="submit">Verify Code</button>
            </form>
            
            <div class="resend-otp">
                <p>Didn't receive the code? <a href="resend-otp.php">Resend OTP</a></p>
            </div>
            
            <div class="security-info">
                <i class="fas fa-lock"></i>
                <span>256-bit SSL encryption secured connection</span>
            </div>
        </div>
    </div>
</body>
</html>