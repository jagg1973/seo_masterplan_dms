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
        $zip_filename = str_replace(' ', '_', $parent_title) . '_Documents.zip';

        // 2. Get all child links associated with this parent
        $stmt_children = $pdo->prepare("SELECT url FROM dashboard_links WHERE parent_id = ?");
        $stmt_children->execute([$parent_id]);
        $child_urls = $stmt_children->fetchAll(PDO::FETCH_COLUMN);

        // 3. From the URLs, extract the document filepaths and fetch from DB
        if (!empty($child_urls)) {
            $base_upload_url = rtrim(BASE_URL, '/') . '/uploads/';
            $placeholders = rtrim(str_repeat('?,', count($child_urls)), ',');
            $filepaths = [];
            foreach ($child_urls as $url) {
                if (strpos($url, $base_upload_url) === 0) {
                    $filepaths[] = substr($url, strlen($base_upload_url));
                }
            }
            if(!empty($filepaths)){
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
    if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
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
        header('Content-Length: ' . filesize($zip_filename));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($zip_filename);
        
        // Clean up the temporary zip file
        unlink($zip_filename);
        exit;
    } else {
        die('Failed to create the ZIP file.');
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}