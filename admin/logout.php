<?php
// admin/logout.php
require_once '../config/config.php'; // Ensures session is started

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect to login page
$unified_login_url = rtrim(BASE_URL, '/') . '/login.php';
header("Location: " . $unified_login_url);
exit;
?>