<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

if (!isset($_SESSION['user_id'])) exit("Unauthorized access.");

// Check report type
$report_type = $_POST['report_type'] ?? 'consumption_log';

if ($report_type === 'category_inventory') {
    // Handle category inventory report
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $category_name = $_POST['category_name'] ?? 'Unknown Category';
    $selected_assets = $_POST['selected_assets'] ?? [];
    
    if (empty($selected_assets)) {
        exit("No assets selected for report generation.");
    }
    
    // Generate category inventory report
    generateCategoryInventoryReport($conn, $category_id, $category_name, $selected_assets);
    exit();
}

// --- Original consumption log report logic ---
// --- Filters ---
$selected_office = isset($_POST['office']) ? intval($_POST['office']) : 0;
$selected_year   = isset($_POST['year']) ? intval($_POST['year']) : 0;
$selected_month  = isset($_POST['month']) ? intval($_POST['month']) : 0;
$selected_day    = isset($_POST['day']) ? intval($_POST['day']) : 0;

// Date filter
$date_conditions = [];
if ($selected_year)  $date_conditions[] = "YEAR(cl.consumption_date) = $selected_year";
if ($selected_month) $date_conditions[] = "MONTH(cl.consumption_date) = $selected_month";
if ($selected_day)   $date_conditions[] = "DAY(cl.consumption_date) = $selected_day";
$date_sql = $date_conditions ? " AND " . implode(" AND ", $date_conditions) : "";

// Office filter
$office_sql = $selected_office ? " AND cl.office_id = $selected_office" : "";

// --- Fetch consumption log ---
$sql = "
    SELECT cl.id, a.description, o.office_name, cl.quantity_consumed, 
           u1.fullname AS recipient, u2.fullname AS dispensed_by, 
           cl.consumption_date, cl.remarks
    FROM consumption_log cl
    LEFT JOIN assets a ON cl.asset_id = a.id
    JOIN offices o ON cl.office_id = o.id
    LEFT JOIN users u1 ON cl.recipient_user_id = u1.id
    LEFT JOIN users u2 ON cl.dispensed_by_user_id = u2.id
    WHERE 1=1 $office_sql $date_sql
    ORDER BY cl.consumption_date DESC
";
$result = $conn->query($sql);

// --- Chart data ---
$chart_labels = [];
$chart_totals = [];
$chart_sql = $selected_office
    ? "SELECT a.description AS label, SUM(cl.quantity_consumed) AS total
       FROM consumption_log cl
       LEFT JOIN assets a ON cl.asset_id = a.id
       WHERE 1=1 $office_sql $date_sql
       GROUP BY cl.asset_id
       ORDER BY total DESC"
    : "SELECT o.office_name AS label, SUM(cl.quantity_consumed) AS total
       FROM consumption_log cl
       JOIN offices o ON cl.office_id = o.id
       WHERE 1=1 $date_sql
       GROUP BY o.id
       ORDER BY total DESC";
$chart_result = $conn->query($chart_sql);
while ($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = $row['label'];
    $chart_totals[] = (float)$row['total'];
}

// --- PDF Header ---
$reportDate = date('F j, Y');
$reportFilename = 'Consumption_Report_' . date('Ymd_His') . '.pdf';
$logoPath = '../img/PILAR LOGO TRANSPARENT.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

// --- HTML ---
$html = '
<style>
body { font-family: DejaVu Sans, sans-serif; font-size:10px; margin:0; padding:10px;}
.header-row { display: table; width:100%; margin-bottom:10px; }
.header-col { display: table-cell; vertical-align: top; }
.left-col { width: 15%; text-align:left; }
.center-col { width: 70%; text-align:center; }
.logo { height:50px; }
.center-col h4, .center-col h2, .center-col p { margin:1px 0; line-height:1.2; }
table { width:100%; border-collapse: collapse; font-size:10px; margin-top:5px;}
th, td { border:1px solid #000; padding:4px; text-align:left;}
th { background-color:#f2f2f2; }
.chart-container { text-align:center; margin-top:5px; margin-bottom:5px; }
.page-break { page-break-before: always; }
</style>

<div class="header-row">
    <div class="header-col left-col">';
if ($logoBase64) $html .= '<img src="'.$logoBase64.'" class="logo">';
$html .= '</div>
    <div class="header-col center-col">
        <p>Republic of the Philippines</p>
        <h4>Municipality of Pilar</h4>
        <p>Province of Sorsogon</p>
        <h2>CONSUMPTION REPORT</h2>
        <p><em>As of '.$reportDate.'</em></p>
    </div>
    <div class="header-col"></div>
</div>';

// --- Chart ---
$chartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
    'type' => 'bar',
    'data' => [
        'labels' => $chart_labels,
        'datasets' => [[
            'label' => 'Total Consumed',
            'data' => $chart_totals,
            'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
            'borderColor' => 'rgba(54, 162, 235, 1)',
            'borderWidth' => 1
        ]]
    ],
    'options' => ['plugins'=>['legend'=>['display'=>false]], 'scales'=>['y'=>['beginAtZero'=>true]]],
    'width'=>800,
    'height'=>120
]));
$chartBase64 = base64_encode(file_get_contents($chartUrl));
$html .= '<div class="chart-container"><img src="data:image/png;base64,' . $chartBase64 . '" style="max-width:90%; height:auto;"></div>';

// --- Table (NEW PAGE) ---
$html .= '<div class="page-break">
<table>
<thead>
<tr>
<th>Asset</th>
<th>Office</th>
<th>Quantity</th>
<th>Dispensed By</th>
<th>Recipient</th>
<th>Date</th>
<th>Remarks</th>
</tr>
</thead>
<tbody>';

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>'.htmlspecialchars($row['description'] ?? '-').'</td>
        <td>'.htmlspecialchars($row['office_name']).'</td>
        <td>'.$row['quantity_consumed'].'</td>
        <td>'.htmlspecialchars($row['dispensed_by'] ?? '-').'</td>
        <td>'.htmlspecialchars($row['recipient'] ?? '-').'</td>
        <td>'.date('F j, Y', strtotime($row['consumption_date'])).'</td>
        <td>'.htmlspecialchars($row['remarks']).'</td>
    </tr>';
}

$html .= '</tbody></table></div>';

// --- Generate PDF ---
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// --- Save PDF ---
$pdfOutput = $dompdf->output();
$savePath = '../generated_reports/'.$reportFilename;
file_put_contents($savePath, $pdfOutput);

// --- Log ---
$userId = $_SESSION['user_id'];
$officeId = $_SESSION['office_id'];
$insert = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
$insert->bind_param("iis", $userId, $officeId, $reportFilename);
$insert->execute();

// --- Stream PDF ---
$dompdf->stream($reportFilename, ['Attachment' => false]);
exit;

function generateCategoryInventoryReport($conn, $category_id, $category_name, $selected_assets) {
    // Sanitize asset IDs
    $asset_ids = array_map('intval', $selected_assets);
    $asset_ids_str = implode(',', $asset_ids);
    
    if (empty($asset_ids_str)) {
        exit("Invalid asset selection.");
    }
    
    // Fetch selected assets data
    $sql = "
        SELECT 
            an.id AS an_id,
            an.description,
            an.quantity,
            an.unit,
            an.unit_cost,
            an.date_created,
            (an.quantity * an.unit_cost) AS total_value,
            COALESCE((
                SELECT c.category_name
                FROM assets a
                LEFT JOIN categories c ON a.category = c.id
                WHERE a.asset_new_id = an.id
                ORDER BY a.id ASC
                LIMIT 1
            ), 'Uncategorized') AS category_name,
            f.ics_no AS ics_no
        FROM assets_new an
        LEFT JOIN ics_form f ON f.id = an.ics_id
        WHERE an.id IN ($asset_ids_str)
        ORDER BY an.description ASC
    ";
    
    $result = $conn->query($sql);
    
    if (!$result || $result->num_rows === 0) {
        exit("No data found for selected assets.");
    }
    
    // Get system info
    $system_result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
    $system = $system_result->fetch_assoc();
    $logo_path = !empty($system['logo']) ? '../img/' . $system['logo'] : '';
    $system_title = $system['system_title'] ?? 'Asset Inventory System';
    
    // Calculate totals
    $total_quantity = 0;
    $total_value = 0;
    $assets_data = [];
    
    while ($row = $result->fetch_assoc()) {
        $assets_data[] = $row;
        $total_quantity += $row['quantity'];
        $total_value += $row['total_value'];
    }
    
    // Generate HTML for PDF
    $current_date = date('F j, Y');
    $current_time = date('g:i A');
    $report_filename = 'Category_Inventory_Report_' . str_replace(' ', '_', $category_name) . '_' . date('Y-m-d_H-i-s') . '.pdf';
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { max-height: 80px; margin-bottom: 10px; }
            .title { font-size: 24px; font-weight: bold; margin: 10px 0; }
            .subtitle { font-size: 18px; color: #666; margin: 5px 0; }
            .info-section { margin: 20px 0; }
            .info-row { margin: 5px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f5f5f5; font-weight: bold; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .summary { background-color: #f9f9f9; padding: 15px; margin: 20px 0; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            ' . ($logo_path ? '<img src="' . $logo_path . '" alt="Logo" class="logo">' : '') . '
            <div class="title">' . htmlspecialchars($system_title) . '</div>
            <div class="subtitle">Category Inventory Report</div>
        </div>
        
        <div class="info-section">
            <div class="info-row"><strong>Category:</strong> ' . htmlspecialchars($category_name) . '</div>
            <div class="info-row"><strong>Report Date:</strong> ' . $current_date . ' at ' . $current_time . '</div>
            <div class="info-row"><strong>Total Assets:</strong> ' . count($assets_data) . ' items</div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ICS No.</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th class="text-center">Quantity</th>
                    <th>Unit</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Total Value</th>
                    <th class="text-center">Date Created</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($assets_data as $row) {
        $html .= '<tr>
            <td>' . htmlspecialchars($row['ics_no'] ?? '-') . '</td>
            <td>' . htmlspecialchars($row['description']) . '</td>
            <td>' . htmlspecialchars($row['category_name']) . '</td>
            <td class="text-center">' . number_format($row['quantity']) . '</td>
            <td>' . htmlspecialchars($row['unit']) . '</td>
            <td class="text-right">₱' . number_format($row['unit_cost'], 2) . '</td>
            <td class="text-right">₱' . number_format($row['total_value'], 2) . '</td>
            <td class="text-center">' . date('M j, Y', strtotime($row['date_created'])) . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
        </table>
        
        <div class="summary">
            <div style="display: flex; justify-content: space-between;">
                <div><strong>Summary:</strong></div>
                <div>
                    <div><strong>Total Quantity:</strong> ' . number_format($total_quantity) . ' items</div>
                    <div><strong>Total Value:</strong> ₱' . number_format($total_value, 2) . '</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div>Generated on ' . $current_date . ' at ' . $current_time . '</div>
            <div>Report generated by: ' . htmlspecialchars($_SESSION['fullname'] ?? 'System User') . '</div>
        </div>
    </body>
    </html>';
    
    // Generate PDF
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    // Save PDF (create directory if it doesn't exist)
    $report_dir = '../generated_reports/';
    if (!is_dir($report_dir)) {
        mkdir($report_dir, 0755, true);
    }
    
    $pdf_output = $dompdf->output();
    $save_path = $report_dir . $report_filename;
    file_put_contents($save_path, $pdf_output);
    
    // Log the report generation
    $user_id = $_SESSION['user_id'];
    $office_id = $_SESSION['office_id'] ?? null;
    $insert = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
    $insert->bind_param("iis", $user_id, $office_id, $report_filename);
    $insert->execute();
    
    // Stream PDF to browser
    $dompdf->stream($report_filename, ['Attachment' => false]);
}
?>
