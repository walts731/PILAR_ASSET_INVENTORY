<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Inputs
$reportType = $_POST['report_type'] ?? 'monthly'; // 'monthly' or 'yearly'
$month = isset($_POST['month']) ? (int)$_POST['month'] : null; // 1-12 when monthly
$year = isset($_POST['year']) ? (int)$_POST['year'] : (int)date('Y');
$office = $_POST['office'] ?? 'all';

// Validate inputs
if ($reportType !== 'monthly' && $reportType !== 'yearly') {
  $reportType = 'monthly';
}
if ($reportType === 'monthly') {
  if (!$month || $month < 1 || $month > 12) {
    $month = (int)date('n');
  }
}
if ($year < 2000 || $year > 2100) {
  $year = (int)date('Y');
}

// Build WHERE conditions
$where = ["a.status = 'unserviceable'", 'a.quantity > 0'];
$params = [];
$types = '';

// Date filter using last_updated (consistent with Unserviceable tab ordering)
if ($reportType === 'monthly') {
  $where[] = 'YEAR(a.last_updated) = ? AND MONTH(a.last_updated) = ?';
  $types .= 'ii';
  $params[] = $year;
  $params[] = $month;
} else { // yearly
  $where[] = 'YEAR(a.last_updated) = ?';
  $types .= 'i';
  $params[] = $year;
}

// Office filter (if provided and not "all")
if (isset($office) && $office !== 'all' && ctype_digit((string)$office)) {
  $where[] = 'a.office_id = ?';
  $types .= 'i';
  $params[] = (int)$office;
}

$whereSql = implode(' AND ', $where);

$sql = "
  SELECT a.*, c.category_name
  FROM assets a
  LEFT JOIN categories c ON a.category = c.id
  WHERE $whereSql
  ORDER BY a.last_updated DESC
";

$stmt = $conn->prepare($sql);
if ($types !== '') {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
  $qty = (int)($r['quantity'] ?? 0);
  $val = (float)($r['value'] ?? 0);
  $rows[] = [
    'description' => $r['description'] ?? '',
    'category_name' => $r['category_name'] ?? 'Uncategorized',
    'quantity' => $qty,
    'unit' => $r['unit'] ?? '',
    'status' => $r['status'] ?? '',
    'unit_cost' => $val,
    'total_value' => $val * $qty,
    'acquired' => $r['acquisition_date'] ?? '',
    'updated' => $r['last_updated'] ?? ''
  ];
}
$stmt->close();

// Report header date text
if ($reportType === 'monthly') {
  $reportDate = date('F', mktime(0,0,0,$month,1,$year)) . ' ' . $year;
  $filenameSuffix = sprintf('%04d-%02d', $year, $month);
} else {
  $reportDate = (string)$year;
  $filenameSuffix = (string)$year;
}

$reportFilename = 'Unserviceable_Inventory_Report_' . $filenameSuffix . '_' . date('Ymd_His') . '.pdf';
$logoPath = '../img/PILAR LOGO TRANSPARENT.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

$html = '
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
  .header-row { display: table; width: 100%; margin-bottom: 20px; }
  .header-col { display: table-cell; vertical-align: top; }
  .left-col { width: 20%; text-align: left; }
  .center-col { width: 60%; text-align: center; }
  .right-col { width: 20%; }
  .logo { height: 80px; }
  .center-col h4, .center-col h2, .center-col p { margin: 2px 0; line-height: 1.3; }
  table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
  th, td { border: 1px solid #000; padding: 6px; text-align: left; }
  th { background-color: #f2f2f2; }
</style>
<div class="header-row">
  <div class="header-col left-col">';

if ($logoBase64) {
  $html .= '<img src="' . $logoBase64 . '" class="logo" alt="Logo">';
}

$html .= '</div>
  <div class="header-col center-col">
    <p>Republic of the Philippines</p>
    <h4>Municipality of Pilar</h4>
    <p>Province of Sorsogon</p>
    <h2>INVENTORY REPORT (UNSERVICEABLE)</h2>
    <p><em>As of ' . htmlspecialchars($reportDate) . '</em></p>
  </div>
  <div class="header-col right-col"></div>
</div>
<table>
  <thead>
    <tr>
      <th>Description</th>
      <th>Category</th>
      <th>Qty</th>
      <th>Unit</th>
      <th>Status</th>
      <th>Unit Cost</th>
      <th>Total Value</th>
      <th>Acquired</th>
      <th>Updated</th>
    </tr>
  </thead>
  <tbody>';

foreach ($rows as $row) {
  $acq = !empty($row['acquired']) ? date('M d, Y', strtotime($row['acquired'])) : '';
  $upd = !empty($row['updated']) ? date('M d, Y', strtotime($row['updated'])) : '';
  $html .= '<tr>'
    . '<td>' . htmlspecialchars($row['description']) . '</td>'
    . '<td>' . htmlspecialchars($row['category_name']) . '</td>'
    . '<td>' . (int)$row['quantity'] . '</td>'
    . '<td>' . htmlspecialchars($row['unit']) . '</td>'
    . '<td>' . htmlspecialchars(ucfirst($row['status'])) . '</td>'
    . '<td>&#8369; ' . number_format((float)$row['unit_cost'], 2) . '</td>'
    . '<td>&#8369; ' . number_format((float)$row['total_value'], 2) . '</td>'
    . '<td>' . $acq . '</td>'
    . '<td>' . $upd . '</td>'
    . '</tr>';
}

$html .= '</tbody></table>';

// Generate PDF
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Save the PDF
$pdfOutput = $dompdf->output();
$savePath = '../generated_reports/' . $reportFilename;
file_put_contents($savePath, $pdfOutput);

// Log to generated_reports table with office_id
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
  $officeQuery = $conn->prepare('SELECT office_id FROM users WHERE id = ?');
  $officeQuery->bind_param('i', $userId);
  $officeQuery->execute();
  $officeResult = $officeQuery->get_result();
  $officeRow = $officeResult->fetch_assoc();
  $officeId = $officeRow['office_id'] ?? null;
  $insert = $conn->prepare('INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())');
  $insert->bind_param('iis', $userId, $officeId, $reportFilename);
  $insert->execute();
}

// Stream to browser
$dompdf->stream($reportFilename, ['Attachment' => false]);
exit;
