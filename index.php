<?php
// Capistra - canonical session bootstrap (consistent session name + flags).
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/session.php';
capistra_session_start();

// Check if user is already logged in
if (isset($_SESSION['username'])) {
    header('Location: admin/index.php');
    exit();
}

// Include database connection
require_once('protect/db_connection.php');

// The legacy hardcoded default-password bypass has been removed.
// Login always requires a verified password hash.
$defaultPassword = null;

// Initialize variables
$loginError = '';
$passwordChangeSuccess = '';
$showPasswordChangeForm = false;
$usernameForPasswordChange = '';

// Handle Login
if (isset($_POST['submit'])) {
    $usernameOrEmail = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // Validate inputs
    if (empty($usernameOrEmail) || empty($password)) {
        $loginError = "Username/Email and password are required";
    } else {
        // Check if using default password
        if ($password === $defaultPassword && checkUsernameExists($usernameOrEmail, $conn)) {
            // Show password change form
            $showPasswordChangeForm = true;
            $usernameForPasswordChange = $usernameOrEmail;
            $_SESSION['temp_username'] = $usernameOrEmail;
        } 
        // Check normal login
        elseif (checkUserCredentials($usernameOrEmail, $password, $conn)) {
            $userDetails = getUserDetails($usernameOrEmail, $conn);
            
            // Set session variables
            $_SESSION = [
                'user_id' => $userDetails['id'],
                'username' => $userDetails['username'],
                'email' => $userDetails['email'],
                'timeout' => time(),
                'created' => time()
            ];
            
            // Regenerate session ID after login
            session_regenerate_id(true);

            // Redirect to admin panel
            header('Location: admin/index.php');
            exit;
        } else {
            $loginError = "Invalid username or password";
        }
    }
}

// Handle Password Change
if (isset($_POST['change_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $usernameOrEmail = $_SESSION['temp_username'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $loginError = "Please enter and confirm your new password";
        $showPasswordChangeForm = true;
    } elseif ($newPassword !== $confirmPassword) {
        $loginError = "Passwords do not match";
        $showPasswordChangeForm = true;
    } elseif ($newPassword === $defaultPassword) {
        $loginError = "New password cannot be the same as the default password";
        $showPasswordChangeForm = true;
    } elseif (!isStrongPassword($newPassword)) {
        $loginError = "Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters";
        $showPasswordChangeForm = true;
    } else {
        // Update password in database
        if (updateUserPassword($usernameOrEmail, $newPassword, $conn)) {
            // Password updated successfully
            $passwordChangeSuccess = "Password changed successfully! Please log in with your new password.";
            unset($_SESSION['temp_username']);
            session_destroy(); // Clear all session data
        } else {
            $loginError = "Failed to update password. Please try again.";
            $showPasswordChangeForm = true;
        }
    }
}

// Helper Functions

function isStrongPassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $password);
}

function checkUsernameExists($usernameOrEmail, $conn) {
    $stmt = $conn->prepare("SELECT id FROM adminusers WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function checkUserCredentials($usernameOrEmail, $password, $conn) {
    $stmt = $conn->prepare("SELECT id, username, email, password FROM adminusers WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return password_verify($password, $user['password']);
    }
    return false;
}

function getUserDetails($usernameOrEmail, $conn) {
    $stmt = $conn->prepare("SELECT id, username, email FROM adminusers WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateUserPassword($usernameOrEmail, $newPassword, $conn) {
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE adminusers SET password = ? WHERE username = ? OR email = ?");
    $stmt->bind_param("sss", $hashedPassword, $usernameOrEmail, $usernameOrEmail);
    return $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="protect/css/login.css">
    <style>
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: <?= !empty($loginError) ? 'block' : 'none' ?>;
        }
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .password-change-form {
            display: <?= $showPasswordChangeForm ? 'block' : 'none' ?>;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 0.8rem;
        }
        .password-strength.weak { color: #dc3545; }
        .password-strength.medium { color: #fd7e14; }
        .password-strength.strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="login-wrapper">
    <div class="login-hero">
    <h1>Capistras</h1>
    <p>Secure access to your investment portfolio and management tools.</p>
    <ul class="features-list">
        <li><i class="fas fa-shield-alt"></i> Bank-level security</li>
        <li><i class="fas fa-chart-line"></i> Real-time market data</li>
        <li><i class="fas fa-headset"></i> 24/7 support</li>
    </ul>
    <a href="iiiindex.php" style="font-size:30px; font-weight:bold; text-decoration:none;" class="btn btn-outline-light btn-lg m-t3">Click Here</a>
    <i class="fas fa-info-circle me-2"></i>Learn More About Our Company
</a>
</div>
        
        <div class="login-container">
            <div class="logo">
                <div class="logo-icon">GMI</div>
                <h2>Capistras</h2>
            </div>
            
            <?php if (!empty($passwordChangeSuccess)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($passwordChangeSuccess) ?>
                </div>
                
                <h3>Welcome Back</h3>
                <p class="subtitle">Please login with your new password</p>
                
                <form id="loginForm" method="POST" action="">
                    <div class="input-container">
                        <label for="username">Username or Email</label>
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username or email" 
                               value="<?= htmlspecialchars($usernameForPasswordChange ?? '') ?>" required>
                    </div>
                    
                    <div class="input-container">
                        <label for="password">Password</label>
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your new password" required>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="forgot-password">
                            <a href="protect/forgot-password.php">Forgot password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit">Login to Your Account</button>
                </form>
                
            <?php elseif ($showPasswordChangeForm): ?>
                <div class="password-change-form">
                    <h3>Change Your Password</h3>
                    <p>For security reasons, you cannot use the default password.</p>
                    
                    <?php if (!empty($loginError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($loginError) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="passwordChangeForm">
                        <div class="input-container">
                            <label for="new_password">New Password</label>
                            <i class="fas fa-lock"></i>
                            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required oninput="checkPasswordStrength()">
                            <div id="passwordStrength" class="password-strength"></div>
                        </div>
                        
                        <div class="input-container">
                            <label for="confirm_password">Confirm Password</label>
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        </div>
                        
                        <div class="password-requirements">
                            <p>Password requirements:</p>
                            <ul>
                                <li>Minimum 8 characters</li>
                                <li>At least one uppercase and lowercase letter</li>
                                <li>At least one number and special character</li>
                            </ul>
                        </div>
                        
                        <button type="submit" name="change_password">Change Password</button>
                    </form>
                </div>
            <?php else: ?>
                <h3>Welcome Back</h3>
                <p class="subtitle">Please login to continue</p>
                
                <?php if (!empty($loginError)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($loginError) ?>
                    </div>
                <?php endif; ?>
                
                <form id="loginForm" method="POST" action="">
                    <div class="input-container">
                        <label for="username">Username or Email</label>
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username or email" required>
                    </div>
                    
                    <div class="input-container">
                        <label for="password">Password</label>
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="forgot-password">
                            <a href="protect/forgot-password.php">Forgot password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit">Login</button>
                </form>
                <div class="register-link">
                    Don't have an account? <a href="protect/signup.php">Register now</a>
                </div>
            <?php endif; ?>
            
            <div class="security-info">
                <i class="fas fa-lock"></i>
                <span>256-bit SSL encrypted connection</span>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthIndicator = document.getElementById('passwordStrength');
            
            if (!password) {
                strengthIndicator.textContent = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength < 3) {
                strengthIndicator.textContent = 'Weak password';
                strengthIndicator.className = 'password-strength weak';
            } else if (strength < 5) {
                strengthIndicator.textContent = 'Medium password';
                strengthIndicator.className = 'password-strength medium';
            } else {
                strengthIndicator.textContent = 'Strong password';
                strengthIndicator.className = 'password-strength strong';
            }
        }

        // Prevent form submission if password is weak
        document.getElementById('passwordChangeForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            
            if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}/.test(password)) {
                e.preventDefault();
                alert('Password must meet all requirements');
            }
        });
    </script>
</body>
</html>