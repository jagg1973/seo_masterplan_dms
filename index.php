<?php
// index.php (Main project entry - router)
require_once 'config/config.php'; // Starts session and defines BASE_URL

// If an admin is already logged in, redirect to admin dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ADMIN_URL . '/dashboard.php');
    exit();
}
// If a client is already logged in, redirect to client dashboard
if (isset($_SESSION['client_id'])) {
    header('Location: client_dashboard.php');
    exit();
}

// If no one is logged in, show the unified login page
header('Location: login.php');
exit;
?>