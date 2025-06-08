<?php
// config/config.php

// Error Reporting (Development vs Production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Protocol and Host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Determine Project Root Path relative to Document Root
// SCRIPT_NAME is like /seo_masterplan_dms/admin/some_script.php or /seo_masterplan_dms/index.php
$script_directory = dirname($_SERVER['SCRIPT_NAME']); // e.g., /seo_masterplan_dms/admin or /seo_masterplan_dms

// If the script is in 'admin' or 'public', the project root is one level up
if (basename($script_directory) === 'admin' || basename($script_directory) === 'public') {
    $project_root_url_path = dirname($script_directory);
} else {
    $project_root_url_path = $script_directory;
}

// Normalize: remove trailing slash for consistency, unless it's the web root "/"
$project_root_url_path = rtrim($project_root_url_path, '/\\');
if ($project_root_url_path === '') {
    $project_root_url_path = '/'; // App is in the web root
}

define('BASE_URL', $protocol . $host . ($project_root_url_path === '/' ? '' : $project_root_url_path));

// Define server path for uploads (for PHP file operations)
// __DIR__ is the directory of the current file (config.php), so dirname(__DIR__) is the project root.
define('PROJECT_ROOT_SERVER_PATH', dirname(__DIR__));
define('UPLOAD_DIR_SERVER', PROJECT_ROOT_SERVER_PATH . '/uploads/'); // Server file system path

// Define public URL path for uploads (for links in HTML)
define('UPLOAD_URL_PUBLIC', BASE_URL . '/uploads/');

// Site Name
define('SITE_NAME', 'SEO Masterplan DMS');

// Admin path URL
define('ADMIN_URL', BASE_URL . '/admin');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>