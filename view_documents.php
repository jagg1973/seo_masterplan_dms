<?php
require_once 'config/config.php';    // Defines BASE_URL, SITE_NAME, UPLOAD_URL_PUBLIC, etc.
require_once 'config/database.php';  // Provides $pdo
require_once 'core/helpers.php';     // Provides get_branding_setting()

// Client Authentication Check
if (!isset($_SESSION['client_id'])) {
    header("Location: client_login.php");
    exit;
}

$client_name = htmlspecialchars($_SESSION['client_full_name']); // For header

// Get the category identifier from the URL
$category_param = $_GET['category'] ?? null;
$category_id = null;
$category_name_display = "Documents"; // Default title
$documents_for_category = [];
$error_message_page = '';

if (!$category_param) {
    $error_message_page = "No category specified.";
} else {
    // Map URL param to actual category name for querying
    $category_name_map = [
        "c-level" => "C-Level Documents",
        "management" => "Management Level",
        "seo-expert" => "SEO Expert Levels",
        "supporting-files" => "Supporting Files"
    ];

    $target_category_name = $category_name_map[strtolower($category_param)] ?? ucfirst(str_replace('-', ' ', $category_param)); // Fallback

    try {
        $stmt_cat = $pdo->prepare("SELECT id, name, description FROM document_categories WHERE name = ?");
        $stmt_cat->execute([$target_category_name]);
        $category_data = $stmt_cat->fetch();

        if ($category_data) {
            $category_id = $category_data['id'];
            $category_name_display = $category_data['name'];
            $category_description_display = $category_data['description']; // For display

            // =======================================================================
            // MODIFICATION 1: SQL query is updated to order for hierarchy.
            // =======================================================================
            $stmt_docs = $pdo->prepare(
                 "SELECT * FROM documents WHERE category_id = ? ORDER BY parent_id, display_order, title ASC"
            );
            $stmt_docs->execute([$category_id]);
            $documents_for_category = $stmt_docs->fetchAll();

            if (empty($documents_for_category)) {
                $error_message_page = "No documents found in the '" . htmlspecialchars($category_name_display) . "' category.";
            }
        } else {
            $error_message_page = "Category '" . htmlspecialchars($target_category_name) . "' not found.";
        }
    } catch (PDOException $e) {
        $error_message_page = "Error fetching documents. Please try again later.";
        error_log("View Documents PDOException: " . $e->getMessage());
    }
}

// =======================================================================
// ADDITION 1: Hierarchy helper functions are now part of this file.
// =======================================================================
/**
 * Builds a hierarchical array from a flat array of documents.
 * @param array $elements The flat array of documents.
 * @param int|null $parentId The ID of the parent to start from.
 * @return array The hierarchical array of documents.
 */
function build_doc_hierarchy(array $elements, $parentId = null) {
    $branch = [];
    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            $children = build_doc_hierarchy($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }
    return $branch;
}

/**
 * Renders a hierarchical list of documents as nested HTML <ul> elements.
 * @param array $documents The hierarchical array of documents.
 */
function render_doc_list(array $documents) {
    echo '<ul class="document-list">';
    foreach ($documents as $doc) {
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
                <?php if (!empty($doc['version'])): ?>
                    <span><strong>Version:</strong> <?php echo htmlspecialchars($doc['version']); ?></span>
                <?php endif; ?>
                <?php if (!empty($doc['language'])): ?>
                    <span><strong>Language:</strong> <?php echo htmlspecialchars($doc['language']); ?></span>
                <?php endif; ?>
                <span><strong>File:</strong> <?php echo htmlspecialchars($doc['filename_orig']); ?></span>
            </div>
            <?php if (!empty($doc['description'])): ?>
                <p class="document-description"><?php echo nl2br(htmlspecialchars($doc['description'])); ?></p>
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
            <?php
            // Recursive call to render children if they exist
            if (!empty($doc['children'])) {
                echo '<div class="document-children-container">';
                render_doc_list($doc['children']);
                echo '</div>';
            }
            ?>
        </li>
        <?php
    }
    echo '</ul>';
}

// Fetch branding for consistency
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
    <title><?php echo htmlspecialchars($category_name_display); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="assets/css/client_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($primary_color); ?>;
        }
        .back-to-dashboard {
            display: inline-block;
            margin-bottom: 25px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .back-to-dashboard:hover {
            text-decoration: underline;
        }
        .page-title { /* For category name on this page */
            font-size: 2em;
            color: var(--text-color, #333);
            margin-bottom: 10px; /* Reduced from dashboard title */
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        .category-page-description { /* For category description */
            font-size: 1.05em;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.7;
        }
    </style>
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
        <a href="client_dashboard.php" class="back-to-dashboard">&larr; Back to Document Hub</a>

        <h1 class="page-title"><?php echo htmlspecialchars($category_name_display); ?></h1>

        <?php if (!empty($category_description_display)): ?>
            <p class="category-page-description"><?php echo nl2br(htmlspecialchars($category_description_display)); ?></p>
        <?php endif; ?>

        <?php if (!empty($error_message_page) && empty($documents_for_category)): // Show error if it's set and no docs (covers "no docs in cat" too) ?>
            <div class="empty-table-message">
                <p><?php echo htmlspecialchars($error_message_page); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($documents_for_category)):
            // =======================================================================
            // MODIFICATION 2: Build and render the hierarchy.
            // =======================================================================
            $document_hierarchy = build_doc_hierarchy($documents_for_category);

            if (!empty($document_hierarchy)) {
                render_doc_list($document_hierarchy);
            } else {
                // This handles cases where documents exist but none are top-level (all have a parent_id)
                // which might indicate an orphaned data structure.
                echo '<div class="empty-table-message"><p>No top-level documents found to display.</p></div>';
            }
        ?>
        <?php elseif (empty($error_message_page)):
             echo '<div class="empty-table-message"><p>No documents are currently available in this category.</p></div>';
        ?>
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
            <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(SITE_NAME); ?>. All rights reserved.</p>
        </div>
    </footer>
<script src="assets/js/client_scripts.js"></script>
</body>
</html>