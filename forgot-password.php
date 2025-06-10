<?php
session_start(); // Used for messages if needed

// ======================================================================
// Configuration (UPDATE THESE - MUST MATCH YOUR IPN SCRIPT)
// ======================================================================
$db_host = 'localhost'; // Usually 'localhost'
$db_name = 'seo_masterplan_db';
$db_user = 'masterplan_user';
$db_pass = 'Xx11422470@';

$smtp_host = 'smtp.zoho.eu';
$smtp_user = 'info@speed.cy';
$smtp_pass = '52SHtjT0qQ6T';
$smtp_port = 465; // Or 587
$smtp_secure = 'ssl'; // Or 'tls'

$from_email = 'support@speed.cy';
$from_name = 'M&G Speed Marketing LTD.';
$reset_page_url = 'https://seo-dashboard.speed.cy/reset-password.php'; // URL for the *next* script
$client_table = 'clients'; // Your client table name
$email_col = 'email';

// ======================================================================
// PHPMailer Includes & Use Statements
// ======================================================================
// *** IMPORTANT: Adjust this path based on where PHPMailer is relative to this script! ***
// If forgot-password.php is in the root and PHPMailer is too:
require '/var/www/html/ipn-dev/PHPMailer/src/Exception.php';
require '/var/www/html/ipn-dev/PHPMailer/src/PHPMailer.php';
require '/var/www/html/ipn-dev/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ======================================================================
// Variables for displaying messages
// ======================================================================
$message_to_show = '';
$error_message = '';

// ======================================================================
// Handle Form Submission
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        try {
            // Connect to DB
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if email exists in clients table
            $stmt = $pdo->prepare("SELECT `$email_col` FROM `$client_table` WHERE `$email_col` = ?");
            $stmt->execute([$email]);

            if (!$stmt->fetchColumn()) {
                // IMPORTANT: Show a generic message even if email isn't found
                // This prevents 'email harvesting' (guessing valid emails).
                $message_to_show = "If an account exists for that email, a password reset link has been sent. Please check your inbox (and spam folder).";
            } else {
                // Email exists - Proceed with token generation and sending
                $token = bin2hex(random_bytes(32)); // 64-char hex token
                $expires = date('Y-m-d H:i:s', time() + 3600); // Token expires in 1 hour

                // Delete any old tokens for this user
                $stmt = $pdo->prepare("DELETE FROM `password_resets` WHERE `email` = ?");
                $stmt->execute([$email]);

                // Insert the new token
                $stmt = $pdo->prepare("INSERT INTO `password_resets` (`email`, `token`, `expires_at`) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires]);

                // --- Send Reset Email via PHPMailer ---
                $reset_link = $reset_page_url . '?token=' . $token;
                $mail = new PHPMailer(true);

                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host       = $smtp_host;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtp_user;
                    $mail->Password   = $smtp_pass;
                    $mail->SMTPSecure = ($smtp_secure == 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS);
                    $mail->Port       = $smtp_port;

                    //Recipients
                    $mail->setFrom($from_email, $from_name);
                    $mail->addAddress($email); // Send to the user who requested it

                    //Content
                    $mail->isHTML(true);
                    $mail->Subject = "Password Reset Request for Your SEO Masterplan Account";

                    // Format the expiry time for display
                    $expires_obj = new DateTime($expires); // $expires is from when you saved the token
                    $expires_formatted = $expires_obj->format('F j, Y, g:i a T'); // e.g., May 26, 2025, 3:00 pm CEST
                    $reset_link_escaped = htmlspecialchars($reset_link);

                    // --- Build HTML Body ---
                    $html_body = "
<html>
<head>
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 5px; }
    .header { background-color: #ffffff; padding: 30px 20px; text-align: center; border-bottom: 1px solid #e0e0e0;}
    .header h1 { color: #333; margin: 0; font-size: 24px; }
    .content { padding: 30px 40px; }
    .content p { margin-bottom: 20px; }
    .button-cta { display: inline-block; background-color: #4A47A3; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-size: 18px; text-align: center; }
    .footer { background-color: #f4f4f4; padding: 20px 30px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #e0e0e0;}
    .footer a { color: #888888; }
  </style>
</head>
<body>
  <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f4f4f4;'><tr><td align='center' style='padding: 20px 0;'>
  <table class='container' border='0' cellspacing='0' cellpadding='0'>
    <tr><td class='header'><h1>Password Reset Request</h1></td></tr>
    <tr><td class='content'>
      <p>Hello,</p>
      <p>We received a request to reset the password for your SEO Masterplan account associated with this email address.</p>
      <p>If you made this request, please click the button below to set a new password. This link is valid for 1 hour (until $expires_formatted):</p>
      <p style='text-align: center; margin: 30px 0;'>
        <a href='$reset_link_escaped' class='button-cta'>Reset Your Password</a>
      </p>
      <p>If the button above doesn't work, please copy and paste the following link into your browser:<br><a href='$reset_link_escaped' style='color: #007bff;'>$reset_link_escaped</a></p>
      <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
    </td></tr>
    <tr><td class='footer'>
      <p>M&G Speed Marketing LTD. | Speed.cy | <a href='https://speed.cy/contact-us/'>Contact Us</a></p>
    </td></tr>
  </table>
  </td></tr></table>
</body>
</html>";
                    $mail->Body = $html_body;

                    // --- Build Plain Text Alt Body ---
                    $alt_body = "Hello,\n\nWe received a request to reset your password for your SEO Masterplan account.\n\n";
                    $alt_body .= "Click here (or copy/paste into your browser) to reset your password. This link is valid for 1 hour (until $expires_formatted):\n";
                    $alt_body .= "$reset_link_escaped\n\n";
                    $alt_body .= "If you did not request this, please ignore this email.\n\n";
                    $alt_body .= "Regards,\nThe M&G Speed Marketing LTD. Team\n(Speed.cy)";
                    $mail->AltBody = $alt_body;

                    $mail->send();
                    $message_to_show = "If an account exists for that email, a password reset link has been sent. Please check your inbox (and spam folder).";

                } catch (Exception $e_mail) {
                    $error_message = "Could not send reset email at this time. Please try again later or contact support.";
                    // Log the actual error for your reference: error_log("PHPMailer Reset Error: {$mail->ErrorInfo}");
                }
            }

        } catch (PDOException $e_db) {
            $error_message = "A database error occurred. Please try again later.";
            // Log the actual error: error_log("PDO Reset Error: " . $e_db->getMessage());
        } catch (Exception $e_gen) {
            $error_message = "An unexpected error occurred. Please try again later.";
            // Log the actual error: error_log("General Reset Error: " . $e_gen->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="57x57" href="/images/favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/images/favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/images/favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/images/favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/images/favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/images/favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/images/favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/images/favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192" href="/images/favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/images/favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/images/favicon/favicon-16x16.png">
<link rel="manifest" href="/images/favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/images/favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
    <title>Forgot Password - SEO Masterplan</title>
<style>
    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        background-color: #f4f7f6; /* Light neutral background */
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .container {
        background-color: #ffffff;
        padding: 30px 40px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 450px;
        text-align: left;
    }
    h2 {
        text-align: center;
        color: #2c3e50; /* Darker, professional blue/grey */
        margin-bottom: 25px;
        font-weight: 500;
    }
    p.description {
        text-align: center;
        color: #555;
        margin-bottom: 25px;
        font-size: 15px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #444;
        font-size: 14px;
    }
    input[type="email"],
    input[type="password"] {
        width: calc(100% - 24px); /* Account for padding */
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        box-sizing: border-box; /* Important for width calculation */
    }
    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #4A47A3; /* Primary blue from screenshot */
        outline: none;
        box-shadow: 0 0 0 2px rgba(74, 71, 163, 0.2);
    }
    button[type="submit"] {
        background-color: #4A47A3; /* Primary blue/purple from screenshot */
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        width: 100%;
        transition: background-color 0.3s ease;
    }
    button[type="submit"]:hover {
        background-color: #3b3882; /* Darker shade */
    }
    .message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-size: 14px;
        text-align: center;
    }
    .success {
        background-color: #d1e7dd; /* Bootstrap success green */
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .error {
        background-color: #f8d7da; /* Bootstrap danger red */
        color: #842029;
        border: 1px solid #f5c2c7;
    }
    .back-link {
        display: block;
        text-align: center;
        margin-top: 25px;
        font-size: 14px;
    }
    .back-link a {
        color: #4A47A3;
        text-decoration: none;
    }
    .back-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
    <div class="container">
    <h2>Forgot Your Password?</h2>
    <p class="description">Enter your email address below, and we'll send you a link to reset your password.</p>

    <?php if ($message_to_show): ?>
        <p class="message success"><?php echo htmlspecialchars($message_to_show); ?></p>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="post" action="forgot-password.php">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <p class="back-link"><a href="login.php">Back to Login</a></p>

</body>
</html>