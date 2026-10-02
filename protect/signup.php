<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db_connection.php');

// Access password (change this to your desired password)
$accessPassword = "0000";

// Check if access password is already verified
$accessGranted = isset($_SESSION['signup_access_granted']) && $_SESSION['signup_access_granted'] === true;

// Handle access password submission
if (isset($_POST['access_password'])) {
    if ($_POST['access_password'] === $accessPassword) {
        $_SESSION['signup_access_granted'] = true;
        $accessGranted = true;
    } else {
        $accessError = "Invalid access password";
    }
}

// Check if user is already logged in as admin
if (isset($_SESSION['username'])) {
    header('Location: ../admin/index.php');
    exit;
}

$error = '';

// Handle initial signup form submission
if ($accessGranted && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = 'Password must contain at least one special character.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM adminusers WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Store user data in session for after OTP verification
            $_SESSION['temp_user'] = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT)
            ];
            
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['signup_otp'] = $otp;
            $_SESSION['otp_expiration'] = time() + 80; // 2 minutes expiration
            
            // Send OTP to email
            include('../ssendotp.php');
            
            // Redirect to OTP verification page
            header('Location: verifyotp.php');
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
    <title>Admin Signup | Capistra</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/sign.css">
    <style>
        .password-requirements {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #666;
        }
        .password-requirements ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }
        .requirement i {
            margin-right: 5px;
            font-size: 0.8rem;
        }
        .requirement.valid {
            color: #28a745;
        }
        .requirement.invalid {
            color: #dc3545;
        }
        .password-strength-meter {
            height: 5px;
            background-color: #e9ecef;
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
        .password-strength-meter-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .weak {
            background-color: #dc3545;
            width: 25%;
        }
        .medium {
            background-color: #fd7e14;
            width: 50%;
        }
        .strong {
            background-color: #28a745;
            width: 75%;
        }
        .very-strong {
            background-color: #007bff;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php if (!$accessGranted): ?>
        <!-- Access Password Form -->
        <div class="access-container">
            <h2>Enter Access Password</h2>
            
            <?php if (isset($accessError)): ?>
                <div class="error"><?php echo htmlspecialchars($accessError); ?></div>
            <?php endif; ?>
            
            <form action="signup.php" method="post">
                <div class="input-container">
                    <label for="access_password">Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="access_password" name="access_password" required>
                </div>
                <button type="submit">Continue to Signup</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Signup Form (only shown after password verification) -->
        <div class="signup-wrapper">
            <div class="signup-hero">
                <h1>Capistra</h1>
                <p>Create your admin account to access the powerful dashboard and manage the platform with full privileges.</p>
                
                <ul class="features-list">
                    <li><i class="fas fa-shield-alt"></i> Secure admin authentication</li>
                    <li><i class="fas fa-cog"></i> Full system control</li>
                    <li><i class="fas fa-chart-pie"></i> Advanced analytics</li>
                    <li><i class="fas fa-users-cog"></i> User management</li>
                </ul>
                
                <p>Already have an account? You'll be redirected to login after successful verification.</p>
            </div>
            
            <div class="signup-container">
                <div class="logo">
                    <div style="background-color: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.8rem; font-weight: bold;">GWI</div>
                    <h2>Admin Portal</h2>
                </div>
                
                <h3>Create Admin Account</h3>
                <p class="subtitle">Please fill in the details to register as an admin</p>
                
                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form id="signupForm" action="signup.php" method="post">
                    <div class="input-container">
                        <label for="username">Username</label>
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Choose your username" required>
                    </div>
                    
                    <div class="input-container">
                        <label for="email">Email</label>
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Your email address" required>
                    </div>
                    
                    <div class="input-container">
                        <label for="password">Password (min 8 characters)</label>
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required minlength="8">
                        <div class="password-strength-meter">
                            <div class="password-strength-meter-fill" id="password-strength-meter-fill"></div>
                        </div>
                        <div class="password-requirements">
                            <div class="requirement" id="length-req">
                                <i class="fas fa-circle"></i>
                                <span>At least 8 characters</span>
                            </div>
                            <div class="requirement" id="uppercase-req">
                                <i class="fas fa-circle"></i>
                                <span>At least one uppercase letter</span>
                            </div>
                            <div class="requirement" id="lowercase-req">
                                <i class="fas fa-circle"></i>
                                <span>At least one lowercase letter</span>
                            </div>
                            <div class="requirement" id="number-req">
                                <i class="fas fa-circle"></i>
                                <span>At least one number</span>
                            </div>
                            <div class="requirement" id="special-req">
                                <i class="fas fa-circle"></i>
                                <span>At least one special character</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-container">
                        <label for="confirm_password">Confirm Password</label>
                        <i class="fas fa-key"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required minlength="8">
                    </div>
                    
                    <button type="submit" id="submit-btn" disabled>Send OTP Verification</button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="../index.php">Login here</a>
                </div>
                
                <div class="security-info">
                    <i class="fas fa-lock"></i>
                    <span>256-bit SSL encryption secured connection</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const submitBtn = document.getElementById('submit-btn');
            const strengthMeter = document.getElementById('password-strength-meter-fill');
            
            // Requirements elements
            const lengthReq = document.getElementById('length-req');
            const uppercaseReq = document.getElementById('uppercase-req');
            const lowercaseReq = document.getElementById('lowercase-req');
            const numberReq = document.getElementById('number-req');
            const specialReq = document.getElementById('special-req');
            
            let requirementsMet = {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false
            };
            
            function checkPasswordStrength(password) {
                let strength = 0;
                
                // Reset requirements
                requirementsMet = {
                    length: false,
                    uppercase: false,
                    lowercase: false,
                    number: false,
                    special: false
                };
                
                // Check length
                if (password.length >= 8) {
                    strength += 1;
                    requirementsMet.length = true;
                }
                
                // Check uppercase letters
                if (/[A-Z]/.test(password)) {
                    strength += 1;
                    requirementsMet.uppercase = true;
                }
                
                // Check lowercase letters
                if (/[a-z]/.test(password)) {
                    strength += 1;
                    requirementsMet.lowercase = true;
                }
                
                // Check numbers
                if (/[0-9]/.test(password)) {
                    strength += 1;
                    requirementsMet.number = true;
                }
                
                // Check special characters
                if (/[^A-Za-z0-9]/.test(password)) {
                    strength += 1;
                    requirementsMet.special = true;
                }
                
                // Update strength meter
                strengthMeter.className = 'password-strength-meter-fill';
                if (strength <= 1) {
                    strengthMeter.classList.add('weak');
                } else if (strength <= 3) {
                    strengthMeter.classList.add('medium');
                } else if (strength <= 4) {
                    strengthMeter.classList.add('strong');
                } else {
                    strengthMeter.classList.add('very-strong');
                }
                
                // Update requirements UI
                updateRequirementsUI();
                
                // Enable/disable submit button based on all requirements
                const allRequirementsMet = Object.values(requirementsMet).every(val => val);
                submitBtn.disabled = !allRequirementsMet || (password !== confirmPasswordInput.value);
            }
            
            function updateRequirementsUI() {
                // Update each requirement indicator
                lengthReq.className = requirementsMet.length ? 'requirement valid' : 'requirement invalid';
                uppercaseReq.className = requirementsMet.uppercase ? 'requirement valid' : 'requirement invalid';
                lowercaseReq.className = requirementsMet.lowercase ? 'requirement valid' : 'requirement invalid';
                numberReq.className = requirementsMet.number ? 'requirement valid' : 'requirement invalid';
                specialReq.className = requirementsMet.special ? 'requirement valid' : 'requirement invalid';
                
                // Update icons
                lengthReq.querySelector('i').className = requirementsMet.length ? 'fas fa-check-circle' : 'fas fa-circle';
                uppercaseReq.querySelector('i').className = requirementsMet.uppercase ? 'fas fa-check-circle' : 'fas fa-circle';
                lowercaseReq.querySelector('i').className = requirementsMet.lowercase ? 'fas fa-check-circle' : 'fas fa-circle';
                numberReq.querySelector('i').className = requirementsMet.number ? 'fas fa-check-circle' : 'fas fa-circle';
                specialReq.querySelector('i').className = requirementsMet.special ? 'fas fa-check-circle' : 'fas fa-circle';
            }
            
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (password && confirmPassword) {
                    if (password !== confirmPassword) {
                        confirmPasswordInput.setCustomValidity("Passwords do not match");
                    } else {
                        confirmPasswordInput.setCustomValidity("");
                    }
                }
                
                // Enable submit button only if all requirements are met and passwords match
                const allRequirementsMet = Object.values(requirementsMet).every(val => val);
                submitBtn.disabled = !allRequirementsMet || (password !== confirmPassword);
            }
            
            // Event listeners
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                checkPasswordMatch();
            });
            
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
            
            // Initial check in case of autofill
            if (passwordInput.value) {
                checkPasswordStrength(passwordInput.value);
                checkPasswordMatch();
            }
        });
    </script>
</body>
</html>