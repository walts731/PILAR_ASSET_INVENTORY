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

// Inputs
$allowed_group = ['fo_request', 'fo_plate_no', 'fo_fuel_type', 'fo_receiver', 'fo_vehicle_type'];
$group_by = (isset($_GET['group_by']) && in_array($_GET['group_by'], $allowed_group, true)) ? $_GET['group_by'] : 'fo_request';
$from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : null; // YYYY-MM-DD
$to   = isset($_GET['to']) && $_GET['to'] !== '' ? $_GET['to']   : null;     // YYYY-MM-DD

// System info and logo
$logoPath = '../img/new_logo.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
$system_info = [
  'system_title' => 'PILAR Asset Inventory System',
  'logo' => $logoBase64
];
$system_query = $conn->query("SELECT system_title FROM system LIMIT 1");
if ($system_query && $system_row = $system_query->fetch_assoc()) {
  $system_info['system_title'] = $system_row['system_title'] ?? $system_info['system_title'];
}

// Build SQL
$where = [];
$params = [];
$types = '';
if ($from) { $where[] = 'fo_date >= ?'; $params[] = $from; $types .= 's'; }
if ($to)   { $where[] = 'fo_date <= ?'; $params[] = $to;   $types .= 's'; }
$where_sql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT $group_by AS group_key,
               COALESCE(SUM(fo_liters),0) AS total_liters,
               COUNT(*) AS trips,
               COUNT(DISTINCT NULLIF(TRIM(fo_plate_no),'')) AS unique_plates,
               GROUP_CONCAT(DISTINCT NULLIF(TRIM(fo_fuel_type),'') ORDER BY fo_fuel_type SEPARATOR ', ') AS fuel_types
        FROM fuel_out
        $where_sql
        GROUP BY $group_by
        ORDER BY total_liters DESC, trips DESC";

$records = [];
$total_groups = 0;
$grand_total_liters = 0.0;
$grand_total_trips = 0;
$unique_plates_overall = 0; // not a true global unique, but keep for summary display

try {
  if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      $records[] = [
        'group_key' => $row['group_key'] ?? '',
        'total_liters' => (float)($row['total_liters'] ?? 0),
        'trips' => (int)($row['trips'] ?? 0),
        'unique_plates' => (int)($row['unique_plates'] ?? 0),
        'fuel_types' => $row['fuel_types'] ?? ''
      ];
      $grand_total_liters += (float)($row['total_liters'] ?? 0);
      $grand_total_trips  += (int)($row['trips'] ?? 0);
      $unique_plates_overall += (int)($row['unique_plates'] ?? 0);
    }
    $stmt->close();
  } else {
    throw new Exception('Query prepare failed');
  }
  $total_groups = count($records);
} catch (Exception $e) {
  http_response_code(500);
  echo 'Server error: ' . htmlspecialchars($e->getMessage());
  exit;
}

// Labels
$group_labels = [
  'fo_request' => 'Request',
  'fo_plate_no' => 'Plate No',
  'fo_fuel_type' => 'Fuel Type',
  'fo_receiver' => 'Receiver',
  'fo_vehicle_type' => 'Vehicle Type',
];
$group_label = $group_labels[$group_by] ?? 'Group';

$filter_desc = 'All Records';
if ($from || $to) {
  $from_disp = $from ? date('M d, Y', strtotime($from)) : 'Beginning';
  $to_disp = $to ? date('M d, Y', strtotime($to)) : 'Today';
  $filter_desc = $from_disp . ' - ' . $to_disp;
}

$reportDate = date('F j, Y');

// Build HTML
$html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size:10px; margin:0; padding:10px; }
  .header-row { display: table; width:100%; margin-bottom:10px; }
  .header-col { display: table-cell; vertical-align: top; }
  .left-col { width: 15%; text-align:left; }
  .center-col { width: 70%; text-align:center; }
  .logo { height:50px; }
  .center-col h4, .center-col h2, .center-col p { margin:1px 0; line-height:1.2; }
  .filter-info { background-color:#f8f9fa; padding:10px; border-radius:5px; margin: 12px 0; border-left:4px solid #007bff; }
  .summary { background-color:#e8f5e8; padding:12px; border-radius:5px; margin: 12px 0; border-left:4px solid #28a745; }
  .summary-item { display:inline-block; margin-right:24px; font-weight:bold; }
  table { width:100%; border-collapse: collapse; font-size:9px; margin-top:6px; }
  th, td { border:1px solid #000; padding:3px; text-align:left; }
  th { background-color:#f2f2f2; font-size:8px; }
  .text-end { text-align:right; }
  .no-records { text-align:center; padding:40px; color:#666; font-style:italic; }
  .footer { margin-top: 20px; text-align:center; font-size:10px; color:#666; border-top:1px solid #ddd; padding-top:8px; }
</style>
</head>
<body>';

$html .= '  <div class="header-row">   <div class="header-col left-col">';
if (!empty($system_info['logo'])) {
  $html .= '<img src="' . $system_info['logo'] . '" class="logo">';
}
$html .= '</div>    <div class="header-col center-col">      <p>Republic of the Philippines</p>      <h4>Municipality of Pilar</h4>      <p>Province of Sorsogon</p>      <h2>FUEL CONSUMPTION REPORT</h2>      <p><em>As of ' . htmlspecialchars($reportDate) . '</em></p>    </div>    <div class="header-col"></div>  </div>  <div class="filter-info">    <strong>Period:</strong> ' . htmlspecialchars($filter_desc) . ' &nbsp; | &nbsp; <strong>Group By:</strong> ' . htmlspecialchars($group_label) . '  </div>  <div class="summary">    <div class="summary-item">Groups: ' . (int)$total_groups . '</div>    <div class="summary-item">Total Liters: ' . number_format($grand_total_liters, 2) . ' L</div>    <div class="summary-item">Trips: ' . (int)$grand_total_trips . '</div>  </div>';

if (empty($records)) {
  $html .= '<div class="no-records">No fuel consumption data found for the selected period.</div>';
} else {
  $html .= "
  <table>
    <thead>
      <tr>
        <th>".htmlspecialchars($group_label)."</th>
        <th class=\"text-end\">Total Liters</th>
        <th class=\"text-end\">Trips</th>
        <th class=\"text-end\">Unique Plates</th>
        <th>Fuel Types</th>
      </tr>
    </thead>
    <tbody>";
  foreach ($records as $r) {
    $html .= '<tr>'
      . '<td>' . htmlspecialchars($r['group_key'] ?: '(blank)') . '</td>'
      . '<td class="text-end">' . number_format((float)$r['total_liters'], 2) . '</td>'
      . '<td class="text-end">' . (int)$r['trips'] . '</td>'
      . '<td class="text-end">' . (int)$r['unique_plates'] . '</td>'
      . '<td>' . htmlspecialchars($r['fuel_types']) . '</td>'
      . '</tr>';
  }
  $html .= "</tbody></table>";
}

$html .= "
  <div class=\"footer\">
    <p>This report was generated automatically by the ".htmlspecialchars($system_info['system_title'])."</p>
    <p>Report contains ".$total_groups." group(s) totaling ".number_format($grand_total_liters, 2)." liters across ".$grand_total_trips." trip(s).</p>
  </div>
</body>
</html>";

// Configure Dompdf
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filter_suffix = '';
if ($from || $to || $group_by) {
  $parts = [];
  if ($from) $parts[] = 'from_'.$from;
  if ($to) $parts[] = 'to_'.$to;
  if ($group_by) $parts[] = 'group_'.$group_by;
  $filter_suffix = '_' . implode('_', $parts);
}
$filename = 'fuel_consumption_report' . $filter_suffix . '_' . date('Ymd_His') . '.pdf';

// Ensure directory and save PDF
if (!is_dir('../generated_reports/')) {
  mkdir('../generated_reports/', 0755, true);
}
file_put_contents('../generated_reports/' . $filename, $dompdf->output());

// Track in generated_reports
$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'] ?? null;
try {
  $insert_stmt = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
  $insert_stmt->bind_param('iis', $user_id, $office_id, $filename);
  $insert_stmt->execute();
  $insert_stmt->close();
} catch (Exception $e) {
  error_log('Failed to insert fuel consumption PDF export into generated_reports: ' . $e->getMessage());
}

// Stream to browser
$dompdf->stream($filename, ['Attachment' => false]);
