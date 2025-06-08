<?php
require_once '../config/config.php';    // Defines constants including UPLOAD_DIR_SERVER, UPLOAD_URL_PUBLIC
require_once '../config/database.php';
require_once '../core/helpers.php';
  // Provides $pdo

if (!isset($_SESSION["user_id"])) {
    $root_login_url = rtrim(BASE_URL, '/') . '/login.php';
    header("Location: " . $root_login_url);
    exit;
}

$page_title = "Branding Settings";
$current_page = basename($_SERVER['PHP_SELF']);
$messages = ['success' => '', 'error' => ''];

// Define branding uploads directory and allowed file types for logo
define('BRANDING_UPLOAD_SUBDIR', 'branding/'); // Subdirectory within UPLOAD_DIR_SERVER
define('BRANDING_UPLOAD_DIR_SERVER', UPLOAD_DIR_SERVER . BRANDING_UPLOAD_SUBDIR);
define('BRANDING_UPLOAD_URL_PUBLIC', UPLOAD_URL_PUBLIC . BRANDING_UPLOAD_SUBDIR);
define('ALLOWED_LOGO_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml']);
define('MAX_LOGO_SIZE', 2 * 1024 * 1024); // 2 MB

// Ensure branding upload directory exists and is writable
if (!is_dir(BRANDING_UPLOAD_DIR_SERVER)) {
    if (!mkdir(BRANDING_UPLOAD_DIR_SERVER, 0775, true)) { // Create recursively with permissions
        $messages['error'] = "Error: Branding upload directory ('" . htmlspecialchars(BRANDING_UPLOAD_SUBDIR) . "') could not be created. Please check server permissions.";
    }
} elseif (!is_writable(BRANDING_UPLOAD_DIR_SERVER)) {
    $messages['error'] = "Error: Branding upload directory ('" . htmlspecialchars(BRANDING_UPLOAD_SUBDIR) . "') is not writable. Please check server permissions.";
}


// Helper function to get a setting value
function get_branding_setting($pdo, $setting_name) {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM branding_settings WHERE setting_name = ?");
        $stmt->execute([$setting_name]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : null;
    } catch (PDOException $e) {
        // Log error or handle appropriately
        return null;
    }
}

// Helper function to update or insert a setting value
function set_branding_setting($pdo, $setting_name, $setting_value) {
    try {
        $stmt = $pdo->prepare("INSERT INTO branding_settings (setting_name, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        return $stmt->execute([$setting_name, $setting_value]);
    } catch (PDOException $e) {
        // Log error or handle appropriately
        return false;
    }
}

// --- Handle Form Submissions ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($messages['error'])) { // Process only if no initial dir errors

    // Handle Logo Upload
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'upload_logo') {
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['logo_file']['tmp_name'];
            $logo_filename_orig = basename($_FILES['logo_file']['name']);
            $logo_filesize = $_FILES['logo_file']['size'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $logo_filetype = $finfo->file($file_tmp_path);

            if (!in_array($logo_filetype, ALLOWED_LOGO_TYPES)) {
                $messages['error'] = "Invalid logo file type. Allowed: JPG, PNG, GIF, SVG.";
            } elseif ($logo_filesize > MAX_LOGO_SIZE) {
                $messages['error'] = "Logo file is too large. Maximum size: " . (MAX_LOGO_SIZE / 1024 / 1024) . " MB.";
            } else {
                $file_extension = strtolower(pathinfo($logo_filename_orig, PATHINFO_EXTENSION));
                // Use a fixed name for the logo for easier reference, or make it unique
                $logo_filename_sys = 'logo.' . $file_extension; // e.g., logo.png
                $destination = BRANDING_UPLOAD_DIR_SERVER . $logo_filename_sys;

                // Delete old logo if it exists and has a different extension
                $current_logo_path = get_branding_setting($pdo, 'logo_path');
                if ($current_logo_path && file_exists(BRANDING_UPLOAD_DIR_SERVER . basename($current_logo_path))) {
                    if (basename($current_logo_path) !== $logo_filename_sys) { // Different extension or just want to replace
                         unlink(BRANDING_UPLOAD_DIR_SERVER . basename($current_logo_path));
                    }
                }
                // Or, more simply, find any file starting with 'logo.' and delete it
                $existing_logos = glob(BRANDING_UPLOAD_DIR_SERVER . "logo.*");
                foreach ($existing_logos as $existing_logo) {
                    if (BRANDING_UPLOAD_DIR_SERVER . $logo_filename_sys !== $existing_logo) { // don't delete if it's the same name
                        unlink($existing_logo);
                    }
                }


                if (move_uploaded_file($file_tmp_path, $destination)) {
                    // Store the relative path from the *public uploads* perspective
                    $saved_logo_path = BRANDING_UPLOAD_SUBDIR . $logo_filename_sys;
                    if (set_branding_setting($pdo, 'logo_path', $saved_logo_path)) {
                        $messages['success'] = "Logo uploaded successfully!";
                    } else {
                        $messages['error'] = "Logo uploaded but failed to save path to database.";
                        unlink($destination); // Delete orphaned file
                    }
                } else {
                    $messages['error'] = "Failed to move uploaded logo file.";
                }
            }
        } elseif(isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $messages['error'] = "Error uploading logo: " . $_FILES['logo_file']['error'];
        } else {
            $messages['error'] = "Please select a logo file to upload.";
        }
    }

    // Handle Primary Color Update
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'update_color') {
        $primary_color = trim($_POST['primary_color']);
        // Basic hex color validation (e.g., #RRGGBB or #RGB)
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $primary_color) || $primary_color === '') {
            if (set_branding_setting($pdo, 'primary_color', $primary_color)) {
                $messages['success'] = "Primary color updated successfully!";
            } else {
                $messages['error'] = "Failed to update primary color.";
            }
        } else {
            $messages['error'] = "Invalid hex color format. Please use #RRGGBB or #RGB (e.g., #007bff). Leave empty to clear.";
        }
    }
}

// Fetch current settings for display
$current_logo_relative_path = get_branding_setting($pdo, 'logo_path');
$current_logo_url = null;

// For file_exists, we need the full server path. BRANDING_UPLOAD_DIR_SERVER is .../uploads/branding/
// and basename($current_logo_relative_path) would be just 'logo.ext'.
// So, the file_exists check should be:
// BRANDING_UPLOAD_DIR_SERVER . basename($current_logo_relative_path) is correct for checking the file.
// OR, even better: PROJECT_ROOT_SERVER_PATH . '/uploads/' . $current_logo_relative_path
// Let's use the more direct full path for file_exists if $current_logo_relative_path already includes 'branding/'

if ($current_logo_relative_path && defined('PROJECT_ROOT_SERVER_PATH') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $current_logo_relative_path)) {
    // UPLOAD_URL_PUBLIC comes from config.php
    $current_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($current_logo_relative_path, '/') . '?v=' . time();
}

$current_primary_color = get_branding_setting($pdo, 'primary_color');
if (empty($current_primary_color) && $current_primary_color !== '' ) { // if it's null (not found) or truly empty string from DB that is not intended
    $current_primary_color = '#007bff'; // Default if not set in DB
}

include_once 'includes/header.php';
?>

<?php if (!empty($messages['success'])): ?>
    <div class="message success"><?php echo htmlspecialchars($messages['success']); ?></div>
<?php endif; ?>
<?php if (!empty($messages['error'])): ?>
    <div class="message error"><?php echo htmlspecialchars($messages['error']); ?></div>
<?php endif; ?>

<div class="admin-page-content">
    <div class="form-container">
        <h3>Upload Logo</h3>
        <form action="branding_settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action_type" value="upload_logo">
            <div class="form-group">
                <label for="logo_file" class="required">Logo File (JPG, PNG, GIF, SVG - Max 2MB):</label>
                <input type="file" id="logo_file" name="logo_file" required>
                <?php if ($current_logo_url): ?>
                    <p style="margin-top: 15px;"><strong>Current Logo:</strong><br>
                        <img src="<?php echo htmlspecialchars($current_logo_url); ?>" alt="Current Logo" style="max-height: 100px; max-width: 200px; margin-top: 10px; border: 1px solid #ddd; padding: 5px;">
                    </p>
                <?php else: ?>
                    <p style="margin-top: 10px;">No logo currently uploaded.</p>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Upload Logo</button>
        </form>
    </div>

    <div class="form-container">
        <h3>Set Primary Color</h3>
        <form action="branding_settings.php" method="POST">
            <input type="hidden" name="action_type" value="update_color">
            <div class="form-group">
                <label for="primary_color">Primary Hex Color (e.g., #007bff):</label>
                <div style="display: flex; align-items: center;">
                    <input type="text" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($current_primary_color); ?>" placeholder="#RRGGBB" style="max-width: 200px;">
                    <span style="display: inline-block; width: 30px; height: 30px; background-color: <?php echo htmlspecialchars($current_primary_color); ?>; border: 1px solid #ccc; margin-left: 10px; border-radius: 4px;"></span>
                </div>
                 <p class="file-input-note" style="margin-top: 5px;">Leave empty to revert to default (or clear).</p>
            </div>
            <button type="submit" class="btn btn-primary">Save Color</button>
        </form>
    </div>
</div>

<?php
// Dynamic CSS for primary color (simple example for buttons and active sidebar)
// More robust theming would involve CSS variables used throughout admin_style.css
if (!empty($current_primary_color) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $current_primary_color)):
?>
<style id="dynamic-primary-color">
    :root {
        --admin-primary-color: <?php echo htmlspecialchars($current_primary_color); ?>;
        /* You might want to calculate a hover color, or define it separately */
        /* --admin-primary-color-hover: darken(<?php echo htmlspecialchars($current_primary_color); ?>, 10%); */
    }

    /* Apply to some key elements */
    .btn-primary,
    .admin-sidebar li.active a,
    .content-table thead tr /* Overriding existing table header */ {
        background-color: var(--admin-primary-color) !important; /* Use important carefully or refactor CSS */
        border-color: var(--admin-primary-color) !important; /* For buttons */
    }
    .admin-sidebar li a:hover { /* Optional: make sidebar hover use primary color */
        /* background-color: var(--admin-primary-color); */
        /* color: #fff; */
        /* border-left-color: var(--admin-primary-color); */
    }
    .admin-header .logout-btn, /* Matching logout button with primary */
    .modal-actions .btn-danger /* Example: if you want "Yes, Proceed" to be primary */
     {
        /* background-color: var(--admin-primary-color) !important; */ /* Decide if logout/modal yes use primary */
        /* border-color: var(--admin-primary-color) !important; */
    }
    /* If you want hover states to be a darker shade, you'd need a more complex setup or a SASS-like preprocessor,
       or define a separate --admin-primary-color-hover variable and set it manually or with JS.
       For now, this shows basic application.
    */
</style>
<?php
endif;

include_once 'includes/footer.php';
?>