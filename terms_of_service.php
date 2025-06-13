<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/helpers.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit;
}

$client_name = htmlspecialchars($_SESSION['client_full_name']);
$page_title = "Terms of Service - " . SITE_NAME;
$site_logo_path = get_branding_setting($pdo, 'logo_path');
$primary_color = get_branding_setting($pdo, 'primary_color');
$primary_hover_color = '#0056b3'; // You might want to calculate this based on $primary_color
$site_logo_url = null;
if ($site_logo_path && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path)) {
    $site_logo_url = rtrim(UPLOAD_URL_PUBLIC, '/') . '/' . ltrim($site_logo_path, '/') . '?v=' . time();
}
if(empty($primary_color)) $primary_color = '#007bff';

include 'includes/client_header.php';
?>

<div class="static-page-container">
    <h1 class="page-main-title">Terms of Service</h1>
    <p class="last-updated">Last updated: <?php echo date("F j, Y"); ?></p>

    <p>Please read these Terms of Service ("Terms", "Terms of Service") carefully before using the <?php echo htmlspecialchars(SITE_NAME); ?> (the "Service") operated by <?php echo htmlspecialchars(defined('COMPANY_NAME') ? COMPANY_NAME : 'Our Company'); ?> ("us", "we", or "our").</p>

    <p>Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users, and others who access or use the Service.</p>

    <p>By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the Service.</p>

    <h2 class="section-title-minor">1. Accounts</h2>
    <p>When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.</p>
    <p>You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password, whether your password is with our Service or a third-party service.</p>
    <p>You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>

    <h2 class="section-title-minor">2. Intellectual Property & Document Ownership</h2>
    <p>The Service itself and its original content (excluding Content provided by users), features, and functionality are and will remain the exclusive property of <?php echo htmlspecialchars(defined('COMPANY_NAME') ? COMPANY_NAME : 'Our Company'); ?> and its licensors.</p>
    <p>You retain all ownership rights to the documents and content you upload, store, or share through the Service ("Your Content"). We do not claim any ownership rights over Your Content. You are solely responsible for Your Content and the consequences of storing or transmitting it.</p>

    <h2 class="section-title-minor">3. Acceptable Use</h2>
    <p>You agree not to use the Service:</p>
    <ul>
        <li>In any way that violates any applicable national or international law or regulation.</li>
        <li>For the purpose of exploiting, harming, or attempting to exploit or harm minors in any way by exposing them to inappropriate content or otherwise.</li>
        <li>To transmit, or procure the sending of, any advertising or promotional material, including any "junk mail", "chain letter," "spam," or any other similar solicitation.</li>
        <li>To impersonate or attempt to impersonate <?php echo htmlspecialchars(defined('COMPANY_NAME') ? COMPANY_NAME : 'Our Company'); ?>, a company employee, another user, or any other person or entity.</li>
        <li>To engage in any other conduct that restricts or inhibits anyone's use or enjoyment of the Service, or which, as determined by us, may harm <?php echo htmlspecialchars(defined('COMPANY_NAME') ? COMPANY_NAME : 'Our Company'); ?> or users of the Service or expose them to liability.</li>
        <li>To upload or transmit viruses, Trojan horses, worms, time-bombs, keystroke loggers, spyware, adware, or any other harmful programs or similar computer code designed to adversely affect the operation of any computer software or hardware.</li>
    </ul>

    <h2 class="section-title-minor">4. Termination</h2>
    <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
    <p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service, or contact us to request account deletion.</p>

    <h2 class="section-title-minor">5. Limitation Of Liability</h2>
    <p>In no event shall <?php echo htmlspecialchars(defined('COMPANY_NAME') ? COMPANY_NAME : 'Our Company'); ?>, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the Service; (ii) any conduct or content of any third party on the Service; (iii) any content obtained from the Service; and (iv) unauthorized access, use or alteration of your transmissions or content, whether based on warranty, contract, tort (including negligence) or any other legal theory, whether or not we have been informed of the possibility of such damage, and even if a remedy set forth herein is found to have failed of its essential purpose.</p>

    <h2 class="section-title-minor">6. Disclaimer</h2>
    <p>Your use of the Service is at your sole risk. The Service is provided on an "AS IS" and "AS AVAILABLE" basis. The Service is provided without warranties of any kind, whether express or implied, including, but not limited to, implied warranties of merchantability, fitness for a particular purpose, non-infringement or course of performance.</p>
    <p><?php echo htmlspecialchars(defined('COMPANY_NAME') ? COMPANY_NAME : 'Our Company'); ?> its subsidiaries, affiliates, and its licensors do not warrant that a) the Service will function uninterrupted, secure or available at any particular time or location; b) any errors or defects will be corrected; c) the Service is free of viruses or other harmful components; or d) the results of using the Service will meet your requirements.</p>

    <h2 class="section-title-minor">7. Governing Law</h2>
    <p>These Terms shall be governed and construed in accordance with the laws of Cyprus, without regard to its conflict of law provisions.</p>
    <p>Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect. These Terms constitute the entire agreement between us regarding our Service, and supersede and replace any prior agreements we might have between us regarding the Service.</p>

    <h2 class="section-title-minor">8. Changes</h2>
    <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>
    <p>By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, please stop using the Service.</p>

    <h2 class="section-title-minor">9. Contact Us</h2>
    <p>If you have any questions about these Terms, please contact us through the support channels provided within the Service or via our company contact information.</p>

</div>
<style>
.static-page-container {
    background-color: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem; /* Space before footer */
}
.page-main-title {
    font-size: 2em;
    color: var(--primary-color, #007bff);
    margin-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color, #007bff);
    padding-bottom: 0.5rem;
}
.last-updated {
    font-size: 0.9em;
    color: #6c757d;
    margin-bottom: 1.5rem;
}
.section-title-minor {
    font-size: 1.5em;
    color: #333;
    margin-top: 2rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
}
.static-page-container p, .static-page-container ul {
    line-height: 1.7;
    margin-bottom: 1rem;
    color: #454545;
}
.static-page-container ul {
    list-style-position: outside;
    padding-left: 20px;
}
.static-page-container li {
    margin-bottom: 0.5rem;
}
</style>
<?php
include 'includes/client_footer.php';
?>