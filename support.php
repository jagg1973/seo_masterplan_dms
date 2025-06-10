<?php
// support.php (DEFINITIVE BUTTON & EMAIL FIX)
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/helpers.php';

require '/var/www/html/ipn-dev/PHPMailer/src/PHPMailer.php';
require '/var/www/html/ipn-dev/PHPMailer/src/SMTP.php';
require '/var/www/html/ipn-dev/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['client_id'])) { header("Location: login.php"); exit; }

$client_id = $_SESSION['client_id'];
$messages = ['success' => '', 'error' => ''];
$form_subject = '';
$form_message = '';

$stmt = $pdo->prepare("SELECT email, full_name FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client_info = $stmt->fetch();
$client_email = $client_info['email'];
$client_full_name = $client_info['full_name'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_subject = trim($_POST['subject']);
    $form_message = trim($_POST['message']);

    if (empty($form_subject) || empty($form_message)) {
        $messages['error'] = 'Please fill out both subject and message fields.';
    } else {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.zoho.eu';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@speed.cy';
            $mail->Password   = '52SHtjT0qQ6T';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom('support@speed.cy', 'DMS Support Portal');
            $mail->addAddress('jagg1973@gmail.com');
            $mail->addReplyTo($client_email, $client_full_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'DMS Support Request: ' . htmlspecialchars($form_subject);
            $mail->Body    = "<b>New support request from DMS Client Portal:</b><br><br>" .
                           "<b>Client Name:</b> " . htmlspecialchars($client_full_name) . "<br>" .
                           "<b>Client Email:</b> " . htmlspecialchars($client_email) . "<br>" .
                           "<b>Subject:</b> " . htmlspecialchars($form_subject) . "<br><br>" .
                           "<b>Message:</b><br>" . nl2br(htmlspecialchars($form_message));
            $mail->AltBody = "New support request from: " . $client_full_name . " (" . $client_email . ")\n\n" . "Subject: " . $form_subject . "\n\n" . "Message:\n" . $form_message;

            $mail->send();
            $messages['success'] = 'Your support request has been sent successfully. We will get back to you shortly.';
            $form_subject = '';
            $form_message = '';

        } catch (Exception $e) {
            // This now provides a detailed error message on the page itself.
            $messages['error'] = "Your message could not be sent. Please contact us directly. Error: " . $mail->ErrorInfo;
        }
    }
}

$client_name = htmlspecialchars($_SESSION['client_full_name']);
$page_title = "Support - " . SITE_NAME;
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
        <h3>Submit a Support Request</h3>
        <p style="display: block;">Have a question or need help? Fill out the form below to contact our support team.</p>
        <form action="support.php" method="POST">
            <?php if (!empty($messages['success'])): ?><div class="message success"><?php echo htmlspecialchars($messages['success']); ?></div><?php endif; ?>
            <?php if (!empty($messages['error'])): ?><div class="message error"><?php echo htmlspecialchars($messages['error']); ?></div><?php endif; ?>
            <div class="form-group"><label for="name">Your Name</label><input type="text" id="name" value="<?php echo htmlspecialchars($client_full_name); ?>" disabled></div>
            <div class="form-group"><label for="email">Your Email</label><input type="email" id="email" value="<?php echo htmlspecialchars($client_email); ?>" disabled></div>
            <div class="form-group"><label for="subject">Subject</label><input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($form_subject); ?>" required></div>
            <div class="form-group"><label for="message">Message</label><textarea name="message" id="message" rows="6" required><?php echo htmlspecialchars($form_message); ?></textarea></div>
            
            <button type="submit" id="supportSendRequestBtn" class="dms-form-submit-button">Send Request</button>

        </form>
    </div>
</div>
<?php include 'includes/client_footer.php'; ?>
