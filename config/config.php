<?php
// config/config.php

// Error Reporting (Development vs Production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Protocol and Host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Determine Project Root URL Path based on server paths
$project_root_on_server = dirname(__DIR__); // Server path to the project's root directory (seo_masterplan_dms)
$doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'); // Server's document root

$project_root_url_path = '';
// If the project is in a subdirectory of the document root, get that subdirectory path
if (strpos($project_root_on_server, $doc_root) === 0 && strlen($project_root_on_server) > strlen($doc_root)) {
    $project_root_url_path = substr($project_root_on_server, strlen($doc_root));
}
// Ensure forward slashes and no trailing slash for BASE_URL if it's just the host.
// $project_root_url_path will be like "/seo_masterplan_dms" or "" if at web root.
define('BASE_URL', $protocol . $host . str_replace('\\', '/', $project_root_url_path));

// Define server path for uploads (for PHP file operations)
// __DIR__ is the directory of the current file (config.php), so dirname(__DIR__) is the project root.
define('PROJECT_ROOT_SERVER_PATH', dirname(__DIR__));
define('UPLOAD_DIR_SERVER', PROJECT_ROOT_SERVER_PATH . '/uploads/'); // Server file system path

// Define public URL path for uploads (for links in HTML)
define('UPLOAD_URL_PUBLIC', BASE_URL . '/uploads/');

// Site Name
define('SITE_NAME', 'SEO Masterplan DMS');

// Company Name (for legal pages, etc.)
define('COMPANY_NAME', 'M&G Speed Marketing LTD.');

// Admin path URL
define('ADMIN_URL', BASE_URL . '/admin');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>