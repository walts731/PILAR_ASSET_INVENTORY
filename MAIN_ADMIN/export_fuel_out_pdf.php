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

// Permission guard: admin/office_admin or explicit fuel_inventory permission
function user_has_fuel_permission(mysqli $conn, int $user_id): bool {
  $role = null;
  if ($stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) { $role = $row['role'] ?? null; }
    $stmt->close();
  }
  if ($role === 'admin' || $role === 'office_admin' || $role === 'user') return true;
  if ($stmt2 = $conn->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = 'fuel_inventory' LIMIT 1")) {
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $stmt2->store_result();
    $ok = $stmt2->num_rows > 0;
    $stmt2->close();
    return $ok;
  }
  return false;
}

if (!user_has_fuel_permission($conn, (int)$_SESSION['user_id'])) {
  http_response_code(403);
  echo 'Forbidden';
  exit;
}

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

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

// Build SQL query with date filtering
$sql = "SELECT fo_date, fo_time_in, fo_fuel_type, fo_fuel_no, fo_plate_no, fo_request, fo_liters, fo_vehicle_type, fo_receiver, fo_time_out FROM fuel_out";
$params = [];
$types = '';

// Add date filtering if specified
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $sql .= " WHERE fo_date >= ? AND fo_date <= ?";
  $params[] = $from_date;
  $params[] = $to_date;
  $types = 'ss';
}

$sql .= " ORDER BY fo_date DESC, id DESC";

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
$total_liters = 0;
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $records[] = $r;
    $total_liters += (float)($r['fo_liters'] ?? 0);
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

// Create HTML content for PDF
$reportDate = date('F j, Y');

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:10px; margin:0; padding:10px;}
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
        table { width:100%; border-collapse: collapse; font-size:10px; margin-top:5px;}
        th, td { border:1px solid #000; padding:4px; text-align:left;}
        th { background-color:#f2f2f2; }
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
            <h2>FUEL OUT RECORDS REPORT</h2>
            <p><em>As of '.$reportDate.'</em></p>
        </div>
        <div class="header-col"></div>
    </div>

    <div class="filter-info">
        <strong>Filter Applied:</strong> ' . htmlspecialchars($filter_description) . '
    </div>

    <div class="summary">
        <div class="summary-item">Total Records: ' . count($records) . '</div>
        <div class="summary-item">Total Liters: ' . number_format($total_liters, 2) . ' L</div>
    </div>';

if (empty($records)) {
    $html .= '<div class="no-records">No fuel out records found for the selected period.</div>';
} else {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Fuel Type</th>
                <th>Fuel No</th>
                <th>Plate No</th>
                <th>Request</th>
                <th class="text-right">Liters</th>
                <th>Vehicle Type</th>
                <th>Receiver</th>
                <th>Time Out</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($records as $record) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($record['fo_date'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['fo_time_in'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['fo_fuel_type'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['fo_fuel_no'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['fo_plate_no'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['fo_request'] ?? '') . '</td>
                <td class="text-right">' . number_format((float)($record['fo_liters'] ?? 0), 2) . '</td>
                <td>' . htmlspecialchars($record['fo_vehicle_type'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['fo_receiver'] ?? '') . '</td>
                <td>' . htmlspecialchars($record['fo_time_out'] ?? '') . '</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>';
}

$html .= '
    <div class="footer">
        <p>This report was generated automatically by the ' . htmlspecialchars($system_info['system_title']) . '</p>
        <p>Report contains ' . count($records) . ' fuel out record(s) with a total of ' . number_format($total_liters, 2) . ' liters</p>
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
$filename = 'fuel_out_report' . $filter_suffix . '_' . date('Ymd_His') . '.pdf';

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
  error_log("Failed to insert fuel out PDF export into generated_reports: " . $e->getMessage());
}

// Output PDF to browser
$dompdf->stream($filename, array('Attachment' => false)); // false = display in browser, true = force download
?>
