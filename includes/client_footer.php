<?php
// includes/client_footer_dark.php
// This file is responsible for closing the main content area and the HTML document.
?>
</main> <!-- Close the main content container opened in client_header.php -->

    <style>
        /* CSS for the Professional Light Footer */
        .professional-light-footer {
            background-color: var(--background-color, #f7f9fc); /* Use a light background color */
            color: var(--text-color, #212529); /* Default dark text color */
            width: 100%;
            padding: 3rem 1.5rem; /* py-12 px-6 */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin-top: auto; /* Pushes footer to the bottom if content is short */
            border-top: 1px solid var(--border-color, #e3e6f0); /* Add a top border */
            box-sizing: border-box; /* Ensures padding is included in the 100% width */
        }
        .professional-light-footer .footer-container {
            max-width: 1280px; /* Standard large container width */
            margin-left: auto;
            margin-right: auto;
        }
        .professional-light-footer .footer-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 2rem;
        }
        @media (min-width: 768px) { /* md breakpoint */
            .professional-light-footer .footer-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .professional-light-footer h4 {
            color: var(--headings-color, #1a253c); /* Dark color for headings */
            font-weight: 600; /* semibold */
            margin-bottom: 1rem;
        }
        .professional-light-footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem; /* space-y-2 */
        }
        .professional-light-footer a {
            color: var(--text-color, #212529); /* Dark color for links */
            text-decoration: none;
            transition: color 0.2s ease-in-out;
        }
        .professional-light-footer a:hover {
            color: var(--primary-color, #007bff); /* Primary color on hover */
        }
        .professional-light-footer .footer-brand-text {
            color: var(--headings-color, #1a253c); /* Dark color for brand text */
            font-size: 1.125rem; /* text-lg */
            font-weight: 600; /* semibold */
            margin-bottom: 1rem;
        }
        .professional-light-footer .footer-brand-description {
            font-size: 0.875rem; /* text-sm */
            color: var(--text-muted-color, #6c757d); /* Muted dark gray for description */
        }
        .professional-light-footer .footer-social-links {
            display: flex;
            gap: 1rem; /* space-x-4 */
        }
         .professional-light-footer .footer-social-links a {
            font-size: 1.25rem; /* text-xl */
            color: var(--text-muted-color, #6c757d); /* Muted color for social icons */
         }
        .professional-light-footer .footer-social-links a:hover {
            color: var(--primary-color, #007bff); /* Primary color on hover for social icons */
        }
        .professional-light-footer .footer-bottom-bar {
            margin-top: 3rem; /* mt-12 */
            padding-top: 2rem; /* pt-8 */
            border-top: 1px solid var(--border-color, #e3e6f0); /* Lighter border */
            text-align: center;
            font-size: 0.875rem; /* text-sm */
            color: var(--text-muted-color, #6c757d); /* Muted dark gray */
        }
    </style>

    <div id="documentViewModal" class="modal-overlay" style="display: none;">
        <div class="modal-content document-viewer-modal-content">
            <div class="document-viewer-header">
                <h4 id="documentViewerTitle">Document Preview</h4>
                <button id="closeDocumentViewModal" class="close-modal-btn">&times;</button>
            </div>
            <div id="documentViewerContent" class="document-viewer-body"></div>
            <div class="document-viewer-footer">
                <a href="#" id="documentViewerDownloadLink" class="btn btn-primary" download>Download File</a>
            </div>
        </div>
    </div>

    <footer class="professional-light-footer">
        <div class="footer-container">
            <div class="footer-grid">

                <!-- Column 1: Brand -->
                <div>
                    <?php if (!empty($site_logo_url)): // $site_logo_url should be set in client_header.php ?>
                        <a href="client_dashboard.php" class="footer-logo-link">
                            <img src="<?php echo htmlspecialchars($site_logo_url); ?>" alt="<?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'DMS Portal'); ?> Logo" style="max-height: 80px; margin-bottom: 1rem;">
                        </a>
                    <?php else: ?>
                        <h3 class="footer-brand-text">
                            <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'DMS Portal'); ?>
                        </h3>
                    <?php endif; ?>
                    <p class="footer-brand-description">
                        Providing secure and reliable access to your essential documents and strategic plans.
                    </p>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="client_dashboard.php">Document Hub</a></li>
                        <li><a href="my_account.php">My Account</a></li> <!-- Assuming my_account.php exists -->
                        <li><a href="<?php echo rtrim(defined('BASE_URL') ? BASE_URL : '', '/'); ?>/DMS-SECTION/investment_rationale.php">SEO Rationale Builder</a></li>
                        <li><a href="support.php">Support</a></li> <!-- Assuming support.php exists -->
                    </ul>
                </div>

                <!-- Column 3: Legal -->
                <div>
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="<?php echo rtrim(defined('BASE_URL') ? BASE_URL : '', '/'); ?>/privacy_policy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo rtrim(defined('BASE_URL') ? BASE_URL : '', '/'); ?>/terms_of_service.php">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Column 4: Social -->
                <div>
                    <h4>Connect With Us</h4>
                    <div class="footer-social-links">
                        <a href="https://www.facebook.com/speed.organic.traffic/" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/seo_speed" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/company/mg-speed-marketing-ltd/" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

            </div>

            <!-- Bottom Bar -->
            <div class="footer-bottom-bar">
                <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Your Company'); ?>. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <?php
        $base_url_for_assets_footer = rtrim(defined('BASE_URL') ? BASE_URL : '.', '/');
    ?>
    <script src="<?php echo $base_url_for_assets_footer; ?>/assets/js/client_scripts.js"></script>
</body>
</html>
