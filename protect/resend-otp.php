<?php
session_start();
include('protect/db_connection.php');

// Check if email exists in session
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot-password.php');
    exit;
}

// Generate new OTP
$otp = rand(100000, 999999);
$_SESSION['reset_otp'] = $otp;
$_SESSION['otp_expiration'] = time() + 120; // 2 minutes expiration

// Send OTP to email
include('../sssendotp.php');

// Redirect back to OTP verification
header('Location: verify-reset-otp.php');
exit;
?>