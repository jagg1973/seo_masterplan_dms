<?php
// FILE: generate_presentation.php (DEFINITIVE, COMPLETE, AND STABLE VERSION)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Setup variables for client_header.php & client_footer.php ---
$client_name = isset($_SESSION['client_full_name']) ? htmlspecialchars($_SESSION['client_full_name']) : 'Guest';
// $page_title for header will be set after $data is loaded.

$site_logo_path_from_db = isset($pdo) ? get_branding_setting($pdo, 'logo_path') : null;
$primary_color_from_db = isset($pdo) ? get_branding_setting($pdo, 'primary_color') : null;
$primary_hover_color = '#0056b3'; // Default or fetch if available

$site_logo_url = null;
if ($site_logo_path_from_db && defined('PROJECT_ROOT_SERVER_PATH') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path_from_db)) {
    $base_upload_url = defined('UPLOAD_URL_PUBLIC') ? UPLOAD_URL_PUBLIC : ((defined('BASE_URL') ? rtrim(BASE_URL, '/') : '..') . '/uploads');
    $site_logo_url = rtrim($base_upload_url, '/') . '/' . ltrim($site_logo_path_from_db, '/') . '?v=' . time();
}
$primary_color = !empty($primary_color_from_db) ? $primary_color_from_db : '#007bff';


function val($data, $key, $default = '') { return isset($data[$key]) && $data[$key] !== '' ? htmlspecialchars($data[$key]) : $default; }
function money($value) { return is_numeric($value) ? '$' . number_format((float)$value, 2) : ($value === 'N/A' ? $value : ($value ?: '$0.00')); }
function percent($value) { return is_numeric($value) ? $value . '%' : ($value === 'N/A' ? $value : ($value ?: '0%')); }
function textBlock($text, $max_chars = 250, $default = 'N/A') { return !empty($text) ? nl2br(htmlspecialchars(substr($text, 0, $max_chars) . (strlen($text) > $max_chars ? '...' : ''))) : $default; }
$file_path = ''; $data = []; $error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST; // All form data is now in $_POST
    // The 'logo_path' if present in $data, will be a Data URL.
    // Personnel and technology data will be arrays like $data['personnel']['item'][], etc.
    if (empty($data['company_name'])) {
        // Basic check
        // $error_message = 'Essential presentation data (e.g., company name) is missing.';
    }
} else {
    $error_message = 'No data submitted to generate the presentation. Please use the rationale builder tool.';
}

$theme = val($data, 'theme', 'dark'); // Default to dark for presentations
$watermark_text = val($data, 'watermark', '');

// Determine the page title for the <title> tag and for client_header.php
$html_page_title = "SEO Investment Presentation"; // Default for <title>
if (!$error_message && !empty($data['company_name'])) {
    $html_page_title .= " | " . val($data, 'company_name');
} elseif ($error_message) {
    $html_page_title = "Error Generating Presentation";
}
$page_title = $html_page_title; // This variable is used by client_header.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($html_page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ====================================================================== */
        /* CSS THEMES - REBUILT FOR STABILITY & CORRECTNESS */
        /* ====================================================================== */
        :root { /* Default: Light Theme */
            --slide-bg: #f8f9fa;
            --slide-text: #212529;
            --slide-primary: #007bff; /* Brighter Blue */
            --slide-accent: #28a745;  /* Vibrant Green */
            --slide-border: #dee2e6;
            --slide-muted: #6c757d;
            --slide-primary-rgb: 0, 123, 255;
            --slide-font-family: 'Inter', sans-serif;
        }
        body.theme-dark {
           --slide-bg: #1a1d21; /* Darker, less blue */
            --slide-text: #f0f2f5; /* Lighter text */
            --slide-primary: #6cb2f7; /* Softer, modern blue */
            --slide-accent: #3ddc84;  /* Bright, modern green */
            --slide-border: #3a3f44;
            --slide-muted: #a0a8b0;
            --slide-primary-rgb: 108, 178, 247;
        }
        body.theme-corporate {
            --slide-bg: #ffffff; /* Clean white */
            --slide-text: #2c3e50; /* Softer black/navy */
            --slide-primary: #005A9C; /* Classic, strong blue */
            --slide-accent: #00B2A9;  /* Teal accent */
            --slide-border: #e0e6ed;
            --slide-muted: #7f8c8d;
            --slide-primary-rgb: 0, 90, 156;
            --slide-font-family: 'Lato', 'Inter', sans-serif; /* Option for a slightly different font */
        }

        /* Import Lato font if corporate theme might use it */
        @import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap');

        body {
            background-color: #212529; /* Fallback/outer page background */
            font-family: var(--slide-font-family);
            transition: background-color 0.3s ease, color 0.3s ease; /* Smooth transitions for theme changes if ever dynamic */
        }
        /* Apply theme-specific body background for the slides area if needed, or keep it dark */
        body.theme-light { background-color: #e9ecef; }
        body.theme-dark { background-color: #0c0e10; }
        body.theme-corporate { background-color: #e8eff5; }
         .presentation-container { max-width: 1200px; margin: 2rem auto; }
        .slide { background-color: var(--slide-bg); color: var(--slide-text); border-radius: 12px; margin-bottom: 2rem; aspect-ratio: 16 / 9; display: flex; flex-direction: column; padding: 40px; /* Changed from 5% to fixed 40px */ position: relative; overflow: hidden; box-sizing: border-box; /* Ensure padding is included correctly */ }
        .slide::after { content: '<?php echo addslashes($watermark_text); ?>'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 6rem; color: var(--slide-text); opacity: 0.05; font-weight: 800; pointer-events: none; z-index: 0; }
        @media print { body { background-color: #fff; } .no-print { display: none; } .presentation-container { margin: 0; max-width: 100%; } .slide { width: 100%; height: 100vh; box-shadow: none; border: none; page-break-after: always; margin-bottom: 0; } .slide:last-child { page-break-after: avoid; } }
        .slide-footer { position: absolute; bottom: 30px; left: 40px; right: 40px; /* Matched to slide padding */ display: flex; justify-content: space-between; color: var(--slide-muted); font-size: 0.9rem; }
        .title-slide h1 { font-size: 5rem; font-weight: 800; color: var(--slide-primary); }
        .slide h2.slide-title { font-size: 2.8rem; font-weight: 700; color: var(--slide-primary); margin-bottom: 2rem; text-align: center;}
       .slide .content-area { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; z-index: 1; /* Ensure content is above watermark pseudo-element */ }
        .slide .content-area p { font-size: 1.2rem; line-height: 1.6; }
        .slide .content-area ul { list-style: none; padding-left: 0; }
        .slide .content-area ul li { font-size: 1.2rem; margin-bottom: 0.75rem; padding-left: 1.5em; position: relative; }
        .slide .content-area ul li::before { content: "✓"; color: var(--slide-accent); position: absolute; left: 0; font-weight: bold; }
        .kpi-box { background-color: rgba(var(--slide-primary-rgb), 0.08); padding: 1.5rem; border-radius: 10px; text-align: center; margin-bottom:1rem; border: 1px solid rgba(var(--slide-primary-rgb), 0.2); }
        .kpi-box h3 { font-size: 1.4rem; color: var(--slide-primary); margin-bottom: 0.5rem; font-weight: 600; }
        .kpi-box .value { font-size: 2.5rem; font-weight: 700; color: var(--slide-accent); }
    </style>
</head>
<body class="theme-<?php echo $theme; ?>">
<?php @include __DIR__ . '/../includes/client_header.php'; ?>

<div class="no-print text-center p-3 d-flex justify-content-center align-items-center" style="background-color: #343a40;">
    <a href="investment_rationale.php" class="btn btn-info btn-lg me-3"><i class="bi bi-arrow-left-circle me-2"></i>Go Back to Rationale Builder</a>
    <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="bi bi-printer me-2"></i>Download as PDF</button>
</div>

<div class="presentation-container">
    <?php if (!empty($error_message)): ?> <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php else: ?>
        <!-- Slide 1: Title -->
        <div class="slide title-slide">
            <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center">
                <?php if (!empty($data['logo_path'])): ?><img src="<?php echo val($data, 'logo_path'); ?>" alt="Logo" style="max-height: 100px; margin-bottom: 2rem;"><?php endif; ?>
                <h1><?php echo val($data, 'company_name'); ?></h1>
                <p class="mt-4 fs-4" style="color: var(--slide-muted);">SEO Investment Rationale</p>
                <?php if (val($data, 'prepared_by')): ?>
                    <p class="mt-3 fs-5" style="color: var(--slide-muted);">Prepared by: <?php echo val($data, 'prepared_by'); ?></p>
                <?php endif; ?>
            </div>
            <div class="slide-footer"><span><?php echo val($data, 'company_name'); ?></span><span>Page 1</span></div>
        </div>

        <!-- Slide 2: The Challenge & Opportunity -->
        <div class="slide">
            <h2 class="slide-title">The Challenge & Opportunity</h2>
            <div class="content-area">
                <h3>Current Reality & Threat</h3>
                <p><?php echo textBlock(val($data, 'exec_current_reality'), 300); ?></p>
                <h3 class="mt-4">Cost of Inaction</h3>
                <p><?php echo textBlock(val($data, 'exec_cost_of_inaction'), 300); ?></p>
            </div>
            <div class="slide-footer"><span><?php echo val($data, 'company_name'); ?></span><span>Page 2</span></div>
        </div>

        <!-- Slide 3: Our Proposed Solution -->
        <div class="slide">
            <h2 class="slide-title">Our Proposed Solution</h2>
            <div class="content-area">
                <p><?php echo textBlock(val($data, 'exec_solution'), 500); ?></p>
            </div>
            <div class="slide-footer"><span><?php echo val($data, 'company_name'); ?></span><span>Page 3</span></div>
        </div>

        <!-- Slide 4: Key Performance Projections -->
        <div class="slide">
            <h2 class="slide-title">Key Performance Projections (Year 1)</h2>
            <div class="content-area row align-items-center">
                <div class="col-md-4">
                    <div class="kpi-box"><h3>Organic Traffic Growth</h3><span class="value"><?php echo percent(val($data, 'impact_y1_traffic')); ?></span></div>
                </div>
                <div class="col-md-4">
                    <div class="kpi-box"><h3>Top 3 Keyword Increase</h3><span class="value"><?php echo val($data, 'impact_y1_keywords', 'N/A'); ?> #</span></div>
                </div>
                 <div class="col-md-4">
                    <div class="kpi-box"><h3>Attributable Revenue</h3><span class="value"><?php echo money(val($data, 'impact_y1_revenue')); ?></span></div>
                </div>
                <div class="col-12 mt-3">
                    <h4>Rationale for Keyword Growth:</h4>
                    <p><?php echo textBlock(val($data, 'impact_keyword_rationale'), 200); ?></p>
                </div>
            </div>
            <div class="slide-footer"><span><?php echo val($data, 'company_name'); ?></span><span>Page 4</span></div>
        </div>

        <!-- Slide 5: The Ask & Financial Impact -->
        <div class="slide">
            <h2 class="slide-title">The Ask & Financial Impact</h2>
            <div class="row h-100 align-items-center">
                <div class="col-md-7"><canvas id="roiChart"></canvas></div>
                <div class="col-md-5">
                    <div class="mb-4"><h3>Investment (Y1): <span style="color:var(--slide-accent);"><?php echo money(val($data, 'investment_y1_total')); ?></span></h3></div>
                    <div class="mb-4"><h3>Projected Y1 ROI: <span style="color:var(--slide-accent);"><?php echo percent(val($data, 'roi_y1')); ?></span></h3></div>
                    <div class="mb-4"><h3>Payback Period: <span style="color:var(--slide-accent);"><?php echo val($data, 'payback_period', 'N/A'); ?> Months</span></h3></div>
                </div>
            </div>
            <div class="slide-footer"><span><?php echo val($data, 'company_name'); ?></span><span>Page 5</span></div>
        </div>

        <!-- Slide 6: Mitigating Risks -->
        <div class="slide">
            <h2 class="slide-title">Mitigating Risks</h2>
            <div class="content-area">
                <h3>Mitigating Algorithm Volatility</h3>
                <p><?php echo textBlock(val($data, 'derisk_algo'), 300); ?></p>
                <h3 class="mt-4">Accountability & Governance</h3>
                <p><?php echo textBlock(val($data, 'derisk_accountability'), 300); ?></p>
            </div>
            <div class="slide-footer"><span><?php echo val($data, 'company_name'); ?></span><span>Page 6</span></div>
        </div>
    <?php endif; ?>
</div>
<script>
    <?php if (empty($error_message)): ?>
    const ctx = document.getElementById('roiChart');
    const investmentY1 = <?php echo floatval(val($data, 'investment_y1_total', 0)); ?>;
    const revenueY1 = <?php echo floatval(val($data, 'impact_y1_revenue', 0)); ?>;
    const revenueY3 = <?php echo floatval(val($data, 'impact_y3_revenue', 0)); ?>;
    const bodyStyles = window.getComputedStyle(document.body);
    const textColor = bodyStyles.getPropertyValue('--slide-text').trim();
    const gridColor = bodyStyles.getPropertyValue('--slide-border').trim();
    new Chart(ctx, { type: 'bar', data: { labels: ['Y1 Investment', 'Y1 Revenue', 'Y3 Revenue'], datasets: [{ data: [investmentY1, revenueY1, revenueY3], backgroundColor: ['#ef4444', '#22c55e', '#3b82f6'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, title: { display: true, text: 'Investment vs. Projected Revenue', color: textColor, font: { size: 18 } } }, scales: { y: { beginAtZero: true, ticks: { color: textColor, font: { size: 14 } }, grid: { color: gridColor } }, x: { ticks: { color: textColor, font: { size: 14 } }, grid: { display: false } } } } });
    // Update --slide-primary-rgb based on the current theme for kpi-box background    const root = document.documentElement;
    const primaryColorForRGB = bodyStyles.getPropertyValue('--slide-primary').trim();
    const rgbMatch = primaryColorForRGB.match(/(\d+),\s*(\d+),\s*(\d+)/) || primaryColorForRGB.match(/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/) || primaryColorForRGB.match(/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/);
    if (rgbMatch && rgbMatch.length === 4 && rgbMatch[0].startsWith('#')) { // Hex
        root.style.setProperty('--slide-primary-rgb', `${parseInt(rgbMatch[1].length === 1 ? rgbMatch[1]+rgbMatch[1] : rgbMatch[1], 16)}, ${parseInt(rgbMatch[2].length === 1 ? rgbMatch[2]+rgbMatch[2] : rgbMatch[2], 16)}, ${parseInt(rgbMatch[3].length === 1 ? rgbMatch[3]+rgbMatch[3] : rgbMatch[3], 16)}`);
    } else if (rgbMatch && rgbMatch.length === 4) { // rgb()
        root.style.setProperty('--slide-primary-rgb', `${rgbMatch[1]}, ${rgbMatch[2]}, ${rgbMatch[3]}`);
    } else { // Fallback if parsing fails
        root.style.setProperty('--slide-primary-rgb', '0, 123, 255'); // Default light theme primary
    }

    <?php endif; ?>
</script>
<?php @include __DIR__ . '/../includes/client_footer.php'; ?>
</body>
</html>