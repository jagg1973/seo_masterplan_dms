<?php
// admin/includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); 
    exit;
}
// $current_page should be defined in the calling script
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
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?> - <?php echo defined('SITE_NAME') ? SITE_NAME : 'DMS'; ?></title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include_once 'sidebar.php'; ?>
        <div class="admin-main-content">
            <header class="admin-header">
                <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Dashboard'; ?></h1>
                <div class="user-info">
                    <span>Welcome, <?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : 'Admin'; ?>!</span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            <div class="admin-page-content">