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
               f.date_received_left, f.date_received_right, f.created_at,
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

// Meta section
$html .= '
<div class="meta">
  <p><strong>Entity Name:</strong> ' . htmlspecialchars($par['entity_name']) . '<br>
  <strong>Fund Cluster:</strong> ' . htmlspecialchars($par['fund_cluster']) . '
  <span style="float:right;">PAR No: ' . htmlspecialchars($par['par_no']) . '</span></p>
  <p><strong>Office/Location:</strong> ' . htmlspecialchars($par['office_name'] ?? 'N/A') . '</p>
</div>

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
} else {
    $html .= '<tr><td colspan="7">No items found.</td></tr>';
}

// Add empty rows for spacing if few items
for ($i = count($par['items']); $i < 10; $i++) {
    $html .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
}

// Grand total row
$html .= '<tr>
  <td colspan="5"></td>
  <td><strong>Total</strong></td>
  <td class="grand-total">' . number_format($grandTotal, 2) . '</td>
</tr>';

// Footer/signatories
$html .= '<tr>
  <td colspan="7" style="padding:0; border-top:1px solid #000;">
    <table style="width:100%; border-collapse:collapse;">
      <tr>
        <td style="width:50%; border-right:1px solid #000; height:80px; vertical-align:bottom; text-align:center;">
          <strong>Received by:</strong><br><br>
          <u>' . strtoupper(htmlspecialchars($par['position_office_left'] ?? '')) . '</u><br>
          <span class="designation">Position / Office</span><br>
          Date: ' . (!empty($par['date_received_left']) ? htmlspecialchars(date('Y-m-d', strtotime($par['date_received_left']))) : '____________') . '
        </td>
        <td style="width:50%; height:80px; vertical-align:bottom; text-align:center;">
          <strong>Issued by:</strong><br><br>
          <u>' . strtoupper(htmlspecialchars($par['position_office_right'] ?? '')) . '</u><br>
          <span class="designation">Position / Office</span><br>
          Date: ' . (!empty($par['date_received_right']) ? htmlspecialchars(date('Y-m-d', strtotime($par['date_received_right']))) : '____________') . '
        </td>
      </tr>
    </table>
  </td>
</tr>';

$html .= '</tbody></table></body></html>';

// Load HTML and generate PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Log PAR PDF generation
$par_number = $par['par_no'] ?? 'Unknown';
$entity_name = $par['entity_name'] ?? 'Unknown Entity';
logReportActivity('PAR PDF', "PAR: {$par_number}, Entity: {$entity_name}");

$dompdf->stream("PAR_" . ($par['par_no'] ?? $par_id) . ".pdf", ["Attachment" => false]);
