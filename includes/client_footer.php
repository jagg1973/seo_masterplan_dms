<?php
// includes/client_header.php (FINAL HERO DESIGN)
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
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="assets/css/client_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>:root { --primary-color: <?php echo htmlspecialchars($primary_color ?? '#007bff'); ?>; --primary-hover-color: <?php echo htmlspecialchars($primary_hover_color ?? '#0056b3'); ?>; }</style>
</head>
<body class="client-portal-body">
    <header class="client-header-redesigned">
        <div class="container header-container">
            <a href="client_dashboard.php" class="logo-and-title">
                <?php if (!empty($site_logo_url)): ?>
                    <img src="<?php echo htmlspecialchars($site_logo_url); ?>" alt="<?php echo htmlspecialchars(SITE_NAME); ?> Logo" class="header-logo">
                <?php endif; ?>
                <span class="site-title"><?php echo htmlspecialchars(SITE_NAME); ?></span>
            </a>
            <form action="search_results.php" method="GET" class="client-search-form"><i class="fas fa-search search-icon"></i><input type="search" name="query" class="search-input" placeholder="Search documents..." aria-label="Search documents"></form>
            <div class="client-header-actions"> 
                <span class="welcome-message"><i class="fas fa-user-circle"></i> Welcome, <strong><?php echo htmlspecialchars(explode(' ', $client_name)[0]); ?></strong>!</span>
                <a href="client_logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>
    <main class="container page-content">
        <div class="hero-section-pro">
            <div class="hero-aurora"></div>
            <div class="hero-content">
                <h1 class="hero-title">Your SEO Masterplan Portal</h1>
                <p class="hero-subtitle">All documents, strategies, and resources for success, organized for you.</p>
            </div>
        </div>
