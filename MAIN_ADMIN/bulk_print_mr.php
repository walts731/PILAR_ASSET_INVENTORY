<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get selected MR IDs from URL parameter
$selected_ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($selected_ids)) {
    die('No MR records selected for printing.');
}

// Convert comma-separated IDs to array and sanitize
$id_array = array_map('intval', explode(',', $selected_ids));
$id_placeholders = str_repeat('?,', count($id_array) - 1) . '?';

// Fetch selected MR details
$sql = "SELECT * FROM mr_details WHERE mr_id IN ($id_placeholders) ORDER BY mr_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('i', count($id_array)), ...$id_array);
$stmt->execute();
$result = $stmt->get_result();

$mr_records = [];
while ($row = $result->fetch_assoc()) {
    $mr_records[] = $row;
}
$stmt->close();

if (empty($mr_records)) {
    die('No MR records found.');
}

// Log bulk MR printing
$mr_count = count($mr_records);
logBulkActivity('PRINT', $mr_count, 'MR Records');

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

// Function to get QR code data for an asset
function getQRData($asset_id, $conn) {
    if (!$asset_id) return "";
    
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
            return 'data:image/png;base64,' . $imageData;
        }
    }
    return "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Print MR Records</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
        
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 0; 
            padding: 10px;
            font-size: 12px;
        }
        
        .print-controls {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .mr-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .mr-record {
            border: 2px solid #000;
            padding: 10px;
            background-color: white;
            min-height: 350px;
            box-sizing: border-box;
            font-size: 10px;
        }
        
        .mr-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            position: relative;
        }
        
        .mr-logo {
            width: 50px;
            height: 50px;
        }
        
        .mr-title {
            text-align: center;
            flex: 1;
            margin: 0 10px;
        }
        
        .mr-title h3 {
            margin: 2px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .mr-title .office-location {
            font-size: 9px;
            margin-top: 5px;
        }
        
        .mr-tag-section {
            text-align: center;
            width: 80px;
        }
        
        .mr-tag-section .tag-text {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .mr-qr {
            width: 50px;
            height: 50px;
        }
        
        .mr-line {
            border-bottom: 1px solid #000;
            margin: 8px 0;
        }
        
        .mr-field {
            margin-bottom: 6px;
            font-size: 9px;
        }
        
        .mr-field-label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        
        .mr-field-value {
            text-decoration: underline;
        }
        
        .mr-checkbox-section {
            margin: 8px 0;
        }
        
        .mr-checkbox {
            margin-right: 15px;
            font-size: 9px;
        }
        
        .mr-dates {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 9px;
        }
        
        .mr-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 8px;
        }
        
        .mr-signature {
            text-align: center;
            width: 45%;
        }
        
        .mr-signature-line {
            border-top: 1px solid #000;
            margin-bottom: 3px;
            height: 20px;
        }
        
        @media print {
            .mr-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .mr-record {
                min-height: 330px;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <h4><i class="bi bi-printer"></i> Bulk Print MR Records</h4>
        <p>Printing <?= count($mr_records) ?> MR record(s)</p>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Now
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Close
        </button>
    </div>

    <div class="mr-container">
        <?php foreach ($mr_records as $index => $mr): ?>
            <?php
                $inventory_tag = $mr['inventory_tag'] ?: "No Inventory Tag";
                $serviceableChecked = ($mr['serviceable'] == 1) ? '☑' : '☐';
                $unserviceableChecked = ($mr['unserviceable'] == 1) ? '☑' : '☐';
                $qrData = getQRData($mr['asset_id'], $conn);
            ?>
            <div class="mr-record">
                <div class="mr-header">
                    <?php if ($logoData): ?>
                        <img src="<?= $logoData ?>" alt="Municipal Logo" class="mr-logo">
                    <?php endif; ?>
                    
                    <div class="mr-title">
                        <h3>GOVERNMENT PROPERTY</h3>
                        <div class="office-location">
                            Office/Location: <span class="mr-field-value"><?= htmlspecialchars($mr['office_location']) ?></span>
                        </div>
                    </div>
                    
                    <div class="mr-tag-section">
                        <div class="tag-text">
                            No. <span class="mr-field-value"><?= htmlspecialchars($inventory_tag) ?></span><br>
                            INVENTORY TAG
                        </div>
                        <?php if ($qrData): ?>
                            <img src="<?= $qrData ?>" alt="QR Code" class="mr-qr">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mr-line"></div>

                <div class="mr-field">
                    <span class="mr-field-label">Description of the property:</span>
                    <span class="mr-field-value"><?= htmlspecialchars($mr['description']) ?></span>
                </div>

                <div class="mr-field">
                    <span class="mr-field-label">Model No.:</span>
                    <span class="mr-field-value"><?= htmlspecialchars($mr['model_no']) ?></span>
                    &nbsp;&nbsp;
                    Serial No.: <span class="mr-field-value"><?= htmlspecialchars($mr['serial_no']) ?></span>
                </div>

                <div class="mr-checkbox-section">
                    <span class="mr-checkbox"><?= $serviceableChecked ?> Serviceable</span>
                    <span class="mr-checkbox"><?= $unserviceableChecked ?> Unserviceable</span>
                </div>

                <div class="mr-field">
                    <span class="mr-field-label">Unit/Quantity:</span>
                    <span class="mr-field-value"><?= htmlspecialchars($mr['unit_quantity']) ?> <?= htmlspecialchars($mr['unit']) ?></span>
                    &nbsp;&nbsp;
                    Acquisition Date/Cost: <span class="mr-field-value"><?= htmlspecialchars($mr['acquisition_date']) ?> / ₱<?= htmlspecialchars($mr['acquisition_cost']) ?></span>
                </div>

                <div class="mr-field">
                    <span class="mr-field-label">Person Accountable:</span>
                    <span class="mr-field-value"><?= htmlspecialchars($mr['person_accountable']) ?></span>
                </div>

                <div class="mr-dates">
                    <div>Date: (acquired) <span class="mr-field-value"><?= htmlspecialchars($mr['acquired_date']) ?></span></div>
                    <div>Date: (counted) <span class="mr-field-value"><?= htmlspecialchars($mr['counted_date']) ?></span></div>
                </div>

                <div class="mr-signatures">
                    <div class="mr-signature">
                        <div class="mr-signature-line"></div>
                        COA REPRESENTATIVE
                    </div>
                    <div class="mr-signature">
                        <div class="mr-signature-line"></div>
                        Signature of the Inventory Committee
                    </div>
                </div>
            </div>
            
            <?php 
            // Add page break after every 4 MR records (2x2 grid)
            if (($index + 1) % 4 === 0 && $index + 1 < count($mr_records)): 
            ?>
                <div class="page-break"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); };
        
        // Close window after printing
        window.onafterprint = function() {
            // Uncomment the line below if you want to auto-close after printing
            // window.close();
        };
    </script>
</body>
</html>
