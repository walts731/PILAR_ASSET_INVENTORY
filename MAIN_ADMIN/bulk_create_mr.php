<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Print mode: render bulk MR print layout when requested
if (isset($_GET['print']) && (int)$_GET['print'] === 1) {
    $idsParam = $_GET['ids'] ?? '';
    $ids = array_values(array_filter(array_map(function($v){
        $n = intval($v);
        return $n > 0 ? $n : null;
    }, preg_split('/[,\s]+/', $idsParam))));

    if (empty($ids)) {
        echo '<script>alert("No assets to print."); window.location.href = "bulk_create_mr.php";</script>';
        exit();
    }

    // Fetch system details
    $sys = ['logo' => '', 'system_title' => 'PILAR'];
    $rs = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
    if ($rs && $rs->num_rows > 0) { $sys = $rs->fetch_assoc(); }

    // Build placeholders list for prepared IN clause
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    // Fetch latest MR details per asset together with asset, office, employee (mirror bulk_print_mr.php data needs)
    $sql = "SELECT 
                a.id AS asset_id,
                a.inventory_tag,
                a.property_no,
                a.status,
                a.qr_code,
                o.office_name,
                e.name AS employee_name,
                md.mr_id,
                md.description AS mr_description,
                md.model_no,
                md.serial_no AS mr_serial_no,
                md.serviceable,
                md.unserviceable,
                md.unit_quantity,
                md.unit,
                md.acquisition_date,
                md.acquisition_cost,
                md.person_accountable,
                md.end_user,
                md.acquired_date,
                md.counted_date
            FROM assets a
            LEFT JOIN offices o ON o.id = a.office_id
            LEFT JOIN employees e ON e.employee_id = a.employee_id
            LEFT JOIN (
                SELECT x.* FROM mr_details x
                INNER JOIN (
                    SELECT asset_id, MAX(mr_id) AS max_id FROM mr_details GROUP BY asset_id
                ) m ON m.asset_id = x.asset_id AND m.max_id = x.mr_id
            ) md ON md.asset_id = a.id
            WHERE a.id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($res && ($r = $res->fetch_assoc())) { $rows[] = $r; }
    $stmt->close();

    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bulk Print MR - <?= htmlspecialchars($sys['system_title'] ?? 'PILAR') ?></title>
        <style>
            @media print { body { margin: 0; } .no-print { display: none; } .page-break { page-break-after: always; } }
            body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 0; padding: 10px; font-size: 12px; }
            .print-controls { text-align: center; margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; }
            .mr-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; }
            .mr-record { border: 2px solid #000; padding: 10px; background-color: #fff; min-height: 350px; box-sizing: border-box; font-size: 10px; }
            .mr-header { display:flex; justify-content: space-between; align-items:flex-start; margin-bottom:10px; position:relative; }
            .mr-logo { width: 50px; height: 50px; }
            .mr-title { text-align:center; flex:1; margin: 0 10px; }
            .mr-title h3 { margin: 2px 0; font-size: 14px; font-weight: bold; }
            .office-location { font-size: 9px; margin-top: 5px; }
            .mr-tag-section { text-align:center; width: 80px; }
            .tag-text { font-size: 8px; font-weight: bold; margin-bottom: 3px; }
            .mr-qr { width: 50px; height: 50px; }
            .mr-line { border-bottom: 1px solid #000; margin: 8px 0; }
            .mr-field { margin-bottom: 6px; font-size: 9px; }
            .mr-field-label { display:inline-block; width: 120px; font-weight:bold; }
            .mr-field-value { text-decoration: underline; }
            .mr-checkbox-section { margin: 8px 0; font-size: 9px; }
            .mr-checkbox { margin-right: 15px; }
            .mr-dates { display:flex; justify-content: space-between; margin: 10px 0; font-size: 9px; }
            .mr-signatures { display:flex; justify-content: space-between; margin-top: 25px; font-size: 8px; }
            .mr-signature { text-align:center; width:45%; }
            .mr-signature-line { border-top:1px solid #000; margin-bottom:3px; height:20px; }
            @media print { .mr-container { grid-template-columns: repeat(2, 1fr); gap: 10px; } .mr-record { min-height: 330px; break-inside: avoid; } }
        </style>
    </head>
    <body>
        <div class="print-controls no-print">
            <h4>Bulk Print MR Records</h4>
            <p>Printing <?= count($rows) ?> MR record(s)</p>
            <button onclick="window.print()">Print Now</button>
            <button onclick="window.location.href='inventory.php?tab=assets'">Back to Inventory</button>
        </div>

        <div class="mr-container">
            <?php foreach ($rows as $index => $mr): ?>
                <?php
                    $inventory_tag = $mr['inventory_tag'] ?: ($mr['property_no'] ?: '');
                    $serviceableChecked = ((int)($mr['serviceable'] ?? 1) === 1) ? '☑' : '☐';
                    $unserviceableChecked = ((int)($mr['unserviceable'] ?? 0) === 1) ? '☑' : '☐';
                    // Prepare QR code data if available
                    $qrData = '';
                    if (!empty($mr['qr_code'])) {
                        $qrPath = realpath(__DIR__ . '/../img/' . $mr['qr_code']);
                        if ($qrPath && file_exists($qrPath)) {
                            $imageData = base64_encode(file_get_contents($qrPath));
                            $ext = strtolower(pathinfo($qrPath, PATHINFO_EXTENSION));
                            $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : (in_array($ext, ['gif']) ? 'image/gif' : 'image/png');
                            $qrData = 'data:' . $mime . ';base64,' . $imageData;
                        }
                    }
                ?>
                <div class="mr-record">
                    <div class="mr-header">
                        <?php if (!empty($sys['logo'])): ?>
                            <img src="../img/<?= htmlspecialchars($sys['logo']) ?>" alt="Municipal Logo" class="mr-logo">
                        <?php endif; ?>
                        <div class="mr-title">
                            <h3>GOVERNMENT PROPERTY</h3>
                            <div class="office-location">
                                Office/Location: <span class="mr-field-value"><?= htmlspecialchars($mr['office_name'] ?? '') ?></span>
                            </div>
                        </div>
                        <div class="mr-tag-section">
                            <div class="tag-text">
                                No. <span class="mr-field-value"><?= htmlspecialchars($inventory_tag) ?></span><br>
                                INVENTORY TAG
                            </div>
                            <?php if (!empty($qrData)): ?>
                                <img src="<?= $qrData ?>" alt="QR Code" class="mr-qr">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mr-line"></div>

                    <div class="mr-field">
                        <span class="mr-field-label">Description of the property:</span>
                        <span class="mr-field-value"><?= htmlspecialchars($mr['mr_description'] ?? '') ?></span>
                    </div>

                    <div class="mr-field">
                        <span class="mr-field-label">Model No.:</span>
                        <span class="mr-field-value"><?= htmlspecialchars($mr['model_no'] ?? '') ?></span>
                        &nbsp;&nbsp;
                        Serial No.: <span class="mr-field-value"><?= htmlspecialchars($mr['mr_serial_no'] ?? '') ?></span>
                    </div>

                    <div class="mr-checkbox-section">
                        <span class="mr-checkbox"><?= $serviceableChecked ?> Serviceable</span>
                        <span class="mr-checkbox"><?= $unserviceableChecked ?> Unserviceable</span>
                    </div>

                    <div class="mr-field">
                        <span class="mr-field-label">Unit/Quantity:</span>
                        <span class="mr-field-value"><?= htmlspecialchars($mr['unit_quantity'] ?? '') ?> <?= htmlspecialchars($mr['unit'] ?? '') ?></span>
                        &nbsp;&nbsp;
                        Acquisition Date/Cost: <span class="mr-field-value"><?= htmlspecialchars($mr['acquisition_date'] ?? '') ?> / ₱<?= htmlspecialchars($mr['acquisition_cost'] ?? '') ?></span>
                    </div>

                    <div class="mr-field">
                        <span class="mr-field-label">Person Accountable:</span>
                        <span class="mr-field-value"><?= htmlspecialchars($mr['person_accountable'] ?? '') ?></span>
                    </div>

                    <div class="mr-dates">
                        <div>Date: (acquired) <span class="mr-field-value"><?= htmlspecialchars($mr['acquired_date'] ?? '') ?></span></div>
                        <div>Date: (counted) <span class="mr-field-value"><?= htmlspecialchars($mr['counted_date'] ?? '') ?></span></div>
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

                <?php if (($index + 1) % 4 === 0): ?>
                    <div class="page-break"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <script>
            // Auto-print on load and then redirect to inventory
            window.addEventListener('load', () => {
                setTimeout(() => window.print(), 300);
            });
            window.onafterprint = function() {
                window.location.href = 'inventory.php?tab=assets&success=bulk_created';
            };
        </script>
    </body>
    </html>
    <?php
    exit();
}

$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}

// Fetch employees for accountable person dropdown
$employeesQuery = "SELECT employee_id, name FROM employees WHERE status = 'permanent' ORDER BY name ASC";
$employeesResult = $conn->query($employeesQuery);

// Fetch offices for dropdown
$officesQuery = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
$officesResult = $conn->query($officesQuery);

// Fetch categories for dropdown
$categoriesQuery = "SELECT id, category_name, category_code FROM categories ORDER BY category_name ASC";
$categoriesResult = $conn->query($categoriesQuery);

// Fetch tag formats for property number generation
$tagFormatsQuery = "SELECT tag_type, format_template FROM tag_formats WHERE tag_type IN ('inventory_tag', 'asset_code', 'serial_no') AND is_active = 1";
$tagFormatsResult = $conn->query($tagFormatsQuery);
$tagFormats = [];
if ($tagFormatsResult) {
    while ($row = $tagFormatsResult->fetch_assoc()) {
        $tagFormats[$row['tag_type']] = $row['format_template'];
    }
}

// Initialize TagFormatHelper
$tagHelper = new TagFormatHelper($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Create Property Tags - <?= htmlspecialchars($system['system_title'] ?? 'PILAR') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px;
            overflow: hidden;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .form-section {
            padding: 30px;
        }
        
        .asset-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .asset-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .asset-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .form-floating label {
            font-weight: 500;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .auto-generated {
            background-color: #e3f2fd;
            border-color: #2196f3;
            color: #1976d2;
        }
        
        .preview-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="inventory.php" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left me-2"></i>Back to Inventory
                    </a>
                    <div class="text-center flex-grow-1">
                        <h2 class="mb-0"><i class="bi bi-tags me-2"></i>Bulk Create Property Tags</h2>
                        <p class="mb-0 mt-2">Create MR records for multiple assets with auto-generated property numbers</p>
                    </div>
                    <div style="width: 120px;"></div> <!-- Spacer for centering -->
                </div>
            </div>

            <!-- Form Section -->
            <div class="form-section">
                <form id="bulkCreateForm" method="POST" action="process_bulk_mr.php" enctype="multipart/form-data">
                    <input type="hidden" id="accountablePersonName" name="accountable_person_name" value="">
                    <!-- Common Details Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-person-check me-2"></i>Common Details</h5>
                                    <small>These details will be applied to all selected assets</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="accountablePerson" name="accountable_person" required>
                                                    <option value="">Select Person Accountable</option>
                                                    <?php while ($employee = $employeesResult->fetch_assoc()): ?>
                                                        <option value="<?= $employee['employee_id'] ?>" data-name="<?= htmlspecialchars($employee['name']) ?>">
                                                            <?= htmlspecialchars($employee['name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <label for="accountablePerson">Person Accountable *</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="office" name="office" required>
                                                    <option value="">Select Office</option>
                                                    <?php while ($office = $officesResult->fetch_assoc()): ?>
                                                        <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['office_name']) ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <label for="office">Office *</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="category" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                                                        <option value="<?= $category['id'] ?>" data-code="<?= htmlspecialchars($category['category_code'] ?? '') ?>">
                                                            <?= htmlspecialchars($category['category_name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <label for="category">Category *</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="date" class="form-control" id="dateReceived" name="date_received" required>
                                                <label for="dateReceived">Date Received *</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="supplier" name="supplier" placeholder="Supplier">
                                                <label for="supplier">Supplier</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Assets Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Selected Assets</h5>
                                        <small>Each asset will get unique property numbers and inventory tags</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-light btn-sm" id="generatePreview">
                                        <i class="bi bi-eye me-1"></i>Generate Preview
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="assetsContainer">
                                        <!-- Assets will be loaded here by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-secondary me-3" onclick="window.history.back()">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="bi bi-check-circle me-2"></i>Create All Property Tags
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        // Tag formats from PHP
        const tagFormats = <?= json_encode($tagFormats) ?>;
        let selectedAssets = [];
        let generatedNumbers = {};

        $(document).ready(function() {
            // Load selected assets from sessionStorage
            const storedAssets = sessionStorage.getItem('selectedAssets');
            if (storedAssets) {
                selectedAssets = JSON.parse(storedAssets);
                displayAssets();
            } else {
                // Redirect back if no assets selected
                alert('No assets selected. Redirecting back to inventory.');
                window.location.href = 'inventory.php';
            }

            // Set default date to today
            $('#dateReceived').val(new Date().toISOString().split('T')[0]);

            // Initialize accountable person name hidden field
            const setAccountableName = () => {
                const $sel = $('#accountablePerson');
                const $opt = $sel.find('option:selected');
                const name = $opt.data('name') || $opt.text() || '';
                $('#accountablePersonName').val(name.trim());
            };
            setAccountableName();
            $('#accountablePerson').on('change', setAccountableName);
        });

        function displayAssets() {
            const container = $('#assetsContainer');
            container.empty();

            if (selectedAssets.length === 0) {
                container.html('<div class="alert alert-warning">No assets selected.</div>');
                return;
            }

            selectedAssets.forEach((asset, index) => {
                const assetCard = `
                    <div class="asset-card" data-index="${index}">
                        <div class="asset-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">${asset.description}</h6>
                                <span class="preview-badge">Asset ${index + 1} of ${selectedAssets.length}</span>
                            </div>
                            <small>Category: ${asset.category} | Qty: ${asset.quantity} ${asset.unit} | Value: ₱${parseFloat(asset.value).toLocaleString()}</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-generated" 
                                           id="propertyNo_${index}" 
                                           name="assets[${index}][property_no]" 
                                           placeholder="Auto-generated" 
                                           readonly>
                                    <label for="propertyNo_${index}">Property Number <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-generated" 
                                           id="inventoryTag_${index}" 
                                           name="assets[${index}][inventory_tag]" 
                                           placeholder="Auto-generated" 
                                           readonly>
                                    <label for="inventoryTag_${index}">Inventory Tag <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-generated" 
                                           id="assetCode_${index}" 
                                           name="assets[${index}][asset_code]" 
                                           placeholder="Auto-generated" 
                                           readonly>
                                    <label for="assetCode_${index}">Asset Code <span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-generated" 
                                           id="serialNo_${index}" 
                                           name="assets[${index}][serial_no]" 
                                           placeholder="Auto-generated" 
                                           readonly>
                                    <label for="serialNo_${index}">Serial Number <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" 
                                           id="model_${index}" 
                                           name="assets[${index}][model]" 
                                           placeholder="Model">
                                    <label for="model_${index}">Model</label>
                                </div>
                                <div class="text-end mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary applyAllBtn" data-field="model" data-index="${index}">
                                        <i class="bi bi-arrow-right-square me-1"></i>Apply to all
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" 
                                           id="brand_${index}" 
                                           name="assets[${index}][brand]" 
                                           placeholder="Brand">
                                    <label for="brand_${index}">Brand</label>
                                </div>
                                <div class="text-end mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary applyAllBtn" data-field="brand" data-index="${index}">
                                        <i class="bi bi-arrow-right-square me-1"></i>Apply to all
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" 
                                           id="endUser_${index}" 
                                           name="assets[${index}][end_user]" 
                                           placeholder="End User">
                                    <label for="endUser_${index}">End User <span class="text-danger">*</span></label>
                                </div>
                                <div class="text-end mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary applyAllBtn" data-field="endUser" data-index="${index}">
                                        <i class="bi bi-arrow-right-square me-1"></i>Apply to all
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image_${index}" class="form-label">Asset Image</label>
                                    <input type="file" class="form-control" 
                                           id="image_${index}" 
                                           name="assets[${index}][image]" 
                                           accept="image/*">
                                    <div class="form-text">Upload an image for this asset (optional)</div>
                                    <div class="text-end mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary applyAllBtn" data-field="image" data-index="${index}">
                                            <i class="bi bi-arrow-right-square me-1"></i>Apply to all
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for asset data -->
                        <input type="hidden" name="assets[${index}][asset_id]" value="${asset.id}">
                        <input type="hidden" name="assets[${index}][description]" value="${asset.description}">
                        <input type="hidden" name="assets[${index}][category_id]" value="${asset.categoryId}">
                        <input type="hidden" name="assets[${index}][value]" value="${asset.value}">
                        <input type="hidden" name="assets[${index}][quantity]" value="${asset.quantity}">
                        <input type="hidden" name="assets[${index}][unit]" value="${asset.unit}">
                    </div>
                `;
                container.append(assetCard);
            });
        }

        // Auto-generate numbers when category is selected
        $('#category').on('change', function() {
            const categoryCode = $(this).find('option:selected').data('code') || '';
            if (categoryCode && selectedAssets.length > 0) {
                generateNumbers(categoryCode);
            }
        });

        // Generate preview numbers
        $('#generatePreview').on('click', function() {
            const categoryCode = $('#category').find('option:selected').data('code') || '';
            if (!categoryCode) {
                alert('Please select a category first to generate asset codes.');
                return;
            }
            generateNumbers(categoryCode);
        });

        function generateNumbers(categoryCode = '') {
            const btn = $('#generatePreview');
            btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Generating...');

            // Call API to generate unique incremental numbers
            $.ajax({
                url: 'generate_bulk_numbers.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    assetCount: selectedAssets.length,
                    categoryCode: categoryCode
                }),
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success && result.numbers) {
                            // Update form fields with generated numbers
                            result.numbers.forEach((numbers, index) => {
                                $(`#propertyNo_${index}`).val(numbers.property_no);
                                $(`#inventoryTag_${index}`).val(numbers.inventory_tag);
                                $(`#assetCode_${index}`).val(numbers.asset_code);
                                $(`#serialNo_${index}`).val(numbers.serial_no);
                            });
                            $('#submitBtn').prop('disabled', false);
                        } else {
                            alert('Error generating numbers: ' + (result.message || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Error parsing response');
                        console.error('Response parsing error:', e);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error generating numbers: ' + error);
                    console.error('AJAX error:', error);
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-eye me-1"></i>Generate Preview');
                }
            });
        }

        // Apply-to-all handler for Model, Brand, End User
        $(document).on('click', '.applyAllBtn', function() {
            const field = $(this).data('field'); // 'model' | 'brand' | 'endUser' | 'image'
            const index = $(this).data('index');

            // Map field -> input ID prefix in each row
            const idPrefix = field === 'endUser' ? 'endUser' : field; // model, brand, endUser, image

            // Special handling for image file inputs
            if (field === 'image') {
                const $sourceInput = $(`#${idPrefix}_${index}`);
                const files = $sourceInput[0]?.files;
                if (!files || files.length === 0) {
                    alert('Please choose an image in this row first before applying to all.');
                    return;
                }
                const file = files[0];
                if (!window.DataTransfer) {
                    alert('Your browser does not support applying files to multiple inputs.');
                    return;
                }
                if (!confirm('Apply this image to all selected assets?')) return;

                for (let i = 0; i < selectedAssets.length; i++) {
                    const inputEl = document.getElementById(`${idPrefix}_${i}`);
                    if (inputEl) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        inputEl.files = dt.files;
                        // brief highlight effect
                        inputEl.classList.add('border-success');
                        setTimeout(() => inputEl.classList.remove('border-success'), 600);
                    }
                }
                return;
            }

            // Text inputs (model, brand, endUser)
            const sourceVal = $(`#${idPrefix}_${index}`).val();
            if (typeof sourceVal === 'undefined') return;
            if (!confirm(`Apply this ${field === 'endUser' ? 'End User' : field.charAt(0).toUpperCase() + field.slice(1)} value to all selected assets?`)) return;

            for (let i = 0; i < selectedAssets.length; i++) {
                const $input = $(`#${idPrefix}_${i}`);
                if ($input.length) {
                    $input.val(sourceVal);
                    $input.addClass('border-success');
                    setTimeout(() => $input.removeClass('border-success'), 600);
                }
            }
        });

        // Form submission
        $('#bulkCreateForm').on('submit', function(e) {
            e.preventDefault();
            
            if (selectedAssets.length === 0) {
                alert('No assets to process.');
                return;
            }

            // Validate required common fields
            const accountablePerson = $('#accountablePerson').val();
            const office = $('#office').val();
            const category = $('#category').val();
            const dateReceived = $('#dateReceived').val();

            if (!accountablePerson || !office || !category || !dateReceived) {
                alert('Please fill in all required common details.');
                return;
            }

            const formData = new FormData(this);
            
            // Show loading state
            $('#submitBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Creating Property Tags...');

            // Submit form with file uploads
            $.ajax({
                url: 'process_bulk_mr.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    // Upload progress
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = evt.loaded / evt.total * 100;
                            $('#submitBtn').html(`<i class="bi bi-hourglass-split me-2"></i>Uploading... ${Math.round(percentComplete)}%`);
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Redirect to print view on this page with asset ids
                            const ids = Array.isArray(result.asset_ids) ? result.asset_ids : [];
                            if (ids.length === 0) {
                                alert('Created, but no asset IDs returned. Redirecting to inventory.');
                                window.location.href = 'inventory.php?tab=assets&success=bulk_created';
                                return;
                            }
                            sessionStorage.removeItem('selectedAssets');
                            const idsParam = encodeURIComponent(ids.join(','));
                            window.location.href = `bulk_create_mr.php?print=1&ids=${idsParam}`;
                        } else {
                            let errorMsg = 'Error: ' + result.message;
                            if (result.debug_steps) {
                                console.error('Debug steps:', result.debug_steps);
                                errorMsg += '\n\nDebug steps:\n' + result.debug_steps.join('\n');
                            }
                            if (result.debug) {
                                console.error('Debug info:', result.debug);
                            }
                            if (result.line && result.file) {
                                console.error('Error location:', result.file + ':' + result.line);
                                errorMsg += '\n\nError at line ' + result.line;
                            }
                            alert(errorMsg);
                        }
                    } catch (e) {
                        alert('An error occurred while processing the request.');
                        console.error('Response parsing error:', e);
                        console.log('Raw response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                    console.error('AJAX error:', error);
                    console.log('Response text:', xhr.responseText);
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Create All Property Tags');
                }
            });
        });
    </script>
</body>
</html>
