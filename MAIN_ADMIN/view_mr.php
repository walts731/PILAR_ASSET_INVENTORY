<?php
require_once '../vendor/autoload.php';
require_once '../connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$mr_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($mr_id) {
    // Fetch MR details
    $stmt = $conn->prepare("SELECT * FROM mr_details WHERE mr_id = ?");
    $stmt->bind_param("i", $mr_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mr_details = $result->fetch_assoc();
    $stmt->close();

    if (!$mr_details) {
        die("MR record not found.");
    }

    // Fetch system logo
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

    // Fetch QR code from assets table
    $asset_id = $mr_details['asset_id'];
    $qrData = "";
    if ($asset_id) {
        $stmt_qr = $conn->prepare("SELECT qr_code FROM assets WHERE id = ?");
        $stmt_qr->bind_param("i", $asset_id);
        $stmt_qr->execute();
        $result_qr = $stmt_qr->get_result();
        $asset_qr_code = $result_qr->fetch_assoc();
        $stmt_qr->close();

        if ($asset_qr_code && !empty($asset_qr_code['qr_code'])) {
            $qrPath = $asset_qr_code['qr_code'];
            $imagePath = realpath(__DIR__ . '/../img/' . $qrPath);
            if ($imagePath && file_exists($imagePath)) {
                $imageData = base64_encode(file_get_contents($imagePath));
                $qrData = 'data:image/png;base64,' . $imageData;
            }
        }
    }

    // Inventory tag fallback
    $inventory_tag = $mr_details['inventory_tag'] ?: "No Inventory Tag";

    // Checkbox status
    $serviceableChecked   = ($mr_details['serviceable'] == 1) ? 'checked' : '';
    $unserviceableChecked = ($mr_details['unserviceable'] == 1) ? 'checked' : '';

    // HTML for PDF
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
            accent-color: black;
        }
        u { text-decoration: underline; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='logo'>
                <img src='" . $logoData . "' alt='Municipal Logo' style='height: 60px;' />
            </div>
            <div class='header-title'>
                <h2>GOVERNMENT PROPERTY</h2>
                <div class='office-under-title'>Office/Location: <u>" . htmlspecialchars($mr_details['office_location']) . "</u></div>
            </div>
            <div class='header-right'>
                <div class='tag-text'>No. <u>" . htmlspecialchars($inventory_tag) . "</u><br>INVENTORY TAG</div>
                <img src='" . $qrData . "' alt='QR Code' />
            </div>
        </div>

        <div class='line'></div>

        <p><span class='field-label'>Description of the property:</span> <u>" . htmlspecialchars($mr_details['description']) . "</u></p>
        <p><span class='field-label'>Model No.:</span> <u>" . htmlspecialchars($mr_details['model_no']) . "</u> &nbsp;&nbsp;&nbsp; Serial No.: <u>" . htmlspecialchars($mr_details['serial_no']) . "</u></p>
        
        <p>
            <input type='checkbox' $serviceableChecked> Serviceable
            &nbsp;&nbsp;&nbsp;
            <input type='checkbox' $unserviceableChecked> Unserviceable
        </p>

        <p><span class='field-label'>Unit/Quantity:</span> <u>" . htmlspecialchars($mr_details['unit_quantity']) . " " . htmlspecialchars($mr_details['unit']) . "</u> &nbsp;&nbsp; Acquisition Date/Cost: <u>" . htmlspecialchars($mr_details['acquisition_date']) . " / ₱ " . htmlspecialchars($mr_details['acquisition_cost']) . "</u></p>
        <p><span class='field-label'>Person Accountable:</span> <u>" . htmlspecialchars($mr_details['person_accountable']) . "</u></p>

        <div class='row'>
            <div class='inline-date'>Date: (acquired) &nbsp;&nbsp; <u>" . htmlspecialchars($mr_details['acquired_date']) . "</u></div>
            <div class='inline-date'>Date: (counted) &nbsp;&nbsp; <u>" . htmlspecialchars($mr_details['counted_date']) . "</u></div>
        </div>

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
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A5', 'landscape');
    $dompdf->render();
    $dompdf->stream("inventory_tag_{$mr_id}.pdf", ["Attachment" => 0]);

} else {
    echo "MR ID not provided.";
}
?>
