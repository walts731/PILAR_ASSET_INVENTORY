<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
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
                                    <label for="propertyNo_${index}">Property Number</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-generated" 
                                           id="inventoryTag_${index}" 
                                           name="assets[${index}][inventory_tag]" 
                                           placeholder="Auto-generated" 
                                           readonly>
                                    <label for="inventoryTag_${index}">Inventory Tag</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control auto-generated" 
                                           id="assetCode_${index}" 
                                           name="assets[${index}][asset_code]" 
                                           placeholder="Auto-generated" 
                                           readonly>
                                    <label for="assetCode_${index}">Asset Code</label>
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
                                    <label for="serialNo_${index}">Serial Number</label>
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
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" 
                                           id="brand_${index}" 
                                           name="assets[${index}][brand]" 
                                           placeholder="Brand">
                                    <label for="brand_${index}">Brand</label>
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
                                    <label for="endUser_${index}">End User</label>
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
                            let message = `Successfully created property tags for ${result.count} assets!`;
                            if (result.warnings && result.warnings.length > 0) {
                                message += '\n\nWarnings:\n' + result.warnings.slice(0, 3).join('\n');
                                if (result.warnings.length > 3) {
                                    message += `\n... and ${result.warnings.length - 3} more warnings.`;
                                }
                            }
                            alert(message);
                            sessionStorage.removeItem('selectedAssets');
                            window.location.href = 'inventory.php?tab=assets&success=bulk_created';
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
