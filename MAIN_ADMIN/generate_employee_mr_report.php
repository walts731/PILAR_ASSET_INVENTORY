<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure POST with selected employees
$selectedEmployees = isset($_POST['selected_employees']) ? array_map('intval', (array)$_POST['selected_employees']) : [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($selectedEmployees)) {
    http_response_code(400);
    echo 'No employees selected.';
    exit;
}

// Fetch logo
$systemLogo = '';
$systemRes = $conn->query("SELECT logo FROM system LIMIT 1");
if ($systemRes && ($row = $systemRes->fetch_assoc()) && !empty($row['logo'])) {
    $logoPath = realpath(__DIR__ . '/../img/' . $row['logo']);
    if ($logoPath && file_exists($logoPath)) {
        $systemLogo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
}

// Prepare data: for each employee, get details and their MR assets
$employeesData = [];
$empIds = implode(',', $selectedEmployees);

// Get employee basic info
$empQuery = $conn->query("SELECT e.employee_id, e.employee_no, e.name, e.email, o.office_name FROM employees e LEFT JOIN offices o ON e.office_id = o.id WHERE e.employee_id IN ($empIds)");
while ($emp = $empQuery->fetch_assoc()) {
    $employeesData[(int)$emp['employee_id']] = [
        'employee_no' => $emp['employee_no'] ?? '',
        'name' => $emp['name'] ?? '',
        'email' => $emp['email'] ?? '',
        'office_name' => $emp['office_name'] ?? 'N/A',
        'items' => [],
    ];
}

if (!empty($employeesData)) {
    // Use a JOIN to map MR rows to the selected employee IDs via name match
    $mrSql = "SELECT md.*, a.qr_code, a.inventory_tag AS asset_inventory_tag, a.description AS asset_description, e.employee_id
              FROM mr_details md
              LEFT JOIN assets a ON a.id = md.asset_id
              JOIN employees e ON e.name = md.person_accountable
              WHERE e.employee_id IN ($empIds)
              ORDER BY e.employee_id ASC";
    $mrRes = $conn->query($mrSql);
    while ($mr = $mrRes->fetch_assoc()) {
        $empId = (int)$mr['employee_id'];
        if (!isset($employeesData[$empId])) { continue; }
        $edata = &$employeesData[$empId];
        $edata['items'][] = [
            'description' => $mr['description'] ?: ($mr['asset_description'] ?? ''),
            'model_no' => $mr['model_no'] ?? '',
            'serial_no' => $mr['serial_no'] ?? '',
            'serviceable' => (int)($mr['serviceable'] ?? 0),
            'unserviceable' => (int)($mr['unserviceable'] ?? 0),
            'unit_quantity' => $mr['unit_quantity'] ?? '',
            'unit' => $mr['unit'] ?? '',
            'acquisition_date' => $mr['acquisition_date'] ?? '',
            'acquisition_cost' => $mr['acquisition_cost'] ?? '',
            'acquired_date' => $mr['acquired_date'] ?? '',
            'counted_date' => $mr['counted_date'] ?? '',
            'office_location' => $mr['office_location'] ?? ($edata['office_name'] ?? ''),
            'inventory_tag' => (!empty($mr['inventory_tag']) ? $mr['inventory_tag'] : ($mr['asset_inventory_tag'] ?? '')),
            'qr_code' => $mr['qr_code'] ?? '',
        ];
        unset($edata);
    }
}

// Remove employees with no MR items
$employeesData = array_filter($employeesData, function($e){ return !empty($e['items']); });

if (empty($employeesData)) {
    echo 'No MR assets found for the selected employees.';
    exit;
}

$reportDate = date('F d, Y');
$reportFilename = 'Employee_MR_Report_' . date('Ymd_His') . '.pdf';

// Build HTML
$html = '<html><head><meta charset="UTF-8"><style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
.header { display: table; width: 100%; margin-bottom: 12px; }
.header .col { display: table-cell; vertical-align: middle; }
.logo { height: 60px; }
h2 { margin: 0 0 4px 0; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border: 1px solid #000; padding: 6px; }
.table th { background: #f2f2f2; }
.section-title { background:#e9ecef; border:1px solid #000; padding:8px; font-weight:bold; margin-top: 18px; }
.meta { margin-bottom: 6px; }
.small { font-size: 10px; color: #555; }
</style></head><body>';

// Header
$html .= '<div class="header">'
      . '<div class="col" style="width:20%">' . ($systemLogo ? '<img class="logo" src="' . $systemLogo . '" />' : '') . '</div>'
      . '<div class="col" style="width:60%; text-align:center">'
      . '<div>Republic of the Philippines</div>'
      . '<div><strong>Municipality of Pilar</strong></div>'
      . '<div>Province of Sorsogon</div>'
      . '<h2>EMPLOYEE MR REPORT</h2>'
      . '<div class="small"><em>As of ' . htmlspecialchars($reportDate) . '</em></div>'
      . '</div>'
      . '<div class="col" style="width:20%"></div>'
      . '</div>';

foreach ($employeesData as $emp) {
    $html .= '<div class="section-title">' . htmlspecialchars($emp['name']) . ' — ' . htmlspecialchars($emp['office_name']) . ' (Emp No: ' . htmlspecialchars($emp['employee_no']) . ')</div>';
    if (!empty($emp['email'])) {
        $html .= '<div class="meta"><strong>Email:</strong> ' . htmlspecialchars($emp['email']) . '</div>';
    }

    $html .= '<table class="table">'
          . '<thead>'
          . '<tr>'
          . '<th>Description</th>'
          . '<th>Model No.</th>'
          . '<th>Serial No.</th>'
          . '<th>Serviceable</th>'
          . '<th>Unit/Qty</th>'
          . '<th>Acq. Date</th>'
          . '<th>Acq. Cost</th>'
          . '<th>Inventory Tag</th>'
          . '</tr>'
          . '</thead><tbody>';

    foreach ($emp['items'] as $it) {
        $html .= '<tr>'
              . '<td>' . htmlspecialchars((string)$it['description']) . '</td>'
              . '<td>' . htmlspecialchars((string)$it['model_no']) . '</td>'
              . '<td>' . htmlspecialchars((string)$it['serial_no']) . '</td>'
              . '<td>' . ((int)$it['serviceable'] === 1 ? 'Yes' : ((int)$it['unserviceable'] === 1 ? 'No' : '')) . '</td>'
              . '<td>' . htmlspecialchars((string)$it['unit_quantity'] . ' ' . (string)$it['unit']) . '</td>'
              . '<td>' . (!empty($it['acquisition_date']) ? htmlspecialchars(date('M d, Y', strtotime($it['acquisition_date']))) : '') . '</td>'
              . '<td>₱ ' . htmlspecialchars(number_format((float)($it['acquisition_cost'] ?: 0), 2)) . '</td>'
              . '<td>' . htmlspecialchars((string)($it['inventory_tag'] ?: '')) . '</td>'
              . '</tr>';
    }

    $html .= '</tbody></table>';
}

$html .= '</body></html>';

// Generate PDF
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
$reportFilenameSafe = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $reportFilename);
$savePath = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'generated_reports' . DIRECTORY_SEPARATOR . $reportFilenameSafe;
file_put_contents($savePath, $pdfOutput);

// Log to generated_reports
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
    $officeId = null;
    $officeQuery = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $officeQuery->bind_param("i", $userId);
    $officeQuery->execute();
    $officeResult = $officeQuery->get_result();
    if ($officeRow = $officeResult->fetch_assoc()) {
        $officeId = $officeRow['office_id'] ?? null;
    }
    $insert = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
    $insert->bind_param("iis", $userId, $officeId, $reportFilenameSafe);
    $insert->execute();
}

// Stream to browser
$dompdf->stream($reportFilenameSafe, ['Attachment' => false]);
exit;
