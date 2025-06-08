<?php
// admin/includes/sidebar.php
// $current_page variable should be set in the script that includes this sidebar.

// Fetch logo path - this requires $pdo to be available.
// If $pdo is not always available here, this logic might need to be in header.php
// or the logo path passed as a variable.
// For simplicity, assuming $pdo is accessible or config.php is included before sidebar.
if (!isset($pdo) && file_exists(__DIR__ . '/../../config/database.php')) { // Relative path to database.php from admin/includes/
    require_once __DIR__ . '/../../config/database.php';
}

$site_logo_url = null;
if (isset($pdo)) { // Check if $pdo is available
    // Re-use the helper function if it can be included, or duplicate minimal logic
    // For now, direct query for simplicity in this isolated file:
    try {
        $stmt_logo = $pdo->prepare("SELECT setting_value FROM branding_settings WHERE setting_name = 'logo_path'");
        $stmt_logo->execute();
        $logo_relative_path_from_db = $stmt_logo->fetchColumn();
        if ($logo_relative_path_from_db) {
            // Construct full public URL using UPLOAD_URL_PUBLIC, assuming it ends with /uploads/
            // and logo_relative_path_from_db is like 'branding/logo.png'
            // UPLOAD_URL_PUBLIC is like http://localhost/seo_masterplan_dms/uploads/
            // So we need to ensure the path is correct if logo_relative_path_from_db is just 'branding/logo.png'
            // It should be UPLOAD_URL_PUBLIC . $logo_relative_path_from_db

            // Correctly reference UPLOAD_URL_PUBLIC if config.php is included before this sidebar
            if (defined('UPLOAD_URL_PUBLIC') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $logo_relative_path_from_db) ) {
                 $site_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($logo_relative_path_from_db, '/') . '?v=' . time();
            }
        }
    } catch (PDOException $e) {
        // Error fetching logo, $site_logo_url remains null
    }
}
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2><?php echo defined('SITE_NAME') ? SITE_NAME : 'DMS Admin'; ?></h2>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php">Dashboard</a>
            </li>     
            <li class="<?php echo ($current_page == 'manage_categories.php') ? 'active' : ''; ?>">
                <a href="manage_categories.php">Manage Categories</a>
            </li>
            <li class="<?php echo ($current_page == 'manage_documents.php') ? 'active' : ''; ?>">
                <a href="manage_documents.php">Manage Documents</a>
            </li>
            <li class="<?php echo (isset($current_page) && $current_page == 'branding_settings.php') ? 'active' : ''; ?>">
                <a href="branding_settings.php">Branding</a>
            </li>
            </ul>
    </nav>
</aside>