<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) exit();

// 1. Fetch generation setting for the user
$stmt = $conn->prepare("SELECT frequency, day_of_week, day_of_month FROM report_generation_settings WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($frequency, $day_of_week, $day_of_month);
$stmt->fetch();
$stmt->close();

// 2. Check if today matches the setting
$today = date('Y-m-d');
$current_day_of_week = date('l'); // e.g., 'Monday'
$current_day_of_month = date('j'); // e.g., 15

$shouldGenerate = false;
switch ($frequency) {
    case 'daily':
        $shouldGenerate = true;
        break;
    case 'weekly':
        $shouldGenerate = ($current_day_of_week === $day_of_week);
        break;
    case 'monthly':
        $shouldGenerate = ((int)$current_day_of_month === (int)$day_of_month);
        break;
}

// 3. Prevent duplicate generation for the same day
$check = $conn->prepare("SELECT COUNT(*) FROM generated_reports WHERE user_id = ? AND DATE(generated_at) = ?");
$check->bind_param("is", $user_id, $today);
$check->execute();
$check->bind_result($report_count);
$check->fetch();
$check->close();

if (!$shouldGenerate || $report_count > 0) exit(); // Already generated today

// 4. Generate the report (reusing your working logic)
$stmt = $conn->query("
    SELECT a.*, c.category_name
    FROM assets a
    JOIN categories c ON a.category = c.id
");

$reportDate = date('F Y');
$reportFilename = 'Auto_Inventory_Report_' . date('Ymd_His') . '.pdf';
$logoPath = '../img/PILAR LOGO TRANSPARENT.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

$html = '
<style>/* Your full CSS styling here */</style>
<div class="header-row">
    <div class="header-col left-col">';
if ($logoBase64) {
    $html .= '<img src="' . $logoBase64 . '" class="logo">';
}
$html .= '</div>
    <div class="header-col center-col">
        <p>Republic of the Philippines</p>
        <h4>Municipality of Pilar</h4>
        <p>Province of Sorsogon</p>
        <h2>AUTO INVENTORY REPORT</h2>
        <p><em>As of ' . $reportDate . '</em></p>
    </div>
</div>
<table>
<thead>
<tr>
    <th>Asset Name</th><th>Category</th><th>Description</th><th>Qty</th>
    <th>Unit</th><th>Status</th><th>Value</th><th>Acquired</th><th>Updated</th>
</tr>
</thead><tbody>';

while ($row = $stmt->fetch_assoc()) {
    $html .= '<tr>
        <td>' . htmlspecialchars($row['asset_name']) . '</td>
        <td>' . htmlspecialchars($row['category_name']) . '</td>
        <td>' . htmlspecialchars($row['description']) . '</td>
        <td>' . $row['quantity'] . '</td>
        <td>' . htmlspecialchars($row['unit']) . '</td>
        <td>' . ucfirst(htmlspecialchars($row['status'])) . '</td>
        <td>&#8369; ' . number_format($row['value'], 2) . '</td>
        <td>' . date('M d, Y', strtotime($row['acquisition_date'])) . '</td>
        <td>' . date('M d, Y', strtotime($row['last_updated'])) . '</td>
    </tr>';
}
$html .= '</tbody></table>';

// 5. Generate PDF
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$savePath = '../generated_reports/' . $reportFilename;
file_put_contents($savePath, $dompdf->output());

// 6. Log generation
$insert = $conn->prepare("INSERT INTO generated_reports (user_id, filename, generated_at) VALUES (?, ?, NOW())");
$insert->bind_param("is", $user_id, $reportFilename);
$insert->execute();
?>
