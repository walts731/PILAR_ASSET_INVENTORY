<?php
require_once '../vendor/autoload.php';
require_once '../connect.php';
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

$asset_id_param = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : null;
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : null;

// Resolve MR details by asset_id (preferred) or by item_id
if ($asset_id_param || $item_id) {
    $mr_details = null;
    if ($asset_id_param) {
        $stmt = $conn->prepare("SELECT * FROM mr_details WHERE asset_id = ? LIMIT 1");
        $stmt->bind_param("i", $asset_id_param);
        $stmt->execute();
        $result = $stmt->get_result();
        $mr_details = $result->fetch_assoc();
        $stmt->close();
        // If still not found and we have an asset_id, try to resolve item_id via ics_items mapping
        if (!$mr_details) {
            $stmt = $conn->prepare("SELECT item_id FROM ics_items WHERE asset_id = ? ORDER BY item_id ASC LIMIT 1");
            $stmt->bind_param("i", $asset_id_param);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $item_id = (int)$row['item_id'];
            }
            $stmt->close();
        }
    }
    if (!$mr_details && $item_id) {
        $stmt = $conn->prepare("SELECT * FROM mr_details WHERE item_id = ? LIMIT 1");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $mr_details = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$mr_details) {
        die("MR record not found.");
    }

    // Fetch logo
    $stmt_logo = $conn->prepare("SELECT logo FROM system WHERE id = 1");
    $stmt_logo->execute();
    $result_logo = $stmt_logo->get_result();
    $system_logo = $result_logo->fetch_assoc();
    $stmt_logo->close();

    $logoData = "";
    if ($system_logo && !empty($system_logo['logo'])) {
        $logoPath = $system_logo['logo'];
        $imagePath = realpath(__DIR__ . '/../img/' . $logoPath);
        if ($imagePath && file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $logoData = 'data:image/png;base64,' . $imageData;
        }
    }

    // Fetch QR code and also get property_no, inventory_tag fallback from assets
    $asset_id = $mr_details['asset_id'];
    $stmt_qr = $conn->prepare("SELECT qr_code, property_no, inventory_tag FROM assets WHERE id = ?");
    $stmt_qr->bind_param("i", $asset_id);
    $stmt_qr->execute();
    $result_qr = $stmt_qr->get_result();
    $asset_row = $result_qr->fetch_assoc();
    $stmt_qr->close();

    $qrData = "";
    if ($asset_row && !empty($asset_row['qr_code'])) {
        $qrPath = $asset_row['qr_code'];
        $imagePath = realpath(__DIR__ . '/../img/' . $qrPath);
        if ($imagePath && file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $qrData = 'data:image/png;base64,' . $imageData;
        }
    }

    // Fetch inventory tag
    $inventory_tag = !empty($mr_details['inventory_tag']) ? $mr_details['inventory_tag'] : ($asset_row['inventory_tag'] ?? "No Inventory Tag");

    // Prepare checked status for checkboxes
    $serviceableChecked = ($mr_details['serviceable'] == 1) ? 'checked' : '';
    $unserviceableChecked = ($mr_details['unserviceable'] == 1) ? 'checked' : '';

    // Normalize dates to avoid showing placeholders like 0-0-0-0-0 or 0000-00-00
    $formatDateSafe = function($val) {
        $v = trim((string)($val ?? ''));
        if ($v === '' || $v === '0000-00-00') { return ''; }
        // Common garbage pattern sometimes saved
        if ($v === '0-0-0-0-0' || $v === '0-0-0' || $v === '00-00-0000') { return ''; }
        $ts = strtotime($v);
        if ($ts && $ts > 0) { return date('Y-m-d', $ts); }
        // If unparsable but not obviously junk, return as-is
        return htmlspecialchars($v);
    };
    $acquired_disp = $formatDateSafe($mr_details['acquired_date'] ?? '');
    $counted_disp  = $formatDateSafe($mr_details['counted_date'] ?? '');

    // Prepare Model No. with blank underline if empty
    $model_raw = trim((string)($mr_details['model_no'] ?? ''));
    $model_disp = ($model_raw !== '') ? htmlspecialchars($model_raw) : str_repeat('&nbsp;', 12);

    // HTML structure for the property sticker
    $html = "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; margin: 0; padding: 0; }
        .container { width: 700px; border: 1px solid #000; padding: 10px; position: relative; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .logo { position: absolute; top: 0; left: 0; }
        .header-title { font-weight: bold; font-size: 16px; margin-top: 10px; text-align: center; flex: 1; }
        .header-right { position: absolute; top: 0; right: 0; text-align: center; }
        .header-right .tag-text { font-size: 10px; font-weight: bold; margin-bottom: 5px; }
        .header-right img { height: 60px; }
        .line { border-bottom: 1px solid #000; margin: 6px 0; }
        .field-label { display: inline-block; width: 180px; }
        .row { display: flex; justify-content: space-between; margin-top: 10px; }
        .col { width: 48%; text-align: left; }
        .signature-space { border-top: 1px solid #000; margin-top: 25px; }
        .bold { font-weight: bold; }
        .inline-date { display: inline-block; width: 48%; }
        .inline-signature { display: inline-block; width: 48%; text-align: center; }
        input[type='checkbox'] {
            width: 14px;
            height: 14px;
            margin-right: 5px;
            accent-color: black; /* For supported browsers */
        }
        u { text-decoration: underline; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <!-- Municipal Logo (Left) -->
            <div class='logo'>
                <img src='" . $logoData . "' alt='Municipal Logo' style='height: 60px;' />
            </div>
                
            <!-- Title & Office Location -->
            <div class='header-title'>
                <h2>GOVERNMENT PROPERTY</h2>
                <div class='office-under-title'>Office/Location: <u>" . htmlspecialchars($mr_details['office_location']) . "</u></div>
            </div>
                
            <!-- Tag Info & QR -->
            <div class='header-right'>
                <div class='tag-text'>No. <u>" . htmlspecialchars($inventory_tag) . "</u><br>INVENTORY TAG</div>
                <img src='" . $qrData . "' alt='QR Code' />
            </div>
        </div>

        <div class='line'></div>

        <p><span class='field-label'>Description of the property:</span> <u>" . htmlspecialchars($mr_details['description']) . "</u></p>
        <p><span class='field-label'>Model No.:</span><u>" . ($model_disp) . "</u> &nbsp;&nbsp;&nbsp; Serial No.: <u>" . htmlspecialchars($mr_details['serial_no']) . "</u></p>
        
        <p>
            <input type='checkbox' $serviceableChecked> Serviceable
            &nbsp;&nbsp;&nbsp;
            <input type='checkbox' $unserviceableChecked> Unserviceable
        </p>

        <p><span class='field-label'>Unit/Quantity:</span> <u>" . htmlspecialchars($mr_details['unit_quantity']) . " " . htmlspecialchars($mr_details['unit']) . "</u> &nbsp;&nbsp; Acquisition Date/Cost: <u>" . htmlspecialchars($mr_details['acquisition_date']) . " / ₱ " . htmlspecialchars($mr_details['acquisition_cost']) . "</u></p>
        <p><span class='field-label'>Person Accountable:</span> <u>" . htmlspecialchars($mr_details['person_accountable']) . "</u></p>

        <!-- Date Section (Inline) -->
        <div class='row'>
            <div class='inline-date'>Date: (acquired) &nbsp;&nbsp; <u>" . ($acquired_disp !== '' ? $acquired_disp : str_repeat('&nbsp;', 12)) . "</u></div>
            <div class='inline-date'>Date: (counted) &nbsp;&nbsp; <u>" . ($counted_disp !== '' ? $counted_disp : str_repeat('&nbsp;', 12)) . "</u></div>
        </div>

        <!-- Signature Section (Inline) -->
        <div class='row' style='margin-top: 40px;'>
            <div class='inline-signature'>
                <div class='signature-space'></div>
                COA REPRESENTATIVE
            </div>
            <div class='inline-signature'>
                <div class='signature-space'></div>
                Signature of the Inventory Committee
            </div>
        </div>
    </div>
</body>
</html>
";


    // Generate PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    // Use default Dompdf font paths to avoid invalid placeholders
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A5', 'landscape'); // Tag size
    $dompdf->render();

    // Save PDF to generated_reports directory and log it
    try {
        $output = $dompdf->output();

        // Ensure directory exists
        $reportsDir = __DIR__ . '/../generated_reports';
        if (!is_dir($reportsDir)) {
            @mkdir($reportsDir, 0755, true);
        }

        // Build filename: MR_Tag_{mr_id or item_id}_{timestamp}.pdf
        $mrId = isset($mr_details['mr_id']) ? (int)$mr_details['mr_id'] : null;
        $stamp = date('Ymd_His');
        $fileBase = $mrId ? "MR_Tag_{$mrId}_{$stamp}.pdf" : (isset($item_id) && $item_id ? "MR_Tag_ITEM_{$item_id}_{$stamp}.pdf" : "MR_Tag_{$stamp}.pdf");
        $filePath = $reportsDir . DIRECTORY_SEPARATOR . $fileBase;

        // Write file
        file_put_contents($filePath, $output);

        // Insert into generated_reports (if table exists)
        if (isset($_SESSION) && isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];
            $office_id = isset($_SESSION['office_id']) ? (int)$_SESSION['office_id'] : null;
            // Use relative path from project root for filename, as in other exports
            $filenameForDb = 'generated_reports/' . $fileBase;
            if ($stmtIns = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())")) {
                $stmtIns->bind_param('iis', $user_id, $office_id, $filenameForDb);
                $stmtIns->execute();
                $stmtIns->close();
            }
        }
    } catch (Throwable $e) {
        // Non-fatal: continue to stream to browser even if save/log fails
    }

    // Stream to browser
    $streamName = isset($mrId) && $mrId ? ("mr_tag_{$mrId}.pdf") : (isset($item_id) && $item_id ? "inventory_tag_{$item_id}.pdf" : "mr_tag.pdf");
    $dompdf->stream($streamName, ["Attachment" => 0]);
} else {
    echo "Item ID not provided.";
}
?>
