<?php
// FILE: generate_report.php (NEW - Detailed Document)

// --- Enable Error Reporting & Load Core Files ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Setup variables for client_header.php ---
$client_name = isset($_SESSION['client_full_name']) ? htmlspecialchars($_SESSION['client_full_name']) : 'Guest';
$page_title = "SEO Investment Rationale Report"; // Base title

$site_logo_path = isset($pdo) ? get_branding_setting($pdo, 'logo_path') : null;
$primary_color = isset($pdo) ? get_branding_setting($pdo, 'primary_color') : null;
$primary_hover_color = '#0056b3'; // Or fetch from settings

$site_logo_url = null;
if ($site_logo_path && defined('PROJECT_ROOT_SERVER_PATH') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path)) {
    $base_upload_url = defined('UPLOAD_URL_PUBLIC') ? UPLOAD_URL_PUBLIC : ((defined('BASE_URL') ? rtrim(BASE_URL, '/') : '..') . '/uploads');
    $site_logo_url = rtrim($base_upload_url, '/') . '/' . ltrim($site_logo_path, '/') . '?v=' . time();
}
if(empty($primary_color)) $primary_color = '#007bff';

function val($data, $key, $default = '') { return isset($data[$key]) && $data[$key] !== '' ? htmlspecialchars($data[$key]) : $default; }
function money($value) { return is_numeric($value) ? '$' . number_format((float)$value, 2) : ($value === 'N/A' ? $value : ($value ?: '$0.00')); }
function percent($value) { return is_numeric($value) ? $value . '%' : ($value === 'N/A' ? $value : ($value ?: '0%')); }
function textBlock($text, $default = 'N/A') { return !empty($text) ? nl2br(htmlspecialchars($text)) : $default; }

$data = [];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST; // All form data is now in $_POST
    // The 'logo_path' if present in $data, will be a Data URL.
    // Personnel and technology data will be arrays like $data['personnel']['item'][], etc.
    if (empty($data['company_name'])) {
        // This is a basic check; more robust validation might be needed depending on requirements.
        // $error_message = 'Essential report data (e.g., company name) is missing.';
    }
} else {
    $error_message = 'No data submitted to generate the report. Please use the rationale builder tool.';
}

if (!$error_message && !empty($data['company_name'])) {
    $page_title .= " | " . val($data, 'company_name'); // Append company name to page title if data loaded
}

$theme_class = val($data, 'theme', 'light'); // Default to 'light' if not specified
$watermark_text = val($data, 'watermark', '');

function render_investment_table($items, $type_name) {
    if (empty($items) || !is_array($items)) {
        return "<p>No {$type_name} costs listed.</p>";
    }
    $html = '<table class="table table-sm table-bordered report-table"><thead><tr><th>Item/Role</th><th>Purpose</th><th class="text-end">Cost</th><th>Cycle</th></tr></thead><tbody>';
    // Ensure $items['item'] exists and is an array before trying to count it.
    if (!isset($items['item']) || !is_array($items['item'])) {
        return "<p>Invalid data structure for {$type_name} costs.</p>";
    }
    $count = count($items['item']); // Assuming all sub-arrays have the same count based on 'item'

    for ($i = 0; $i < $count; $i++) {
        $html .= '<tr>';
        $html .= '<td>' . val($items['item'], $i) . '</td>';
        $html .= '<td>' . val($items['purpose'], $i) . '</td>';
        $html .= '<td class="text-end">' . money(val($items['cost'], $i)) . '</td>';
        $html .= '<td>' . ucfirst(val($items['cycle'], $i)) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet"> <!-- For Corporate theme -->
    <style>
        /* Default Light Theme */
        body.theme-light { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #212529; }
        .theme-light .report-container { background-color: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .theme-light .report-header { border-bottom: 1px solid #dee2e6; }
        .theme-light .report-section h2 { color: #004a99; border-bottom: 2px solid #004a99; }
        .theme-light .report-section h3 { color: #333; }
        .theme-light .report-table th { background-color: #e9ecef; }
        .theme-light .report-table, .theme-light .report-table td, .theme-light .report-table th { border-color: #dee2e6; }
        .theme-light .watermark { color: rgba(0,0,0,0.05); }
        .theme-light .text-muted { color: #6c757d !important; }

        /* Dark Theme */
        body.theme-dark { font-family: 'Inter', sans-serif; background-color: #0c0e10; color: #f0f2f5; }
        .theme-dark .report-container { background-color: #1a1d21; border: 1px solid #3a3f44; box-shadow: 0 0 20px rgba(0,0,0,0.2); }
        .theme-dark .report-header { border-bottom: 1px solid #3a3f44; }
        .theme-dark .report-section h2 { color: #6cb2f7; border-bottom: 2px solid #6cb2f7; }
        .theme-dark .report-section h3 { color: #cccccc; }
        .theme-dark .report-table th { background-color: #2c3034; color: #f0f2f5; }
        .theme-dark .report-table, .theme-dark .report-table td, .theme-dark .report-table th { border-color: #3a3f44; }
        .theme-dark .watermark { color: rgba(255,255,255,0.05); }
        .theme-dark .text-muted { color: #a0a8b0 !important; }

        /* Corporate Theme */
        body.theme-corporate { font-family: 'Lato', 'Inter', sans-serif; background-color: #f0f4f8; /* Very light blue/gray for page background */ color: #212529; /* Dark text for readability */ }
        .theme-corporate .report-container { background-color: #ffffff; box-shadow: 0 2px 10px rgba(44,62,80,0.07); }
        .theme-corporate .report-header { border-bottom: 1px solid #c8d0d8; } /* Slightly darker border */
        .theme-corporate .report-section h2 { color: #002d5c; border-bottom: 2px solid #002d5c; } /* Navy Blue */
        .theme-corporate .report-section h3 { color: #003f7f; } /* Slightly lighter Navy for subheadings */
        .theme-corporate .report-table th { background-color: #e2e9f0; color: #002d5c; } /* Light blue-gray header, navy text */
        .theme-corporate .report-table, .theme-corporate .report-table td, .theme-corporate .report-table th { border-color: #c8d0d8; }
        .theme-corporate .watermark { color: rgba(0, 45, 92, 0.05); } /* Watermark based on Navy Blue */
        .theme-corporate .text-muted { color: #526b7e !important; } /* Muted text, still professional */

        /* Common Styles */
        .report-container { max-width: 800px; margin: 2rem auto; padding: 2rem; border-radius: 8px; position: relative; }
        .report-header { text-align: center; margin-bottom: 2rem; padding-bottom: 1rem;}
        .report-header img { max-height: 80px; margin-bottom: 1rem; }
        .report-section { margin-bottom: 2rem; }
        .report-section h2 { font-size: 1.75rem; font-weight: 600; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        .report-section h3 { font-size: 1.25rem; font-weight: 500; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .data-pair { margin-bottom: 0.75rem; }
        .data-pair strong { display: inline-block; min-width: 220px; font-weight: 600; } /* Adjusted width and weight */
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 5rem; font-weight: 800; pointer-events: none; z-index: 0; } /* z-index to ensure it's behind content */

        @media print {
            body, body.theme-light, body.theme-dark, body.theme-corporate {
                background-color: #fff !important; /* Override for print */
                color: #000 !important; /* Override for print */
                font-family: 'Inter', sans-serif !important; /* Consistent print font */
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
            .report-container { box-shadow: none; margin: 0; max-width: 100%; border-radius: 0; padding: 1rem; }
            .no-print { display: none; }
            .watermark { position: fixed !important; /* Ensure it's fixed for print */}
            .theme-dark .report-section h2, .theme-dark .report-section h3, .theme-dark .report-table th, .theme-dark .text-muted,
            .theme-corporate .report-section h2, .theme-corporate .report-section h3, .theme-corporate .report-table th, .theme-corporate .text-muted {
                color: #000 !important; /* Ensure readability for dark/corporate themes when printed */
            }
            .theme-dark .report-table th, .theme-corporate .report-table th { background-color: #eee !important; }
            .theme-dark .report-header, .theme-corporate .report-header,
            .theme-dark .report-table, .theme-dark .report-table td, .theme-dark .report-table th,
            .theme-corporate .report-table, .theme-corporate .report-table td, .theme-corporate .report-table th {
                 border-color: #ccc !important;
            }
        }
    </style>
</head>
<body class="theme-<?php echo $theme_class; ?>">
    <?php 
    // Use the main client header
    @include __DIR__ . '/../includes/client_header.php'; 
    ?>
    <div class="no-print text-center p-3" style="background-color: #e9ecef; border-bottom: 1px solid #dee2e6;">
        <a href="investment_rationale.php" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left-circle"></i> Go Back to Rationale Builder</a>
        <button onclick="window.print()" class="btn btn-primary me-3"><i class="bi bi-printer"></i> Print Report</button>
        <button type="button" onclick="document.getElementById('slidesForm').submit();" class="btn btn-success"><i class="bi bi-file-slides"></i> See Slides Version</button>
    </div>
    <div class="report-container">
        <?php if (!empty($watermark_text)): ?>
            <div class="watermark"><?php echo htmlspecialchars($watermark_text); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php else: ?>
            <div class="report-header">
                <?php if (!empty($data['logo_path'])): ?>
                    <img src="<?php echo val($data, 'logo_path'); ?>" alt="<?php echo val($data, 'company_name'); ?> Logo">
                <?php endif; ?>
                <h1>SEO Investment Rationale</h1>
                <h2><?php echo val($data, 'company_name'); ?></h2>
                <p class="text-muted">Prepared by: <?php echo val($data, 'prepared_by', 'N/A'); ?> | Date: <?php echo date('F j, Y'); ?></p>
            </div>

            <div class="report-section">
                <h2>I. Executive Summary</h2>
                <h3>Current Reality & Threat</h3>
                <p><?php echo textBlock(val($data, 'exec_current_reality')); ?></p>
                <h3>Cost of Inaction</h3>
                <p><?php echo textBlock(val($data, 'exec_cost_of_inaction')); ?></p>
                <h3>Our Solution</h3>
                <p><?php echo textBlock(val($data, 'exec_solution')); ?></p>
            </div>

            <div class="report-section">
                <h2>II. Strategic Investment</h2>
                <h3>A. Personnel Costs</h3>
                <?php echo render_investment_table(isset($data['personnel']) ? $data['personnel'] : [], 'Personnel'); ?>
                <h3>B. Technology & Tools</h3>
                <?php echo render_investment_table(isset($data['technology']) ? $data['technology'] : [], 'Technology & Tools'); ?>
            </div>

            <div class="report-section">
                <h2>III. The Business Impact</h2>
                <h3>A. SEO Performance Projections</h3>
                <div class="data-pair"><strong>Y1 Organic Traffic Growth:</strong> <?php echo percent(val($data, 'impact_y1_traffic')); ?></div>
                <div class="data-pair"><strong>Y1 Top 3 Keyword Increase:</strong> <?php echo val($data, 'impact_y1_keywords', 'N/A'); ?> #</div>
                <h3>Business Rationale for Keyword Growth</h3>
                <p><?php echo textBlock(val($data, 'impact_keyword_rationale')); ?></p>
                
                <h3>B. Financial Projections</h3>
                <div class="data-pair"><strong>Y1 Attributable Revenue:</strong> <?php echo money(val($data, 'impact_y1_revenue')); ?></div>
                <div class="data-pair"><strong>Y3 Attributable Revenue:</strong> <?php echo money(val($data, 'impact_y3_revenue')); ?></div>
            </div>

            <div class="report-section">
                <h2>IV. Financial Summary</h2>
                <div class="data-pair"><strong>Total Monthly Investment:</strong> <?php echo money(val($data, 'investment_monthly_total')); ?></div>
                <div class="data-pair"><strong>Total Year 1 Investment:</strong> <?php echo money(val($data, 'investment_y1_total')); ?></div>
                <div class="data-pair"><strong>Projected Year 1 ROI:</strong> <?php echo percent(val($data, 'roi_y1')); ?></div>
                <div class="data-pair"><strong>Payback Period (Months):</strong> <?php echo val($data, 'payback_period', 'N/A'); ?></div>
            </div>

            <div class="report-section">
                <h2>V. De-Risking the Investment</h2>
                <h3>Mitigating Algorithm Volatility</h3>
                <p><?php echo textBlock(val($data, 'derisk_algo')); ?></p>
                <h3>Accountability & Governance</h3>
                <p><?php echo textBlock(val($data, 'derisk_accountability')); ?></p>
            </div>

        <?php endif; ?>
    </div>
    <?php 
    // Use the main client footer
    @include __DIR__ . '/../includes/client_footer.php'; 
    ?>

    <?php if (!$error_message && !empty($data)): ?>
    <form id="slidesForm" method="POST" action="generate_presentation.php" target="_blank" style="display: none;">
        <?php
        // Re-populate all data as hidden fields for the presentation script
        foreach ($data as $key => $value) {
            if ($key === 'personnel' || $key === 'technology') {
                if (is_array($value)) {
                    foreach ($value as $sub_key => $sub_array) { // $sub_key is 'item', 'purpose', etc.
                        if (is_array($sub_array)) {
                            foreach ($sub_array as $item_value) {
                                echo '<input type="hidden" name="' . htmlspecialchars($key . '[' . $sub_key . '][]') . '" value="' . htmlspecialchars($item_value) . '">' . "\n";
                            }
                        }
                    }
                }
            } elseif (is_array($value)) { // For any other potential flat arrays
                foreach($value as $arr_val) {
                     echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($arr_val) . '">' . "\n";
                }
            } else {
                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">' . "\n";
            }
        }
        ?>
    </form>
    <?php endif; ?>
</body>
</html>
