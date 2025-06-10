<?php
// admin/dashboard.php (CORRECTED & COMPLETE)
require_once '../config/config.php';
require_once '../config/database.php';

// --- Authentication Check ---
if (!isset($_SESSION["user_id"])) {
    $root_login_url = rtrim(BASE_URL, '/') . '/login.php';
    header("Location: " . $root_login_url);
    exit;
}

$page_title = "Dashboard";
$current_page = basename($_SERVER['PHP_SELF']);

// --- Fetch Dashboard Links ---
try {
    $columns_stmt = $pdo->query("SHOW COLUMNS FROM dashboard_links LIKE 'icon'");
    $icon_column_exists = $columns_stmt->fetch() !== false;

    $query = "SELECT id, title, url, parent_id, " . ($icon_column_exists ? "icon" : "'' as icon") . " FROM dashboard_links ORDER BY parent_id, display_order, title";
    $stmt = $pdo->query($query);
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}

// --- Helper function to group children under their parents ---
function build_card_structure(array $links) {
    $structured = [];
    $children = [];

    foreach ($links as $link) {
        if ($link['parent_id'] === null) {
            $structured[$link['id']] = $link;
            $structured[$link['id']]['children'] = [];
        } else {
            $children[] = $link;
        }
    }

    foreach ($children as $child) {
        if (isset($structured[$child['parent_id']])) {
            $structured[$child['parent_id']]['children'][] = $child;
        }
    }
    return array_values($structured);
}

$card_structure = build_card_structure($links);

// --- Include Header ---
include_once 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="dashboard-main-actions">
    <a href="download_handler.php" class="btn btn-primary btn-download-all">
        <i class="fas fa-cloud-download-alt"></i> Download All Documents
    </a>
</div>
<div class="dashboard-grid-container">
    <?php if (empty($card_structure)): ?>
        <div class="empty-table-message">
            No dashboard items have been configured yet. Please add entries in "Manage Dashboard".
        </div>
    <?php else: ?>
        <?php foreach ($card_structure as $card): ?>
            <div class="dashboard-card">
                <a href="<?php echo htmlspecialchars($card['url']); ?>" class="card-main-link">
                    <div class="card-icon-wrapper">
                        <i class="fas <?php echo !empty($card['icon']) ? htmlspecialchars($card['icon']) : 'fa-folder'; ?>"></i>
                    </div>
                    <h3 class="card-title"><?php echo htmlspecialchars($card['title']); ?></h3>
                </a>

                <?php if (!empty($card['children'])): ?>
                    <div class="sub-card-container">
                        <?php foreach ($card['children'] as $sub_card): ?>
                            <a href="<?php echo htmlspecialchars($sub_card['url']); ?>" class="sub-card">
                                <i class="fas <?php echo !empty($sub_card['icon']) ? htmlspecialchars($sub_card['icon']) : 'fa-file-alt'; ?> sub-card-icon"></i>
                                <span class="sub-card-title"><?php echo htmlspecialchars($sub_card['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="card-footer-actions">
                    <a href="download_handler.php?parent_id=<?php echo $card['id']; ?>" class="btn btn-secondary btn-download-folder">
                        <i class="fas fa-download"></i> Download Folder
                    </a>
                </div>
                </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
// --- Include Footer ---
include_once 'includes/footer.php';
?>