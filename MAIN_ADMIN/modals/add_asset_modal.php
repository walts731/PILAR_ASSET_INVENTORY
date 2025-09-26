<?php
require_once '../connect.php';

// Fetch categories for assets
$category_query = $conn->query("SELECT id, category_name FROM categories");
$categories = $category_query->fetch_all(MYSQLI_ASSOC);

// Fetch offices
$office_query = $conn->query("SELECT id, office_name FROM offices");
$offices = $office_query->fetch_all(MYSQLI_ASSOC);

// Fetch units
$unit_query = $conn->query("SELECT id, unit_name FROM unit");
$units = $unit_query->fetch_all(MYSQLI_ASSOC);

// Auto-generate property number (for assets)
$property_query = $conn->query("SELECT COUNT(*) AS total FROM assets WHERE type='asset'");
$totalAssets = $property_query->fetch_assoc()['total'] + 1;
$property_no = "PROP-" . str_pad($totalAssets, 4, "0", STR_PAD_LEFT);

// Auto-generate stock number (for consumables)
$stock_query = $conn->query("SELECT COUNT(*) AS total FROM assets WHERE type='consumable'");
$totalStock = $stock_query->fetch_assoc()['total'] + 1;
$stock_no = "STOCK-" . str_pad($totalStock, 4, "0", STR_PAD_LEFT);
?>

<!-- Add Asset Modal -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="add_asset.php" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssetModalLabel"><i class="bi bi-plus-circle"></i> Add New Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label> <span class="text-danger">*</span>
                    <textarea name="description" id="description" class="form-control" rows="2" required></textarea>
                </div>
                <!-- Optional Fields -->
                <div class="col-12">
                    <h6 class="text-muted fw-semibold mt-2 mb-1"><i class="bi bi-box-seam me-1"></i> Asset Details (Optional)</h6>
                    <hr class="mt-1 mb-3">
                </div>
                <div class="col-md-12">
                    <h6 class="text-muted fw-semibold mt-2 mb-1"><i class="bi bi-tag me-1"></i> Brand/Model</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" name="model" id="model" class="form-control" placeholder="Enter model">
                        </div>

                        <div class="col-md-6">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control" placeholder="Enter brand">
                        </div>
                    </div>
                </div>

                <!-- Property / Stock No. -->
                <div class="col-md-4" id="propertyNoGroup">
                    <label for="property_no" class="form-label">Property No.</label>
                    <input type="text" name="property_no" id="property_no" class="form-control" placeholder="Enter Property No.">
                </div>

                <div class="col-md-4 d-none" id="stockNoGroup">
                    <label for="stock_no" class="form-label">Stock No.</label>
                    <input type="text" name="stock_no" id="stock_no" class="form-control"
                        value="<?= $stock_no ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label for="inventory_tag" class="form-label">Inventory Tag (optional)</label>
                    <input type="text" name="inventory_tag" id="inventory_tag" class="form-control" placeholder="e.g., INV-0001">
                    <small class="text-muted">Providing Inventory Tag and Employee will auto-create MR details.</small>
                </div>

                <div class="col-md-4">
                    <label for="employee_name" class="form-label">Employee (Person Accountable)</label>
                    <input list="employeesList" name="employee_name" id="employee_name" class="form-control" placeholder="Type to search…">
                    <datalist id="employeesList">
                        <?php
                        $emp_rs = $conn->query("SELECT name FROM employees ORDER BY name ASC");
                        if ($emp_rs) {
                            while ($er = $emp_rs->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($er['name']) . '"></option>';
                            }
                        }
                        ?>
                    </datalist>
                </div>

                <div class="col-md-4">
                    <label for="end_user" class="form-label">End User (optional)</label>
                    <input type="text" name="end_user" id="end_user" class="form-control" placeholder="End user name">
                </div>
                <div class="col-md-12">
                    <div class="row align-items-center">
                        <!-- Upload Input (Left) -->
                        <div class="col-md-6">
                            <input type="file" name="asset_image" id="asset_image" class="form-control" accept="image/*" onchange="previewImage(event)">
                        </div>

                        <!-- Image Preview (Right) -->
                        <div class="col-md-6 text-center">
                            <label class="form-label d-block">Preview</label>
                            <img id="assetImagePreview" src="#" alt="Image Preview" class="img-thumbnail d-none" style="max-width: 200px; height: auto;">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="quantity" class="form-label">Quantity</label> <span class="text-danger">*</span>
                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                </div>

                <div class="col-md-3">
                    <label for="unit" class="form-label">Unit</label> <span class="text-danger">*</span>
                    <select name="unit" id="unit" class="form-select" required>
                        <option value="" disabled selected>Select Unit</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= htmlspecialchars($unit['unit_name']) ?>">
                                <?= htmlspecialchars(ucfirst($unit['unit_name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="value" class="form-label">Value (₱)</label> <span class="text-danger">*</span>
                    <input type="number" name="value" id="value" class="form-control" step="0.01" min="0" required>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label> <span class="text-danger">*</span>
                    <select name="status" id="status" class="form-select" required>
                        <option value="serviceable">Serviceable</option>
                        <option value="unserviceable">Unserviceable</option>
                    </select>
                </div>

                <input type="hidden" name="office_id" value="<?= $_SESSION['office_id'] ?>">

                <div class="col-md-4">
                    <label for="type" class="form-label">Type</label> <span class="text-danger">*</span>
                    <select name="type" id="type" class="form-select" required>
                        <option value="asset">Asset</option>
                        <option value="consumable">Consumable</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label> <span class="text-danger">*</span>
                    <select name="category" id="category" class="form-select" required>
                        <option value="" disabled selected>Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="acquisition_date" class="form-label">Acquisition Date</label>
                    <input type="date" name="acquisition_date" id="acquisition_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">
                <p class="text-danger text-start mb-0">* Required fields</p>
                <div>
                    <button type="submit" class="btn btn-info"><i class="bi bi-save"></i> Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
    // Preview uploaded image
    function previewImage(event) {
        const preview = document.getElementById('assetImagePreview');
        const file = event.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        } else {
            preview.src = '#';
            preview.classList.add('d-none');
        }
    }

    // Toggle Property No. / Stock No.
    document.getElementById('type').addEventListener('change', function() {
        const propertyGroup = document.getElementById('propertyNoGroup');
        const stockGroup = document.getElementById('stockNoGroup');

        if (this.value === 'consumable') {
            propertyGroup.classList.add('d-none');
            stockGroup.classList.remove('d-none');
            document.getElementById('property_no').disabled = true;
            document.getElementById('stock_no').disabled = false;
        } else {
            stockGroup.classList.add('d-none');
            propertyGroup.classList.remove('d-none');
            document.getElementById('stock_no').disabled = true;
            document.getElementById('property_no').disabled = false;
        }
    });

    // Default Unit selection to 'unit' if available
    (function () {
        const unitSelect = document.getElementById('unit');
        if (!unitSelect) return;
        for (const opt of unitSelect.options) {
            if ((opt.value || '').toLowerCase() === 'unit') {
                unitSelect.value = opt.value;
                break;
            }
        }
    })();
</script>