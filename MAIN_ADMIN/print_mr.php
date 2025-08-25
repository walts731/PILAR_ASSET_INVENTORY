<?php
require_once '../vendor/autoload.php';
require_once '../connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : null;

if ($item_id) {
    // Fetch MR details
    $stmt = $conn->prepare("SELECT * FROM mr_details WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mr_details = $result->fetch_assoc();
    $stmt->close();

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

    // Fetch QR code
    $asset_id = $mr_details['asset_id'];
    $stmt_qr = $conn->prepare("SELECT qr_code FROM assets WHERE id = ?");
    $stmt_qr->bind_param("i", $asset_id);
    $stmt_qr->execute();
    $result_qr = $stmt_qr->get_result();
    $asset_qr_code = $result_qr->fetch_assoc();
    $stmt_qr->close();

    $qrData = "";
    if ($asset_qr_code && !empty($asset_qr_code['qr_code'])) {
        $qrPath = $asset_qr_code['qr_code'];
        $imagePath = realpath(__DIR__ . '/../img/' . $qrPath);
        if ($imagePath && file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $qrData = 'data:image/png;base64,' . $imageData;
        }
    }

    // HTML structure for the property sticker
   $html = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 0; }
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
        .inline-date { display: inline-block; width: 48%; } /* For inline dates */
        .inline-signature { display: inline-block; width: 48%; text-align: center; } /* For inline signatures */
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <!-- Municipal Logo (Left) -->
            <div class='logo'>
                <img src='" . $logoData . "' alt='Municipal Logo' style='height: 60px;'>
            </div>
                
            <!-- Title & Office Location -->
            <div class='header-title'>
                <h2>GOVERNMENT PROPERTY</h2>
                <div class='office-under-title'>Office/Location: " . htmlspecialchars($mr_details['office_location']) . "</div>
            </div>
                
            <!-- Tag Info & QR -->
            <div class='header-right'>
                <div class='tag-text'>No. PS-5S-03-F02-01-01<br>INVENTORY TAG</div>
                <img src='" . $qrData . "' alt='QR Code'>
            </div>
        </div>

        <div class='line'></div>

        <p><span class='field-label'>Description of the property:</span> " . htmlspecialchars($mr_details['description']) . "</p>
        <p><span class='field-label'>Model No.:</span> " . htmlspecialchars($mr_details['model_no']) . " &nbsp;&nbsp;&nbsp; Serial No.: " . htmlspecialchars($mr_details['serial_no']) . "</p>
        <p><span class='field-label'>Serviceable:</span> ____________ &nbsp;&nbsp; Unserviceable: ____________</p>
        <p><span class='field-label'>Unit/Quantity:</span> " . htmlspecialchars($mr_details['unit_quantity']) . " " . htmlspecialchars($mr_details['unit']) . " &nbsp;&nbsp; Acquisition Date/Cost: " . htmlspecialchars($mr_details['acquisition_date']) . " / PHP " . htmlspecialchars($mr_details['acquisition_cost']) . "</p>
        <p><span class='field-label'>Person Accountable:</span> " . htmlspecialchars($mr_details['person_accountable']) . "</p>

        <!-- Date Section (Inline) -->
        <div class='row bold'>
            <div class='inline-date'>DATE: (ACQUIRED) &nbsp;&nbsp; " . htmlspecialchars($mr_details['acquired_date']) . "</div>
            <div class='inline-date'>DATE: (COUNTED) &nbsp;&nbsp; " . htmlspecialchars($mr_details['counted_date']) . "</div>
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
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A5', 'landscape'); // Similar to tag size
    $dompdf->render();

    $dompdf->stream("inventory_tag_{$item_id}.pdf", ["Attachment" => 0]);
} else {
    echo "Item ID not provided.";
}
?>
