<?php
// my_account.php (DEFINITIVE BUTTON FIX)
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/helpers.php';

if (!isset($_SESSION['client_id'])) { header("Location: login.php"); exit; }

$client_id = $_SESSION['client_id'];
$messages = ['success' => '', 'error' => ''];

// Password Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $stmt = $pdo->prepare("SELECT password FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    if (!$client || !password_verify($current_password, $client['password'])) {
        $messages['error'] = "Your current password is not correct.";
    } elseif (strlen($new_password) < 8) {
        $messages['error'] = "New password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $messages['error'] = "New passwords do not match.";
    } else {
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE clients SET password = ? WHERE id = ?");
        if ($update_stmt->execute([$new_password_hashed, $client_id])) {
            $messages['success'] = "Your password has been updated successfully.";
        } else {
            $messages['error'] = "Failed to update password. Please try again.";
        }
    }
}

// Fetch client data for display
$stmt = $pdo->prepare("SELECT full_name, email FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client_data = $stmt->fetch();

// Setup variables for header
$client_name = htmlspecialchars($_SESSION['client_full_name']);
$page_title = "My Account - " . SITE_NAME;
$site_logo_path = get_branding_setting($pdo, 'logo_path');
$primary_color = get_branding_setting($pdo, 'primary_color');
$site_logo_url = null;
if ($site_logo_path && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path)) {
    $site_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($site_logo_path, '/') . '?v=' . time();
}

include 'includes/client_header.php';
?>
<div class="account-container">
    <div class="account-details-card">
        <h3>Account Information</h3>
        <p><strong>Name:</strong> <span><?php echo htmlspecialchars($client_data['full_name']); ?></span></p>
        <p><strong>Email:</strong> <span><?php echo htmlspecialchars($client_data['email']); ?></span></p>
    </div>
    <div class="account-details-card">
        <h3>Change Password</h3>
        <form action="my_account.php" method="POST">
            <?php if (!empty($messages['success'])): ?><div class="message success"><?php echo $messages['success']; ?></div><?php endif; ?>
            <?php if (!empty($messages['error'])): ?><div class="message error"><?php echo $messages['error']; ?></div><?php endif; ?>
            <div class="form-group"><label for="current_password">Current Password</label><input type="password" name="current_password" id="current_password" required></div>
            <div class="form-group"><label for="new_password">New Password</label><input type="password" name="new_password" id="new_password" required></div>
            <div class="form-group"><label for="confirm_password">Confirm New Password</label><input type="password" name="confirm_password" id="confirm_password" required></div>
            
           <button type="submit" name="update_password" id="accountUpdatePasswordBtn" class="dms-form-submit-button">Update Password</button>


        </form>
    </div>
</div>
<?php include 'includes/client_footer.php'; ?>
