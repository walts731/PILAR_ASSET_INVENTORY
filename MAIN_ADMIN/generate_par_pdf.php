<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
require_once '../vendor/autoload.php';
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

// Validate PAR ID
$par_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($par_id <= 0) {
    die("Invalid PAR ID.");
}

// Fetch PAR form details
$sql = "SELECT f.id AS par_id, f.header_image, f.entity_name, f.fund_cluster, f.par_no,
               f.position_office_left, f.position_office_right,
               f.received_by_name, f.issued_by_name,
               f.date_received_left, f.date_received_right, f.created_at,
               f.office_id,
               o.office_name
        FROM par_form f
        LEFT JOIN offices o ON f.office_id = o.id
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $par_id);
$stmt->execute();
$result = $stmt->get_result();
$par = $result->fetch_assoc();
$stmt->close();

if (!$par) {
    die("PAR record not found.");
}

// Fetch PAR items
$sql_items = "SELECT item_id, asset_id, quantity, unit, description, property_no, date_acquired, unit_price, amount
              FROM par_items
              WHERE form_id = ?
              ORDER BY item_id ASC";
$stmt = $conn->prepare($sql_items);
$stmt->bind_param("i", $par_id);
$stmt->execute();
$result_items = $stmt->get_result();
$par['items'] = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Compute grand total
$grandTotal = 0;
foreach ($par['items'] as $item) {
    $grandTotal += (float)($item['amount'] ?? 0);
}

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Start HTML
$html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 12mm; }
  html, body {
    height: 100%;
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    display: flex;
    flex-direction: column;
  }
  h2 { text-align: center; margin: 0; font-size: 16px; }
  .meta { margin: 10px 0; font-size: 12px; }
  .uline { display: inline-block; border-bottom: 1px solid #000; min-width: 220px; padding: 0 4px; }

  .content {
    flex: 1; /* fills available vertical space */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    flex-grow: 1;
  }
  th, td {
    border: 1px solid #000;
    padding: 4px;
    text-align: center;
    font-size: 11px;
  }
  th {
    font-weight: bold;
    background-color: #f9f9f9;
  }

  .grand-total { color: red; font-weight: bold; }
  .designation { font-size: 10px; }
  .header-img { text-align: center; margin-bottom: 10px; }
  .header-img img { max-width: 100%; height: auto; }
</style>
</head>
<body>';

// Header image
if (!empty($par['header_image'])) {
    if (filter_var($par['header_image'], FILTER_VALIDATE_URL)) {
        $src = htmlspecialchars($par['header_image']);
        $html .= '<div class="header-img"><img src="' . $src . '" alt="Header"></div>';
    } else {
        $imagePath = realpath(__DIR__ . '/../img/' . $par['header_image']);
        if ($imagePath && file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $src = 'data:image/png;base64,' . $imageData;
            $html .= '<div class="header-img"><img src="' . $src . '" alt="Header"></div>';
        }
    }
}

// Office/Location
$officeText = '';
if (!empty($par['office_id']) && $par['office_id'] === 'outside_lgu') {
    $officeText = 'Outside LGU';
} elseif (!empty($par['office_name'])) {
    $officeText = $par['office_name'];
}
if ($officeText !== '') {
  $html .= '<div style="text-align:center; margin: 2px 0 8px 0;">'
  . '  <span class="uline">' . htmlspecialchars($officeText) . '</span><br>'
  . '  <strong>Office/Location</strong>'
  . '</div>';
}

$html .= '
<div class="meta">
  <p>
    <strong>Entity Name:</strong> <span class="uline">' . htmlspecialchars($par['entity_name']) . '</span>
    <span style="float:right;"><strong>PAR No.:</strong> <span class="uline">' . htmlspecialchars($par['par_no']) . '</span></span>
  </p>
  <p><strong>Fund Cluster:</strong> <span class="uline">' . htmlspecialchars($par['fund_cluster']) . '</span></p>
</div>

<div class="content">
<table>
  <thead>
    <tr>
      <th>Quantity</th>
      <th>Unit</th>
      <th style="width:30%">Description</th>
      <th>Property No</th>
      <th>Date Acquired</th>
      <th>Unit Price</th>
      <th>Amount</th>
    </tr>
  </thead>
  <tbody>';

// Items section
if (!empty($par['items'])) {
    foreach ($par['items'] as $item) {
        $html .= '<tr>
          <td>' . htmlspecialchars($item['quantity']) . '</td>
          <td>' . htmlspecialchars($item['unit']) . '</td>
          <td style="text-align:left;">' . htmlspecialchars($item['description']) . '</td>
          <td>' . htmlspecialchars($item['property_no']) . '</td>
          <td>' . htmlspecialchars($item['date_acquired']) . '</td>
          <td>' . number_format((float)($item['unit_price'] ?? 0), 2) . '</td>
          <td>' . number_format((float)($item['amount'] ?? 0), 2) . '</td>
        </tr>';
    }
    $html .= '<tr>
      <td colspan="7" style="text-align: center; font-style: italic; padding: 6px 0;">— NOTHING FOLLOWS —</td>
    </tr>';
} else {
    $html .= '<tr><td colspan="7">No items found.</td></tr>';
}

// Fill empty rows so table reaches bottom
$totalRows = count($par['items']);
$minRows = 20; // adjust as needed to ensure full-page fill
if ($totalRows < $minRows) {
    for ($i = $totalRows; $i < $minRows; $i++) {
        $html .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
    }
}

// Grand total
$html .= '<tr>
  <td colspan="5"></td>
  <td><strong>Total</strong></td>
  <td class="grand-total">' . number_format($grandTotal, 2) . '</td>
</tr>';

// Footer/signatories
$html .= '<tr>
  <td colspan="3" style="height:120px; vertical-align:bottom; text-align:center;">
    <strong><i>Received by:</i></strong><br><br>
    <strong><u>' . strtoupper(htmlspecialchars($par['received_by_name'] ?? '')) . '</u></strong><br>
    <span style="display:block; text-align:center; margin-top:2px;">Signature over Printed Name</span><br>
    <strong><u>' . htmlspecialchars($par['position_office_left'] ?? '____________') . '</u></strong><br>
    <span style="display:block; text-align:center; margin-top:2px;">Position / Office</span><br>
    <strong><u>' . (!empty($par['date_received_left']) ? htmlspecialchars(date('Y-m-d', strtotime($par['date_received_left']))) : '____________') . '</u></strong><br>
    <span style="display:block; text-align:center; margin-top:2px;">Date</span>
  </td>
  <td></td>
  <td colspan="3" style="height:120px; vertical-align:bottom; text-align:center;">
    <strong><i>Issued by:</i></strong><br><br>
    <strong><u>' . strtoupper(htmlspecialchars($par['issued_by_name'] ?? '')) . '</u></strong><br>
    <span style="display:block; text-align:center; margin-top:2px;">Signature over Printed Name</span><br>
    <strong><u>' . htmlspecialchars($par['position_office_right'] ?? '____________') . '</u></strong><br>
    <span style="display:block; text-align:center; margin-top:2px;">Position / Office</span><br>
    <strong><u>' . (!empty($par['date_received_right']) ? htmlspecialchars(date('Y-m-d', strtotime($par['date_received_right']))) : '____________') . '</u></strong><br>
    <span style="display:block; text-align:center; margin-top:2px;">Date</span>
  </td>
</tr>
</tbody>
</table>
</div>
</body>
</html>';

// Generate PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Log PAR PDF generation
$par_number = $par['par_no'] ?? 'Unknown';
$entity_name = $par['entity_name'] ?? 'Unknown Entity';
logReportActivity('PAR PDF', "PAR: {$par_number}, Entity: {$entity_name}");

$dompdf->stream("PAR_" . ($par['par_no'] ?? $par_id) . ".pdf", ["Attachment" => false]);
