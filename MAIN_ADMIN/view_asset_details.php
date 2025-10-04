<?php
require_once '../connect.php';
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

// Fetch asset details from the assets table
$sql = "SELECT a.*, c.category_name, o.office_name, e.name AS employee_name
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON a.office_id = o.id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
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

// No need to fetch asset_items - we'll use the main asset data

// Maintenance records removed - focusing on asset details only

// Fetch lifecycle events for the asset
$lifecycle_events = [];

// 1. Asset acquisition event
if ($asset['acquisition_date']) {
    $lifecycle_events[] = [
        'type' => 'acquired',
        'date' => $asset['acquisition_date'],
        'title' => 'Asset Acquired',
        'description' => 'Asset was acquired and added to inventory',
        'icon' => 'bi-plus-circle'
    ];
}

// 2. Assignment events
if ($asset['employee_name']) {
    $lifecycle_events[] = [
        'type' => 'assigned',
        'date' => $asset['last_updated'] ?? $asset['acquisition_date'],
        'title' => 'Asset Assigned',
        'description' => 'Assigned to ' . $asset['employee_name'] . ' at ' . ($asset['office_name'] ?? 'Unknown Office'),
        'icon' => 'bi-person-check'
    ];
}

// 3. Status change events
if ($asset['status'] === 'unserviceable') {
    $lifecycle_events[] = [
        'type' => 'status-change',
        'date' => $asset['last_updated'] ?? date('Y-m-d'),
        'title' => 'Status Changed',
        'description' => 'Asset marked as unserviceable',
        'icon' => 'bi-exclamation-triangle'
    ];
}

// Sort events by date
usort($lifecycle_events, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

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
            background: linear-gradient(135deg,rgb(45, 64, 149) 0%,rgb(64, 74, 188) 100%);
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
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php' ?>
    <div class="main">
        <?php include 'includes/topbar.php' ?>

        <div class="container-fluid mt-4">
                <!-- Asset Header -->
                <div class="asset-header">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h2 mb-2"><?= htmlspecialchars($asset['description']) ?></h1>
                                <p class="mb-0 opacity-75">
                                    <i class="bi bi-tag me-2"></i><?= htmlspecialchars($asset['inventory_tag'] ?? 'No Inventory Tag') ?>
                                    <?php if ($asset['property_no']): ?>
                                        | <i class="bi bi-hash me-1"></i><?= htmlspecialchars($asset['property_no']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <a href="inventory.php" class="back-button">
                                    <i class="bi bi-arrow-left"></i>
                                    Back to Inventory
                                </a>
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
                        <span class="badge bg-light text-dark ms-2"><?= count($lifecycle_events) ?> Events</span>
                    </div>
                    
                    <?php if (!empty($lifecycle_events)): ?>
                    <div class="lifecycle-timeline">
                        <?php foreach ($lifecycle_events as $event): ?>
                        <div class="lifecycle-event <?= $event['type'] ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-dark">
                                        <i class="<?= $event['icon'] ?> me-2 text-primary"></i>
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h6>
                                    <p class="mb-1 text-muted"><?= htmlspecialchars($event['description']) ?></p>
                                </div>
                                <small class="text-muted ms-3">
                                    <?= date('M j, Y', strtotime($event['date'])) ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history display-4 text-muted mb-3"></i>
                        <p class="text-muted">No lifecycle events recorded for this asset.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="container-fluid">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-lg-8">
                            <!-- Basic Information -->
                            <div class="card info-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                                    <span class="badge status-badge <?= 
                                        $asset['status'] === 'available' ? 'bg-success' : 
                                        ($asset['status'] === 'unserviceable' ? 'bg-danger' : 'bg-warning') 
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
                                            <strong>Model:</strong> <?= htmlspecialchars($asset['model'] ?? 'N/A') ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Serial Number:</strong> <?= htmlspecialchars($asset['serial_no'] ?? 'N/A') ?>
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

                            



                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-4">
                            <!-- Asset Image -->
                            <?php if ($asset['image']): ?>
                            <div class="card info-card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-image me-2"></i>Asset Image</h5>
                                </div>
                                <div class="card-body text-center">
                                    <img src="../img/assets/<?= htmlspecialchars($asset['image']) ?>" 
                                         alt="Asset Image" 
                                         class="asset-image img-fluid">
                                </div>
                            </div>
                            <?php endif; ?>

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
                                    </div>
                                </div>
                            </div>

                            <!-- Asset Summary -->
                            <div class="card info-card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Asset Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 text-center">
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <div class="h4 mb-0 text-primary"><?= $asset['quantity'] ?></div>
                                                <small class="text-muted">Quantity</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <div class="h4 mb-0 text-success">₱<?= number_format($asset['value'], 2) ?></div>
                                                <small class="text-muted">Unit Cost</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <div class="h4 mb-0 text-info">₱<?= number_format($asset['value'] * $asset['quantity'], 2) ?></div>
                                                <small class="text-muted">Total Value</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <div class="h4 mb-0 text-warning"><?= ucfirst($asset['status']) ?></div>
                                                <small class="text-muted">Status</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close database connection at the very end
if (isset($conn)) {
    $conn->close();
}
?>
