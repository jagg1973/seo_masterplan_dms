<?php
require_once 'config/config.php';    // Defines BASE_URL, SITE_NAME, UPLOAD_URL_PUBLIC, etc.
require_once 'config/database.php';  // Provides $pdo
require_once 'core/helpers.php';     // Provides get_branding_setting() & get_file_icon_class()

// Client Authentication Check
if (!isset($_SESSION['client_id'])) {
    header("Location: login.php"); // Redirect to unified login
    exit;
}

$client_name = htmlspecialchars($_SESSION['client_full_name']); // For header

// Get the search query
$search_query = $_GET['query'] ?? '';
$search_query_trimmed = trim($search_query);
$search_results = [];
$page_title_search = "Search Results"; // Default page title
$error_message_page = ''; // Initialize error message

if (!empty($search_query_trimmed)) {
    $page_title_search = "Search Results for \"" . htmlspecialchars($search_query_trimmed) . "\"";
    try {
        $search_term_like = "%" . $search_query_trimmed . "%";

        $sql = "SELECT d.*, c.name as category_name
                FROM documents d
                JOIN document_categories c ON d.category_id = c.id
                WHERE (d.title LIKE :query_title OR d.description LIKE :query_desc OR d.filename_orig LIKE :query_fname)
                ORDER BY d.title ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':query_title', $search_term_like, PDO::PARAM_STR);
        $stmt->bindParam(':query_desc', $search_term_like, PDO::PARAM_STR);
        $stmt->bindParam(':query_fname', $search_term_like, PDO::PARAM_STR);
        
        $stmt->execute();
        $search_results = $stmt->fetchAll();

    } catch (PDOException $e) {
        $error_message_page = "Error performing search. Please try again later.";
        error_log("Search Results PDOException: " . $e->getMessage());
    }
} elseif (isset($_GET['query'])) { // Query was submitted but empty after trimming
    $error_message_page = "Please enter a search term to find documents.";
} else {
    // No query submitted (e.g., direct access to search_results.php without ?query=)
    $error_message_page = "Please use the search bar to find documents.";
}

// Fetch branding for consistency
$site_logo_path = get_branding_setting($pdo, 'logo_path');
$site_logo_url = null;
if ($site_logo_path && defined('UPLOAD_URL_PUBLIC') && defined('PROJECT_ROOT_SERVER_PATH') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path)) {
    $site_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($site_logo_path, '/') . '?v=' . time();
}
$primary_color_setting = get_branding_setting($pdo, 'primary_color');
if (empty($primary_color_setting) || !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $primary_color_setting)) {
    $primary_color = '#007bff'; // Fallback default defined in client_style.css :root will also have this
} else {
    $primary_color = $primary_color_setting;
}
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
    <title><?php echo htmlspecialchars($page_title_search); ?> - <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'DMS'); ?></title>
    <link rel="stylesheet" href="assets/css/client_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($primary_color); ?>;
            /* Define hover color based on primary, or set a fixed one if calculation is complex */
            /* For simplicity, client_style.css can use filter: brightness(90%) on elements using --primary-color for hover */
        }
        /* Specific styles for this page if needed, otherwise rely on client_style.css */
        .search-results-header {
            font-size: 1.8em; /* Ensure consistency with .page-title from client_style.css if desired */
            color: var(--headings-color); /* Relies on --headings-color from client_style.css */
            margin-bottom: 25px;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }
        .no-results-message {
            text-align: center;
            font-size: 1.1em;
            color: var(--text-muted-color); /* Relies on --text-muted-color from client_style.css */
            padding: 30px 0;
        }
        /* Custom highlight style for search terms */
        mark {
            background-color: var(--primary-color); /* Use a distinct highlight color, primary or yellow */
            color: #ffffff; /* Text color on highlight */
            padding: 0.1em 0.2em;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <header class="client-header">
        <div class="container">
            <a href="client_dashboard.php" class="logo-and-title">
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
                    <input type="search" name="query" class="search-input" placeholder="Search documents..." aria-label="Search documents" value="<?php echo htmlspecialchars($search_query); ?>">
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
        <a href="client_dashboard.php" class="back-to-dashboard">&larr; Back to Document Hub</a>

        <h1 class="search-results-header"><?php echo htmlspecialchars($page_title_search); ?></h1>

        <?php if (!empty($error_message_page)): // Display general page errors (e.g. "enter search term") ?>
            <p class="no-results-message"><?php echo htmlspecialchars($error_message_page); ?></p>
        <?php elseif (!empty($search_query_trimmed) && empty($search_results)): // Query was made, but no results ?>
            <p class="no-results-message">No documents found matching your search for "<strong><?php echo htmlspecialchars($search_query_trimmed); ?></strong>".</p>
        <?php elseif (!empty($search_results)): ?>
            <p style="color: var(--text-muted-color); margin-bottom: 20px;">Found <?php echo count($search_results); ?> document(s) matching your query:</p>
            <ul class="document-list">
                <?php foreach ($search_results as $doc):
                    $icon_class = get_file_icon_class($doc['filename_orig']);
                ?>
                    <li class="document-item">
                        <div class="document-icon-type">
                             <i class="<?php echo htmlspecialchars($icon_class); ?>"></i>
                        </div>
                        <h4>
                            <a href="<?php echo htmlspecialchars(rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($doc['filepath'], '/')); ?>" download="<?php echo htmlspecialchars($doc['filename_orig']); ?>">
                                <?php echo htmlspecialchars($doc['title']); ?>
                            </a>
                        </h4>
                        <div class="document-meta">
                            <span><strong>Category:</strong> <?php echo htmlspecialchars($doc['category_name']); ?></span>
                            <?php if (!empty($doc['version'])): ?>
                                <span><strong>Version:</strong> <?php echo htmlspecialchars($doc['version']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($doc['language'])): ?>
                                <span><strong>Language:</strong> <?php echo htmlspecialchars($doc['language']); ?></span>
                            <?php endif; ?>
                            <span><strong>File:</strong> <?php echo htmlspecialchars($doc['filename_orig']); ?></span>
                        </div>
                        <?php if (!empty($doc['description'])): ?>
                            <p class="document-description">
                                <?php
                                $desc_highlighted = htmlspecialchars($doc['description']);
                                if (!empty($search_query_trimmed)) {
                                    // Case-insensitive highlighting
                                    $desc_highlighted = preg_replace('/(' . preg_quote($search_query_trimmed, '/') . ')/i', '<mark>$1</mark>', $desc_highlighted);
                                }
                                echo nl2br($desc_highlighted);
                                ?>
                            </p>
                        <?php endif; ?>
                        <div class="document-actions">
                            <button type="button" class="btn btn-secondary view-document-btn" 
                                    data-filepath="<?php echo htmlspecialchars(rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($doc['filepath'], '/')); ?>"
                                    data-filename="<?php echo htmlspecialchars($doc['filename_orig']); ?>"
                                    data-fileext="<?php echo htmlspecialchars(strtolower(pathinfo($doc['filename_orig'], PATHINFO_EXTENSION))); ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <a href="<?php echo htmlspecialchars(rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($doc['filepath'], '/')); ?>" class="download-button" download="<?php echo htmlspecialchars($doc['filename_orig']); ?>">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>

    <div id="documentViewModal" class="modal-overlay" style="display: none;">
        <div class="modal-content document-viewer-modal-content">
            <div class="document-viewer-header">
                <h4 id="documentViewerTitle">Document Preview</h4>
                <button id="closeDocumentViewModal" class="close-modal-btn">&times;</button>
            </div>
            <div id="documentViewerContent" class="document-viewer-body">
                </div>
            <div class="document-viewer-footer">
                <a href="#" id="documentViewerDownloadLink" class="btn btn-primary" download>Download File</a>
            </div>
        </div>
    </div>

    <footer class="client-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'DMS'); ?>. All rights reserved.</p>
        </div>
    </footer>
<script src="assets/js/client_scripts.js"></script>
</body>
</html>