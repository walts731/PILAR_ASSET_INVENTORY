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

// Get category ID and validate
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
if ($category_id <= 0) {
  http_response_code(400);
  echo 'Invalid category ID';
  exit;
}

// Get selected asset IDs if provided
$selected_assets = [];
if (isset($_GET['selected_assets']) && !empty($_GET['selected_assets'])) {
  $selected_assets = array_map('intval', explode(',', $_GET['selected_assets']));
}

// Fetch category details
$category = null;
$stmt = $conn->prepare("SELECT id, category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $category = $result->fetch_assoc();
}
$stmt->close();

if (!$category) {
  http_response_code(404);
  echo 'Category not found';
  exit;
}

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

// Build SQL query
$sql = "
  SELECT 
    an.id AS an_id,
    an.description,
    an.quantity,
    an.unit,
    an.unit_cost,
    an.date_created,
    COALESCE((
      SELECT c.category_name
      FROM assets a
      LEFT JOIN categories c ON a.category = c.id
      WHERE a.asset_new_id = an.id
      ORDER BY a.id ASC
      LIMIT 1
    ), 'Uncategorized') AS category_name,
    f.ics_no AS ics_no,
    COALESCE(o.office_name, 'Outside LGU') AS office_name
  FROM assets_new an
  LEFT JOIN ics_form f ON f.id = an.ics_id
  LEFT JOIN offices o ON o.id = an.office_id
  WHERE EXISTS (
    SELECT 1 FROM assets ax WHERE ax.asset_new_id = an.id AND ax.category = ?
  )";

$params = [$category_id];
$types = 'i';

// Add selected assets filter if provided
if (!empty($selected_assets)) {
  $placeholders = str_repeat('?,', count($selected_assets) - 1) . '?';
  $sql .= " AND an.id IN ($placeholders)";
  $params = array_merge($params, $selected_assets);
  $types .= str_repeat('i', count($selected_assets));
}

$sql .= " ORDER BY an.date_created DESC";

// Execute query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$records = [];
$total_quantity = 0;
$total_value = 0;
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $unit_cost = (float)($r['unit_cost'] ?? 0);
    $quantity = (int)($r['quantity'] ?? 0);
    $item_total = $unit_cost * $quantity;
    
    $r['item_total'] = $item_total;
    $records[] = $r;
    $total_quantity += $quantity;
    $total_value += $item_total;
  }
}

$stmt->close();

// Build filename
$category_name_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category['category_name']);
$filename = 'category_' . $category_name_safe . '_export_' . date('Ymd_His') . '.pdf';

// Create filter description
$filter_description = 'Category: ' . htmlspecialchars($category['category_name']);
if (!empty($selected_assets)) {
  $filter_description .= ' (Selected Items: ' . count($selected_assets) . ')';
} else {
  $filter_description .= ' (All Items)';
}

// Generate HTML for PDF
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
        .info-section { margin: 20px 0; }
        .info-row { margin: 5px 0; }
        .label { font-weight: bold; color: #333; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f8f9fa; font-weight: bold; color: #333; }
        .table tr:nth-child(even) { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .summary-title { font-size: 14px; font-weight: bold; color: #0d6efd; margin-bottom: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin: 5px 0; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>';

// Header section
$html .= '<div class="header-row">';
$html .= '<div class="header-col left-col">';
if ($logoBase64) {
    $html .= '<img src="' . $logoBase64 . '" class="logo">';
}
$html .= '</div>';
$html .= '<div class="header-col center-col">';
$html .= '<p>Republic of the Philippines</p>';
$html .= '<h4>Municipality of Pilar</h4>';
$html .= '<p>Province of Sorsogon</p>';
$html .= '<h2>CATEGORY INVENTORY REPORT</h2>';
$html .= '<p><em>' . htmlspecialchars($category['category_name']) . ' - As of ' . date('F j, Y') . '</em></p>';
$html .= '</div>';
$html .= '<div class="header-col"></div>';
$html .= '</div>';

// Report information
$html .= '<div class="info-section">';
$html .= '<div class="info-row"><span class="label">Report Type:</span> Category Inventory Export</div>';
$html .= '<div class="info-row"><span class="label">Filter:</span> ' . $filter_description . '</div>';
$html .= '<div class="info-row"><span class="label">Generated:</span> ' . date('F j, Y g:i A') . '</div>';
$html .= '<div class="info-row"><span class="label">Total Records:</span> ' . count($records) . '</div>';
$html .= '</div>';

// Summary section
$html .= '<div class="summary">';
$html .= '<div class="summary-title">Summary Statistics</div>';
$html .= '<div class="summary-row"><span>Total Records:</span><span>' . count($records) . '</span></div>';
$html .= '<div class="summary-row"><span>Total Quantity:</span><span>' . number_format($total_quantity) . '</span></div>';
$html .= '<div class="summary-row"><span>Total Value:</span><span>₱' . number_format($total_value, 2) . '</span></div>';
$html .= '<div class="summary-row"><span>Category:</span><span>' . htmlspecialchars($category['category_name']) . '</span></div>';
$html .= '</div>';

// Data table
if (!empty($records)) {
    $html .= '<table class="table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>ICS No</th>';
    $html .= '<th>Description</th>';
    $html .= '<th>Office</th>';
    $html .= '<th class="text-center">Qty</th>';
    $html .= '<th>Unit</th>';
    $html .= '<th class="text-right">Unit Cost</th>';
    $html .= '<th class="text-right">Total Value</th>';
    $html .= '<th class="text-center">Date Created</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    foreach ($records as $record) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($record['ics_no'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($record['description'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($record['office_name'] ?? '') . '</td>';
        $html .= '<td class="text-center">' . number_format($record['quantity']) . '</td>';
        $html .= '<td>' . htmlspecialchars($record['unit'] ?? '') . '</td>';
        $html .= '<td class="text-right">₱' . number_format($record['unit_cost'], 2) . '</td>';
        $html .= '<td class="text-right">₱' . number_format($record['item_total'], 2) . '</td>';
        $html .= '<td class="text-center">' . date('M j, Y', strtotime($record['date_created'])) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
} else {
    $html .= '<div class="text-center" style="margin: 40px 0; color: #666;">No records found for the selected criteria.</div>';
}

// Footer
$html .= '<div class="footer">';
$html .= 'Generated on ' . date('F j, Y \a\t g:i A') . ' | ' . htmlspecialchars($system_info['system_title']);
$html .= '</div>';

$html .= '</body></html>';

// Create PDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Save PDF to generated_reports directory
$reports_dir = '../generated_reports';
if (!is_dir($reports_dir)) {
    mkdir($reports_dir, 0755, true);
}

$pdf_path = $reports_dir . '/' . $filename;
file_put_contents($pdf_path, $dompdf->output());

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
  error_log("Failed to insert category PDF export into generated_reports: " . $e->getMessage());
}

// Output PDF to browser
$dompdf->stream($filename, array('Attachment' => false));
exit;
?>
