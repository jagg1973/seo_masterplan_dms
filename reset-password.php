<?php
session_start();

// ======================================================================
// Configuration (UPDATE THESE - MUST MATCH YOUR OTHER SCRIPTS)
// ======================================================================
$db_host = 'localhost'; // Usually 'localhost'
$db_name = 'seo_masterplan_db';
$db_user = 'masterplan_user';
$db_pass = 'Xx11422470@';

$client_table = 'clients'; // Your client table name
$email_col = 'email';
$pass_col = 'password';
$login_page = 'https://seo-dashboard.speed.cy/login.php'; // Your client login page

// ======================================================================
// Variables
// ======================================================================
$token_valid = false;
$token_from_url = $_GET['token'] ?? null;
$error_message = '';
$success_message = '';

// ======================================================================
// Database Connection
// ======================================================================
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // For a production site, you might want to log this and show a generic error.
    die("Database connection failed. Please try again later or contact support.");
}

// ======================================================================
// Token Validation (on initial GET request)
// ======================================================================
if ($token_from_url && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->execute([$token_from_url]);
    $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset_request) {
        $expires_at = new DateTime($reset_request['expires_at']);
        $now = new DateTime();
        if ($now < $expires_at) {
            $token_valid = true;
            $_SESSION['reset_email'] = $reset_request['email']; // Store email for POST request
            $_SESSION['reset_token'] = $token_from_url; // Store token for POST request
        } else {
            $error_message = "This password reset link has expired. Please request a new one.";
            // Optionally, delete the expired token here
            // $stmt_delete = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            // $stmt_delete->execute([$token_from_url]);
        }
    } else {
        $error_message = "Invalid password reset link. Please try again or request a new one.";
    }
}

// ======================================================================
// Handle Password Reset Form Submission (POST request)
// Handle Password Reset Form Submission (POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token_from_form = $_POST['token'] ?? null;
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Re-verify token
    if (!$token_from_form || !isset($_SESSION['reset_token']) || $token_from_form !== $_SESSION['reset_token']) {
        $error_message = "Invalid session or token mismatch. Please start the reset process again.";
        $token_valid = false;
    } elseif (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please enter and confirm your new password.";
        $token_valid = true;
    } elseif ($new_password !== $confirm_password) {
        $error_message = "The passwords do not match. Please try again.";
        $token_valid = true;
    } else {
        // *** NEW PASSWORD STRENGTH CHECKS ***
        $is_strong = true;
        if (strlen($new_password) < 10) { // Increased minimum length
            $error_message = "Password must be at least 10 characters long.";
            $is_strong = false;
        } elseif (!preg_match('/[A-Z]/', $new_password)) {
            $error_message = "Password must include at least one uppercase letter.";
            $is_strong = false;
        } elseif (!preg_match('/[a-z]/', $new_password)) {
            $error_message = "Password must include at least one lowercase letter.";
            $is_strong = false;
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $error_message = "Password must include at least one number.";
            $is_strong = false;
        } elseif (!preg_match('/[\W_]/', $new_password)) { // \W is non-alphanumeric, _ is underscore
            $error_message = "Password must include at least one special character (e.g., !@#$%^&*).";
            $is_strong = false;
        }

        if (!$is_strong) {
            $token_valid = true; // Keep form visible if password is not strong
        } else {
            // Password is strong, proceed with reset
            $email_to_reset = $_SESSION['reset_email'] ?? null;
            if ($email_to_reset) {
                try {
                    $password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE `$client_table` SET `$pass_col` = ? WHERE `$email_col` = ?");
                    $stmt->execute([$password_hashed, $email_to_reset]);

                    $stmt = $pdo->prepare("DELETE FROM `password_resets` WHERE `token` = ?");
                    $stmt->execute([$token_from_form]);

                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_token']);
                    $success_message = "Your password has been successfully reset! You can now <a href='{$login_page}'>log in</a>.";
                    $token_valid = false;
                } catch (PDOException $e) {
                    $error_message = "Database error during password update. Please try again.";
                    // Log $e->getMessage()
                    $token_valid = true;
                }
            } else {
                $error_message = "Session expired or invalid. Please request a new password reset link.";
                $token_valid = false;
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
    <link rel="apple-touch-icon" sizes="57x57" href="https://speed.cy/images/favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="https://speed.cy/images/favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="https://speed.cy/images/favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="https://speed.cy/images/favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="https://speed.cy/images/favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="https://speed.cy/images/favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="https://speed.cy/images/favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="https://speed.cy/images/favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="https://speed.cy/images/favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192" href="https://speed.cy/images/favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="https://speed.cy/images/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="https://speed.cy/images/favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="https://speed.cy/images/favicon/favicon-16x16.png">
<link rel="manifest" href="https://speed.cy/images/favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="https://speed.cy/images/favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
    <title>Reset Your Password - SEO Masterplan</title>
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
    <h2>Reset Your Password</h2>

    <?php if ($success_message): ?>
        <p class="message success"><?php echo $success_message; // HTML is allowed here if $success_message contains it ?></p>
    <?php elseif ($error_message): ?>
        <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <?php if ($token_valid && !$success_message): // Show form if token is valid and not yet successfully reset ?>
        <p class="description">Please enter your new password below.</p>
        <form method="post" action="reset-password.php?token=<?php echo htmlspecialchars($token_from_url); // Keep token in URL for action ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_from_url); ?>">
            
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <button type="submit">Set New Password</button>
        </form>
    <?php elseif (!$success_message && !$error_message && !$token_from_url): // If no token at all ?>
        <p class="message error">No reset token provided. Please request a password reset from the <a href="forgot-password.php">forgot password page</a>.</p>
    <?php endif; ?>

    <p class="back-link"><a href="<?php echo htmlspecialchars($login_page); ?>">Back to Login</a></p>

</body>
</html>