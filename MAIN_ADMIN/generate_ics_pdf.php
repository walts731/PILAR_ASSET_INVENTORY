<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Validate ICS ID
$ics_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($ics_id <= 0) {
    die("Invalid ICS ID.");
}

// Fetch ICS form details
$sql = "SELECT f.id AS ics_id, f.header_image, f.entity_name, f.fund_cluster, f.ics_no,
               f.received_from_name, f.received_from_position,
               f.received_by_name, f.received_by_position, f.created_at,
               o.office_name
        FROM ics_form f
        LEFT JOIN offices o ON f.id = o.id
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result = $stmt->get_result();
$ics = $result->fetch_assoc();
$stmt->close();

if (!$ics) {
    die("ICS record not found.");
}

// Fetch ICS items
$sql_items = "SELECT item_id, item_no, description, quantity, unit, unit_cost, total_cost, estimated_useful_life
              FROM ics_items
              WHERE ics_id = ?
              ORDER BY item_no ASC";
$stmt = $conn->prepare($sql_items);
$stmt->bind_param("i", $ics_id);
$stmt->execute();
$result_items = $stmt->get_result();
$ics['items'] = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Compute grand total
$grandTotal = 0;
foreach ($ics['items'] as $item) {
    $grandTotal += $item['total_cost'];
}

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Start HTML
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
  h2 { text-align: center; margin: 0; font-size: 16px; }
  .meta { margin: 10px 0; font-size: 12px; }

  table { 
    width: 100%; 
    border-collapse: collapse; 
    border: 1px solid #000; /* keep outer border */
  }
  th, td { 
    border-right: 1px solid #000;  /* only vertical column lines */
    padding: 4px; 
    text-align: center; 
    font-size: 11px; 
  }
  th:last-child, td:last-child { border-right: none; } /* remove last column border */
  th { font-weight: bold; border-bottom: 1px solid #000; } /* header has bottom line */

  .grand-total { color: red; font-weight: bold; }
  .designation { font-size: 10px; }
  .header-img { text-align: center; margin-bottom: 10px; }
  .header-img img { max-width: 100%; height: auto; }
</style>


</head>
<body>
';

// ✅ Show header image if available
if (!empty($ics['header_image'])) {
    if (filter_var($ics['header_image'], FILTER_VALIDATE_URL)) {
        $src = htmlspecialchars($ics['header_image']);
        $html .= '<div class="header-img"><img src="' . $src . '" alt="Header"></div>';
    } else {
        $imagePath = realpath(__DIR__ . '/../img/' . $ics['header_image']);
        if ($imagePath && file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $src = 'data:image/png;base64,' . $imageData;
            $html .= '<div class="header-img"><img src="' . $src . '" alt="Header"></div>';
        }
    }
}

// Meta section
$html .= '
<div class="meta">
  <p><strong>Entity Name:</strong> ' . htmlspecialchars($ics['entity_name']) . '<br>
  <strong>Fund Cluster:</strong> ___________________________
  <span style="float:right;">ICS No: ' . htmlspecialchars($ics['ics_no']) . '</span></p>
</div>

<table>
  <thead>
    <tr>
      <th rowspan="2">Quantity</th>
      <th rowspan="2">Unit</th>
      <th colspan="2">Amount</th>
      <th rowspan="2">Description</th>
      <th rowspan="2">Item No.</th>
      <th rowspan="2">Estimated Useful Life</th>
    </tr>
    <tr>
      <th>Unit Cost</th>
      <th>Total Cost</th>
    </tr>
  </thead>
  <tbody>';

if (!empty($ics['items'])) {
    foreach ($ics['items'] as $item) {
        $html .= '
        <tr>
          <td>' . htmlspecialchars($item['quantity']) . '</td>
          <td>' . htmlspecialchars($item['unit']) . '</td>
          <td>' . number_format($item['unit_cost'], 2) . '</td>
          <td>' . number_format($item['total_cost'], 2) . '</td>
          <td>' . htmlspecialchars($item['description']) . '</td>
          <td>' . htmlspecialchars($item['item_no']) . '</td>
          <td>' . htmlspecialchars($item['estimated_useful_life']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="7">No items found.</td></tr>';
}

// Add empty rows for spacing (to keep table size uniform)
for ($i = 0; $i < 10; $i++) {
    $html .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
}

// Grand total row
$html .= '
  <tr>
    <td colspan="3"></td>
    <td class="grand-total">' . number_format($grandTotal, 2) . '</td>
    <td colspan="3"></td>
  </tr>
  <tr>
    <td colspan="7" style="padding:0; border:none;">
      <table style="width:100%; border-collapse:collapse; border-top:1px solid #000;">
        <tr>
          <td style="border:1px solid #000; height:60px; vertical-align:bottom; text-align:center;">
            <strong>Received from:</strong><br><br>
            <u>' . strtoupper(htmlspecialchars($ics['received_from_name'])) . '</u><br>
            <span class="designation">' . htmlspecialchars($ics['received_from_position']) . '</span><br>
            Date: ____________
          </td>
          <td style="border:1px solid #000; height:60px; vertical-align:bottom; text-align:center;">
            <strong>Received by:</strong><br><br>
            <u>' . strtoupper(htmlspecialchars($ics['received_by_name'])) . '</u><br>
            <span class="designation">' . htmlspecialchars($ics['received_by_position']) . '</span><br>
            Date: ____________
          </td>
        </tr>
      </table>
    </td>
  </tr>
</tbody>
</table>
</body>
</html>
';

// Load HTML and generate PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("ICS_" . $ics['ics_no'] . ".pdf", ["Attachment" => false]);
