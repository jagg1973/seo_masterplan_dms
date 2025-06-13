<?php
// client_dashboard.php (FINAL VERSION w/ CARD WRAPPER)
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/helpers.php';

if (!isset($_SESSION['client_id'])) { header("Location: login.php"); exit; }
$client_name = htmlspecialchars($_SESSION['client_full_name']);
$page_title = "Client Dashboard - " . SITE_NAME;
$site_logo_path = get_branding_setting($pdo, 'logo_path');
$primary_color = get_branding_setting($pdo, 'primary_color');
$primary_hover_color = '#0056b3';
$site_logo_url = null;
if ($site_logo_path && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path)) {
    $site_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($site_logo_path, '/') . '?v=' . time();
}
if(empty($primary_color)) $primary_color = '#007bff';

try {
    $stmt = $pdo->query("SELECT id, title, url, parent_id, icon, is_container FROM dashboard_links ORDER BY parent_id, display_order, title");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("Error fetching dashboard data: " . $e->getMessage()); }

function build_card_structure(array $links) { $structured = []; $children = []; foreach ($links as $link) { if ($link['parent_id'] === null) { $structured[$link['id']] = $link; $structured[$link['id']]['children'] = []; } else { $children[] = $link; } } foreach ($children as $child) { if (isset($structured[$child['parent_id']])) { $structured[$child['parent_id']]['children'][] = $child; } } return array_values($structured); }
$card_structure = build_card_structure($links);

include 'includes/client_header.php';

?>
<h2 class="section-title" style="margin-top:40px;">Document Library</h2>
<div class="document-card-grid">
    <?php if (empty($card_structure)): ?>
        <div class="empty-table-message">The document library is currently being organized. Please check back soon.</div>
    <?php else: ?>
        <?php foreach ($card_structure as $item):
            $is_container = $item['is_container'];
            $has_children = !empty($item['children']);
            $children_id = 'card-children-' . $item['id'];
            $default_icon = $is_container ? 'fa-folder' : 'fa-file-alt';
            $icon = !empty($item['icon']) ? htmlspecialchars($item['icon']) : $default_icon;
        ?>
            <div class="document-card">
                <div class="document-card-header">
                    <div class="document-card-icon"><i class="fas <?php echo $icon; ?>"></i></div>
                    <h3 class="document-card-title">
                        <?php if (!$is_container && !$has_children): // Direct link, not a container itself ?>
                            <a href="<?php echo htmlspecialchars($item['url']); ?>" class="document-card-title-link"><?php echo htmlspecialchars($item['title']); ?></a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($item['title']); ?>
                        <?php endif; ?>
                    </h3>
                    <?php if ($has_children): ?>
                        <button class="document-card-toggle" data-target="#<?php echo $children_id; ?>" aria-expanded="false" aria-controls="<?php echo $children_id; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($has_children): ?>
                    <div id="<?php echo $children_id; ?>" class="document-card-children collapsible-content">
                        <?php foreach ($item['children'] as $child):
                            $child_icon = !empty($child['icon']) ? htmlspecialchars($child['icon']) : 'fa-file-alt';
                            $file_url = $child['url'];
                            $is_document_link = strpos($file_url, UPLOAD_URL_PUBLIC) === 0;
                            $path_parts = $is_document_link ? pathinfo($file_url) : null;
                            $derived_file_ext = $is_document_link ? strtolower($path_parts['extension']) : '';
                            $data_filename_for_modal = $child['title'];
                        ?>
                            <div class="document-child-item">
                                <div class="document-child-icon"><i class="fas <?php echo $child_icon; ?>"></i></div>
                                <span class="document-child-title">
                                     <a href="<?php echo htmlspecialchars($child['url']); ?>" <?php if(!$is_document_link) echo 'target="_blank" rel="noopener noreferrer"';?> class="document-child-title-link">
                                        <?php echo htmlspecialchars($child['title']); ?>
                                    </a>
                                </span>
                                <?php if ($is_document_link): ?>
                                <div class="document-child-actions">
                                    <button type="button" class="btn-preview hierarchy-view-btn" data-filepath="<?php echo htmlspecialchars($file_url); ?>" data-filename="<?php echo htmlspecialchars($data_filename_for_modal); ?>" data-fileext="<?php echo htmlspecialchars($derived_file_ext); ?>"><i class="fas fa-eye"></i></button>
                                    <a href="<?php echo htmlspecialchars($file_url); ?>" class="btn-download" download><i class="fas fa-download"></i></a>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($is_container): // Offer download all for any folder/container ?>
                <div class="document-card-footer">
                    <a href="download_handler.php?parent_id=<?php echo $item['id']; ?>" class="btn-download-folder">
                        <i class="fas fa-download"></i> Download All
                    </a>
                </div>
                <?php elseif (!$is_container && !$has_children && strpos($item['url'], UPLOAD_URL_PUBLIC) === 0): // Direct document link actions in footer if no children section ?>
                    <div class="document-card-footer single-doc-actions">
                         <a href="<?php echo htmlspecialchars($item['url']); ?>" class="btn-download" download><i class="fas fa-download"></i> Download</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/client_footer.php'; ?>
