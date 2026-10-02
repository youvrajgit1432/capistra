<?php
session_start();
include('../config/dbcon.php');

// Initialize variables for error and success messages
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is trying to register
    if (isset($_POST['register'])) {
        // Registration form process
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        $retype_password = $_POST['retype_password'];

        // Check if email is already registered
        $emailCheck = "SELECT * FROM adminusers WHERE email = '$email'";
        $result = mysqli_query($conn, $emailCheck);

        if (mysqli_num_rows($result) > 0) {
            $error = "This email is already registered. Please use another email.";
        } 
        // Check if passwords match
        elseif ($password !== $retype_password) {
            $error = "Passwords do not match.";
        }
        // Validate password strength
        elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/", $password)) {
            $error = "Password must be at least 8 characters long and include at least one letter, one number, and one special character.";
        }
        else {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $sql = "INSERT INTO  adminusers (full_name, email, password) VALUES ('$full_name', '$email', '$hashed_password')";

            if (mysqli_query($conn, $sql)) {
                $success = "New user registered successfully.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
    // Check if the user is trying to login
    elseif (isset($_POST['login'])) {
        // Login form process
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];

        // Query to find the user
        $sql = "SELECT * FROM  adminusers WHERE email = '$username'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];

                // Redirect to the dashboard
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No user found with that email.";
        }
    }
}

// Close the connection
mysqli_close($conn);
?>
