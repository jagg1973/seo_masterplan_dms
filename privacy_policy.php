<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'core/helpers.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit;
}

$client_name = htmlspecialchars($_SESSION['client_full_name']);
$page_title = "Privacy Policy - " . SITE_NAME;
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
    <h1 class="page-main-title">Privacy Policy</h1>
    <p class="last-updated">Last updated: <?php echo date("F j, Y"); ?></p>

    <p>Welcome to <?php echo htmlspecialchars(SITE_NAME); ?> (the "Service"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our Service. Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the service.</p>

    <h2 class="section-title-minor">Information We Collect</h2>
    <p>We may collect information about you in a variety of ways. The information we may collect via the Service includes:</p>
    <ul>
        <li><strong>Personal Data:</strong> Personally identifiable information, such as your name, email address, and company name, that you voluntarily give to us when you register with the Service or when you choose to participate in various activities related to the Service.</li>
        <li><strong>Usage Data:</strong> Information our servers automatically collect when you access the Service, such as your IP address, your browser type, your operating system, your access times, and the pages you have viewed directly before and after accessing the Service.</li>
        <li><strong>Document Data:</strong> Files and documents you upload, download, or access through the Service. We treat your documents as confidential information.</li>
    </ul>

    <h2 class="section-title-minor">Use of Your Information</h2>
    <p>Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you via the Service to:</p>
    <ul>
        <li>Create and manage your account.</li>
        <li>Provide you with access to your documents.</li>
        <li>Email you regarding your account or order.</li>
        <li>Monitor and analyze usage and trends to improve your experience with the Service.</li>
        <li>Notify you of updates to the Service.</li>
        <li>Prevent fraudulent transactions, monitor against theft, and protect against criminal activity.</li>
        <li>Respond to customer service requests.</li>
    </ul>

    <h2 class="section-title-minor">Disclosure of Your Information</h2>
    <p>We may share information we have collected about you in certain situations. Your information may be disclosed as follows:</p>
    <ul>
        <li><strong>By Law or to Protect Rights:</strong> If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others, we may share your information as permitted or required by any applicable law, rule, or regulation.</li>
        <li><strong>Service Providers:</strong> We may share your information with third-party service providers that perform services for us or on our behalf, including data storage, hosting services, and customer service. These third parties are obligated to protect your information.</li>
    </ul>
     <p>We do not sell, rent, or lease your personal information to third parties.</p>

    <h2 class="section-title-minor">Security of Your Information</h2>
    <p>We use administrative, technical, and physical security measures to help protect your personal information and documents. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that despite our efforts, no security measures are perfect or impenetrable, and no method of data transmission can be guaranteed against any interception or other type of misuse.</p>

    <h2 class="section-title-minor">Policy for Children</h2>
    <p>We do not knowingly solicit information from or market to children under the age of 13. If you become aware of any data we have collected from children under age 13, please contact us using the contact information provided below.</p>

    <h2 class="section-title-minor">Changes to This Privacy Policy</h2>
    <p>We may update this Privacy Policy from time to time in order to reflect, for example, changes to our practices or for other operational, legal, or regulatory reasons. We will notify you of any changes by posting the new Privacy Policy on this page. You are advised to review this Privacy Policy periodically for any changes.</p>

    <h2 class="section-title-minor">Contact Us</h2>
    <p>If you have questions or comments about this Privacy Policy, please contact us through the support channels provided within the Service or via our company contact information.</p>

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