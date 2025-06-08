<?php
// client_dashboard.php
require_once 'config/config.php'; // Starts session, defines BASE_URL
require_once 'core/helpers.php';   // For get_branding_setting (if needed here, or just for header/footer includes)
require_once 'config/database.php'; // For $pdo if used directly on this page

// Client Authentication Check
if (!isset($_SESSION['client_id'])) {
    header("Location: client_login.php");
    exit;
}

$client_name = htmlspecialchars($_SESSION['client_full_name']);

// Fetch branding for consistency (logo, primary_color for header/footer or main page elements)
$site_logo_path = get_branding_setting($pdo, 'logo_path');
$site_logo_url = null;
if ($site_logo_path && defined('UPLOAD_URL_PUBLIC') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path)) {
    $site_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($site_logo_path, '/') . '?v=' . time();
}
$primary_color = get_branding_setting($pdo, 'primary_color');
if (empty($primary_color) || !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $primary_color)) {
    $primary_color = '#007bff'; // Default
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
    <title>Client Dashboard - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="assets/css/client_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>:root { --primary-color: <?php echo htmlspecialchars($primary_color); ?>; }</style>
</head>
<body>
    <header class="client-header">
        <div class="container">
            <a href="client_dashboard.php" class="logo-and-title"> <?php // Group logo and title ?>
                <?php if ($site_logo_url): ?>
                    <div class="logo">
                        <img src="<?php echo htmlspecialchars($site_logo_url); ?>" alt="<?php echo htmlspecialchars(SITE_NAME); ?> Logo">
                    </div>
                <?php endif; ?>
                <span class="site-title"><?php echo htmlspecialchars(SITE_NAME); ?> - Client Portal</span>
            </a>
            
            <form action="search_results.php" method="GET" class="client-search-form">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="search" name="query" class="search-input" placeholder="Search documents..." aria-label="Search documents" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : htmlspecialchars($search_query ?? ''); // $search_query defined in search_results.php ?>">
                </div>
                <button type="submit" class="btn btn-primary search-submit-btn">Search</button>
            </form>
            
            <div class="client-header-actions"> 
                <span class="welcome-message">Welcome, <?php echo $client_name; ?>!</span>
                <a href="client_logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </header>

    <main class="container">
        <h1 class="dashboard-title">Document Hub</h1> <?php // Changed title ?>

        <div class="category-cards-container">
            <?php
            // Define the specific categories we want cards for and their display order/details
            // You might fetch these from the DB, but for a fixed set of top-level areas, this is also okay.
            // We'll link them to a page that can filter by category name or ID.
            // Let's assume your category names in the DB are "C-Level Documents", "Management Level Documents", "SEO Expert Levels", "Supporting Files"
            // Or we can use predefined slugs/IDs. For now, let's use names for display and link stubs.

            $card_categories = [
                [
                    'name' => "C-Level Documents",
                    'link_param' => "c-level", // Parameter for URL
                    'description' => "Strategic documents and reports for executive review.",
                    'icon_class' => "icon-c-level" // Placeholder for a potential icon
                ],
                [
                    'name' => "Management Level", // Matched your earlier naming
                    'link_param' => "management",
                    'description' => "Resources and guidelines for managers.",
                    'icon_class' => "icon-management"
                ],
                [
                    'name' => "SEO Expert Levels", // Matched your earlier naming
                    'link_param' => "seo-expert",
                    'description' => "Technical documentation, tools, and advanced SEO strategies.",
                    'icon_class' => "icon-seo-expert"
                ],
                [
                    'name' => "Supporting Files",
                    'link_param' => "supporting-files",
                    'description' => "General resources, templates, and other supporting materials.",
                    'icon_class' => "icon-supporting-files"
                ]
            ];

            foreach ($card_categories as $card_cat) :
                // In a more dynamic setup, you'd fetch the actual category ID from the DB based on a known name/slug
                // For now, we'll construct a link assuming view_documents.php can handle a 'category_slug' or 'category_name'
                $category_link = 'view_documents.php?category=' . urlencode($card_cat['link_param']);
            ?>
            <div class="category-card">
                <div class="card-icon <?php echo htmlspecialchars($card_cat['icon_class']); ?>">
                    <?php /* You can place an SVG or <i> tag here later for icons */ ?>
                </div>
                <h3 class="card-title"><?php echo htmlspecialchars($card_cat['name']); ?></h3>
                <p class="card-description"><?php echo htmlspecialchars($card_cat['description']); ?></p>
                <a href="<?php echo $category_link; ?>" class="btn btn-primary card-button">View Documents</a>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
     

    <footer class="client-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(SITE_NAME); ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>