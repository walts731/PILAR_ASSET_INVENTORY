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

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$office_filter = $_GET['office'] ?? 'all';
$red_tag_filter = $_GET['red_tag'] ?? 'all';

// Get system information and logo
$logoPath = '../img/PILAR LOGO TRANSPARENT.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

$system_info = [
  'system_title' => 'PILAR Asset Inventory System',
  'logo' => $logoBase64
];

$system_query = $conn->query("SELECT system_title, logo FROM system LIMIT 1");
if ($system_query && $system_row = $system_query->fetch_assoc()) {
  $system_info['system_title'] = $system_row['system_title'] ?? $system_info['system_title'];
}

// Get office name for filtering
$office_name = 'All Offices';
if ($office_filter !== 'all') {
  $office_stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
  $office_stmt->bind_param('i', $office_filter);
  $office_stmt->execute();
  $office_result = $office_stmt->get_result();
  if ($office_row = $office_result->fetch_assoc()) {
    $office_name = $office_row['office_name'];
  }
  $office_stmt->close();
}

// Build SQL query with filtering
$sql = "SELECT 
          a.inventory_tag,
          a.property_no,
          a.description,
          COALESCE(c.category_name, 'Uncategorized') AS category_name,
          o.office_name,
          e.name as employee_name,
          ii.iirup_id,
          a.quantity,
          a.unit,
          a.value as unit_price,
          (a.quantity * a.value) as total_value,
          a.red_tagged,
          a.status,
          a.acquisition_date as date_created,
          a.last_updated
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON a.office_id = o.id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
        LEFT JOIN iirup_items ii ON a.id = ii.asset_id
        WHERE a.status = 'unserviceable' AND a.quantity > 0";

$params = [];
$types = '';

// Add office filtering if specified
if ($office_filter !== 'all') {
  $sql .= " AND a.office_id = ?";
  $params[] = $office_filter;
  $types .= 'i';
}

// Add red tag filtering if specified
if ($red_tag_filter === 'tagged') {
  $sql .= " AND a.red_tagged = 1";
} elseif ($red_tag_filter === 'not_tagged') {
  $sql .= " AND a.red_tagged = 0";
}

// Add date filtering if specified
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $sql .= " AND DATE(a.acquisition_date) >= ? AND DATE(a.acquisition_date) <= ?";
  $params[] = $from_date;
  $params[] = $to_date;
  $types .= 'ss';
}

$sql .= " ORDER BY a.last_updated DESC, a.id DESC";

// Execute query with or without parameters
if (!empty($params)) {
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  $res = $conn->query($sql);
}

$records = [];
$total_quantity = 0;
$total_value = 0;
$red_tagged_count = 0;
$not_red_tagged_count = 0;

if ($res) {
  while ($r = $res->fetch_assoc()) {
    $records[] = $r;
    $total_quantity += (float)($r['quantity'] ?? 0);
    $total_value += (float)($r['total_value'] ?? 0);
    
    if ($r['red_tagged'] == 1) {
      $red_tagged_count++;
    } else {
      $not_red_tagged_count++;
    }
  }
}

// Close prepared statement if used
if (isset($stmt)) {
  $stmt->close();
}

// Generate filter description
$filter_description = 'All Records';
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $filter_descriptions = [
    'current_month' => 'Current Month',
    'current_quarter' => 'Current Quarter',
    'current_year' => 'Current Year',
    'last_month' => 'Last Month',
    'last_quarter' => 'Last Quarter',
    'last_year' => 'Last Year',
    'custom' => 'Custom Range'
  ];
  
  $filter_description = $filter_descriptions[$filter_type] ?? 'Custom Range';
  $filter_description .= ' (' . date('M d, Y', strtotime($from_date)) . ' - ' . date('M d, Y', strtotime($to_date)) . ')';
}

// Red tag filter description
$red_tag_description = 'All Items';
if ($red_tag_filter === 'tagged') {
  $red_tag_description = 'Red Tagged Items Only';
} elseif ($red_tag_filter === 'not_tagged') {
  $red_tag_description = 'Not Red Tagged Items Only';
}

// Create HTML content for PDF
$reportDate = date('F j, Y');

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:8px; margin:0; padding:10px;}
        .header-row { display: table; width:100%; margin-bottom:10px; }
        .header-col { display: table-cell; vertical-align: top; }
        .left-col { width: 15%; text-align:left; }
        .center-col { width: 70%; text-align:center; }
        .logo { height:50px; }
        .center-col h4, .center-col h2, .center-col p { margin:1px 0; line-height:1.2; }
        .filter-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
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
        .red-tag-alert {
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        table { width:100%; border-collapse: collapse; font-size:7px; margin-top:5px;}
        th, td { border:1px solid #000; padding:2px; text-align:left;}
        th { background-color:#f2f2f2; font-size:6px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .red-tagged { color: #dc3545; font-weight: bold; }
        .not-red-tagged { color: #ffc107; font-weight: bold; }
        .no-records {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
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
            <h2>UNSERVICEABLE ASSETS REPORT</h2>
            <p><em>As of '.$reportDate.'</em></p>
        </div>
        <div class="header-col"></div>
    </div>

    <div class="filter-info">
        <strong>Date Filter:</strong> ' . htmlspecialchars($filter_description) . '<br>
        <strong>Office:</strong> ' . htmlspecialchars($office_name) . '<br>
        <strong>Red Tag Filter:</strong> ' . htmlspecialchars($red_tag_description) . '
    </div>

    <div class="summary">
        <div class="summary-item">Total Records: ' . count($records) . '</div>
        <div class="summary-item">Total Quantity: ' . number_format($total_quantity, 0) . '</div>
        <div class="summary-item">Total Value: ₱' . number_format($total_value, 2) . '</div>
        <div class="summary-item">Red Tagged: ' . $red_tagged_count . '</div>
        <div class="summary-item">Not Red Tagged: ' . $not_red_tagged_count . '</div>
    </div>';

if ($not_red_tagged_count > 0) {
    $html .= '
    <div class="red-tag-alert">
        <strong>⚠️ Action Required:</strong> ' . $not_red_tagged_count . ' unserviceable asset(s) have not been red tagged yet. Consider creating IIRUP forms and red tags for proper documentation.
    </div>';
}

if (empty($records)) {
    $html .= '<div class="no-records">No unserviceable asset records found for the selected criteria.</div>';
} else {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>Inv. Tag</th>
                <th>Property No</th>
                <th>Description</th>
                <th>Category</th>
                <th>Office</th>
                <th>Employee</th>
                <th>IIRUP ID</th>
                <th class="text-right">Qty</th>
                <th>Unit</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total Value</th>
                <th class="text-center">Red Tagged</th>
                <th class="text-center">Date Created</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($records as $record) {
        $red_tag_status = ($record['red_tagged'] == 1) ? 'Yes' : 'No';
        $red_tag_class = ($record['red_tagged'] == 1) ? 'red-tagged' : 'not-red-tagged';
        
        $html .= '
            <tr>
                <td>' . htmlspecialchars($record['inventory_tag'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['property_no'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['description'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['category_name'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['office_name'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['employee_name'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['iirup_id'] ?? '') . '</td>
                <td class="text-right">' . number_format((float)($record['quantity'] ?? 0), 0) . '</td>
                <td>' . htmlspecialchars($record['unit'] ?? '') . '</td>
                <td class="text-right">₱' . number_format((float)($record['unit_price'] ?? 0), 2) . '</td>
                <td class="text-right">₱' . number_format((float)($record['total_value'] ?? 0), 2) . '</td>
                <td class="text-center ' . $red_tag_class . '">' . $red_tag_status . '</td>
                <td class="text-center">' . date('M j, Y', strtotime($record['date_created'])) . '</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>';
}

$html .= '
    <div class="footer">
        <p>This report was generated automatically by the ' . htmlspecialchars($system_info['system_title']) . '</p>
        <p>Report contains ' . count($records) . ' unserviceable asset record(s) with a total quantity of ' . number_format($total_quantity, 0) . ' items and total value of ₱' . number_format($total_value, 2) . '</p>
        <p>Red Tagged: ' . $red_tagged_count . ' | Not Red Tagged: ' . $not_red_tagged_count . ' (require IIRUP forms)</p>
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
$filter_suffix = '';
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $filter_suffix = '_' . str_replace(['-', ' '], ['', '_'], $filter_type) . '_' . $from_date . '_to_' . $to_date;
}
$filename = 'unserviceable_report' . $filter_suffix . '_' . date('Ymd_His') . '.pdf';

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
  error_log("Failed to insert unserviceable PDF export into generated_reports: " . $e->getMessage());
}

// Output PDF to browser
$dompdf->stream($filename, array('Attachment' => false)); // false = display in browser, true = force download
?>
