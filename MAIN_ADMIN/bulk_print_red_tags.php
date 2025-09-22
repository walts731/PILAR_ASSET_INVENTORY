<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get selected red tag IDs from URL parameter
$selected_ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($selected_ids)) {
    die('No red tags selected for printing.');
}

// Convert comma-separated IDs to array and sanitize
$id_array = array_map('intval', explode(',', $selected_ids));
$id_placeholders = str_repeat('?,', count($id_array) - 1) . '?';

// Fetch selected red tag details with related information
$sql = "SELECT rt.*, 
               u.fullname as tagged_by_name,
               a.property_no, a.description as asset_description, a.acquisition_date,
               ii.particulars, ii.dept_office
        FROM red_tags rt
        LEFT JOIN users u ON rt.tagged_by = u.id
        LEFT JOIN assets a ON rt.asset_id = a.id
        LEFT JOIN iirup_items ii ON rt.asset_id = ii.asset_id AND rt.iirup_id = ii.iirup_id
        WHERE rt.id IN ($id_placeholders)
        ORDER BY rt.red_tag_number ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('i', count($id_array)), ...$id_array);
$stmt->execute();
$result = $stmt->get_result();

$red_tags = [];
while ($row = $result->fetch_assoc()) {
    $red_tags[] = $row;
}
$stmt->close();

if (empty($red_tags)) {
    die('No red tag records found.');
}

// Log bulk red tag printing
$red_tag_count = count($red_tags);
logBulkActivity('PRINT', $red_tag_count, 'Red Tags');

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Print Red Tags</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
        
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 10px;
            font-size: 11px;
        }
        
        .print-controls {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .red-tag-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .red-tag {
            border: 2px solid #dc3545;
            padding: 10px;
            background-color: white;
            min-height: 300px;
            box-sizing: border-box;
        }
        
        .red-tag-header {
            text-align: center;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        
        .red-tag-header .logo {
            width: 40px;
            height: 40px;
            float: left;
        }
        
        .red-tag-header .title {
            text-align: center;
            margin: 0 50px;
        }
        
        .red-tag-header .title h3 {
            color: #dc3545;
            margin: 2px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .red-tag-header .title p {
            margin: 1px 0;
            font-size: 9px;
        }
        
        .red-tag-header .red-tag-no {
            float: right;
            text-align: right;
            font-weight: bold;
            font-size: 10px;
        }
        
        .red-tag-content {
            clear: both;
            margin-top: 10px;
        }
        
        .field-row {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
        }
        
        .field-label {
            font-weight: bold;
            width: 80px;
            margin-right: 5px;
            font-size: 9px;
        }
        
        .field-value {
            flex: 1;
            border-bottom: 1px solid #ccc;
            padding-bottom: 1px;
            min-height: 12px;
            font-size: 9px;
        }
        
        .description-field {
            min-height: 30px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }
        
        .status-pending { background-color: #ffc107; color: #000; }
        .status-completed { background-color: #28a745; }
        .status-in-progress { background-color: #007bff; }
        
        .reason-badge, .action-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        @media print {
            .red-tag-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .red-tag {
                min-height: 280px;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <h4><i class="bi bi-printer"></i> Bulk Print Red Tags</h4>
        <p>Printing <?= count($red_tags) ?> red tag(s)</p>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Now
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Close
        </button>
    </div>

    <div class="red-tag-container">
        <?php foreach ($red_tags as $index => $red_tag): ?>
            <div class="red-tag">
                <div class="red-tag-header">
                    <?php if ($logoData): ?>
                        <img src="<?= $logoData ?>" alt="Logo" class="logo">
                    <?php endif; ?>
                    
                    <div class="title">
                        <p>Republic of the Philippines</p>
                        <p>Province of Sorsogon</p>
                        <p>Municipality of Pilar</p>
                        <h3>5S RED TAG</h3>
                    </div>
                    
                    <div class="red-tag-no">
                        <div>Red Tag No.:</div>
                        <div><?= htmlspecialchars($red_tag['red_tag_number']) ?></div>
                    </div>
                    
                    <div style="clear: both;"></div>
                </div>

                <div class="red-tag-content">
                    <div class="field-row">
                        <div class="field-label">Control No.:</div>
                        <div class="field-value"><?= htmlspecialchars($red_tag['red_tag_number']) ?></div>
                    </div>

                    <div class="field-row">
                        <div class="field-label">Date:</div>
                        <div class="field-value"><?= date('F d, Y', strtotime($red_tag['date_received'])) ?></div>
                    </div>

                    <div class="field-row">
                        <div class="field-label">Tagged By:</div>
                        <div class="field-value"><?= htmlspecialchars($red_tag['tagged_by_name'] ?? 'N/A') ?></div>
                    </div>

                    <div class="field-row">
                        <div class="field-label">Property No.:</div>
                        <div class="field-value"><?= htmlspecialchars($red_tag['property_no'] ?? 'N/A') ?></div>
                    </div>

                    <div class="field-row">
                        <div class="field-label">Location:</div>
                        <div class="field-value"><?= htmlspecialchars($red_tag['item_location']) ?></div>
                    </div>

                    <div class="field-row">
                        <div class="field-label">Description:</div>
                        <div class="field-value description-field"><?= htmlspecialchars($red_tag['description']) ?></div>
                    </div>

                    <div class="field-row">
                        <div class="field-label">Reason:</div>
                        <div class="field-value">
                            <span class="reason-badge"><?= htmlspecialchars($red_tag['removal_reason']) ?></span>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-label">Action:</div>
                        <div class="field-value">
                            <span class="action-badge"><?= htmlspecialchars($red_tag['action']) ?></span>
                        </div>
                    </div>

                </div>
            </div>
            
            <?php 
            // Add page break after every 4 red tags (2x2 grid)
            if (($index + 1) % 4 === 0 && $index + 1 < count($red_tags)): 
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
