<?php
require_once __DIR__ . '/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if temp user data exists
if (!isset($_SESSION['temp_user'])) {
    header('Location: signup.php');
    exit;
}

// Check if OTP was recently sent (prevent spamming)
if (isset($_SESSION['last_otp_time']) && (time() - $_SESSION['last_otp_time'] < 60)) {
    $_SESSION['otp_error'] = "Please wait at least 60 seconds before requesting a new OTP.";
    header('Location: verifyotp.php');
    exit;
}

// Include PHPMailer
require_once __DIR__ . '/config/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Generate new OTP
$new_otp = rand(100000, 999999);
$_SESSION['signup_otp'] = $new_otp;
$_SESSION['otp_expiration'] = time() + 120; // 2 minutes expiration
$_SESSION['last_otp_time'] = time(); // Update last OTP time

$email = $_SESSION['temp_user']['email'];

// Send the new OTP via email
// Email needs the optional composer package; fail safely without it.
if (!class_exists(PHPMailer::class)) {
    capistra_optional_class_missing('PHPMailer', 'phpmailer/phpmailer');
}

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
    $mail->setFrom(MAIL_FROM !== '' ? MAIL_FROM : MAIL_USERNAME, 'OTP Service');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your New Signup OTP Code';
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Your New OTP Code</title>
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
                <div class="title">New OTP Verification</div>
            </div>
            
            <div class="content">
                <h2 style="color: #2b5876; margin-top: 0;">Hello,</h2>
                
                <p class="message">
                    Here is your new One-Time Password (OTP) for account verification:
                </p>
                
                <div class="otp-container">
                    <div>Your new verification code is:</div>
                    <div class="otp-code">' . $new_otp . '</div>
                    <div>This code will expire in <strong>2 minutes</strong>.</div>
                </div>
                
                <p class="message">
                    For security reasons, please do not share this code with anyone.
                </p>
                
                <p class="note">
                    Note: This is an automated message. Please do not reply to this email.
                </p>
            </div>
            
            <div class="footer">
                © ' . date("Y") . ' Capistra. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ';
    
    $mail->send();
    $_SESSION['otp_success'] = "A new OTP has been sent to your email.";
} catch (Exception $e) {
    $_SESSION['otp_error'] = "Failed to send new OTP. Please try again.";
    error_log("Mailer Error: {$mail->ErrorInfo}");
}

// Redirect back to verifyotp.php
header('Location: protect/verifyotp.php');
exit;
?>