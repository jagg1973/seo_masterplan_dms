<?php
// FILE: investment_rationale.php (Corrected and Improved)

// --- Initialize session and load core files ---
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in (recommended for secure areas)
if (!isset($_SESSION['client_id'])) {
    // Assuming BASE_URL is defined in config.php
    // header("Location: " . (defined('BASE_URL') ? BASE_URL : '../') . "login.php");
    // exit;
    // For now, allow access but header variables might be for a 'Guest'
}

// --- Setup variables for client_header.php ---
$client_name = isset($_SESSION['client_full_name']) ? htmlspecialchars($_SESSION['client_full_name']) : 'Guest';
$page_title = "SEO Rationale Builder - " . (defined('SITE_NAME') ? SITE_NAME : 'DMS Portal');

$site_logo_path = isset($pdo) ? get_branding_setting($pdo, 'logo_path') : null;
$primary_color = isset($pdo) ? get_branding_setting($pdo, 'primary_color') : null;
$primary_hover_color = '#0056b3'; // Or fetch from settings if available

$site_logo_url = null;
if ($site_logo_path && defined('PROJECT_ROOT_SERVER_PATH') && file_exists(PROJECT_ROOT_SERVER_PATH . '/uploads/' . $site_logo_path)) {
    $base_upload_url = defined('UPLOAD_URL_PUBLIC') ? UPLOAD_URL_PUBLIC : ((defined('BASE_URL') ? rtrim(BASE_URL, '/') : '..') . '/uploads');
    $site_logo_url = rtrim($base_upload_url, '/') . '/' . ltrim($site_logo_path, '/') . '?v=' . time();
}
if(empty($primary_color)) $primary_color = '#007bff'; // Default primary color

// --- PHP form handling logic (Verified Stable) ---
// Server-side saving is removed. Data will be passed to generation scripts via client-side JavaScript.
$success_message = ''; 
$error_message = ''; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive SEO Rationale Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary-color: #4A90E2; --light-gray: #f7f8fa; --border-color: #eef2f6; --card-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        body { background-color: var(--light-gray); font-family: 'Inter', sans-serif; }
        .form-section-card { background-color: #fff; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 2rem; box-shadow: var(--card-shadow); }
        .card-header { background-color: transparent; border-bottom: 1px solid var(--border-color); padding: 1rem 1.5rem; font-weight: 600; font-size: 1.05rem; display: flex; align-items: center; }
        .card-header .bi { margin-right: 0.75rem; color: var(--primary-color); font-size: 1.25rem; }
        .card-body { padding: 1.5rem; }
        .input-block { padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); }
        .card-body .input-block:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .form-label { font-size: 0.9rem; font-weight: 500; color: #5a6a7d; margin-bottom: 0.5rem; }
        .action-panel { position: sticky; top: 2rem; }
        .dynamic-table-row { display: grid; grid-template-columns: 1fr 1fr 100px 110px 60px; gap: 10px; align-items: center; margin-bottom: .5rem; }
        @media (max-width: 767px) { .dynamic-table-row { grid-template-columns: 1fr; } .dynamic-table-row > * { margin-bottom: 0.5rem; } .dynamic-table-row > *:last-child { margin-bottom: 0; } }
        .tooltip-icon { color: #aaa; cursor: help; margin-left: 8px; }
    </style>
</head>
<body>
    <?php 
    // Corrected path to client_header.php
    @include '../includes/client_header.php'; 
    ?>
<div class="container my-5">
    <div class="mb-4"> <!-- Back button container -->
        <a href="<?php echo rtrim(defined('BASE_URL') ? BASE_URL : '..', '/'); ?>/client_dashboard.php?t=<?php echo time(); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle me-2"></i>Go Back
        </a>
    </div>
    <div class="page-header mb-5 text-center"><h1 class="display-6 fw-bold">Comprehensive SEO Rationale Builder</h1></div>
    <?php if(!empty($success_message)) echo $success_message; ?>
    <?php if(!empty($error_message)) echo '<div class="alert alert-danger" id="phpErrorMessage">'.$error_message.'</div>'; ?>
    <div id="js_message_container"></div> <!-- For JS messages -->
    <form id="rationaleForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="form-section-card"><div class="card-header"><i class="bi bi-file-earmark-text"></i>I. Executive Summary</div><div class="card-body"><div class="input-block"><label class="form-label">Current Reality & Threat</label><textarea class="form-control" name="exec_current_reality" rows="2"></textarea></div><div class="input-block"><label class="form-label">Cost of Inaction</label><textarea class="form-control" name="exec_cost_of_inaction" rows="2"></textarea></div><div class="input-block"><label class="form-label">Our Solution</label><textarea class="form-control" name="exec_solution" rows="2"></textarea></div></div></div>
                <div class="form-section-card"><div class="card-header"><i class="bi bi-rocket-takeoff"></i>III. The Strategic Investment</div><div class="card-body"><div class="dynamic-table-row mb-2 d-none d-md-grid"><label class="form-label small">Item/Role</label><label class="form-label small">Purpose</label><label class="form-label small">Cost</label><label class="form-label small">Cycle</label><div></div></div><h6>A. Personnel Costs</h6><div id="personnel-container"></div><button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addDynamicRow('personnel')"><i class="bi bi-plus-circle"></i> Add</button><hr class="my-4"><h6>B. Technology & Tools</h6><div id="technology-container"></div><button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addDynamicRow('technology')"><i class="bi bi-plus-circle"></i> Add</button></div></div>
                <div class="form-section-card">
                    <div class="card-header"><i class="bi bi-graph-up-arrow"></i>IV. The Business Impact</div>
                    <div class="card-body">
                        <div class="input-block">
                            <h6>A. SEO Performance Projections</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0"><label class="form-label">Y1 Organic Traffic Growth</label><div class="input-group"><input type="number" class="form-control" name="impact_y1_traffic"><span class="input-group-text">%</span></div></div>
                                <div class="col-md-6"><label class="form-label">Y1 Top 3 Keyword Increase</label><div class="input-group"><input type="number" class="form-control" name="impact_y1_keywords"><span class="input-group-text">#</span></div></div>
                            </div>
                            <!-- NEW FIELD ADDED HERE -->
                            <div class="mt-3">
                                <label class="form-label">Business Rationale for Keyword Growth</label>
                                <textarea class="form-control" name="impact_keyword_rationale" rows="2" placeholder="e.g., These keywords represent high-purchase intent searches, directly capturing market share from our main competitors."></textarea>
                            </div>
                        </div>
                        <div class="input-block">
                            <h6>B. Financial Projections</h6>
                            <div class="row"><div class="col-md-6 mb-3 mb-md-0"><label class="form-label">Y1 Attributable Revenue</label><div class="input-group"><span class="input-group-text">$</span><input type="number" class="form-control" name="impact_y1_revenue" id="impact_y1_revenue_input" oninput="calculateTotals()"></div></div><div class="col-md-6"><label class="form-label">Y3 Attributable Revenue</label><div class="input-group"><span class="input-group-text">$</span><input type="number" class="form-control" name="impact_y3_revenue"></div></div></div>
                        </div>
                    </div>
                </div>
                <div class="form-section-card"><div class="card-header"><i class="bi bi-shield-check"></i>V. De-Risking the Investment</div><div class="card-body"><div class="input-block"><label class="form-label">Mitigating Algorithm Volatility</label><textarea class="form-control" name="derisk_algo" rows="2"></textarea></div><div class="input-block"><label class="form-label">Accountability & Governance</label><textarea class="form-control" name="derisk_accountability" rows="2"></textarea></div></div></div>
            </div> <!-- End col-lg-8 -->
            <div class="col-lg-4">
                <div class="action-panel">
                    <div class="form-section-card"><div class="card-header"><i class="bi bi-gear-wide-connected"></i>Controls</div><div class="card-body"><div class="input-block"><label class="form-label">Company Name</label><input type="text" class="form-control" name="company_name" required></div><div class="input-block"><label class="form-label">Company Logo</label><input class="form-control" type="file" name="company_logo"></div><div class="input-block"><label class="form-label">Report Theme</label><select class="form-select" name="theme"><option value="light" selected>Light</option><option value="dark">Dark</option><option value="corporate">Corporate</option></select></div><div class="input-block"><label class="form-label">Prepared By</label><input type="text" class="form-control" name="prepared_by"></div><div class="input-block"><label class="form-label">Watermark Text</label><input type="text" class="form-control" name="watermark"></div></div></div>
                    <div class="form-section-card mt-4"><div class="card-header"><i class="bi bi-calculator"></i>Financial Summary</div><div class="card-body"><div class="input-block"><label class="form-label">Total Monthly Investment</label><div class="input-group"><span class="input-group-text">$</span><input type="number" class="form-control" name="investment_monthly_total" id="investment_monthly_total" readonly></div></div><div class="input-block"><label class="form-label">Total Year 1 Investment <i class="bi bi-question-circle-fill tooltip-icon" data-bs-toggle="tooltip" title="Calculated automatically."></i></label><div class="input-group"><span class="input-group-text">$</span><input type="number" class="form-control" name="investment_y1_total" id="investment_y1_total" readonly></div></div><div class="input-block"><label class="form-label">Projected Year 1 ROI <i class="bi bi-question-circle-fill tooltip-icon" data-bs-toggle="tooltip" title="((Revenue - Investment) / Investment) * 100"></i></label><div class="input-group"><input type="number" class="form-control" name="roi_y1" id="roi_y1" readonly><span class="input-group-text">%</span></div></div><div class="input-block"><label class="form-label">Payback Period (Months)</label><input type="number" class="form-control" name="payback_period" placeholder="e.g., 11"></div></div></div>
                    <div class="d-grid mt-4"><button type="submit" name="save_rationale" class="btn btn-primary btn-lg w-100"><i class="bi bi-arrow-right-circle-fill me-2"></i>Generate Rationale</button></div>
                </div>
            </div>
        </div> <!-- End row -->
    </form>
</div>
<?php 
    // Corrected path to client_footer.php
    @include '../includes/client_footer.php';
    ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // This stable JS block is unchanged.
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    function addDynamicRow(type) {
        const container = document.getElementById(`${type}-container`);
        const newRow = document.createElement('div');
        newRow.className = 'dynamic-table-row';
        newRow.innerHTML = `
            <input type="text" name="${type}[item][]" class="form-control form-control-sm" placeholder="Item/Role Name">
            <input type="text" name="${type}[purpose][]" class="form-control form-control-sm" placeholder="Purpose">
            <input type="number" name="${type}[cost][]" class="form-control form-control-sm cost-input" placeholder="Cost" oninput="calculateTotals()">
            <select name="${type}[cycle][]" class="form-select form-select-sm billing-cycle-select" onchange="calculateTotals()">
                <option value="monthly">Monthly</option><option value="yearly">Yearly</option>
            </select>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove(); calculateTotals();"><i class="bi bi-trash"></i></button>`;
        container.appendChild(newRow);
        calculateTotals();
    }

    function calculateTotals() {
        let totalMonthly = 0; let totalAnnual = 0;
        document.querySelectorAll('#personnel-container .dynamic-table-row, #technology-container .dynamic-table-row').forEach(row => {
            const costInput = row.querySelector('.cost-input'); const cycleSelect = row.querySelector('.billing-cycle-select');
            if (costInput && cycleSelect) { const cost = parseFloat(costInput.value) || 0; if (cycleSelect.value === 'monthly') { totalMonthly += cost; totalAnnual += cost * 12; } else if (cycleSelect.value === 'yearly') { totalAnnual += cost; } }
        });
        const monthlyTotalInput = document.getElementById('investment_monthly_total');
        const annualTotalInput = document.getElementById('investment_y1_total');
        if (monthlyTotalInput) monthlyTotalInput.value = totalMonthly.toFixed(2);
        if (annualTotalInput) annualTotalInput.value = totalAnnual.toFixed(2);
        const impactY1RevenueInput = document.getElementById('impact_y1_revenue_input');
        const roiY1Input = document.getElementById('roi_y1');
        if (impactY1RevenueInput && roiY1Input && annualTotalInput) {
            const revenueY1 = parseFloat(impactY1RevenueInput.value) || 0; const investmentY1 = totalAnnual;
            if (investmentY1 > 0) { const roi = ((revenueY1 - investmentY1) / investmentY1) * 100; roiY1Input.value = roi.toFixed(2); } else { roiY1Input.value = (revenueY1 > 0) ? 'N/A' : '0.00'; }
        } else if (roiY1Input) { roiY1Input.value = '0.00'; }
    }

    document.getElementById('rationaleForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const logoFile = form.querySelector('input[name="company_logo"]').files[0];
        const messageContainer = document.getElementById('js_message_container');
        messageContainer.innerHTML = '<div class="alert alert-info">Processing... Please wait.</div>';

        const submitData = (logoDataURL) => {
            if (logoDataURL) formData.set('logo_path', logoDataURL); // Use 'logo_path' as expected by generation scripts
            
            // Only generate the report initially
            const reportForm = document.createElement('form');
            reportForm.method = 'POST';
            reportForm.action = 'generate_report.php';
            reportForm.target = '_blank';
            for (let [key, value] of formData.entries()) {
                if (key.endsWith('[]')) { // Handle array data (personnel, technology)
                    formData.getAll(key).forEach(val => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = val;
                        reportForm.appendChild(input);
                    });
                } else {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    reportForm.appendChild(input);
                }
            }
            document.body.appendChild(reportForm);
            reportForm.submit();
            document.body.removeChild(reportForm);
            messageContainer.innerHTML = '<div class="alert alert-success">Report generation initiated in a new tab.</div>';
        };

        if (logoFile) {
            const reader = new FileReader();
            reader.onloadend = function() { submitData(reader.result); }
            reader.onerror = function() { 
                messageContainer.innerHTML = '<div class="alert alert-danger">Error reading logo file.</div>';
                submitData(null); // Proceed without logo
            }
            reader.readAsDataURL(logoFile);
        } else {
            submitData(null); // No logo selected
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        addDynamicRow('personnel'); addDynamicRow('technology');
        const impactY1RevenueElem = document.getElementById('impact_y1_revenue_input');
        if (impactY1RevenueElem && !impactY1RevenueElem.hasAttribute('oninput')) {
            impactY1RevenueElem.addEventListener('input', calculateTotals);
        }
    });
</script>
</body>
</html>