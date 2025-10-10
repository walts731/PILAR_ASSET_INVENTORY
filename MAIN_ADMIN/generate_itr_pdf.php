<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
require_once '../vendor/autoload.php';

session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

// ---- Auth check ----
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// ---- Utility: fetch assoc single row with fallback if get_result() missing ----
function stmt_fetch_one_assoc(mysqli_stmt $stmt) {
    if (method_exists($stmt, 'get_result')) {
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // fallback for environments without mysqlnd
    $meta = $stmt->result_metadata();
    if (!$meta) return null;
    $fields = $meta->fetch_fields();
    $row = [];
    $bind = [];
    foreach ($fields as $f) {
        $bind[] = &$row[$f->name];
    }
    call_user_func_array([$stmt, 'bind_result'], $bind);
    if ($stmt->fetch()) {
        // copy values (because $row values are references)
        $out = [];
        foreach ($row as $k => $v) $out[$k] = $v;
        return $out;
    }
    return null;
}

function stmt_fetch_all_assoc(mysqli_stmt $stmt) {
    if (method_exists($stmt, 'get_result')) {
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    $meta = $stmt->result_metadata();
    if (!$meta) return [];
    $fields = $meta->fetch_fields();
    $row = [];
    $bind = [];
    foreach ($fields as $f) {
        $bind[] = &$row[$f->name];
    }
    call_user_func_array([$stmt, 'bind_result'], $bind);
    $out = [];
    while ($stmt->fetch()) {
        $copy = [];
        foreach ($row as $k => $v) $copy[$k] = $v;
        $out[] = $copy;
    }
    return $out;
}

// ---- Get and validate ITR id ----
$itr_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$itr_id = $itr_id ? intval($itr_id) : 0;
if ($itr_id <= 0) {
    http_response_code(400);
    die('Invalid ITR ID');
}

// ---- Fetch ITR header ----
$itr_stmt = $conn->prepare("SELECT itr_id, header_image, entity_name, fund_cluster, from_accountable_officer, 
    to_accountable_officer, itr_no, `date`, transfer_type, reason_for_transfer, approved_by, approved_designation, 
    approved_date, released_by, released_designation, released_date, received_by, received_designation, received_date
    FROM itr_form WHERE itr_id = ? LIMIT 1");
if (!$itr_stmt) {
    error_log("Prepare failed (itr_form): " . $conn->error);
    die('Database error');
}
$itr_stmt->bind_param('i', $itr_id);
$itr_stmt->execute();
$itr = stmt_fetch_one_assoc($itr_stmt);
$itr_stmt->close();

if (!$itr) {
    die('ITR not found');
}

// ---- Fetch ITR items ----
$items_stmt = $conn->prepare("SELECT item_id, itr_id, date_acquired, property_no, asset_id, description, amount, condition_of_PPE
    FROM itr_items WHERE itr_id = ? ORDER BY item_id ASC");
if (!$items_stmt) {
    error_log("Prepare failed (itr_items): " . $conn->error);
    die('Database error');
}
$items_stmt->bind_param('i', $itr_id);
$items_stmt->execute();
$items = stmt_fetch_all_assoc($items_stmt);
$items_stmt->close();

// ---- Calculate total amount ----
$totalAmount = 0.0;
foreach ($items as $item) {
    // protect against non-numeric or null amount
    $totalAmount += isset($item['amount']) && is_numeric($item['amount']) ? floatval($item['amount']) : 0.0;
}

// ---- Dompdf initialization (use explicit setters) ----
$options = new Options();
$options->setIsHtml5ParserEnabled(true);
$options->setIsRemoteEnabled(true);
$dompdf = new Dompdf($options);

// ---- Build HTML ----
$html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 0; }
  table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 2px; }
  td, th { border: 1px solid #000; padding: 3px; vertical-align: top; font-size: 10px; }
  .header-table td { font-weight: bold; }
  .items-table th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; text-align: center; line-height: 10px; }
  .checked { background-color: #000; color: white; }
  .header-img { text-align: center; margin-bottom: 10px; }
  .header-img img { max-width: 100%; height: auto; }
</style>
</head>
<body>';

// ---- Header image handling: local or remote -> try base64 embed for reliability ----
if (!empty($itr['header_image'])) {
    $header_src = null;
    if (filter_var($itr['header_image'], FILTER_VALIDATE_URL)) {
        // try to fetch remote image and embed as base64; if not possible, fallback to direct URL
        $imgContents = @file_get_contents($itr['header_image']);
        if ($imgContents !== false) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $imgContents);
            finfo_close($finfo);
            $header_src = 'data:' . ($mime ?: 'image/png') . ';base64,' . base64_encode($imgContents);
        } else {
            // fallback to raw URL (dompdf remote must be enabled)
            $header_src = htmlspecialchars($itr['header_image']);
        }
    } else {
        $imagePath = realpath(__DIR__ . '/../img/' . $itr['header_image']);
        if ($imagePath && file_exists($imagePath)) {
            $imgContents = @file_get_contents($imagePath);
            if ($imgContents !== false) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $imgContents);
                finfo_close($finfo);
                $header_src = 'data:' . ($mime ?: 'image/png') . ';base64,' . base64_encode($imgContents);
            }
        }
    }

    if ($header_src) {
        $html .= '<div class="header-img"><img src="' . $header_src . '" alt="Header"></div>';
    }
}

// ---- Header tables ----
$html .= '
<table class="header-table">
    <tr>
        <td style="width: 40%;">Entity Name: ' . htmlspecialchars($itr['entity_name'] ?? 'LGU-PILAR-CAMUR') . '</td>
        <td style="width: 30%;">Fund Cluster: ' . htmlspecialchars($itr['fund_cluster'] ?? '') . '</td>
        <td style="width: 30%;"></td>
    </tr>
</table>

<table class="header-table">
    <tr>
        <td style="width: 50%;">From Accountable Officer/Agency/Fund Cluster: ' . htmlspecialchars($itr['from_accountable_officer'] ?? '') . '</td>
        <td style="width: 25%;">ITR No.:</td>
        <td style="width: 25%;">' . htmlspecialchars($itr['itr_no'] ?? '') . '</td>
    </tr>
    <tr>
        <td>To Accountable Officer/Agency/Fund Cluster: ' . htmlspecialchars($itr['to_accountable_officer'] ?? '') . '</td>
        <td>Date:</td>
        <td>' . ($itr['date'] ? date('m/d/Y', strtotime($itr['date'])) : '') . '</td>
    </tr>
    <tr>
        <td style="width: 50%;">
            Transfer Type: (check only)<br/>
            <span class="checkbox ' . (($itr['transfer_type'] ?? '') === 'Donation' ? 'checked' : '') . '">✓</span> Donation &nbsp;&nbsp;
            <span class="checkbox ' . (($itr['transfer_type'] ?? '') === 'Relocate' ? 'checked' : '') . '">✓</span> Relocate<br/>
            <span class="checkbox ' . (($itr['transfer_type'] ?? '') === 'Reassignment' ? 'checked' : '') . '">✓</span> Reassignment &nbsp;&nbsp;
            <span class="checkbox ' . ((!in_array($itr['transfer_type'] ?? '', ['Donation', 'Relocate', 'Reassignment']) && !empty($itr['transfer_type'])) ? 'checked' : '') . '">✓</span> Others (Specify): ' . ((!in_array($itr['transfer_type'] ?? '', ['Donation', 'Relocate', 'Reassignment']) && !empty($itr['transfer_type'])) ? htmlspecialchars($itr['transfer_type']) : '___________') . '
        </td>
        <td colspan="2"></td>
    </tr>
</table>';

$html .= '
<table class="items-table">
    <tr>
        <th style="width: 12%;">Date Acquired</th>
        <th style="width: 8%;">Item No.</th>
        <th style="width: 15%;">ICS & PAR No./Date</th>
        <th style="width: 25%;">Description</th>
        <th style="width: 12%;">Unit Price</th>
        <th style="width: 12%;">Total Amount</th>
        <th style="width: 16%;">Condition of Inventory</th>
    </tr>';

$itemCount = 1;
foreach ($items as $item) {
    $dateAcquired = !empty($item['date_acquired']) ? date('m/d/Y', strtotime($item['date_acquired'])) : '';
    $propertyNo = htmlspecialchars($item['property_no'] ?? '');
    $description = htmlspecialchars($item['description'] ?? '');
    $unitPrice = isset($item['amount']) && is_numeric($item['amount']) ? number_format(floatval($item['amount']), 2) : number_format(0,2);
    $totalItemAmount = $unitPrice;
    $condition = htmlspecialchars($item['condition_of_PPE'] ?? '');

    $html .= '
    <tr>
        <td style="text-align: center;">' . $dateAcquired . '</td>
        <td style="text-align: center;">' . $itemCount . '</td>
        <td>' . $propertyNo . '</td>
        <td>' . $description . '</td>
        <td style="text-align: right;">' . $unitPrice . '</td>
        <td style="text-align: right;">' . $totalItemAmount . '</td>
        <td style="text-align: center;">' . $condition . '</td>
    </tr>';

    $itemCount++;
}

// NOTHING FOLLOWS row if items exist
if (!empty($items)) {
    $html .= '
    <tr>
        <td></td><td></td><td></td>
        <td style="text-align: center; font-style: italic; padding: 6px 0;">— NOTHING FOLLOWS —</td>
        <td></td><td></td><td></td>
    </tr>';
}

// Fill to 15 rows for consistent layout
for ($i = $itemCount; $i <= 15; $i++) {
    $html .= '
    <tr>
        <td style="height: 20px;">&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>';
}

$html .= '</table>

<table>
    <tr>
        <td style="font-weight: bold;">Reason/s for Transfer:</td>
    </tr>
    <tr>
        <td style="height: 30px; vertical-align: top;">' . htmlspecialchars($itr['reason_for_transfer'] ?? 'USED FOR PRINTING, PPT AND OTHER PURPOSES') . '</td>
    </tr>
</table>

<div style="height: 20px;"></div>

<table style="width:100%; border:1px solid #000; border-collapse:collapse; margin-top:10px;">
  <tr style="text-align:center; font-weight:bold;">
    <td style="width:33.33%;">Approved by:</td>
    <td style="width:33.33%;">Released/Issued by:</td>
    <td style="width:33.33%;">Received by:</td>
  </tr>

  <tr>
    <td colspan="3" style="height:25px;">Signature:</td>
  </tr>

  <tr>
    <td style="width:33.33%;"><b>Printed Name:</b> ' . htmlspecialchars($itr['approved_by'] ?? '') . '</td>
   <td style="width:33.33%; text-align:center; vertical-align:middle;">' . htmlspecialchars($itr['released_by'] ?? '') . '</td>
<td style="width:33.33%; text-align:center; vertical-align:middle;">' . htmlspecialchars($itr['received_by'] ?? '') . '</td>

  </tr>

  <tr>
    <td><b>Designation:</b> ' . htmlspecialchars($itr['approved_designation'] ?? 'PROPERTY CUSTODIAN') . '</td>
    <td style="text-align:center; vertical-align:middle;"><b></b> ' . htmlspecialchars($itr['released_designation'] ?? '') . '</td>
<td style="text-align:center; vertical-align:middle;"><b></b> ' . htmlspecialchars($itr['received_designation'] ?? '') . '</td>

  </tr>

  <tr>
    <td><b>Date:</b></td>
    <td style="text-align:center;">' . ($itr['released_date'] ? date('n/j/Y', strtotime($itr['released_date'])) : '') . '</td>
    <td></td>
  </tr>
</table>

</body>
</html>';

// ---- Render PDF ----
try {
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Log ITR PDF generation
    $itr_number = $itr['itr_no'] ?? 'Unknown';
    $entity_name = $itr['entity_name'] ?? 'Unknown Entity';
    logReportActivity('ITR PDF', "ITR: {$itr_number}, Entity: {$entity_name}");

    // make a safe filename
    $safeItrNo = preg_replace('/[^A-Za-z0-9_\-]/', '_', ($itr['itr_no'] ?: 'ITR_' . $itr_id));
    $dompdf->stream("ITR_{$safeItrNo}.pdf", ["Attachment" => false]);
} catch (Exception $e) {
    error_log("Dompdf error: " . $e->getMessage());
    die("An error occurred while generating the PDF.");
}
