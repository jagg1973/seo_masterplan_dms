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
<div class="dashboard-grid-container">
    <?php if (empty($card_structure)): ?>
        <div class="empty-table-message">The document library is currently being organized. Please check back soon.</div>
    <?php else: ?>
        <?php foreach ($card_structure as $card): ?>
            <div class="dashboard-card animated-border-card">
                <div class="card-content-wrapper">
                    <?php
                    $header_tag = $card['is_container'] ? 'div' : 'a';
                    $header_href = !$card['is_container'] ? 'href="'.htmlspecialchars($card['url']).'"' : '';
                    ?>
                    <<?php echo $header_tag; ?> <?php echo $header_href; ?> class="card-header" data-url="<?php echo htmlspecialchars($card['url']);?>">
                        <div class="card-icon-wrapper"><i class="fas <?php echo !empty($card['icon'])?htmlspecialchars($card['icon']):'fa-folder';?>"></i></div>
                        <h3 class="card-title"><?php echo htmlspecialchars($card['title']);?></h3>
                        <?php if(!empty($card['children'])):?><button class="toggle-children-btn" data-target="children-<?php echo $card['id'];?>"><i class="fas fa-plus"></i></button><?php endif;?>
                    </<?php echo $header_tag; ?>>
                    
                    <div id="children-<?php echo $card['id'];?>" class="sub-card-container collapsible-content">
                        <?php if(!empty($card['children'])):?>
                            <?php foreach($card['children'] as $sub_card):?>
                            <div class="sub-card interactive-sub-card">
                                <i class="fas <?php echo !empty($sub_card['icon'])?htmlspecialchars($sub_card['icon']):'fa-file-alt';?> sub-card-icon"></i>
                                <span class="sub-card-title"><?php echo htmlspecialchars($sub_card['title']);?></span>
                                <div class="sub-card-actions">
                                    <button class="btn-preview view-document-btn" data-filepath="<?php echo htmlspecialchars($sub_card['url']);?>" data-filename="<?php echo htmlspecialchars($sub_card['title']);?>" data-fileext="<?php echo strtolower(pathinfo($sub_card['url'],PATHINFO_EXTENSION));?>">Preview</button>
                                    <a href="<?php echo htmlspecialchars($sub_card['url']);?>" class="btn-download" download>Download</a>
                                </div>
                            </div>
                            <?php endforeach;?>
                        <?php elseif(!$card['is_container']):?>
                            <p class="no-sub-items">This is a direct link.</p>
                        <?php endif;?>
                    </div>

                    <div class="card-footer-actions">
                        <a href="download_handler.php?parent_id=<?php echo $card['id'];?>" class="btn-download-folder"><i class="fas fa-download"></i> Download Folder</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/client_footer.php'; ?>
