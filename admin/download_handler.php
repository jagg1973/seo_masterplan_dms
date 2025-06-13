<?php
// admin/download_handler.php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION["user_id"])) {
    die("Access denied.");
}

$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;
$zip_filename = 'SEO_Masterplan_Documents.zip';
$documents_to_zip = [];

try {
    if ($parent_id) {
        // Download documents for a specific folder/card
        // 1. Get the parent card's title for the zip file name
        $stmt_parent = $pdo->prepare("SELECT title FROM dashboard_links WHERE id = ?");
        $stmt_parent->execute([$parent_id]);
        $parent_title = $stmt_parent->fetchColumn();
        // Sanitize title for filename
        $safe_parent_title = preg_replace('/[^a-zA-Z0-9_\-]/', '', str_replace(' ', '_', $parent_title ?? 'Folder'));
        $zip_filename = $safe_parent_title . '_Documents.zip';


        // 2. Get all child links associated with this parent
        $stmt_children = $pdo->prepare("SELECT url FROM dashboard_links WHERE parent_id = ?");
        $stmt_children->execute([$parent_id]);
        $child_urls = $stmt_children->fetchAll(PDO::FETCH_COLUMN);

        // 3. From the URLs, extract the document filepaths and fetch from DB
        if (!empty($child_urls)) {
            $base_upload_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/'; // Use UPLOAD_URL_PUBLIC from config
            $filepaths = [];
            foreach ($child_urls as $url) {
                if (strpos($url, $base_upload_url) === 0) {
                    $filepaths[] = substr($url, strlen($base_upload_url));
                }
            }
            if(!empty($filepaths)){
                $placeholders = rtrim(str_repeat('?,', count($filepaths)), ',');
                $sql = "SELECT filename_orig, filepath FROM documents WHERE filepath IN ($placeholders)";
                $stmt_docs = $pdo->prepare($sql);
                $stmt_docs->execute($filepaths);
                $documents_to_zip = $stmt_docs->fetchAll();
            }
        }

    } else {
        // Download ALL documents
        $stmt_all_docs = $pdo->query("SELECT filename_orig, filepath FROM documents");
        $documents_to_zip = $stmt_all_docs->fetchAll();
    }

    if (empty($documents_to_zip)) {
        die("No documents found to download.");
    }

    // --- Create and send the ZIP file ---
    $zip = new ZipArchive();
    $temp_zip_path = tempnam(sys_get_temp_dir(), 'admin_dmszip'); // Create a temporary file

    if ($zip->open($temp_zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($documents_to_zip as $doc) {
            $file_on_server = UPLOAD_DIR_SERVER . $doc['filepath'];
            if (file_exists($file_on_server)) {
                $zip->addFile($file_on_server, $doc['filename_orig']);
            }
        }
        $zip->close();

        // Send headers and stream the file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zip_filename) . '"');
        header('Content-Length: ' . filesize($temp_zip_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($temp_zip_path);
        
        // Clean up the temporary zip file
        unlink($temp_zip_path);
        exit;
    } else {
        die('Failed to create the ZIP file.');
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}