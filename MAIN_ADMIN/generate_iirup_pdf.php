<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
require_once '../vendor/autoload.php';
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

// Validate IIRUP ID
$iirup_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($iirup_id <= 0) {
    die('Invalid IIRUP ID.');
}

// Fetch IIRUP form header/footer
$sql = "SELECT f.id AS iirup_id, f.header_image, f.accountable_officer, f.designation, f.office,
               f.footer_accountable_officer, f.footer_authorized_official,
               f.footer_designation_officer, f.footer_designation_official
        FROM iirup_form f
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $iirup_id);
$stmt->execute();
$res = $stmt->get_result();
$form = $res->fetch_assoc();
$stmt->close();

if (!$form) { die('IIRUP record not found.'); }

// Fetch IIRUP items
$sql_items = "SELECT date_acquired, particulars, property_no, qty, unit_cost, total_cost,
                     accumulated_depreciation, accumulated_impairment_losses, carrying_amount,
                     remarks, sale, transfer, destruction, others, total, appraised_value,
                     or_no, amount, dept_office, code, date_received
              FROM iirup_items WHERE iirup_id = ? ORDER BY item_id ASC";
$stmt = $conn->prepare($sql_items);
$stmt->bind_param('i', $iirup_id);
$stmt->execute();
$res_items = $stmt->get_result();
$items = $res_items->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 8mm; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
  .header-img { text-align: center; margin-bottom: 6px; }
  .header-img img { max-width: 100%; height: auto; }
  .hdr-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 6px; border: none; }
  .hdr-table td { text-align: center; padding: 2px 3px; font-size: 9px; }
  .hdr-underline { display: inline-block; border-bottom: 1px solid #000; padding: 0 4px; min-width: 150px; }

  table { width: 100%; border-collapse: collapse; border: none; }
  table.items-table { border: 1px solid #000; }
  thead th { font-weight: bold; border: none; font-size: 9px; padding: 2px; }
  table.items-table thead th { border-bottom: 1px solid #000; }
  th, td { border: none; padding: 2px; text-align: center; font-size: 9px; }
  table.items-table th, table.items-table td { border: 1px solid #000; }
  th:last-child, td:last-child { border-right: none; }

  .footer-sign { width: 100%; border-collapse: collapse; margin-top: 6px; border: none; }
  .footer-cell { width: 50%; text-align: center; vertical-align: bottom; height: 70px; border: none; }
  .footer-title { font-weight: bold; }
  .sig-name { margin-top: 30px; text-transform: uppercase; display: inline-block; border-bottom: 1px solid #000; padding: 0 6px; }
  .designation { font-size: 9px; }
  .static-footer { width:100%; border-collapse: collapse; margin-top: 6px; font-size: 9px; border: none; }
  .static-footer td { vertical-align: top; padding: 4px; }
  .static-footer .labels td { text-align: center; font-weight: bold; padding-top: 8px; }
  .asof { text-align:center; font-size:9px; margin-bottom:6px; }
</style>
</head>
<body>';

// Header image
if (!empty($form['header_image'])) {
    if (filter_var($form['header_image'], FILTER_VALIDATE_URL)) {
        $src = htmlspecialchars($form['header_image']);
        $html .= '<div class="header-img"><img src="' . $src . '" alt="Header"></div>';
    } else {
        $imagePath = realpath(__DIR__ . '/../img/' . $form['header_image']);
        if ($imagePath && file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $src = 'data:image/png;base64,' . $imageData;
            $html .= '<div class="header-img"><img src="' . $src . '" alt="Header"></div>';
        }
    }
    // Add "As of [current year]" text
    $currentYear = date('Y');
    $html .= '<div class="asof">As of ' . $currentYear . '</div>';
}

// Mirror header three-field layout from view_iirup.php
$html .= '<table class="hdr-table">
  <tr>
    <td>
      <span class="hdr-underline">' . htmlspecialchars($form['accountable_officer']) . '</span><br>
      <small>(Name of Accountable Officer)</small>
    </td>
    <td>
      <span class="hdr-underline">' . htmlspecialchars($form['designation']) . '</span><br>
      <small>(Designation)</small>
    </td>
    <td>
      <span class="hdr-underline">' . htmlspecialchars($form['office']) . '</span><br>
      <small>(Department/Office)</small>
    </td>
  </tr>
</table>

<table class="items-table">
  <thead>
    <tr>
      <th colspan="10">INVENTORY</th>
      <th colspan="6">INSPECTION and DISPOSAL</th>
      <th colspan="2">RECORD OF SALES</th>
      <th rowspan="2">DEPT/OFFICE</th>
      <th rowspan="2">CODE</th>
      <th rowspan="2">DATE RECEIVED</th>
    </tr>
    <tr>
      <th>Date Acquired<br>(1)</th>
      <th>Particulars/ Articles<br>(2)</th>
      <th>Property No.<br>(3)</th>
      <th>Qty<br>(4)</th>
      <th>Unit Cost<br>(5)</th>
      <th>Total Cost<br>(6)</th>
      <th>Accumulated Depreciation<br>(7)</th>
      <th>Accumulated Impairment Losses<br>(8)</th>
      <th>Carrying Amount<br>(9)</th>
      <th>Remarks<br>(10)</th>
      <th>Sale<br>(11)</th>
      <th>Transfer<br>(12)</th>
      <th>Destruction<br>(13)</th>
      <th>Others (Specify)<br>(14)</th>
      <th>Total<br>(15)</th>
      <th>Appraised Value<br>(16)</th>
      <th>OR No.<br>(17)</th>
      <th>Amount<br>(18)</th>
    </tr>
  </thead>
  <tbody>';

if (!empty($items)) {
    foreach ($items as $row) {
        $html .= '<tr>
          <td>' . htmlspecialchars($row['date_acquired']) . '</td>
          <td>' . htmlspecialchars($row['particulars']) . '</td>
          <td>' . htmlspecialchars($row['property_no']) . '</td>
          <td>' . htmlspecialchars($row['qty']) . '</td>
          <td>' . number_format((float)$row['unit_cost'], 2) . '</td>
          <td>' . number_format((float)$row['total_cost'], 2) . '</td>
          <td>' . number_format((float)$row['accumulated_depreciation'], 2) . '</td>
          <td>' . number_format((float)$row['accumulated_impairment_losses'], 2) . '</td>
          <td>' . number_format((float)$row['carrying_amount'], 2) . '</td>
          <td>' . htmlspecialchars($row['remarks']) . '</td>
          <td>' . htmlspecialchars($row['sale']) . '</td>
          <td>' . htmlspecialchars($row['transfer']) . '</td>
          <td>' . htmlspecialchars($row['destruction']) . '</td>
          <td>' . htmlspecialchars($row['others']) . '</td>
          <td>' . number_format((float)$row['total'], 2) . '</td>
          <td>' . number_format((float)$row['appraised_value'], 2) . '</td>
          <td>' . htmlspecialchars($row['or_no']) . '</td>
          <td>' . number_format((float)$row['amount'], 2) . '</td>
          <td>' . htmlspecialchars($row['dept_office']) . '</td>
          <td>' . htmlspecialchars($row['code']) . '</td>
          <td>' . htmlspecialchars($row['date_received']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="21">No items found.</td></tr>';
}

$html .= '</tbody>
</table>

<!-- Static footer text block (mirrors view_iirup.php) -->
<table class="static-footer">
  <tr>
    <td colspan="2" style="width:50%; text-align:left;">
      I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, of the property enumerated above.
    </td>
    <td style="width:25%; text-align:left;">
      I CERTIFY that I have inspected each and every article enumerated in this report, and that the disposition made thereof was, in my judgment, the best for the public interest.
    </td>
    <td style="width:25%; text-align:left;">
      I CERTIFY that I have witnessed the disposition of the articles enumerated on this report this ____ day of _____________, _____.
    </td>
  </tr>
  <tr class="labels">
    <td>Requested by:</td>
    <td>Approved by:</td>
    <td>(Signature over Printed Name of Inspection Officer)</td>
    <td>(Signature over Printed Name of Witness)</td>
  </tr>
</table>

<!-- Footer mirroring view_iirup.php -->
<table class="footer-sign">
  <tr>
    <td class="footer-cell" style="border-right:none;">
      <div class="footer-title">Requested by:</div>
      <div class="sig-name">' . htmlspecialchars($form['footer_accountable_officer'] ?? '') . '</div>
      <div class="designation">(Signature over Printed Name of Accountable Officer)</div>
      <div class="designation">' . htmlspecialchars($form['footer_designation_officer'] ?? '') . '</div>
    </td>
    <td class="footer-cell">
      <div class="footer-title">Approved by:</div>
      <div class="sig-name">' . htmlspecialchars($form['footer_authorized_official'] ?? '') . '</div>
      <div class="designation">(Signature over Printed Name of Authorized Official)</div>
      <div class="designation">' . htmlspecialchars($form['footer_designation_official'] ?? '') . '</div>
    </td>
  </tr>
</table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Log IIRUP PDF generation
$office_name = $form['office'] ?? 'Unknown Office';
logReportActivity('IIRUP PDF', "IIRUP ID: {$iirup_id}, Office: {$office_name}");

$dompdf->stream('IIRUP_' . $iirup_id . '.pdf', ['Attachment' => false]);
