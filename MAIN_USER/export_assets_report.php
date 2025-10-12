<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: user_dashboard.php');
  exit();
}

$format = strtolower(trim($_POST['format'] ?? 'pdf'));
$office_id = isset($_POST['office_id']) && $_POST['office_id'] !== '' ? (int)$_POST['office_id'] : null;
$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
$status = isset($_POST['status']) && $_POST['status'] !== '' ? trim($_POST['status']) : null;
$date_from = isset($_POST['date_from']) && $_POST['date_from'] !== '' ? $_POST['date_from'] : null;
$date_to = isset($_POST['date_to']) && $_POST['date_to'] !== '' ? $_POST['date_to'] : null;

$where = ["a.type = 'asset'"];
$params = [];
$types = '';

if ($office_id) {
  $where[] = 'a.office_id = ?';
  $types .= 'i';
  $params[] = $office_id;
}
if ($category_id) {
  $where[] = 'a.category = ?';
  $types .= 'i';
  $params[] = $category_id;
}
if ($status) {
  $where[] = 'a.status = ?';
  $types .= 's';
  $params[] = $status;
}
if ($date_from) {
  $where[] = 'a.acquisition_date >= ?';
  $types .= 's';
  $params[] = $date_from;
}
if ($date_to) {
  $where[] = 'a.acquisition_date <= ?';
  $types .= 's';
  $params[] = $date_to;
}

$sql = "SELECT a.*, c.category_name, o.office_name
        FROM assets a
        JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON o.id = a.office_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY c.category_name, a.description";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result->fetch_assoc()) { $rows[] = $r; }
$stmt->close();

$reportDate = date('F Y');
if ($format === 'csv') {
  $filename = 'Assets_Report_' . date('Ymd_His') . '.csv';
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename=' . $filename);
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Description', 'Category', 'Office', 'Qty', 'Unit', 'Status', 'Value', 'Acquired']);
  foreach ($rows as $row) {
    fputcsv($out, [
      (string)($row['description'] ?? ''),
      (string)($row['category_name'] ?? ''),
      (string)($row['office_name'] ?? ''),
      (int)($row['quantity'] ?? 0),
      (string)($row['unit'] ?? ''),
      ucfirst((string)($row['status'] ?? '')),
      number_format((float)($row['value'] ?? 0), 2, '.', ''),
      $row['acquisition_date'] ? date('M d, Y', strtotime($row['acquisition_date'])) : ''
    ]);
  }
  fclose($out);
  exit();
}

// Default to PDF
$logoPath = '../img/PILAR LOGO TRANSPARENT.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

$html = '<style>
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
</style>';

$html .= '<div class="header-row">
  <div class="header-col left-col">' . ($logoBase64 ? '<img src="' . $logoBase64 . '" class="logo" alt="Logo">' : '') . '</div>
  <div class="header-col center-col">
    <p>Republic of the Philippines</p>
    <h4>Municipality of Pilar</h4>
    <p>Province of Sorsogon</p>
    <h2>INVENTORY REPORT</h2>
    <p><em>As of ' . htmlspecialchars($reportDate) . '</em></p>
  </div>
  <div class="header-col right-col"></div>
</div>';

$html .= '<table><thead><tr>
  <th>Description</th>
  <th>Category</th>
  <th>Office</th>
  <th>Qty</th>
  <th>Unit</th>
  <th>Status</th>
  <th>Value</th>
  <th>Acquired</th>
</tr></thead><tbody>';

foreach ($rows as $row) {
  $html .= '<tr>'
      . '<td>' . htmlspecialchars((string)($row['description'] ?? '')) . '</td>'
      . '<td>' . htmlspecialchars((string)($row['category_name'] ?? '')) . '</td>'
      . '<td>' . htmlspecialchars((string)($row['office_name'] ?? '')) . '</td>'
      . '<td>' . (int)($row['quantity'] ?? 0) . '</td>'
      . '<td>' . htmlspecialchars((string)($row['unit'] ?? '')) . '</td>'
      . '<td>' . htmlspecialchars(ucfirst((string)($row['status'] ?? ''))) . '</td>'
      . '<td>&#8369; ' . number_format((float)($row['value'] ?? 0), 2) . '</td>'
      . '<td>' . ($row['acquisition_date'] ? date('M d, Y', strtotime($row['acquisition_date'])) : '') . '</td>'
    . '</tr>';
}
$html .= '</tbody></table>';

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = 'Assets_Report_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
exit();
