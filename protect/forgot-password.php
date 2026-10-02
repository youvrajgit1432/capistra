<?php
session_start();
include('db_connection.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT id, username FROM adminusers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Email not found in our system';
        } else {
            $user = $result->fetch_assoc();
            
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp_expiration'] = time() + 120; // 2 minutes expiration
            $_SESSION['reset_user_id'] = $user['id'];
            
            // Send OTP to email
            include('../sssendotp.php');
            
            // Redirect to OTP verification page
            header('Location: verify-reset-otp.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Capistra</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="logo">
                <div style="background-color: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.8rem; font-weight: bold;">GWI</div>
                <h2>Password Recovery</h2>
            </div>
            
            <h3>Reset Your Password</h3>
            <p class="subtitle">Enter your email to receive a verification code</p>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="forgot-password.php">
                <div class="input-container">
                    <label for="email">Email Address</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>
                
                <button type="submit">Send Verification Code</button>
            </form>
            
            <div class="login-link">
                Remember your password? <a href="login.php">Login here</a>
            </div>
            
            <div class="security-info">
                <i class="fas fa-lock"></i>
                <span>256-bit SSL encryption secured connection</span>
            </div>
        </div>
    </div>
</body>
</html>