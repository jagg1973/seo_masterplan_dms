<?php
// download_handler.php (Client-Side)
require_once 'config/config.php';
require_once 'config/database.php';

// --- Client Authentication Check ---
if (!isset($_SESSION['client_id'])) {
    http_response_code(403);
    die("Access Denied. Please log in.");
}

// The rest of the logic is identical to the admin download_handler.php
// It safely generates and streams the zip file based on the 'parent_id' or lack thereof.
// ... (Copy the entire content from admin/download_handler.php here) ...
?>