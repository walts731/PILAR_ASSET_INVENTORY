<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

// Auth check
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo 'Unauthorized';
  exit;
}

// Get system information and logo
$logoPath = '../img/new_logo.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

$system_info = [
  'system_title' => 'PILAR Asset Inventory System',
  'logo' => $logoBase64
];

$system_query = $conn->query("SELECT system_title, logo FROM system LIMIT 1");
if ($system_query && $system_row = $system_query->fetch_assoc()) {
  $system_info['system_title'] = $system_row['system_title'] ?? $system_info['system_title'];
}

// Build SQL query
$sql = "SELECT
    inventory_id,
    classification_type,
    item_description,
    nature_occupancy,
    location,
    date_constructed_acquired_manufactured,
    property_no_or_reference,
    acquisition_cost,
    market_appraisal_insurable_interest,
    date_of_appraisal,
    remarks
FROM infrastructure_inventory
ORDER BY inventory_id DESC";

$records = [];
$total_records = 0;
$total_acquisition_cost = 0;
$total_market_appraisal = 0;

$res = $conn->query($sql);
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $records[] = $r;
    $total_records++;
    $total_acquisition_cost += (float)($r['acquisition_cost'] ?? 0);
    $total_market_appraisal += (float)($r['market_appraisal_insurable_interest'] ?? 0);
  }
}

// Create HTML content for PDF
$reportDate = date('F j, Y');

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:9px; margin:0; padding:10px;}
        .header-row { display: table; width:100%; margin-bottom:10px; }
        .header-col { display: table-cell; vertical-align: top; }
        .left-col { width: 15%; text-align:left; }
        .center-col { width: 70%; text-align:center; }
        .logo { height:50px; }
        .center-col h4, .center-col h2, .center-col p { margin:1px 0; line-height:1.2; }
        .summary {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-weight: bold;
        }
        table { width:100%; border-collapse: collapse; font-size:8px; margin-top:5px;}
        th, td { border:1px solid #000; padding:3px; text-align:left;}
        th { background-color:#f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .no-records {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header-row">
        <div class="header-col left-col">';
if ($system_info['logo']) $html .= '<img src="'.$system_info['logo'].'" class="logo">';
$html .= '</div>
        <div class="header-col center-col">
            <p>Republic of the Philippines</p>
            <h4>Municipality of Pilar</h4>
            <p>Province of Sorsogon</p>
            <h2>INFRASTRUCTURE INVENTORY REPORT</h2>
            <p><em>As of '.$reportDate.'</em></p>
        </div>
        <div class="header-col"></div>
    </div>

    <div class="summary">
        <div class="summary-item">Total Records: ' . $total_records . '</div>
        <div class="summary-item">Total Acquisition Cost: ₱' . number_format($total_acquisition_cost, 2) . '</div>
        <div class="summary-item">Total Market Appraisal: ₱' . number_format($total_market_appraisal, 2) . '</div>
    </div>';

if (empty($records)) {
    $html .= '<div class="no-records">No infrastructure records found.</div>';
} else {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>Classification/Type</th>
                <th>Item Description</th>
                <th>Nature Occupancy</th>
                <th>Location</th>
                <th class="text-center">Date Constructed</th>
                <th>Property No.</th>
                <th class="text-right">Acquisition Cost</th>
                <th class="text-right">Market Appraisal</th>
                <th class="text-center">Date of Appraisal</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($records as $record) {
        // Format dates
        $date_constructed = '';
        if ($record['date_constructed_acquired_manufactured']) {
            $date_constructed = date("M-Y", strtotime($record['date_constructed_acquired_manufactured']));
        }

        $date_appraisal = '';
        if ($record['date_of_appraisal']) {
            $date_appraisal = date("M d, Y", strtotime($record['date_of_appraisal']));
        }

        $html .= '
            <tr>
                <td>' . htmlspecialchars($record['classification_type'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['item_description'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['nature_occupancy'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['location'] ?? '') . '</td>
                <td class="text-center">' . htmlspecialchars($date_constructed) . '</td>
                <td>' . htmlspecialchars($record['property_no_or_reference'] ?? '') . '</td>
                <td class="text-right">' . ($record['acquisition_cost'] ? '₱' . number_format((float)$record['acquisition_cost'], 2) : '') . '</td>
                <td class="text-right">' . ($record['market_appraisal_insurable_interest'] ? '₱' . number_format((float)$record['market_appraisal_insurable_interest'], 2) : '') . '</td>
                <td class="text-center">' . htmlspecialchars($date_appraisal) . '</td>
                <td>' . htmlspecialchars($record['remarks'] ?? '') . '</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>';
}

$html .= '
    <div class="footer">
        <p>This report was generated automatically by the ' . htmlspecialchars($system_info['system_title']) . '</p>
        <p>Report contains ' . $total_records . ' infrastructure record(s) with total acquisition cost of ₱' . number_format($total_acquisition_cost, 2) . '</p>
    </div>
</body>
</html>';

// Configure DomPDF
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render PDF
$dompdf->render();

// Generate filename
$filename = 'infrastructure_inventory_report_' . date('Ymd_His') . '.pdf';

// Save PDF to generated_reports directory
$pdfOutput = $dompdf->output();
$savePath = '../generated_reports/' . $filename;

// Ensure the directory exists
if (!is_dir('../generated_reports/')) {
  mkdir('../generated_reports/', 0755, true);
}

file_put_contents($savePath, $pdfOutput);

// Insert record into generated_reports table for tracking
$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'] ?? null;

try {
  $insert_stmt = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
  $insert_stmt->bind_param("iis", $user_id, $office_id, $filename);
  $insert_stmt->execute();
  $insert_stmt->close();
} catch (Exception $e) {
  // Log error but don't interrupt the export
  error_log("Failed to insert infrastructure PDF export into generated_reports: " . $e->getMessage());
}

// Output PDF to browser
$dompdf->stream($filename, array('Attachment' => false)); // false = display in browser, true = force download
?>
