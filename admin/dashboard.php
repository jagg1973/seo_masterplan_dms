<?php
// admin/dashboard.php
require_once '../config/config.php'; 
// require_once '../config/database.php'; // If needed for this page

if (!isset($_SESSION["user_id"])) {
    $root_login_url = rtrim(BASE_URL, '/') . '/login.php';
    header("Location: " . $root_login_url);
    exit;
}

$page_title = "Dashboard"; 
$current_page = basename($_SERVER['PHP_SELF']); 

include_once 'includes/header.php'; // This line outputs DOCTYPE, html, head, opening body tags etc.
?>

    <p>Welcome to the admin dashboard for your SEO Masterplan DMS.</p>
    <p>From here, you can manage document categories, documents, branding, and more.</p>
    <p>Use the navigation on the left to get started.</p>
    <?php
include_once 'includes/footer.php'; // This line outputs closing body, html tags etc.
?>