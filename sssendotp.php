<?php
require_once __DIR__ . '/config/app.php';

session_start();
require_once __DIR__ . '/config/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if this is for password reset or signup
if (isset($_SESSION['reset_email'])) {
    $email = $_SESSION['reset_email'];
    $otp = $_SESSION['reset_otp'];
    $subject = 'Password Reset OTP';
    $body = 'Your password reset OTP code is: ' . $otp . '. This code will expire in 2 minutes.';
} elseif (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $otp = $_SESSION['signup_otp'];
    $subject = 'Account Verification OTP';
    $body = 'Your account verification OTP code is: ' . $otp . '. This code will expire in 2 minutes.';
} else {
    // No valid session, redirect appropriately
    if (isset($_SESSION['reset_email'])) {
        header('Location: forgot-password.php');
    } else {
        header('Location: signup.php');
    }
    exit;
}

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    if (MAIL_USERNAME === '' || MAIL_PASSWORD === '') {
        throw new Exception('Mail is not configured. Set MAIL_USERNAME and MAIL_PASSWORD in .env.');
    }

    // Server settings for email
    $mail->isSMTP();
    $mail->Host = MAIL_HOST !== '' ? MAIL_HOST : 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = MAIL_PORT > 0 ? MAIL_PORT : 587;

    // Recipients
    $mail->setFrom(MAIL_FROM !== '' ? MAIL_FROM : MAIL_USERNAME, 'Capistra');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    // Send the email
    $mail->send();
    
    // No output here as we'll redirect
} catch (Exception $e) {
    // Log error or handle it appropriately
    error_log("Mailer Error: " . $mail->ErrorInfo);
    
    // You might want to set a session error message here
    $_SESSION['email_error'] = 'Failed to send OTP. Please try again.';
    
    // Redirect back appropriately
    if (isset($_SESSION['reset_email'])) {
        header('Location: forgot-password.php');
    } else {
        header('Location: signup.php');
    }
    exit;
}
?>