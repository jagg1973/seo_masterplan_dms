<?php
// client_logout.php
require_once 'config/config.php'; // Ensures session is started via config.php

// Unset all client-specific session variables
unset($_SESSION['client_id']);
unset($_SESSION['client_full_name']);
unset($_SESSION['client_email']);

// If you want to destroy the entire session (including any admin session if on same browser, less common):
// $_SESSION = array(); // Clear all session data
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params["path"], $params["domain"],
//         $params["secure"], $params["httponly"]
//     );
// }
// session_destroy();

// Redirect to client login page
header("Location: login.php"); // It's in the same directory
exit;
?>