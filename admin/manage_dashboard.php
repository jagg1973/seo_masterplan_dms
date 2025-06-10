<?php
// admin/manage_dashboard.php (COMPLETE & FINAL VERSION)
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: " . rtrim(BASE_URL, '/') . '/login.php');
    exit;
}

$page_title = "Manage Dashboard Links";
$current_page = basename($_SERVER['PHP_SELF']);
$messages = ['success' => '', 'error' => ''];

// Initialize form variables
$action = $_GET['action'] ?? 'view';
$link_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$form_title = '';
$form_url = '';
$form_icon = 'fa-folder';
$form_is_container = 0;
$form_parent_id = '';
$form_display_order = 0;

// Handle POST Requests (Add/Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_title = trim($_POST['title']);
    $form_icon = trim($_POST['icon']);
    $form_is_container = isset($_POST['is_container']) ? 1 : 0;
    $form_parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $form_display_order = (int)$_POST['display_order'];
    $posted_action_type = $_POST['action_type'] ?? 'add';
    $posted_link_id = isset($_POST['link_id']) ? (int)$_POST['link_id'] : null;
    $linked_doc_id = !empty($_POST['linked_doc_id']) ? (int)$_POST['linked_doc_id'] : null;
    $manual_url = trim($_POST['manual_url']);

    if ($form_is_container) {
        $form_url = '#'; // Containers don't need a real URL
    } elseif ($linked_doc_id) {
        $stmt_doc_path = $pdo->prepare("SELECT filepath FROM documents WHERE id = ?");
        $stmt_doc_path->execute([$linked_doc_id]);
        $filepath = $stmt_doc_path->fetchColumn();
        $form_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . $filepath;
    } else {
        $form_url = $manual_url;
    }

    if (empty($form_title)) {
        $messages['error'] = "Title is required.";
    } else {
        try {
            if ($posted_action_type === 'update' && $posted_link_id) {
                $sql = "UPDATE dashboard_links SET title = ?, url = ?, icon = ?, is_container = ?, parent_id = ?, display_order = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$form_title, $form_url, $form_icon, $form_is_container, $form_parent_id, $form_display_order, $posted_link_id]);
            } else {
                $sql = "INSERT INTO dashboard_links (title, url, icon, is_container, parent_id, display_order) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$form_title, $form_url, $form_icon, $form_is_container, $form_parent_id, $form_display_order]);
            }
            header("Location: manage_dashboard.php?success=1");
            exit;
        } catch (PDOException $e) {
            $messages['error'] = "Database error: " . $e->getMessage();
        }
    }
}

// Handle GET Actions (Edit, Delete, Success Message)
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['success'])) $messages['success'] = "Action completed successfully!";
    
    if ($action === 'edit' && $link_id) {
        $stmt_edit = $pdo->prepare("SELECT * FROM dashboard_links WHERE id = ?");
        $stmt_edit->execute([$link_id]);
        $link_to_edit = $stmt_edit->fetch();
        if ($link_to_edit) {
            $form_title = $link_to_edit['title'];
            $form_url = $link_to_edit['url'];
            $form_icon = $link_to_edit['icon'];
            $form_is_container = $link_to_edit['is_container'];
            $form_parent_id = $link_to_edit['parent_id'];
            $form_display_order = $link_to_edit['display_order'];
        }
    } elseif ($action === 'delete' && $link_id) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM dashboard_links WHERE parent_id = ?");
        $stmt_check->execute([$link_id]);
        if ($stmt_check->fetchColumn() > 0) {
            $messages['error'] = "Cannot delete: This is a parent link. Please reassign or delete its children first.";
        } else {
            $stmt_delete = $pdo->prepare("DELETE FROM dashboard_links WHERE id = ?");
            $stmt_delete->execute([$link_id]);
            header("Location: manage_dashboard.php?success=1");
            exit;
        }
    }
}

// Fetch all data needed for the page
$stmt_docs = $pdo->query("SELECT id, title, filename_orig FROM documents ORDER BY title ASC");
$all_documents = $stmt_docs->fetchAll();
$stmt_parents = $pdo->query("SELECT id, title FROM dashboard_links WHERE parent_id IS NULL ORDER BY title ASC");
$parent_links = $stmt_parents->fetchAll();
$stmt_all = $pdo->query("SELECT l.*, p.title as parent_title FROM dashboard_links l LEFT JOIN dashboard_links p ON l.parent_id = p.id ORDER BY l.display_order ASC");
$all_links = $stmt_all->fetchAll();

$sorted_list = [];
foreach($all_links as $link) { if($link['parent_id'] === null) { $sorted_list[] = $link; foreach($all_links as $child) { if($child['parent_id'] == $link['id']) { $sorted_list[] = $child; } } } }

include_once 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<style>.sortable-ghost{opacity:0.4;background-color:#e3f2fd;}.handle{cursor:move;font-size:1.2em;color:#b0bec5;padding-right:15px;}</style>

<?php if(!empty($messages['success'])): ?><div class="message success"><?php echo htmlspecialchars($messages['success']); ?></div><?php endif; ?>
<?php if(!empty($messages['error'])): ?><div class="message error"><?php echo htmlspecialchars($messages['error']); ?></div><?php endif; ?>

<div class="form-container">
    <h3><?php echo ($action === 'edit' && $link_id) ? 'Edit Dashboard Link' : 'Add New Dashboard Link'; ?></h3>
    <form action="manage_dashboard.php" method="POST">
        <input type="hidden" name="action_type" value="<?php echo ($action === 'edit' && $link_id) ? 'update' : 'add'; ?>">
        <?php if ($action === 'edit' && $link_id): ?><input type="hidden" name="link_id" value="<?php echo (int)$link_id; ?>"><?php endif; ?>
        
        <div class="form-group"><label for="title" class="required">Title:</label><input type="text" id="title" name="title" value="<?php echo htmlspecialchars($form_title); ?>" required></div>
        <div class="form-group">
            <label for="icon">Icon:</label>
            <select id="icon" name="icon">
                <optgroup label="Common"><option value="fa-folder" <?php if($form_icon == 'fa-folder') echo 'selected';?>>Folder</option><option value="fa-file-alt" <?php if($form_icon == 'fa-file-alt') echo 'selected';?>>Document</option><option value="fa-link" <?php if($form_icon == 'fa-link') echo 'selected';?>>Link</option></optgroup>
                <optgroup label="Specific"><option value="fa-briefcase" <?php if($form_icon == 'fa-briefcase') echo 'selected';?>>Briefcase</option><option value="fa-chart-bar" <?php if($form_icon == 'fa-chart-bar') echo 'selected';?>>Chart</option><option value="fa-cogs" <?php if($form_icon == 'fa-cogs') echo 'selected';?>>Settings</option><option value="fa-book-open" <?php if($form_icon == 'fa-book-open') echo 'selected';?>>Book</option></optgroup>
            </select>
        </div>
        <div class="form-group"><label><input type="checkbox" name="is_container" value="1" <?php if($form_is_container) echo 'checked';?>> Is a non-clickable container</label></div>
        <div class="form-group"><label for="parent_id">Parent:</label><select name="parent_id"><option value="">-- No Parent (Main Card) --</option><?php foreach ($parent_links as $p):?><option value="<?php echo $p['id'];?>" <?php if($form_parent_id == $p['id']) echo 'selected';?>><?php echo htmlspecialchars($p['title']);?></option><?php endforeach;?></select></div>
        <div class="form-group"><label for="display_order">Display Order:</label><input type="number" name="display_order" value="<?php echo htmlspecialchars($form_display_order);?>"></div>
        <div class="form-group"><label for="linked_doc_id">Link to Document:</label><select name="linked_doc_id"><option value="">-- Or Enter Manual URL Below --</option><?php foreach($all_documents as $doc):?><option value="<?php echo $doc['id'];?>"><?php echo htmlspecialchars($doc['title']);?></option><?php endforeach;?></select></div>
        <div class="form-group"><label for="manual_url">Manual URL:</label><input type="text" name="manual_url" value="<?php echo htmlspecialchars($form_url);?>"></div>
        <button type="submit" class="btn btn-primary"><?php echo ($action === 'edit' && $link_id) ? 'Update Link' : 'Add Link'; ?></button>
        <?php if($action === 'edit' && $link_id):?><a href="manage_dashboard.php" class="btn btn-secondary">Cancel</a><?php endif;?>
    </form>
</div>

<h3>Existing Links (Drag to Reorder)</h3>
<div class="table-wrapper">
    <table class="content-table">
        <thead><tr><th style="width:5%;">Order</th><th>Title</th><th>Parent</th><th>Actions</th></tr></thead>
        <tbody id="dashboard-links-sortable">
            <?php foreach ($sorted_list as $link): ?>
            <tr data-id="<?php echo $link['id']; ?>">
                <td class="handle"><i class="fas fa-grip-vertical"></i></td>
                <td <?php if($link['parent_id']) echo 'style="padding-left:30px;"';?>><i class="fas <?php echo htmlspecialchars($link['icon']);?>"></i> <?php echo htmlspecialchars($link['title']);?></td>
                <td><?php echo $link['parent_id'] ? htmlspecialchars($link['parent_title']) : '<strong>(Main Card)</strong>';?></td>
                <td class="actions"><a href="?action=edit&id=<?php echo $link['id'];?>">Edit</a> <a href="?action=delete&id=<?php echo $link['id'];?>" class="delete js-confirm-modal" data-item-name="<?php echo htmlspecialchars($link['title']);?>">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button id="saveOrderBtn" class="btn btn-primary" style="margin-top:15px;display:none;">Save New Order</button>
</div>

<script>
    const sortableList = document.getElementById('dashboard-links-sortable');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    new Sortable(sortableList, { animation: 150, handle: '.handle', onUpdate: () => saveOrderBtn.style.display = 'inline-block' });
    saveOrderBtn.addEventListener('click', () => {
        const order = Array.from(sortableList.querySelectorAll('tr')).map((row, index) => ({ id: row.dataset.id, order: index }));
        fetch('ajax_update_order.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({order})})
        .then(res => res.json()).then(data => {
            if(data.success) { alert('Order saved!'); saveOrderBtn.style.display = 'none'; } 
            else { alert('Error saving order: ' + data.message); }
        }).catch(err => console.error('Error:', err));
    });
</script>

<?php
include_once 'includes/footer.php';
?>