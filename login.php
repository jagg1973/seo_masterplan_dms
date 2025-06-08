<?php
// login.php - Unified Login Page (Tailwind CSS Version)

require_once 'config/config.php'; // Handles session_start(), defines BASE_URL, SITE_NAME, ADMIN_URL etc.
require_once 'config/database.php'; // Provides $pdo
require_once 'core/helpers.php';    // Provides get_branding_setting()

// Redirect if already logged in
if (isset($_SESSION['user_id'])) { // Admin session
    header('Location: ' . rtrim(ADMIN_URL, '/') . '/dashboard.php');
    exit();
}
if (isset($_SESSION['client_id'])) { // Client session
    header('Location: ' . rtrim(BASE_URL, '/') . '/client_dashboard.php');
    exit();
}

$admin_error_message = '';
$client_error_message = '';
$admin_username_input = '';
$client_email_input = '';

// --- Admin Login Processing ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $admin_username_input = trim($_POST['admin_username']);
    $password = $_POST['admin_password'];

    if (empty($admin_username_input) || empty($password)) {
        $admin_error_message = "Username and password are required.";
    } else {
        try {
            $sql = "SELECT id, username, password, role FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $admin_username_input, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $admin_user = $stmt->fetch();
                if (password_verify($password, $admin_user['password'])) {
                    $_SESSION['user_id'] = $admin_user['id'];
                    $_SESSION['username'] = $admin_user['username'];
                    $_SESSION['role'] = $admin_user['role'];
                    header("Location: " . rtrim(ADMIN_URL, '/') . "/dashboard.php");
                    exit();
                } else {
                    $admin_error_message = "Invalid username or password.";
                }
            } else {
                $admin_error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $admin_error_message = "An error occurred. Please try again.";
            error_log("Admin Login PDOException: " . $e->getMessage());
        }
    }
}

// --- Client Login Processing ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['client_login'])) {
    $client_email_input = trim($_POST['client_email']);
    $password = $_POST['client_password'];

    if (empty($client_email_input) || empty($password)) {
        $client_error_message = "Email and password are required.";
    } else {
        try {
            $sql = "SELECT id, full_name, email, password, status FROM clients WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $client_email_input, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $client_user = $stmt->fetch();
                if ($client_user['status'] === 'active' && password_verify($password, $client_user['password'])) {
                    $_SESSION['client_id'] = $client_user['id'];
                    $_SESSION['client_full_name'] = $client_user['full_name'];
                    $_SESSION['client_email'] = $client_user['email'];

                    $update_login_sql = "UPDATE clients SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id";
                    $update_stmt = $pdo->prepare($update_login_sql);
                    $update_stmt->bindParam(":id", $client_user['id'], PDO::PARAM_INT);
                    $update_stmt->execute();

                    header("Location: " . rtrim(BASE_URL, '/') . "/client_dashboard.php");
                    exit();
                } elseif ($client_user['status'] !== 'active') {
                    $client_error_message = "Your account is not active. Please contact support.";
                } else {
                    $client_error_message = "Invalid email or password.";
                }
            } else {
                $client_error_message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $client_error_message = "An error occurred. Please try again.";
            error_log("Client Login PDOException: " . $e->getMessage());
        }
    }
}

// Fetch branding settings
$logo_relative_path = get_branding_setting($pdo, 'logo_path');
$default_logo_url = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/assets/img/default_logo.png' : 'assets/img/default_logo.png';
$logo_to_display_url = $default_logo_url;

if ($logo_relative_path && defined('UPLOAD_URL_PUBLIC') && defined('PROJECT_ROOT_SERVER_PATH')) {
    $full_logo_path_on_server = rtrim(PROJECT_ROOT_SERVER_PATH, '/') . '/uploads/' . ltrim($logo_relative_path, '/');
    if (file_exists($full_logo_path_on_server)) {
        $logo_to_display_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($logo_relative_path, '/') . '?v=' . time();
    }
}


$primary_color_setting = get_branding_setting($pdo, 'primary_color');
$effective_primary_color = '#007bff'; // Default Tailwind/Bootstrap blue
if (!empty($primary_color_setting) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $primary_color_setting)) {
    $effective_primary_color = $primary_color_setting;
}

// Convert hex to RGB for Tailwind's arbitrary property for box-shadow (if needed)
// Tailwind focus rings can use the hex directly: focus:ring-[#xxxxxx]
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
    <title>Login - <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Project DMS'); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Define primary color as CSS variable for easier use in Tailwind arbitrary values if needed */
        :root {
            --primary-color: <?php echo htmlspecialchars($effective_primary_color); ?>;
        }
        /* Custom styling for a slightly darker hover for buttons if not handled by Tailwind utilities */
        .btn-primary-custom:hover {
            filter: brightness(90%);
        }

        /* Styles for the active tab button to use the primary color */
        .tab-btn.active {
            border-color: var(--primary-color) !important; /* Ensure this overrides other borders */
            color: var(--primary-color) !important;
            font-weight: 600; /* Make active tab bolder */
        }
        .tab-btn:not(.active) {
            color: #6b7280; /* text-gray-500 */
        }
         .tab-btn:not(.active):hover {
            color: #4b5563; /* text-gray-600 */
         }
    </style>
</head>
<body class="bg-gray-100 flex flex-col justify-center items-center min-h-screen p-4">

    <div class="login-container bg-white p-6 sm:p-8 md:p-10 rounded-xl shadow-2xl w-full max-w-lg md:max-w-xl lg:max-w-3xl">
        <div class="login-header text-center mb-6 md:mb-8">
            <img src="<?php echo htmlspecialchars($logo_to_display_url); ?>" 
                 alt="<?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Site'); ?> Logo" class="mx-auto mb-4 max-h-16 md:max-h-20 object-contain">
            <h1 class="text-2xl md:text-3xl font-semibold" style="color: <?php echo htmlspecialchars($effective_primary_color); ?>;">
                <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Project DMS'); ?>
            </h1>
        </div>

        <div class="mb-6">
            <div class="flex border-b border-gray-300">
                <button id="client-tab-btn" 
                        class="tab-btn flex-1 py-3 px-2 sm:px-4 text-center text-sm sm:text-base font-medium border-b-2 focus:outline-none" 
                        data-tab="client"
                        style="border-color: transparent;">
                    <i class="fas fa-users mr-1 sm:mr-2"></i> Client Login
                </button>
                <button id="admin-tab-btn" 
                        class="tab-btn flex-1 py-3 px-2 sm:px-4 text-center text-sm sm:text-base font-medium border-b-2 focus:outline-none" 
                        data-tab="admin"
                        style="border-color: transparent;">
                    <i class="fas fa-user-shield mr-1 sm:mr-2"></i> Admin Login
                </button>
            </div>
        </div>

        <div>
            <div id="client-login-form" class="tab-content">
                <h3 class="text-xl md:text-2xl font-medium text-center mb-6" style="color: <?php echo htmlspecialchars($effective_primary_color); ?>;">Client Portal</h3>
                <form method="post" action="login.php" class="space-y-6">
                    <?php if (!empty($client_error_message)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md text-sm">
                            <?php echo htmlspecialchars($client_error_message); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label for="client_email" class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-envelope mr-2 text-gray-500"></i>Email address</label>
                        <input type="email" id="client_email" name="client_email" 
                               value="<?php echo htmlspecialchars($client_email_input); ?>" required
                               class="mt-1 block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                      focus:outline-none focus:ring-[<?php echo htmlspecialchars($effective_primary_color); ?>] focus:border-[<?php echo htmlspecialchars($effective_primary_color); ?>] sm:text-sm">
                    </div>
                    <div>
                        <label for="client_password" class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-lock mr-2 text-gray-500"></i>Password</label>
                        <input type="password" id="client_password" name="client_password" required
                               class="mt-1 block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                      focus:outline-none focus:ring-[<?php echo htmlspecialchars($effective_primary_color); ?>] focus:border-[<?php echo htmlspecialchars($effective_primary_color); ?>] sm:text-sm">
                    </div>
                    <div class="pt-1"> <?php // Added padding-top for a bit of space ?>
                                <div class="text-sm text-right">
                                    <a href="forgot-password.php?type=client" 
                                       class="font-medium hover:underline" 
                                       style="color: <?php echo htmlspecialchars($effective_primary_color); ?>;">
                                        Forgot your password?
                                    </a>
                                </div>
                            </div>
                    <button type="submit" name="client_login"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-base font-medium text-white btn-primary-custom focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="background-color: <?php echo htmlspecialchars($effective_primary_color); ?>; focus-ring-color: <?php echo htmlspecialchars($effective_primary_color); ?>;">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login as Client
                    </button>
                </form>
            </div>

            <div id="admin-login-form" class="tab-content hidden">
                 <h3 class="text-xl md:text-2xl font-medium text-center mb-6" style="color: <?php echo htmlspecialchars($effective_primary_color); ?>;">Admin Panel</h3>
                <form method="post" action="login.php" class="space-y-6">
                    <?php if (!empty($admin_error_message)): ?>
                         <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md text-sm">
                            <?php echo htmlspecialchars($admin_error_message); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-user mr-2 text-gray-500"></i>Username</label>
                        <input type="text" id="admin_username" name="admin_username"
                               value="<?php echo htmlspecialchars($admin_username_input); ?>" required
                               class="mt-1 block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                      focus:outline-none focus:ring-[<?php echo htmlspecialchars($effective_primary_color); ?>] focus:border-[<?php echo htmlspecialchars($effective_primary_color); ?>] sm:text-sm">
                    </div>
                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-lock mr-2 text-gray-500"></i>Password</label>
                        <input type="password" id="admin_password" name="admin_password" required
                               class="mt-1 block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                                      focus:outline-none focus:ring-[<?php echo htmlspecialchars($effective_primary_color); ?>] focus:border-[<?php echo htmlspecialchars($effective_primary_color); ?>] sm:text-sm">
                    </div>
                    <button type="submit" name="admin_login"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-base font-medium text-white btn-primary-custom focus:outline-none focus:ring-2 focus:ring-offset-2"
                            style="background-color: <?php echo htmlspecialchars($effective_primary_color); ?>; focus-ring-color: <?php echo htmlspecialchars($effective_primary_color); ?>;">
                        <i class="fas fa-user-cog mr-2"></i> Login as Admin
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            const clientTabBtn = document.getElementById('client-tab-btn');
            const adminTabBtn = document.getElementById('admin-tab-btn');
            const clientForm = document.getElementById('client-login-form');
            const adminForm = document.getElementById('admin-login-form');
            const primaryColor = '<?php echo htmlspecialchars($effective_primary_color); ?>';
            const inactiveColor = 'text-gray-500 hover:text-gray-700'; // Tailwind classes for inactive tab
            const activeColor = `text-[${primaryColor}]`; // Tailwind arbitrary value class

            function setActiveTab(activeBtn, inactiveBtn, activeContent, inactiveContent) {
                // Style active button
                activeBtn.style.borderColor = primaryColor;
                activeBtn.style.color = primaryColor;
                activeBtn.classList.remove('text-gray-600', 'hover:text-gray-800'); // Example of removing default/inactive styles
                activeBtn.classList.add('font-semibold');


                // Style inactive button
                inactiveBtn.style.borderColor = 'transparent';
                inactiveBtn.style.color = '#6b7280'; // Default text-gray-500
                inactiveBtn.classList.remove('font-semibold');
                inactiveBtn.classList.add('text-gray-600', 'hover:text-gray-800');


                // Show/hide content
                activeContent.classList.remove('hidden');
                inactiveContent.classList.add('hidden');
            }

            // Default to client tab
            setActiveTab(clientTabBtn, adminTabBtn, clientForm, adminForm);
            <?php if (!empty($admin_error_message) && empty($client_error_message)): ?>
            // If there's an admin error and no client error, switch to admin tab
            setActiveTab(adminTabBtn, clientTabBtn, adminForm, clientForm);
            <?php endif; ?>


            clientTabBtn.addEventListener('click', () => {
                setActiveTab(clientTabBtn, adminTabBtn, clientForm, adminForm);
            });

            adminTabBtn.addEventListener('click', () => {
                setActiveTab(adminTabBtn, clientTabBtn, adminForm, clientForm);
            });
        });
    </script>
</body>
</html>