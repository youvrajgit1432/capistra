<?php
session_start();
require_once __DIR__ . '/config/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if we have the necessary session data
if (!isset($_SESSION['temp_user']) || !isset($_SESSION['signup_otp'])) {
    die("OTP generation error. Please try again.");
}

$email = $_SESSION['temp_user']['email'];
$otp = $_SESSION['signup_otp'];

// Create a new PHPMailer instance for email
$mail = new PHPMailer(true);

try {
    // Server settings for email
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'no-reply@example.test'; // Your email address
    $mail->Password = 'change-me'; // Your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('no-reply@example.test', 'OTP Service');
    $mail->addAddress($email); // Add recipient email

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Signup OTP Code';
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Your OTP Code</title>
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");
            
            body {
                font-family: "Poppins", sans-serif;
                background-color: #f5f7fa;
                margin: 0;
                padding: 0;
                color: #333;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }
            .header {
                background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
                padding: 30px 20px;
                text-align: center;
            }
            .logo {
                color: white;
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 10px;
            }
            .logo span {
                color: #4fd1c5;
            }
            .title {
                color: white;
                font-size: 18px;
                margin-top: 10px;
            }
            .content {
                padding: 30px;
            }
            .otp-container {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                margin: 25px 0;
            }
            .otp-code {
                font-size: 32px;
                font-weight: 700;
                letter-spacing: 3px;
                color: #2b5876;
                margin: 15px 0;
                padding: 10px 20px;
                background: white;
                border-radius: 6px;
                display: inline-block;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            }
            .message {
                line-height: 1.6;
                color: #555;
                margin-bottom: 25px;
            }
            .footer {
                text-align: center;
                padding: 20px;
                font-size: 12px;
                color: #888;
                border-top: 1px solid #eee;
            }
            .button {
                display: inline-block;
                padding: 12px 25px;
                background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 500;
                margin-top: 15px;
            }
            .note {
                font-size: 12px;
                color: #999;
                margin-top: 25px;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <div class="logo">GLOBAL<span>MONEY</span></div>
                <div class="title">Secure OTP Verification</div>
            </div>
            
            <div class="content">
                <h2 style="color: #2b5876; margin-top: 0;">Hello,</h2>
                
                <p class="message">
                    Thank you for choosing Capistra. To complete your signup process, 
                    please use the following One-Time Password (OTP) to verify your account:
                </p>
                
                <div class="otp-container">
                    <div>Your verification code is:</div>
                    <div class="otp-code">' . $otp . '</div>
                    <div>This code will expire in <strong>2 minutes</strong>.</div>
                </div>
                
                <p class="message">
                    For security reasons, please do not share this code with anyone. 
                    If you didn\'t request this code, please ignore this email or contact our support team.
                </p>
                
                <p class="note">
                    Note: This is an automated message. Please do not reply to this email.
                </p>
            </div>
            
            <div class="footer">
                © ' . date("Y") . ' Capistra. All rights reserved.<br>
                Address: 123 Financial District, New York, NY 10001<br>
                <a href="#" style="color: #4e4376;">Privacy Policy</a> | <a href="#" style="color: #4e4376;">Terms of Service</a>
            </div>
        </div>
    </body>
    </html>
    ';
    // Send the email
    $mail->send();
    
    // No output here as it will interfere with the signup.php page
} catch (Exception $e) {
    // Log error but don't show to user (could be security risk)
    error_log("Mailer Error: {$mail->ErrorInfo}");
    $_SESSION['otp_error'] = "Failed to send OTP. Please try again.";
}
?>