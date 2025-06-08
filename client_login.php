<?php
// client_login.php
require_once 'config/config.php';    // Defines BASE_URL, SITE_NAME, etc., starts session
require_once 'config/database.php';  // Provides $pdo
require_once 'core/helpers.php';     // For any future helpers if needed

// If client is already logged in, redirect them to their dashboard
if (isset($_SESSION['client_id'])) {
    header("Location: client_dashboard.php"); // We'll create this page next
    exit;
}

$error_message = '';
$email_input = ''; // To repopulate email field on error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_input = trim($_POST["email"]); // For repopulation
    if (empty(trim($_POST["email"])) || empty(trim($_POST["password"]))) {
        $error_message = "Please enter both email and password.";
    } else {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        try {
            $sql = "SELECT id, full_name, password, status FROM clients WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $client = $stmt->fetch();
                if ($client['status'] === 'active' && password_verify($password, $client["password"])) {
                    // Password is correct, start a new session for client
                    $_SESSION['client_id'] = $client['id'];
                    $_SESSION['client_full_name'] = $client['full_name'];
                    $_SESSION['client_email'] = $email; // Store email if needed

                    // Update last_login_at
                    $update_login_sql = "UPDATE clients SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id";
                    $update_stmt = $pdo->prepare($update_login_sql);
                    $update_stmt->bindParam(":id", $client['id'], PDO::PARAM_INT);
                    $update_stmt->execute();

                    header("Location: client_dashboard.php"); // Redirect to client dashboard
                    exit;
                } elseif ($client['status'] !== 'active') {
                    $error_message = "Your account is not active. Please contact support.";
                } else {
                    $error_message = "Invalid email or password.";
                }
            } else {
                $error_message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Login failed. Please try again later.";
            error_log("Client Login PDOException: " . $e->getMessage());
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
    <title>Client Login - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="assets/css/client_style.css"> <?php // Use client_style.css ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Quick styles for login page - can be moved to client_style.css */
        body.client-login-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--background-color, #f8f9fa);
        }
        .login-form-container {
            background-color: var(--container-background, #fff);
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
            border: 1px solid var(--border-color, #dee2e6);
        }
        .login-form-container h2 {
            text-align: center;
            color: var(--primary-color, #007bff);
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 1.8em;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #495057; }
        .form-group input[type="email"], .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color, #ced4da);
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        .form-group input:focus {
            border-color: var(--primary-color, #007bff);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); /* Default blue shadow */
        }
        .btn-submit-login {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color, #007bff);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.05em;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .btn-submit-login:hover {
            filter: brightness(90%);
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.95em;
        }
        .client-login-logo { text-align: center; margin-bottom: 20px; }
        .client-login-logo img { max-height: 50px; }
    </style>
</head>
<body class="client-login-page">
    <div class="login-form-container">
        <?php
        // Fetch logo for login page
        $login_logo_path = get_branding_setting($pdo, 'logo_path');
        $login_logo_url = null;
        if ($login_logo_path && defined('UPLOAD_URL_PUBLIC') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $login_logo_path)) {
            $login_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($login_logo_path, '/') . '?v=' . time();
        }
        $primary_color_login = get_branding_setting($pdo, 'primary_color');
        if (empty($primary_color_login) || !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $primary_color_login)) {
            $primary_color_login = '#007bff'; // Default
        }
        ?>
        <style>:root { --primary-color: <?php echo htmlspecialchars($primary_color_login); ?>; }</style> <?php // Override primary color for this page ?>

        <?php if ($login_logo_url): ?>
            <div class="client-login-logo">
                <img src="<?php echo htmlspecialchars($login_logo_url); ?>" alt="<?php echo htmlspecialchars(SITE_NAME); ?> Logo">
            </div>
        <?php endif; ?>

        <h2>Client Portal Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email_input); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn-submit-login">Login</button>
        </form>
        <?php /* Add forgot password link later if needed */ ?>
    </div>
</body>
</html>