<?php
// admin/manage_categories.php
require_once '../config/config.php';    // Defines SITE_NAME, BASE_URL, starts session
require_once '../config/database.php';  // Provides $pdo

// Ensure user is logged in (also done in header.php, but good for direct access attempt)
if (!isset($_SESSION["user_id"])) {
    $root_login_url = rtrim(BASE_URL, '/') . '/login.php';
    header("Location: " . $root_login_url);
    exit;
}

$page_title = "Manage Document Categories";
$current_page = basename($_SERVER['PHP_SELF']); // For active link in sidebar

// Initialize messages
$messages = ['success' => '', 'error' => ''];

// Determine current action (view, edit, delete, add_form_visible)
$action = $_GET['action'] ?? 'view'; // Default to 'view' (display list and add form)
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Initialize form fields
$category_name_form = '';
$category_description_form = '';

// If editing, fetch category details to pre-fill the form
if ($action === 'edit' && $category_id) {
    try {
        $stmt_fetch_cat = $pdo->prepare("SELECT * FROM document_categories WHERE id = ?");
        $stmt_fetch_cat->execute([$category_id]);
        $category_to_edit = $stmt_fetch_cat->fetch();

        if ($category_to_edit) {
            $category_name_form = $category_to_edit['name'];
            $category_description_form = $category_to_edit['description'];
        } else {
            $messages['error'] = "Category not found for editing.";
            $action = 'view'; // Revert to view if category doesn't exist
        }
    } catch (PDOException $e) {
        $messages['error'] = "Database error fetching category: " . $e->getMessage();
        $action = 'view';
    }
}

// Handle POST requests (Add or Update category)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_category_name = trim($_POST['category_name']);
    $posted_category_description = trim($_POST['category_description']);
    $posted_action_type = $_POST['action_type'] ?? 'add'; // 'add' or 'update'
    $posted_category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    // Repopulate form fields in case of error
    $category_name_form = $posted_category_name;
    $category_description_form = $posted_category_description;

    if (empty($posted_category_name)) {
        $messages['error'] = "Category name cannot be empty.";
    } else {
        try {
            if ($posted_action_type === 'update' && $posted_category_id) {
                // Check for name uniqueness (excluding self)
                $stmt_check_name = $pdo->prepare("SELECT id FROM document_categories WHERE name = ? AND id != ?");
                $stmt_check_name->execute([$posted_category_name, $posted_category_id]);
                if ($stmt_check_name->fetch()) {
                    $messages['error'] = "Another category with this name already exists.";
                    $action = 'edit'; // Keep form open for correction
                } else {
                    $stmt_update = $pdo->prepare("UPDATE document_categories SET name = ?, description = ? WHERE id = ?");
                    $stmt_update->execute([$posted_category_name, $posted_category_description, $posted_category_id]);
                    $messages['success'] = "Category updated successfully!";
                    $action = 'view'; // Revert to view list
                    $category_name_form = ''; $category_description_form = ''; // Clear form for next potential add
                }
            } elseif ($posted_action_type === 'add') {
                // Check for name uniqueness
                $stmt_check_name = $pdo->prepare("SELECT id FROM document_categories WHERE name = ?");
                $stmt_check_name->execute([$posted_category_name]);
                if ($stmt_check_name->fetch()) {
                    $messages['error'] = "A category with this name already exists.";
                    // Keep form open for correction, action remains 'view' or 'add_form_visible' implicitly
                } else {
                    $stmt_insert = $pdo->prepare("INSERT INTO document_categories (name, description) VALUES (?, ?)");
                    $stmt_insert->execute([$posted_category_name, $posted_category_description]);
                    $messages['success'] = "Category added successfully!";
                    $category_name_form = ''; $category_description_form = ''; // Clear form fields
                }
            }
        } catch (PDOException $e) {
            $messages['error'] = "Database error: " . $e->getMessage();
            if($posted_action_type === 'update') $action = 'edit'; // If update fails, stay in edit mode
        }
    }
    // If there was an error during POST for 'add', ensure add form is visible
    if (!empty($messages['error']) && $posted_action_type === 'add') {
        $action = 'add_form_visible'; // Special state to keep add form open on error
    }
}

// Handle Delete Action (via GET request)
if ($action === 'delete' && $category_id) {
    // Optional: Add a confirmation step here if not relying solely on JS confirm.
    try {
        // Check if any documents are using this category.
        // This requires the 'documents' table to exist with a 'category_id' column.
        // $stmt_check_docs = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE category_id = ?");
        // $stmt_check_docs->execute([$category_id]);
        // $doc_count = $stmt_check_docs->fetchColumn();
        $doc_count = 0; // Placeholder until documents table is ready

        if ($doc_count > 0) {
            $messages['error'] = "Cannot delete category: It is used by " . $doc_count . " document(s).";
        } else {
            $stmt_delete = $pdo->prepare("DELETE FROM document_categories WHERE id = ?");
            $stmt_delete->execute([$category_id]);
            $messages['success'] = "Category deleted successfully!";
        }
    } catch (PDOException $e) {
        $messages['error'] = "Database error during deletion: " . $e->getMessage();
    }
    $action = 'view'; // Revert to view list after attempting delete
}

// Fetch all categories for display in the table
try {
    $stmt_categories = $pdo->query("SELECT * FROM document_categories ORDER BY name ASC");
    $categories = $stmt_categories->fetchAll();
} catch (PDOException $e) {
    $messages['error'] = "Error fetching categories: " . $e->getMessage();
    $categories = []; // Ensure $categories is an array
}

// Include the header
include_once 'includes/header.php';
?>

<?php if (!empty($messages['success'])): ?>
    <div class="message success"><?php echo htmlspecialchars($messages['success']); ?></div>
<?php endif; ?>
<?php if (!empty($messages['error'])): ?>
    <div class="message error"><?php echo htmlspecialchars($messages['error']); ?></div>
<?php endif; ?>

<?php
// Determine if the form should be for 'editing' an existing category or 'adding' a new one
$form_mode = ($action === 'edit' && $category_id) ? 'update' : 'add';
$form_title = ($form_mode === 'update') ? 'Edit Category' : 'Add New Category';
$form_submit_button_text = ($form_mode === 'update') ? 'Update Category' : 'Add Category';

// Show form if 'edit' mode, or 'add_form_visible' (error on add), or default 'view' (for adding new)
$display_form = ($action === 'edit' || $action === 'add_form_visible' || $action === 'view');

if ($display_form):
?>
<div class="form-container">
    <h3><?php echo $form_title; ?></h3>
    <form action="manage_categories.php<?php echo ($form_mode === 'update') ? '?action=edit&id='.(int)$category_id : ''; ?>" method="POST">
        <input type="hidden" name="action_type" value="<?php echo $form_mode; ?>">
        <?php if ($form_mode === 'update'): ?>
            <input type="hidden" name="category_id" value="<?php echo (int)$category_id; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="category_name_form" class="required">Category Name:</label>
            <input type="text" id="category_name_form" name="category_name" value="<?php echo htmlspecialchars($category_name_form); ?>" required>
        </div>
        <div class="form-group">
            <label for="category_description_form">Description (Optional):</label>
            <textarea id="category_description_form" name="category_description"><?php echo htmlspecialchars($category_description_form); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $form_submit_button_text; ?></button>
        <?php if ($form_mode === 'update'): ?>
            <a href="manage_categories.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>


<h3>Existing Categories</h3>
<?php if (!empty($categories)): ?>
    <div class="table-wrapper">
    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?php echo (int)$cat['id']; ?></td>
                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($cat['description'] ?? 'N/A')); ?></td>
                <td><?php echo htmlspecialchars(date("M d, Y H:i", strtotime($cat['created_at']))); ?></td>
                <td class="actions">
                    <a href="manage_categories.php?action=edit&id=<?php echo (int)$cat['id']; ?>">Edit</a>
                    <a href="manage_categories.php?action=delete&id=<?php echo (int)$cat['id']; ?>"
   class="delete js-confirm-modal"
   data-item-name="<?php echo htmlspecialchars($cat['name']); ?>"
   data-confirm-title="Confirm Category Deletion">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

<?php elseif (empty($messages['error'])): // Only show "No categories" if there wasn't a major error fetching them ?>
    <div class="empty-table-message">No categories found. Please use the form above to add some!</div>
<?php endif; ?>

<?php
// Include the footer
include_once 'includes/footer.php';
?>