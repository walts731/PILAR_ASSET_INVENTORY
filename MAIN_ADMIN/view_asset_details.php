<?php
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get asset ID from URL parameter
$asset_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($asset_id <= 0) {
    header("Location: inventory.php");
    exit();
}

// Ensure lifecycle table exists
ensureLifecycleTable($conn);

// Fetch asset details from the assets table including red tag status and additional images
$sql = "SELECT a.*, c.category_name, o.office_name, e.name AS employee_name,
               CASE WHEN rt.id IS NOT NULL THEN 1 ELSE 0 END as has_red_tag,
               ii.iirup_id
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON a.office_id = o.id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
        LEFT JOIN red_tags rt ON rt.asset_id = a.id
        LEFT JOIN iirup_items ii ON ii.asset_id = a.id
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $asset_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: inventory.php");
    exit();
}

$asset = $result->fetch_assoc();
$stmt->close();

// Don't close connection here - topbar.php needs it
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Asset Details - <?= htmlspecialchars($asset['description']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        .asset-header {
            background: linear-gradient(135deg, rgb(45, 64, 149) 0%, rgb(64, 74, 188) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .asset-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: -1;
        }

        .info-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .info-card .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }

        .status-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        .asset-image {
            max-width: 300px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        body {
            background-color: #f8f9fb;
            font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        .card {
            border-radius: 12px;
        }

        .back-button {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .lifecycle-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        .lifecycle-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .lifecycle-timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #007bff, #6610f2);
        }

        .lifecycle-event {
            position: relative;
            margin-bottom: 1.5rem;
            padding-left: 1rem;
        }

        .lifecycle-event::before {
            content: '';
            position: absolute;
            left: -0.5rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            background: #007bff;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .lifecycle-event.status-change::before {
            background: #ffc107;
        }

        .lifecycle-event.red-tagged::before {
            background: #dc3545;
        }

        .lifecycle-event.iirup-created::before {
            background: #fd7e14;
        }

        .lifecycle-event.assigned::before {
            background: #20c997;
        }

        .lifecycle-event.transferred::before {
            background: #17a2b8;
        }

        .lifecycle-event.disposed::before {
            background: #6c757d;
        }

        .lifecycle-event.default::before {
            background: #6f42c1;
        }

        .lifecycle-event a {
            color: inherit !important;
            text-decoration: none !important;
            border-bottom: 1px dotted currentColor;
            transition: all 0.2s ease;
        }

        .lifecycle-event a:hover {
            border-bottom-style: solid;
            opacity: 0.8;
        }

        .lifecycle-event .text-primary a {
            color: #0d6efd !important;
        }

        .lifecycle-event .text-success a {
            color: #198754 !important;
        }

        .lifecycle-event .text-info a {
            color: #0dcaf0 !important;
        }

        .lifecycle-event .text-warning a {
            color: #ffc107 !important;
        }

        .lifecycle-event .text-danger a {
            color: #dc3545 !important;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php' ?>
    <div class="main">
        <?php include 'includes/topbar.php' ?>

        <div class="container-fluid mt-4">
            

            <div class="container-fluid">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Asset Header -->
            <div class="asset-header">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h2 mb-2"><?= htmlspecialchars($asset['description']) ?></h1>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-tag me-2"></i>Inventory Tag <?= htmlspecialchars($asset['inventory_tag'] ?? 'No Inventory Tag') ?>
                                <?php if ($asset['property_no']): ?>
                                    | <i class="bi bi-hash me-1"></i> Property Number <?= htmlspecialchars($asset['property_no']) ?>
                                <?php endif; ?>
                                <?php if (!empty($asset['serial_no'])): ?>
                                    | <small class=""><i class="bi bi-upc-scan me-1"></i>Serial No <?= htmlspecialchars($asset['serial_no']) ?></small>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                    </div>
                </div>
            </div>
                        <!-- Basic Information -->
                        <div class="card info-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                                <span class="badge status-badge <?=
                                                                $asset['status'] === 'serviceable' ? 'bg-success' : ($asset['status'] === 'unserviceable' ? 'bg-danger' : 'bg-warning')
                                                                ?>">
                                    <?= ucfirst($asset['status']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <strong>Category:</strong> <?= htmlspecialchars($asset['category_name'] ?? 'N/A') ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Type:</strong> <?= ucfirst($asset['type']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Office:</strong> <?= htmlspecialchars($asset['office_name'] ?? 'N/A') ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Employee:</strong> <?= htmlspecialchars($asset['employee_name'] ?? 'Unassigned') ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <strong>Brand:</strong> <?= htmlspecialchars($asset['brand'] ?? 'N/A') ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <strong>End User:</strong> <?= htmlspecialchars($asset['end_user'] ?? 'Unassigned') ?>
                                    </div>

                                    <div class="col-md-6">
                                        <strong>Supplier:</strong> <?= htmlspecialchars($asset['supplier'] ?? 'N/A') ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Model:</strong> <?= htmlspecialchars($asset['model'] ?? 'N/A') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="card info-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Financial Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <strong>Unit Cost:</strong> ₱<?= number_format($asset['value'], 2) ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Quantity:</strong> <?= $asset['quantity'] ?> <?= htmlspecialchars($asset['unit'] ?? '') ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Total Value:</strong> ₱<?= number_format($asset['value'] * $asset['quantity'], 2) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Acquisition Date:</strong>
                                        <?= $asset['acquisition_date'] ? date('F j, Y', strtotime($asset['acquisition_date'])) : 'N/A' ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Last Updated:</strong>
                                        <?= $asset['last_updated'] ? date('F j, Y g:i A', strtotime($asset['last_updated'])) : 'N/A' ?>
                                    </div>
                                </div>
                            </div>
                        </div>

 <!-- Asset Lifecycle -->
 <div class="lifecycle-container">
                <div class="d-flex align-items-center mb-3">
                    <h4 class="mb-0 text-primary">
                        <i class="bi bi-clock-history me-2"></i>Asset Lifecycle
                    </h4>
                    <span class="badge bg-light text-dark ms-2" id="lifecycleEventCount">Loading...</span>
                    <button class="btn btn-sm btn-outline-primary ms-auto" onclick="refreshLifecycle()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>

                <div id="lifecycleContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading lifecycle events...</span>
                        </div>
                        <p class="text-muted mt-2">Loading lifecycle events...</p>
                    </div>
                </div>
            </div>



                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card info-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="create_mr.php?asset_id=<?= $asset['id'] ?>"
                                        class="btn btn-outline-primary" target="_blank">
                                        <i class="bi bi-tag me-2"></i>Manage Property Tag
                                    </a>
                                    <!-- Transfer Button - Only show if asset has inventory tag -->
                                    <?php if (!empty($asset['inventory_tag'])): ?>
                                        <button class="btn btn-outline-info transfer-asset"
                                            data-asset-id="<?= $asset['id'] ?>"
                                            data-inventory-tag="<?= htmlspecialchars($asset['inventory_tag'] ?? '') ?>"
                                            data-current-employee-id="<?= $asset['employee_id'] ?? '' ?>"
                                            data-description="<?= htmlspecialchars($asset['description'] ?? '') ?>"
                                            data-acquisition-date="<?= $asset['acquisition_date'] ?? '' ?>"
                                            data-property-no="<?= htmlspecialchars($asset['property_no'] ?? '') ?>"
                                            data-unit-price="<?= $asset['value'] ?? '' ?>"
                                            data-status="<?= $asset['status'] ?? '' ?>"
                                            data-employee-name="<?= htmlspecialchars($asset['employee_name'] ?? '') ?>">
                                            <i class="bi bi-arrow-left-right me-2"></i>Transfer Asset
                                        </button>
                                    <?php endif; ?>
                                    <!-- Add to IIRUP Button - Only show for serviceable assets -->
                                    <?php if ($asset['status'] === 'serviceable'): ?>
                                    <button class="btn btn-outline-warning add-to-iirup-btn"
                                        data-asset-id="<?= $asset['id'] ?>">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Add to IIRUP
                                    </button>
                                    <?php endif; ?>
                                    <!-- Create Red Tag Button - Only show for unserviceable assets without red tags -->
                                    <?php if ($asset['status'] === 'unserviceable' && !$asset['has_red_tag']): ?>
                                        <a href="create_red_tag.php?asset_id=<?= $asset['id'] ?><?= !empty($asset['iirup_id']) ? '&iirup_id=' . $asset['iirup_id'] : '' ?>"
                                            class="btn btn-outline-danger" target="_blank">
                                            <i class="bi bi-tag me-2"></i>Create Red Tag
                                        </a>
                                    <?php elseif ($asset['has_red_tag']): ?>
                                        <!-- Red Tagged Status Badge -->
                                        <div class="alert alert-danger mb-0 py-2">
                                            <i class="bi bi-tag me-2"></i><strong>Red Tagged</strong>
                                            <small class="d-block">This asset has been red tagged</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Asset Image -->
                        <?php if ($asset['image']): ?>
                            <div class="card info-card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-image me-2"></i>Asset Image</h5>
                                </div>
                                <div class="card-body text-center">
                                    <img src="../img/assets/<?= htmlspecialchars($asset['image']) ?>"
                                        alt="Asset Image"
                                        class="asset-image img-fluid rounded border"
                                        style="cursor: pointer; max-height: 300px; object-fit: cover;"
                                        onclick="showImageModal('<?= htmlspecialchars($asset['image']) ?>', 'Main Asset Image')">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Additional Images -->
                        <?php if (!empty($asset['additional_images'])): ?>
                            <?php
                            $additional_images = json_decode($asset['additional_images'], true);
                            if (is_array($additional_images) && count($additional_images) > 0):
                            ?>
                                <div class="card info-card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="bi bi-images me-2"></i>Additional Images</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <?php foreach ($additional_images as $index => $image): ?>
                                                <div class="col-6">
                                                    <div class="position-relative">
                                                        <img src="../img/assets/<?= htmlspecialchars($image) ?>"
                                                            alt="Additional Image <?= $index + 1 ?>"
                                                            class="img-fluid rounded border additional-image"
                                                            style="width: 100%; height: 120px; object-fit: cover; cursor: pointer;"
                                                            onclick="showImageModal('<?= htmlspecialchars($image) ?>', 'Additional Image <?= $index + 1 ?>')">
                                                        <div class="position-absolute top-0 end-0 m-1">
                                                            <span class="badge bg-dark bg-opacity-75"><?= $index + 1 ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (count($additional_images) > 4): ?>
                                            <div class="text-center mt-2">
                                                <small class="text-muted">+<?= count($additional_images) - 4 ?> more images</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        
                    </div>
                </div>
            </div>

           
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
        const assetId = <?= $asset_id ?>;

        // Load lifecycle events on page load
        $(document).ready(function() {
            // Add to IIRUP button click handler
            $(document).on('click', '.add-to-iirup-btn', function(e) {
                e.preventDefault();
                const button = $(this);
                const assetId = button.data('asset-id');
                
                // Show loading state
                button.prop('disabled', true);
                button.html('<i class="bi bi-hourglass-split me-2"></i>Adding...');
                
                // Send AJAX request to add asset to temp table
                $.post('insert_iirup_button.php', {
                    asset_id: assetId
                })
                .done(function(response) {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        // Show success state briefly
                        button.removeClass('btn-outline-warning').addClass('btn-success');
                        button.html('<i class="bi bi-check-circle me-2"></i>Added!');
                        
                        // Redirect to IIRUP form after short delay
                        setTimeout(function() {
                            window.location.href = data.redirect || 'forms.php?id=7';
                        }, 1000);
                    } else {
                        alert(data.message || 'Failed to add asset to IIRUP list');
                        // Reset button
                        button.prop('disabled', false);
                        button.html('<i class="bi bi-exclamation-triangle me-2"></i>Add to IIRUP');
                    }
                })
                .fail(function() {
                    alert('An error occurred while adding asset to IIRUP list');
                    // Reset button
                    button.prop('disabled', false);
                    button.html('<i class="bi bi-exclamation-triangle me-2"></i>Add to IIRUP');
                });
            });

            loadLifecycleEvents();

            $(document).on('click', '.transfer-asset', function(e) {
                e.preventDefault(); // Prevent default button behavior
                console.log('Transfer button clicked'); // Debug log

                // Get asset data
                const assetId = $(this).data('asset-id');
                const inventoryTag = $(this).data('inventory-tag');
                const currentEmployeeId = $(this).data('current-employee-id');
                const description = $(this).data('description');
                const acquisitionDate = $(this).data('acquisition-date');
                const propertyNo = $(this).data('property-no');
                const unitPrice = $(this).data('unit-price');
                const status = $(this).data('status');
                const employeeName = $(this).data('employee-name');

                console.log('Asset data:', {
                    assetId,
                    inventoryTag,
                    currentEmployeeId,
                    description,
                    acquisitionDate,
                    propertyNo,
                    unitPrice,
                    status,
                    employeeName
                }); // Debug log

                // Redirect to forms.php with ITR form ID 9 and all asset parameters
                const ITR_FORM_ID = 9;
                const url = `forms.php?id=${ITR_FORM_ID}&asset_id=${assetId}&inventory_tag=${encodeURIComponent(inventoryTag)}&current_employee_id=${currentEmployeeId}&description=${encodeURIComponent(description)}&acquisition_date=${acquisitionDate}&property_no=${encodeURIComponent(propertyNo)}&unit_price=${unitPrice}&status=${status}&employee_name=${encodeURIComponent(employeeName)}`;
                console.log('Redirecting to:', url); // Debug log

                window.location.href = url;
            });
        });

        // Load lifecycle events from the API
        function loadLifecycleEvents() {
            fetch(`get_asset_lifecycle.php?source=assets&id=${assetId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showLifecycleError(data.error);
                        return;
                    }

                    displayLifecycleEvents(data.events || []);
                    updateEventCount(data.summary?.count || 0);
                })
                .catch(error => {
                    console.error('Error loading lifecycle events:', error);
                    showLifecycleError('Failed to load lifecycle events');
                });
        }

        // Display lifecycle events in the timeline
        function displayLifecycleEvents(events) {
            const container = document.getElementById('lifecycleContent');

            if (!events || events.length === 0) {
                container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-clock-history display-4 text-muted mb-3"></i>
                    <p class="text-muted">No lifecycle events recorded for this asset.</p>
                </div>
            `;
                return;
            }

            let timelineHtml = '<div class="lifecycle-timeline">';

            events.forEach(event => {
                const eventType = getEventTypeClass(event.event_type);
                const eventIcon = getEventIcon(event.event_type);
                const eventDate = new Date(event.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });

                timelineHtml += `
                <div class="lifecycle-event ${eventType}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-dark">
                                <i class="${eventIcon} me-2 text-primary"></i>
                                ${getEventTitle(event.event_type)}
                            </h6>
                            <div class="mb-1 text-muted">${getEventDescription(event)}</div>
                            ${event.notes ? `<small class="text-muted"><strong>Notes:</strong> ${event.notes}</small>` : ''}
                        </div>
                        <small class="text-muted ms-3">${eventDate}</small>
                    </div>
                </div>
            `;
            });

            timelineHtml += '</div>';
            container.innerHTML = timelineHtml;
        }

        // Get CSS class for event type
        function getEventTypeClass(eventType) {
            const classes = {
                'ACQUIRED': 'acquired',
                'ASSIGNED': 'assigned',
                'TRANSFERRED': 'transferred',
                'RED_TAGGED': 'red-tagged',
                'DISPOSAL_LISTED': 'iirup-created',
                'DISPOSED': 'disposed'
            };
            return classes[eventType] || 'default';
        }

        // Get icon for event type
        function getEventIcon(eventType) {
            const icons = {
                'ACQUIRED': 'bi-plus-circle',
                'ASSIGNED': 'bi-person-check',
                'TRANSFERRED': 'bi-arrow-left-right',
                'RED_TAGGED': 'bi-tag',
                'DISPOSAL_LISTED': 'bi-exclamation-triangle',
                'DISPOSED': 'bi-trash'
            };
            return icons[eventType] || 'bi-circle';
        }

        // Get title for event type
        function getEventTitle(eventType) {
            const titles = {
                'ACQUIRED': 'Asset Acquired',
                'ASSIGNED': 'Asset Assigned',
                'TRANSFERRED': 'Asset Transferred',
                'RED_TAGGED': 'Red Tagged',
                'DISPOSAL_LISTED': 'Listed for Disposal',
                'DISPOSED': 'Asset Disposed'
            };
            return titles[eventType] || eventType;
        }

        // Get description for event
        function getEventDescription(event) {
            let description = '';
            let formLinks = '';

            switch (event.event_type) {
                case 'ACQUIRED':
                    description = 'Asset was acquired and added to inventory';
                    // Check for PAR or ICS forms
                    if (event.ref_table === 'par_form' && event.ref_id) {
                        formLinks = `<br><small class="text-success"><i class="bi bi-file-earmark-check me-1"></i><a href="view_par.php?id=${event.ref_id}&form_id=3" target="_blank" class="text-decoration-none">View PAR Form #${event.ref_id}</a></small>`;
                    } else if (event.ref_table === 'ics_form' && event.ref_id) {
                        formLinks = `<br><small class="text-info"><i class="bi bi-file-earmark-text me-1"></i><a href="view_ics.php?id=${event.ref_id}&form_id=4" target="_blank" class="text-decoration-none">View ICS Form #${event.ref_id}</a></small>`;
                    }
                    break;
                case 'ASSIGNED':
                    description = `Assigned to ${event.to_employee || 'employee'} at ${event.to_office || 'office'}`;
                    // Check for PAR or ICS forms
                    if (event.ref_table === 'par_form' && event.ref_id) {
                        formLinks = `<br><small class="text-success"><i class="bi bi-file-earmark-check me-1"></i><a href="view_par.php?id=${event.ref_id}&form_id=3" target="_blank" class="text-decoration-none">View PAR Form #${event.ref_id}</a></small>`;
                    } else if (event.ref_table === 'ics_form' && event.ref_id) {
                        formLinks = `<br><small class="text-info"><i class="bi bi-file-earmark-text me-1"></i><a href="view_ics.php?id=${event.ref_id}&form_id=4" target="_blank" class="text-decoration-none">View ICS Form #${event.ref_id}</a></small>`;
                    }
                    break;
                case 'TRANSFERRED':
                    description = `Transferred from ${event.from_employee || 'previous employee'} to ${event.to_employee || 'new employee'}`;
                    if (event.ref_table === 'itr_form' && event.ref_id) {
                        formLinks = `<br><small class="text-primary"><i class="bi bi-file-earmark-text me-1"></i><a href="view_itr.php?id=${event.ref_id}" target="_blank" class="text-decoration-none">View ITR Form #${event.ref_id}</a></small>`;
                    }
                    break;
                case 'RED_TAGGED':
                    description = 'Asset was red tagged for disposal or repair';
                    if (event.ref_table === 'red_tags' && event.ref_id) {
                        // Use the same approach as inventory.php - direct link with asset_id and iirup_id
                        formLinks = `<br><small class="text-danger"><i class="bi bi-tag me-1"></i><a href="#" onclick="openRedTagEditSimple(${assetId})" class="text-decoration-none">Edit Red Tag #${event.ref_id}</a></small>`;
                    }
                    break;
                case 'DISPOSAL_LISTED':
                    description = 'Asset was listed for disposal through IIRUP form';
                    if (event.ref_table === 'iirup_form' && event.ref_id) {
                        formLinks = `<br><small class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i><a href="view_iirup.php?id=${event.ref_id}&form_id=7" target="_blank" class="text-decoration-none">View IIRUP Form #${event.ref_id}</a></small>`;
                    }
                    break;
                case 'DISPOSED':
                    description = 'Asset was disposed of';
                    break;
                default:
                    description = event.notes || 'Lifecycle event recorded';
            }

            return description + formLinks;
        }

        // Update event count badge
        function updateEventCount(count) {
            document.getElementById('lifecycleEventCount').textContent = `${count} Events`;
        }

        // Show error message
        function showLifecycleError(message) {
            const container = document.getElementById('lifecycleContent');
            container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-exclamation-triangle display-4 text-warning mb-3"></i>
                <p class="text-muted">${message}</p>
            </div>
        `;
            document.getElementById('lifecycleEventCount').textContent = 'Error';
        }

        // Refresh lifecycle events
        function refreshLifecycle() {
            document.getElementById('lifecycleContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading lifecycle events...</span>
                </div>
                <p class="text-muted mt-2">Refreshing lifecycle events...</p>
            </div>
        `;
            document.getElementById('lifecycleEventCount').textContent = 'Loading...';
            loadLifecycleEvents();
        }

        // Open red tag for editing - simple approach like inventory.php
        function openRedTagEditSimple(assetId) {
            // Use the iirup_id from the asset data we already have
            const iirupId = <?= $asset['iirup_id'] ?? 'null' ?>;

            if (!iirupId) {
                alert('No IIRUP form found for this asset. Please create an IIRUP form first.');
                return;
            }

            // Direct redirect like inventory.php red tag button
            const url = `create_red_tag.php?asset_id=${assetId}&iirup_id=${iirupId}`;
            window.open(url, '_blank');
        }

        // Show image in modal
        function showImageModal(imagePath, title) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('imageModalLabel');

            modalImage.src = '../img/assets/' + imagePath;
            modalTitle.textContent = title;

            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    </script>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Asset Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Asset Image" class="img-fluid rounded">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<?php
// Close database connection at the very end
if (isset($conn)) {
    $conn->close();
}
?>