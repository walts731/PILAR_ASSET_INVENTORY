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
  .hdr-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  .hdr-table td { text-align: center; padding: 2px 3px; font-size: 9px; }
  .hdr-underline { display: inline-block; border-bottom: 1px solid #000; padding: 0 4px; min-width: 150px; }

  table { width: 100%; border-collapse: collapse; }
  table.items-table { border: 1px solid #000; }
  thead th { font-weight: bold; border: none; font-size: 9px; padding: 2px; }
  table.items-table thead th { border-bottom: 1px solid #000; }
  th, td { border: 1px solid #000; padding: 2px; text-align: center; font-size: 9px; }
  th:last-child, td:last-child { border-right: none; }

  .static-footer { width:100%; border-collapse: collapse; margin-top: 10px; font-size: 9px; }
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
    $currentYear = date('Y');
    $html .= '<div class="asof">As of ' . $currentYear . '</div>';
}

// Header fields
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
   <td style="border-right:1px solid #000;">
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
      <th>Date Acquired</th>
      <th>Particulars/Articles</th>
      <th>Property No.</th>
      <th>Qty</th>
      <th>Unit Cost</th>
      <th>Total Cost</th>
      <th>Accumulated Depreciation</th>
      <th>Accumulated Impairment Losses</th>
      <th>Carrying Amount</th>
      <th>Remarks</th>
      <th>Sale</th>
      <th>Transfer</th>
      <th>Destruction</th>
      <th>Others</th>
      <th>Total</th>
      <th>Appraised Value</th>
      <th>OR No.</th>
      <th>Amount</th>
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

    $html .= '<tr><td colspan="21" style="text-align:center; font-style:italic;">— NOTHING FOLLOWS —</td></tr>';
} else {
    $html .= '<tr><td colspan="21">No items found.</td></tr>';
}

$html .= '</tbody>
</table>

<!-- Static footer text block (mirrors view_iirup.php) -->
<table class="static-footer" style="width:100%; border:1px solid #000; border-collapse:collapse; margin-top:10px;">
  <tr style="border:none;">
    <td colspan="2" style="width:50%; text-align:left; vertical-align:top; border:none;">
      I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, of the property enumerated above.
    </td>
    <td style="width:25%; text-align:left; vertical-align:top; border:none;">
      I CERTIFY that I have inspected each and every article enumerated in this report, and that the disposition made thereof was, in my judgment, the best for the public interest.
    </td>
    <td style="width:25%; text-align:left; vertical-align:top; border:none;">
      I CERTIFY that I have witnessed the disposition of the articles enumerated on this report this ____ day of _____________, _____.
    </td>
  </tr>

  <!-- Labels -->
  <tr class="labels" style="border:none;">
    <td style="padding-top:12px; font-weight:bold; text-align:center; border:none;">Requested by:</td>
    <td style="padding-top:12px; font-weight:bold; text-align:center; border:none;">Approved by:</td>
    <td style="padding-top:12px; border:none;"></td>
    <td style="padding-top:12px; border:none;"></td>
  </tr>

    <!-- Signatories inline with their labels (centered) -->
  <tr style="border:none;">
    <!-- Requested by -->
    <td style="text-align:center; padding-top:20px; border:none;">
      <div style="font-weight:bold; text-transform:uppercase; display:inline-block; border-bottom:1px solid #000; padding-bottom:1px; width:80%;">
        ' . htmlspecialchars($form['footer_accountable_officer'] ?? '') . '
      </div>
      <div style="font-size:9px; margin-top:3px;">(Signature over Printed Name of Accountable Officer)</div>
      <div style="font-style:italic; margin-top:2px;">
        ' . htmlspecialchars($form['footer_designation_officer'] ?? '') . '
      </div>
      <div style="font-size:9px; margin-top:1px;">(Designation of Accountable Officer)</div>
    </td>

    <!-- Approved by -->
    <td style="text-align:center; padding-top:20px; border:none;">
      <div style="font-weight:bold; text-transform:uppercase; display:inline-block; border-bottom:1px solid #000; padding-bottom:1px; width:80%;">
        ' . htmlspecialchars($form['footer_authorized_official'] ?? '') . '
      </div>
      <div style="font-size:9px; margin-top:3px;">(Signature over Printed Name of Authorized Official)</div>
      <div style="font-style:italic; margin-top:2px;">
        ' . htmlspecialchars($form['footer_designation_official'] ?? '') . '
      </div>
      <div style="font-size:9px; margin-top:1px;">(Designation of Authorized Official)</div>
    </td>

    <!-- Inspection Officer -->
    <td style="text-align:center; vertical-align:bottom; padding-top:20px; border:none;">
      <div style="border-bottom:1px solid #000; width:80%; margin:0 auto; height:0;"></div>
      <div style="font-size:9px; margin-top:3px;">(Signature over Printed Name of Inspection Officer)</div>
    </td>

    <!-- Witness -->
    <td style="text-align:center; vertical-align:bottom; padding-top:20px; border:none;">
      <div style="border-bottom:1px solid #000; width:80%; margin:0 auto; height:0;"></div>
      <div style="font-size:9px; margin-top:3px;">(Signature over Printed Name of Witness)</div>
    </td>
  </tr>

</table>




</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$office_name = $form['office'] ?? 'Unknown Office';
logReportActivity('IIRUP PDF', "IIRUP ID: {$iirup_id}, Office: {$office_name}");

$dompdf->stream('IIRUP_' . $iirup_id . '.pdf', ['Attachment' => false]);
