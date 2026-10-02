<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db_connection.php');

// Check if user is already logged in as admin
if (isset($_SESSION['username'])) {
    header('Location: ../admin/index.php');
    exit;
}

// Check if temp user data exists (user came through signup process)
if (!isset($_SESSION['temp_user'])) {
    header('Location: signup.php');
    exit;
}

$error = '';
$success = '';

// Handle OTP verification and final signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $userOtp = $_POST['otp'];
    
    if (!isset($_SESSION['signup_otp']) || !isset($_SESSION['otp_expiration'])) {
        $error = "OTP expired or not generated. Please try again.";
        unset($_SESSION['temp_user']);
    } elseif (time() > $_SESSION['otp_expiration']) {
        $error = "OTP has expired. Please request a new one.";
    } elseif ($userOtp != $_SESSION['signup_otp']) {
        $error = "Invalid OTP. Please try again.";
    } else {
        // OTP verified, complete the signup process
        $user = $_SESSION['temp_user'];
        
        $stmt = $conn->prepare("INSERT INTO adminusers (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user['username'], $user['email'], $user['password']);
        
        if ($stmt->execute()) {
            $success = 'Admin user created successfully!';
            
            // Store minimal session data
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['newly_registered'] = true; // Flag for new registration
            
            // Clean up session
            unset($_SESSION['temp_user']);
            unset($_SESSION['signup_otp']);
            unset($_SESSION['otp_expiration']);
            unset($_SESSION['last_otp_time']);
            unset($_SESSION['signup_access_granted']);
            
            // Redirect to login page after 2 seconds
            header('Refresh: 2; URL=../index.php');
        } else {
            $error = 'Error creating user: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Global Investment Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/verifyotp.css">
</head>
<body>
    <div class="bg-blur bg-1"></div>
    <div class="bg-blur bg-2"></div>
    <div class="bg-blur bg-3"></div>
    
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2>Capistra Admin</h2>
            <p>Secure OTP Verification</p>
        </div>
        
        <div class="auth-body">
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['otp_success'])): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['otp_success']); unset($_SESSION['otp_success']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['otp_error'])): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['otp_error']); unset($_SESSION['otp_error']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php else: ?>
                <form class="otp-form" action="verifyotp.php" method="post">
                    <div class="form-group">
                        <label for="otp">Enter the 6-digit verification code</label>
                        <div class="otp-input-container">
                            <input type="text" class="otp-input" name="otp1" maxlength="1" pattern="\d" required autofocus>
                            <input type="text" class="otp-input" name="otp2" maxlength="1" pattern="\d" required>
                            <input type="text" class="otp-input" name="otp3" maxlength="1" pattern="\d" required>
                            <input type="text" class="otp-input" name="otp4" maxlength="1" pattern="\d" required>
                            <input type="text" class="otp-input" name="otp5" maxlength="1" pattern="\d" required>
                            <input type="text" class="otp-input" name="otp6" maxlength="1" pattern="\d" required>
                        </div>
                        <input type="hidden" id="otp" name="otp">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock-open" style="margin-right: 10px;"></i> Verify & Complete Registration
                    </button>
                    
                    <div class="resend-otp">
                        Didn't receive code? 
                        <a href="../resendotp.php" onclick="return confirm('Are you sure you want to resend OTP?')">
                            Resend OTP
                            <?php if (isset($_SESSION['last_otp_time'])): ?>
                                <br><small>(Available in <?php echo 60 - (time() - $_SESSION['last_otp_time']); ?> seconds)</small>
                            <?php endif; ?>
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="auth-footer">
            &copy; <?php echo date('Y'); ?> Capistra Admin. All rights reserved.
        </div>
    </div>

    <script>
        // Combine OTP digits into single input
        document.addEventListener('DOMContentLoaded', function() {
            const otpInputs = document.querySelectorAll('.otp-input');
            const hiddenOtpInput = document.getElementById('otp');
            
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    if (this.value.length === 1) {
                        if (index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        } else {
                            this.blur();
                        }
                    }
                    updateHiddenOtp();
                });
                
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0) {
                        if (index > 0) {
                            otpInputs[index - 1].focus();
                        }
                    }
                    updateHiddenOtp();
                });
                
                // Paste OTP from clipboard
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text/plain').trim();
                    if (pasteData.length === 6 && /^\d+$/.test(pasteData)) {
                        for (let i = 0; i < 6; i++) {
                            if (i < otpInputs.length) {
                                otpInputs[i].value = pasteData[i];
                            }
                        }
                        if (otpInputs[5]) {
                            otpInputs[5].blur();
                        }
                        updateHiddenOtp();
                    }
                });
            });
            
            function updateHiddenOtp() {
                let otp = '';
                otpInputs.forEach(input => {
                    otp += input.value;
                });
                hiddenOtpInput.value = otp;
            }
            
            // Auto-focus first input on page load
            if (otpInputs[0]) {
                otpInputs[0].focus();
            }
            
            // Update resend timer if needed
            <?php if (isset($_SESSION['last_otp_time'])): ?>
                let secondsLeft = <?php echo 60 - (time() - $_SESSION['last_otp_time']); ?>;
                const resendLink = document.querySelector('.resend-otp a');
                
                if (secondsLeft > 0) {
                    resendLink.style.pointerEvents = 'none';
                    resendLink.style.color = '#999';
                    
                    const timer = setInterval(() => {
                        secondsLeft--;
                        const smallTag = resendLink.querySelector('small');
                        if (smallTag) {
                            smallTag.textContent = `(Available in ${secondsLeft} seconds)`;
                        }
                        
                        if (secondsLeft <= 0) {
                            clearInterval(timer);
                            resendLink.style.pointerEvents = 'auto';
                            resendLink.style.color = '';
                            if (smallTag) {
                                smallTag.remove();
                            }
                        }
                    }, 1000);
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>