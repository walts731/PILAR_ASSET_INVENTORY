<?php
require_once '../vendor/autoload.php';
require_once '../connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$red_tag_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($red_tag_id) {
    // Fetch Red Tag details with related information
    $stmt = $conn->prepare("
        SELECT rt.*, 
               u.fullname as tagged_by_name,
               a.property_no, a.description as asset_description, a.acquisition_date,
               ii.particulars, ii.dept_office
        FROM red_tags rt
        LEFT JOIN users u ON rt.tagged_by = u.id
        LEFT JOIN assets a ON rt.asset_id = a.id
        LEFT JOIN iirup_items ii ON rt.asset_id = ii.asset_id AND rt.iirup_id = ii.iirup_id
        WHERE rt.id = ?
    ");
    $stmt->bind_param("i", $red_tag_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $red_tag_details = $result->fetch_assoc();
    $stmt->close();

    if (!$red_tag_details) {
        die("Red Tag record not found.");
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
    $asset_id = $red_tag_details['asset_id'];
    $qrData = "";
    if ($asset_id) {
        $stmt_qr = $conn->prepare("SELECT qr_code FROM assets WHERE id = ?");
        $stmt_qr->bind_param("i", $asset_id);
        $stmt_qr->execute();
        $result_qr = $stmt_qr->get_result();
        $asset_qr_code = $result_qr->fetch_assoc();
        $stmt_qr->close();

        if ($asset_qr_code && !empty($asset_qr_code['qr_code'])) {
            $qrCodePath = $asset_qr_code['qr_code'];
            $qrImagePath = realpath(__DIR__ . '/../' . $qrCodePath);
            if ($qrImagePath && file_exists($qrImagePath)) {
                $qrImageData = base64_encode(file_get_contents($qrImagePath));
                $qrData = 'data:image/png;base64,' . $qrImageData;
            }
        }
    }

    // Generate PDF
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px;
                font-size: 12px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #dc3545;
                padding-bottom: 15px;
            }
            .header-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .logo {
                width: 80px;
                height: 80px;
            }
            .title {
                flex-grow: 1;
                text-align: center;
            }
            .title h1 {
                color: #dc3545;
                margin: 5px 0;
                font-size: 24px;
                font-weight: bold;
            }
            .title p {
                margin: 2px 0;
                font-size: 14px;
            }
            .red-tag-no {
                text-align: right;
                font-weight: bold;
                font-size: 14px;
            }
            .content {
                margin-top: 20px;
            }
            .field-group {
                margin-bottom: 15px;
                display: flex;
                align-items: flex-start;
            }
            .field-label {
                font-weight: bold;
                width: 150px;
                margin-right: 10px;
            }
            .field-value {
                flex: 1;
                border-bottom: 1px solid #ccc;
                padding-bottom: 2px;
                min-height: 20px;
            }
            .description-field {
                min-height: 60px;
                vertical-align: top;
            }
            .qr-code {
                text-align: center;
                margin-top: 30px;
            }
            .qr-code img {
                width: 100px;
                height: 100px;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: bold;
                color: white;
            }
            .status-pending { background-color: #ffc107; color: #000; }
            .status-completed { background-color: #28a745; }
            .status-in-progress { background-color: #007bff; }
            .reason-badge {
                display: inline-block;
                padding: 3px 6px;
                border-radius: 3px;
                font-size: 10px;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
            }
            .action-badge {
                display: inline-block;
                padding: 3px 6px;
                border-radius: 3px;
                font-size: 10px;
                background-color: #e9ecef;
                border: 1px solid #ced4da;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="header-content">
                <div class="logo">';
    
    if ($logoData) {
        $html .= '<img src="' . $logoData . '" alt="Logo" style="width: 80px; height: 80px;">';
    }
    
    $html .= '</div>
                <div class="title">
                    <p>Republic of the Philippines</p>
                    <p>Province of Sorsogon</p>
                    <p>Municipality of Pilar</p>
                    <h1>5S RED TAG</h1>
                </div>
                <div class="red-tag-no">
                    <div>Red Tag No.:</div>
                    <div>' . htmlspecialchars($red_tag_details['red_tag_number']) . '</div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="field-group">
                <div class="field-label">Control No.:</div>
                <div class="field-value">' . htmlspecialchars($red_tag_details['red_tag_number']) . '</div>
            </div>

            <div class="field-group">
                <div class="field-label">Date Received:</div>
                <div class="field-value">' . date('F d, Y', strtotime($red_tag_details['date_received'])) . '</div>
            </div>

            <div class="field-group">
                <div class="field-label">Tagged By:</div>
                <div class="field-value">' . htmlspecialchars($red_tag_details['tagged_by_name'] ?? 'N/A') . '</div>
            </div>

            <div class="field-group">
                <div class="field-label">Property No.:</div>
                <div class="field-value">' . htmlspecialchars($red_tag_details['property_no'] ?? 'N/A') . '</div>
            </div>

            <div class="field-group">
                <div class="field-label">Item Location:</div>
                <div class="field-value">' . htmlspecialchars($red_tag_details['item_location']) . '</div>
            </div>

            <div class="field-group">
                <div class="field-label">Description:</div>
                <div class="field-value description-field">' . htmlspecialchars($red_tag_details['description']) . '</div>
            </div>

            <div class="field-group">
                <div class="field-label">Removal Reason:</div>
                <div class="field-value">
                    <span class="reason-badge">' . htmlspecialchars($red_tag_details['removal_reason']) . '</span>
                </div>
            </div>

            <div class="field-group">
                <div class="field-label">Action:</div>
                <div class="field-value">
                    <span class="action-badge">' . htmlspecialchars($red_tag_details['action']) . '</span>
                </div>
            </div>

            <div class="field-group">
                <div class="field-label">Status:</div>
                <div class="field-value">';
    
    $status_class = 'status-pending';
    switch(strtolower($red_tag_details['status'])) {
        case 'completed':
            $status_class = 'status-completed';
            break;
        case 'in progress':
            $status_class = 'status-in-progress';
            break;
    }
    
    $html .= '<span class="status-badge ' . $status_class . '">' . htmlspecialchars($red_tag_details['status']) . '</span>
                </div>
            </div>';

    if ($qrData) {
        $html .= '
            <div class="qr-code">
                <p><strong>Asset QR Code:</strong></p>
                <img src="' . $qrData . '" alt="QR Code">
            </div>';
    }

    $html .= '
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output the generated PDF
    $dompdf->stream("Red_Tag_" . $red_tag_details['red_tag_number'] . ".pdf", array("Attachment" => false));
} else {
    die("Invalid Red Tag ID.");
}
?>
