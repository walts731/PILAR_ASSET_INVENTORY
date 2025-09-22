<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get Red Tag ID from URL
$red_tag_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($red_tag_id <= 0) {
    die('Invalid Red Tag ID.');
}

// Fetch the municipal logo from the system table
$logo_path = '';
$stmt_logo = $conn->prepare("SELECT logo FROM system WHERE id = 1");
$stmt_logo->execute();
$result_logo = $stmt_logo->get_result();

if ($result_logo->num_rows > 0) {
    $logo_data = $result_logo->fetch_assoc();
    $logo_path = '../img/' . $logo_data['logo'];
}
$stmt_logo->close();

// Get Red Tag details
$red_tag_query = $conn->prepare("
    SELECT rt.*, u.fullname as tagged_by_name, a.description as asset_description, 
           a.property_no, a.acquisition_date, o.office_name
    FROM red_tags rt
    LEFT JOIN users u ON rt.tagged_by = u.id
    LEFT JOIN assets a ON rt.asset_id = a.id
    LEFT JOIN offices o ON a.office_id = o.id
    WHERE rt.id = ?
");
$red_tag_query->bind_param('i', $red_tag_id);
$red_tag_query->execute();
$red_tag_result = $red_tag_query->get_result();
$red_tag = $red_tag_result->fetch_assoc();
$red_tag_query->close();

if (!$red_tag) {
    die('Red Tag not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Red Tag - <?= htmlspecialchars($red_tag['red_tag_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 5px;
            }
            .red-tag-print {
                page-break-inside: avoid;
                width: 4in;
                height: 3in;
            }
        }
        
        .red-tag-print {
            width: 4in;
            height: 3in;
            margin: 10px auto;
            border: 3px solid #dc3545;
            padding: 8px;
            font-family: Arial, sans-serif;
            background: white;
            font-size: 9px;
            line-height: 1.1;
            overflow: hidden;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
            border-bottom: 1px solid #dc3545;
            padding-bottom: 4px;
        }
        
        .logo img {
            height: 25px;
            width: auto;
        }
        
        .title {
            text-align: center;
            flex-grow: 1;
            margin: 0 4px;
        }
        
        .title div {
            margin: 0;
            font-size: 7px;
            line-height: 1;
        }
        
        .title h2 {
            color: #dc3545;
            font-weight: bold;
            margin: 2px 0;
            font-size: 12px;
            line-height: 1;
        }
        
        .red-tag-no {
            text-align: right;
            font-weight: bold;
            font-size: 8px;
            min-width: 60px;
        }
        
        .red-tag-no div:first-child {
            margin-bottom: 1px;
            font-size: 7px;
        }
        
        .red-tag-no div:last-child {
            color: #dc3545;
            font-size: 9px;
            font-weight: bold;
        }
        
        .form-content {
            display: block;
            margin-bottom: 4px;
        }
        
        .form-item {
            margin-bottom: 3px;
            text-align: left;
        }
        
        .form-label {
            font-weight: bold;
            font-size: 7px;
            color: #333;
            margin: 0;
            line-height: 1;
            text-align: left;
        }
        
        .form-value {
            font-size: 8px;
            border-bottom: 1px solid #666;
            padding: 1px 2px;
            min-height: 12px;
            line-height: 1.2;
            word-wrap: break-word;
            text-align: left;
        }
        
        .full-width {
            width: 100%;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .footer-info {
            margin-top: 2px;
            text-align: center;
            font-size: 6px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 2px;
        }
    </style>
</head>
<body>
    <button class="btn btn-primary print-button no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>
    
    <div class="red-tag-print">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <img src="<?= $logo_path ?>" alt="Municipal Logo">
            </div>
            <div class="title">
                <div>Republic of the Philippines</div>
                <div>Province of Sorsogon</div>
                <div>Municipality of Pilar</div>
                <h2>5S RED TAG</h2>
            </div>
            <div class="red-tag-no">
                <div>Red Tag No.:</div>
                <div><?= htmlspecialchars($red_tag['red_tag_number']) ?></div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="form-content">
            <div class="form-item">
                <div class="form-label">Control No.:</div>
                <div class="form-value"><?= htmlspecialchars($red_tag['red_tag_number']) ?></div>
            </div>
            
            <div class="form-item">
                <div class="form-label">Date Received:</div>
                <div class="form-value"><?= date('m/d/Y', strtotime($red_tag['date_received'])) ?></div>
            </div>
            
            <div class="form-item">
                <div class="form-label">Tagged By:</div>
                <div class="form-value"><?= htmlspecialchars($red_tag['tagged_by_name'] ?? '') ?></div>
            </div>
            
            <div class="form-item">
                <div class="form-label">Item Location:</div>
                <div class="form-value"><?= htmlspecialchars($red_tag['item_location']) ?></div>
            </div>
            
            <div class="form-item">
                <div class="form-label">Description:</div>
                <div class="form-value"><?= htmlspecialchars($red_tag['description']) ?></div>
            </div>
            
            <div class="form-item">
                <div class="form-label">Removal Reason:</div>
                <div class="form-value"><?= htmlspecialchars($red_tag['removal_reason']) ?></div>
            </div>
            
            <div class="form-item">
                <div class="form-label">Action:</div>
                <div class="form-value"><?= htmlspecialchars($red_tag['action']) ?></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-info">
            Generated: <?= date('m/d/Y g:i A') ?> | PILAR ASSET INVENTORY
        </div>

        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
        
        // Close window after printing
        window.onafterprint = function() {
            // window.close();
        }
    </script>
</body>
</html>
