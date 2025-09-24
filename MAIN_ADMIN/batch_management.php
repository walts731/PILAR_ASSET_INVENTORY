<?php
/**
 * Batch/Lot Management Interface
 * PILAR Asset Inventory System
 *
 * This page allows administrators to create, view, and manage batches/lots
 * for assets that require batch tracking.
 */

require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if user has admin privileges
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin') {
    header("Location: ../MAIN_ADMIN/dashboard.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_batch'])) {
        // Create new batch
        $batch_number = mysqli_real_escape_string($conn, $_POST['batch_number']);
        $batch_name = mysqli_real_escape_string($conn, $_POST['batch_name']);
        $asset_id = (int)$_POST['asset_id'];
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : 'NULL';
        $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
        $manufacturer = mysqli_real_escape_string($conn, $_POST['manufacturer']);
        $manufacture_date = !empty($_POST['manufacture_date']) ? "'".$_POST['manufacture_date']."'" : 'NULL';
        $expiry_date = !empty($_POST['expiry_date']) ? "'".$_POST['expiry_date']."'" : 'NULL';
        $production_date = !empty($_POST['production_date']) ? "'".$_POST['production_date']."'" : 'NULL';
        $lot_number = !empty($_POST['lot_number']) ? mysqli_real_escape_string($conn, $_POST['lot_number']) : 'NULL';
        $batch_size = (int)$_POST['batch_size'];
        $unit_cost = (float)$_POST['unit_cost'];
        $total_value = $batch_size * $unit_cost;
        $storage_location = mysqli_real_escape_string($conn, $_POST['storage_location']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $quality_status = mysqli_real_escape_string($conn, $_POST['quality_status']);

        $sql = "INSERT INTO batches (
            batch_number, batch_name, asset_id, category_id, supplier, manufacturer,
            manufacture_date, expiry_date, production_date, lot_number, batch_size,
            unit_cost, total_value, storage_location, notes, quality_status, created_by
        ) VALUES (
            '$batch_number', '$batch_name', $asset_id, $category_id, '$supplier', '$manufacturer',
            $manufacture_date, $expiry_date, $production_date, '$lot_number', $batch_size,
            $unit_cost, $total_value, '$storage_location', '$notes', '$quality_status', {$_SESSION['user_id']}
        )";

        if (mysqli_query($conn, $sql)) {
            $batch_id = mysqli_insert_id($conn);

            // Create batch items
            for ($i = 1; $i <= $batch_size; $i++) {
                $item_number = $batch_number . '-ITEM-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                $qr_text = 'BATCH-' . $batch_id . '-ITEM-' . $i;
                $qr_filename = 'batch_' . $batch_id . '_item_' . $i . '.png';
                $inventory_tag = 'BATCH' . $batch_id . '-' . $i;

                // Generate QR code
                if (!is_dir('../img/qrcodes/')) {
                    mkdir('../img/qrcodes/', 0755, true);
                }
                QRcode::png($qr_text, '../img/qrcodes/' . $qr_filename, QR_ECLEVEL_L, 4);

                // Insert batch item
                $item_sql = "INSERT INTO batch_items (
                    batch_id, item_number, qr_code, inventory_tag, status
                ) VALUES (
                    $batch_id, '$item_number', '$qr_filename', '$inventory_tag', 'available'
                )";

                mysqli_query($conn, $item_sql);
            }

            // Log activity
            logAssetActivity('CREATE_BATCH', "Created batch: $batch_name ($batch_number)", $asset_id, "Batch ID: $batch_id, Size: $batch_size");

            $success_message = "Batch created successfully!";
        } else {
            $error_message = "Error creating batch: " . mysqli_error($conn);
        }
    }
}

// Get assets that can have batches
$assets_sql = "SELECT a.id, a.asset_name, a.description, c.category_name
               FROM assets a
               LEFT JOIN categories c ON a.category = c.id
               WHERE a.enable_batch_tracking = 1 OR a.type = 'consumable'
               ORDER BY a.asset_name";
$assets_result = mysqli_query($conn, $assets_sql);

// Get existing batches
$batches_sql = "SELECT b.*, a.asset_name, c.category_name,
                COUNT(bi.id) as items_count,
                SUM(CASE WHEN bi.status = 'available' THEN 1 ELSE 0 END) as available_count
                FROM batches b
                LEFT JOIN assets a ON b.asset_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN batch_items bi ON b.id = bi.batch_id
                GROUP BY b.id
                ORDER BY b.created_at DESC";
$batches_result = mysqli_query($conn, $batches_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch/Lot Management - PILAR Asset Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/topbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-4">Batch/Lot Management</h2>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Create New Batch Form -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Create New Batch/Lot</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="batch_number" class="form-label">Batch Number *</label>
                                            <input type="text" class="form-control" id="batch_number" name="batch_number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="batch_name" class="form-label">Batch Name *</label>
                                            <input type="text" class="form-control" id="batch_name" name="batch_name" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="asset_id" class="form-label">Asset *</label>
                                            <select class="form-select" id="asset_id" name="asset_id" required>
                                                <option value="">Select Asset</option>
                                                <?php while ($asset = mysqli_fetch_assoc($assets_result)): ?>
                                                    <option value="<?php echo $asset['id']; ?>">
                                                        <?php echo htmlspecialchars($asset['asset_name'] . ' - ' . $asset['description']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">Select Category</option>
                                                <!-- Categories will be loaded via JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="supplier" class="form-label">Supplier</label>
                                            <input type="text" class="form-control" id="supplier" name="supplier">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="manufacturer" class="form-label">Manufacturer</label>
                                            <input type="text" class="form-control" id="manufacturer" name="manufacturer">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="manufacture_date" class="form-label">Manufacture Date</label>
                                            <input type="date" class="form-control" id="manufacture_date" name="manufacture_date">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="expiry_date" class="form-label">Expiry Date</label>
                                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="production_date" class="form-label">Production Date</label>
                                            <input type="date" class="form-control" id="production_date" name="production_date">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="lot_number" class="form-label">Lot Number</label>
                                            <input type="text" class="form-control" id="lot_number" name="lot_number">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="batch_size" class="form-label">Batch Size *</label>
                                            <input type="number" class="form-control" id="batch_size" name="batch_size" min="1" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="unit_cost" class="form-label">Unit Cost</label>
                                            <input type="number" step="0.01" class="form-control" id="unit_cost" name="unit_cost">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="storage_location" class="form-label">Storage Location</label>
                                            <input type="text" class="form-control" id="storage_location" name="storage_location">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="quality_status" class="form-label">Quality Status</label>
                                            <select class="form-select" id="quality_status" name="quality_status">
                                                <option value="pending">Pending</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Rejected</option>
                                                <option value="quarantined">Quarantined</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="create_batch" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Batch
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Batches -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Existing Batches/Lots</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Batch #</th>
                                            <th>Batch Name</th>
                                            <th>Asset</th>
                                            <th>Size</th>
                                            <th>Available</th>
                                            <th>Status</th>
                                            <th>Expiry Date</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($batch = mysqli_fetch_assoc($batches_result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($batch['batch_number']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['batch_name']); ?></td>
                                                <td><?php echo htmlspecialchars($batch['asset_name']); ?></td>
                                                <td><?php echo $batch['batch_size']; ?></td>
                                                <td><?php echo $batch['available_count'] . '/' . $batch['items_count']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo match($batch['quality_status']) {
                                                            'approved' => 'success',
                                                            'rejected' => 'danger',
                                                            'quarantined' => 'warning',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($batch['quality_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $batch['expiry_date'] ? date('M d, Y', strtotime($batch['expiry_date'])) : 'N/A'; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($batch['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewBatch(<?php echo $batch['id']; ?>)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="editBatch(<?php echo $batch['id']; ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Details Modal -->
    <div class="modal fade" id="batchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batch Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="batchDetails">
                    <!-- Batch details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function viewBatch(batchId) {
            // Load batch details via AJAX
            $.get('get_batch_details.php', {batch_id: batchId}, function(data) {
                $('#batchDetails').html(data);
                $('#batchModal').modal('show');
            });
        }

        function editBatch(batchId) {
            // Redirect to edit page
            window.location.href = 'edit_batch.php?id=' + batchId;
        }

        // Load categories based on selected asset
        $('#asset_id').change(function() {
            const assetId = $(this).val();
            if (assetId) {
                $.get('get_asset_categories.php', {asset_id: assetId}, function(data) {
                    $('#category_id').html(data);
                });
            }
        });
    </script>
</body>
</html>
