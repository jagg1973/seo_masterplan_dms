<?php
// admin/manage_documents.php
require_once '../config/config.php';
require_once '../config/database.php'; // $pdo

if (!isset($_SESSION["user_id"])) {
    $root_login_url = rtrim(BASE_URL, '/') . '/login.php';
    header("Location: " . $root_login_url);
    exit;
}

$page_title = "Manage Documents";
$current_page = basename($_SERVER['PHP_SELF']);
$messages = ['success' => '', 'error' => ''];

// Define uploads directory and allowed file types
define('ALLOWED_TYPES', [
    'application/pdf', // PDF
    'application/msword', // DOC
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
    'application/vnd.ms-excel', // XLS
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // XLSX
    'application/vnd.ms-powerpoint', // PPT
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PPTX
    'application/x-figma', // Figma (if specific type is detected)
    'application/octet-stream' // Generic binary, often used for types like .fig if server unknown
]);

define('MAX_FILE_SIZE', 40 * 1024 * 1024); // 40 MB

// Fetch categories for the upload form's dropdown
try {
    $stmt_cats = $pdo->query("SELECT id, name FROM document_categories ORDER BY name ASC");
    $categories_for_form = $stmt_cats->fetchAll();
} catch (PDOException $e) {
    $categories_for_form = [];
    $messages['error'] = "Error fetching categories: " . $e->getMessage();
}

// --- Action Handling: Add, Edit, Delete ---
$action = $_GET['action'] ?? 'view'; // Default to view
$document_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Initialize form fields
$form_title = '';
$form_description = '';
$form_category_id = '';
$form_version = '';
$form_language = '';
// For editing - store existing file info if not replacing
$existing_filename_sys = '';
$existing_filename_orig = '';


// --- HANDLE POST REQUEST (Add or Update Document) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // These are common for all files in a batch or a single file
    $form_description = trim($_POST['description']);
    $form_category_id = (int)$_POST['category_id'];
    $form_version = trim($_POST['version']);
    $form_language = trim($_POST['language']);
    $posted_action_type = $_POST['action_type'] ?? 'add';
    $posted_document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : null;
    $current_user_id = $_SESSION['user_id'];

    // Initialize arrays for collecting messages for batch uploads
    $batch_success_messages = [];
    $batch_error_messages = [];

    // --- File Upload Handling ---
    if (isset($_FILES['document_file']) && !empty($_FILES['document_file']['name'][0])) { // Check if any file is selected
        
        $file_count = count($_FILES['document_file']['name']);

        for ($i = 0; $i < $file_count; $i++) {
            // Per-file variables
            $current_file_error = $_FILES['document_file']['error'][$i];
            $current_file_tmp_path = $_FILES['document_file']['tmp_name'][$i];
            $current_filename_orig = basename($_FILES['document_file']['name'][$i]);
            $current_filesize = $_FILES['document_file']['size'][$i];

            // Determine title for this specific file
            if ($file_count > 1 || empty(trim($_POST['title']))) { // Multiple files or title field empty
                $current_form_title = pathinfo($current_filename_orig, PATHINFO_FILENAME); // Use filename as title
            } else { // Single file and title field has value
                $current_form_title = trim($_POST['title']);
            }
            
            // Basic Validations for each file
            if (empty($current_form_title) || empty($form_category_id)) {
                $batch_error_messages[] = "Skipped '{$current_filename_orig}': Title (derived from filename) or Category is missing.";
                continue; // Skip this file
            }
            if ($current_file_error !== UPLOAD_ERR_OK) {
                $batch_error_messages[] = "Error uploading '{$current_filename_orig}': Code " . $current_file_error;
                continue; // Skip this file
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $current_filetype = $finfo->file($current_file_tmp_path);

            if (!in_array($current_filetype, ALLOWED_TYPES)) {
                $batch_error_messages[] = "Invalid file type for '{$current_filename_orig}'. Allowed: PDF, DOC(X), etc.";
                continue; // Skip this file
            }
            if ($current_filesize > MAX_FILE_SIZE) {
                $batch_error_messages[] = "'{$current_filename_orig}' is too large. Max: " . (MAX_FILE_SIZE / 1024 / 1024) . " MB.";
                continue; // Skip this file
            }

            // Generate a unique system filename
            $file_extension = strtolower(pathinfo($current_filename_orig, PATHINFO_EXTENSION));
            $current_filename_sys = uniqid('doc_', true) . '.' . $file_extension;
            $current_filepath = $current_filename_sys; // Relative path for DB
            $destination = UPLOAD_DIR_SERVER . $current_filename_sys; // Full server path for move

            if (move_uploaded_file($current_file_tmp_path, $destination)) {
                // File moved, proceed with database operation for THIS file
                try {
                    if ($posted_action_type === 'update' && $posted_document_id && $file_count == 1) {
                        // UPDATE logic (typically for one file at a time, multi-file update is complex)
                        // For simplicity, let's assume 'update' action replaces a SINGLE document.
                        // If multiple files are selected in 'edit' mode, this part needs careful thought.
                        // For now, this example focuses on 'add' for multiple files.
                        // The 'update' scenario will primarily deal with metadata or replacing the one existing file.
                        // If you want to "add more files" under an existing document concept, that's different.

                        // Fetch existing document data to manage file replacement
                        $stmt_old_doc = $pdo->prepare("SELECT filename_sys FROM documents WHERE id = ?");
                        $stmt_old_doc->execute([$posted_document_id]);
                        $old_doc_data = $stmt_old_doc->fetch();

                        $sql = "UPDATE documents SET category_id = ?, title = ?, description = ?, filename_orig = ?, filename_sys = ?, filepath = ?, filetype = ?, filesize = ?, version = ?, language = ?, user_id = ? WHERE id = ?";
                        $params = [$form_category_id, $current_form_title, $form_description, $current_filename_orig, $current_filename_sys, $current_filepath, $current_filetype, $current_filesize, $form_version, $form_language, $current_user_id, $posted_document_id];
                        
                        $stmt_update = $pdo->prepare($sql);
                        $stmt_update->execute($params);
                        $batch_success_messages[] = "Document '{$current_filename_orig}' updated successfully!";
                        
                        // Delete old file if new one is successfully uploaded and different
                        if ($old_doc_data && $old_doc_data['filename_sys'] && file_exists(UPLOAD_DIR_SERVER . $old_doc_data['filename_sys'])) {
                            if ($old_doc_data['filename_sys'] !== $current_filename_sys) {
                                unlink(UPLOAD_DIR_SERVER . $old_doc_data['filename_sys']);
                            }
                        }
                        $action = 'view'; // Go back to list view after update

                    } elseif ($posted_action_type === 'add') {
                        $sql = "INSERT INTO documents (category_id, user_id, title, description, filename_orig, filename_sys, filepath, filetype, filesize, version, language)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt_insert = $pdo->prepare($sql);
                        $stmt_insert->execute([$form_category_id, $current_user_id, $current_form_title, $form_description, $current_filename_orig, $current_filename_sys, $current_filepath, $current_filetype, $current_filesize, $form_version, $form_language]);
                        $batch_success_messages[] = "Document '{$current_filename_orig}' uploaded successfully!";
                    }
                } catch (PDOException $e) {
                    $batch_error_messages[] = "Database error for '{$current_filename_orig}': " . $e->getMessage();
                    // If file was moved but DB failed, attempt to delete orphaned file
                    if (file_exists($destination)) {
                        unlink($destination);
                    }
                }
            } else {
                $batch_error_messages[] = "Failed to move uploaded file '{$current_filename_orig}'. Check server permissions for '".UPLOAD_DIR_SERVER."'.";
            }
        } // End FOR loop for files

        // After loop, set final messages
        if (!empty($batch_success_messages)) {
            $messages['success'] = implode("<br>", $batch_success_messages);
        }
        if (!empty($batch_error_messages)) {
            $messages['error'] = implode("<br>", $batch_error_messages);
            // If there were errors, keep form populated for resubmission or correction if applicable
            $action = ($posted_action_type === 'update' && $posted_document_id) ? 'edit' : 'add_form_visible';
            if ($posted_action_type === 'update') $document_id = $posted_document_id;
             // Repopulate common form fields
            $form_title = ($file_count == 1) ? trim($_POST['title']) : ''; // Only repopulate title if single file attempt
            // $form_description, $form_category_id, $form_version, $form_language are already set from POST
        } else {
             // All successful, clear common form fields for next 'add' operation if it was an add
            if ($posted_action_type === 'add') {
                 $form_title = $form_description = $form_category_id = $form_version = $form_language = '';
            }
        }

    } elseif ($posted_action_type === 'add' && (empty($_FILES['document_file']['name'][0]) || $_FILES['document_file']['error'][0] == UPLOAD_ERR_NO_FILE)) {
        // No files selected for an 'add' operation
        $messages['error'] = "Please select at least one document file to upload.";
        $action = 'add_form_visible'; // Keep form visible
        // Repopulate common form fields
        $form_title = trim($_POST['title']);
        // $form_description, $form_category_id, $form_version, $form_language are already set from POST

    } elseif ($posted_action_type === 'update' && $posted_document_id && empty($_FILES['document_file']['name'][0])) {
        // This is an 'update' action where only metadata might be changing (no new file uploaded)
        // The previous logic for this scenario outside the loop might still be needed if you separate single file update from multi-file add.
        // For this refactor, we assume if $_FILES['document_file'] is not set or empty, no file processing for update happens.
        // The database update for metadata only (no file change) during an 'update' action:
        try {
            $sql_meta_update = "UPDATE documents SET category_id = ?, title = ?, description = ?, version = ?, language = ?, user_id = ? WHERE id = ?";
            $params_meta_update = [$form_category_id, trim($_POST['title']), $form_description, $form_version, $form_language, $current_user_id, $posted_document_id];
            $stmt_meta = $pdo->prepare($sql_meta_update);
            $stmt_meta->execute($params_meta_update);
            $messages['success'] = "Document metadata updated successfully!";
            $action = 'view'; // Go back to list view
        } catch (PDOException $e) {
            $messages['error'] = "Database error updating metadata: " . $e->getMessage();
            $action = 'edit'; // Stay in edit mode
            $document_id = $posted_document_id;
        }
    }


    // If error during POST, set action to keep form visible for correction (already handled above for batch errors)
    if (!empty($messages['error']) && !empty($batch_error_messages)) { // If individual file errors occurred
        $action = ($posted_action_type === 'update' && $posted_document_id) ? 'edit' : 'add_form_visible';
        if ($posted_action_type === 'update') $document_id = $posted_document_id;
    }

} // END OF POST HANDLING


// --- HANDLE GET ACTION: Edit (Fetch data for form) or Delete ---
if ($action === 'edit' && $document_id && $_SERVER["REQUEST_METHOD"] != "POST") { // Only fetch if not a POST error repopulation
    try {
        $stmt_doc = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt_doc->execute([$document_id]);
        $doc_to_edit = $stmt_doc->fetch();
        if ($doc_to_edit) {
            $form_title = $doc_to_edit['title'];
            $form_description = $doc_to_edit['description'];
            $form_category_id = $doc_to_edit['category_id'];
            $form_version = $doc_to_edit['version'];
            $form_language = $doc_to_edit['language'];
            $existing_filename_sys = $doc_to_edit['filename_sys']; // Keep track of current file
            $existing_filename_orig = $doc_to_edit['filename_orig'];
        } else {
            $messages['error'] = "Document not found for editing.";
            $action = 'view';
        }
    } catch (PDOException $e) {
        $messages['error'] = "Error fetching document: " . $e->getMessage();
        $action = 'view';
    }
} elseif ($action === 'delete' && $document_id) {
    try {
        // First, get the filename to delete the actual file
        $stmt_get_file = $pdo->prepare("SELECT filename_sys FROM documents WHERE id = ?");
        $stmt_get_file->execute([$document_id]);
        $file_to_delete_sys = $stmt_get_file->fetchColumn();

        // Delete DB record
        $stmt_delete_db = $pdo->prepare("DELETE FROM documents WHERE id = ?");
        $stmt_delete_db->execute([$document_id]);

        // Delete actual file from server
        if ($file_to_delete_sys && file_exists(UPLOAD_DIR_SERVER . $file_to_delete_sys)) {
            unlink(UPLOAD_DIR_SERVER . $file_to_delete_sys);
        }
        $messages['success'] = "Document deleted successfully.";

    } catch (PDOException $e) {
        $messages['error'] = "Error deleting document: " . $e->getMessage();
    }
    $action = 'view'; // Go back to list view
}


// --- Fetch all documents for display ---
try {
    $stmt_docs = $pdo->query(
        "SELECT d.*, c.name as category_name
         FROM documents d
         JOIN document_categories c ON d.category_id = c.id
         ORDER BY d.created_at DESC"
    );
    $documents = $stmt_docs->fetchAll();
} catch (PDOException $e) {
    $messages['error'] = "Error fetching documents: " . $e->getMessage();
    $documents = [];
}


// Start HTML Output
include_once 'includes/header.php';
?>

<?php if (!empty($messages['success'])): ?>
    <div class="message success"><?php echo htmlspecialchars($messages['success']); ?></div>
<?php endif; ?>
<?php if (!empty($messages['error'])): ?>
    <div class="message error"><?php echo htmlspecialchars($messages['error']); ?></div>
<?php endif; ?>

<?php
// Determine form mode for Add/Edit display
$form_mode_doc = ($action === 'edit' && $document_id) ? 'update' : 'add';
$form_title_doc_page = ($form_mode_doc === 'update') ? 'Edit Document' : 'Add New Document';
$form_submit_button_text_doc = ($form_mode_doc === 'update') ? 'Update Document' : 'Upload Document';

$display_doc_form = ($action === 'edit' || $action === 'add_form_visible' || $action === 'view'); // Show form on view for adding new

if ($display_doc_form):
?>
<div class="form-container">
    <h3><?php echo $form_title_doc_page; ?></h3>
    <form action="manage_documents.php<?php echo ($form_mode_doc === 'update') ? '?action=edit&id='.(int)$document_id : ''; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action_type" value="<?php echo $form_mode_doc; ?>">
        <?php if ($form_mode_doc === 'update'): ?>
            <input type="hidden" name="document_id" value="<?php echo (int)$document_id; ?>">
        <?php endif; ?>

        <div class="form-group">
    <label for="doc_title">Title (if single file upload, otherwise filename is used):</label>
    <input type="text" id="doc_title" name="title" value="<?php echo htmlspecialchars($form_title); ?>" >
</div>

        <div class="form-group">
            <label for="doc_category" class="required">Category:</label>
            <select id="doc_category" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories_for_form as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php echo ($form_category_id == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="doc_description">Description (Optional):</label>
            <textarea id="doc_description" name="description"><?php echo htmlspecialchars($form_description); ?></textarea>
        </div>

        <div class="form-group">
<label for="doc_file" class="<?php echo ($form_mode_doc === 'add') ? 'required' : ''; ?>">Document File(s) (PDF, DOC(X), XLS(X), PPT(X), FIG - Max 40MB):</label>            <input type="file" id="doc_file" name="document_file[]" multiple <?php echo ($form_mode_doc === 'add' && empty($document_id)) ? 'required' : ''; // Required for add, not for edit ?>>
            <?php if ($form_mode_doc === 'update' && $existing_filename_orig): ?>
                <p style="font-size: 0.9em; margin-top: 5px;">Current file: <?php echo htmlspecialchars($existing_filename_orig); ?>. Upload a new file to replace it.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="doc_version">Version (Optional):</label>
            <input type="text" id="doc_version" name="version" value="<?php echo htmlspecialchars($form_version); ?>" placeholder="e.g., 1.0, 2.1b">
        </div>

        <div class="form-group">
            <label for="doc_language">Language (Optional):</label>
            <input type="text" id="doc_language" name="language" value="<?php echo htmlspecialchars($form_language); ?>" placeholder="e.g., English, Greek">
        </div>

        <button type="submit" class="btn btn-primary"><?php echo $form_submit_button_text_doc; ?></button>
        <?php if ($form_mode_doc === 'update'): ?>
            <a href="manage_documents.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>


<h3>Uploaded Documents</h3>
<?php if (!empty($documents)): ?>
    <div class="table-wrapper">
    <table class="content-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Original Filename</th>
                <th>Version</th>
                <th>Language</th>
                <th>Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $doc): ?>
            <tr>
                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                <td><?php echo htmlspecialchars($doc['category_name']); ?></td>
                <td><?php echo htmlspecialchars($doc['filename_orig']); ?></td>
                <td><?php echo htmlspecialchars($doc['version'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($doc['language'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars(date("M d, Y H:i", strtotime($doc['created_at']))); ?></td>
                <td class="actions">
                    <a href="manage_documents.php?action=edit&id=<?php echo (int)$doc['id']; ?>">Edit</a>
                    <a href="manage_documents.php?action=delete&id=<?php echo (int)$doc['id']; ?>"
   class="delete js-confirm-modal"
   data-item-name="<?php echo htmlspecialchars($doc['title']); ?>"
   data-confirm-title="Confirm Document Deletion">Delete</a>
                    <a href="<?php echo rtrim(BASE_URL, '/') . '/uploads/' . rawurlencode($doc['filename_sys']); ?>" target="_blank">Download</a>
                    </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php elseif (empty($messages['error'])): ?>
    <div class="empty-table-message">No documents found. Please use the form above to upload some.</div>
<?php endif; ?>

<?php
include_once 'includes/footer.php';
?>