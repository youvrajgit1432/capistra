<?php
session_start();
include('db_connection.php');

// Access password (change this to your desired password)
$accessPassword = "admin123"; // You can change this to any password you want

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
$success = '';

// Handle form submission (only if access is granted)
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
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM adminusers WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO adminusers (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = 'Admin user created successfully!';
                // Optionally log in the user directly
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                header('Refresh: 2; URL=../admin/index.php');
            } else {
                $error = 'Error creating user: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .signup-container, .access-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
            text-align: center;
        }
        .success {
            color: #4CAF50;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #4CAF50;
            text-decoration: none;
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
                <div class="form-group">
                    <label for="access_password">Password</label>
                    <input type="password" id="access_password" name="access_password" required>
                </div>
                <button type="submit">Continue to Signup</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Signup Form (only shown after password verification) -->
        <div class="signup-container">
            <h2>Admin Signup</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php else: ?>
                <form action="signup.php" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (min 8 characters)</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    <button type="submit">Create Admin Account</button>
                </form>
            <?php endif; ?>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>