<?php
// Fetch categories for assets
$category_query = $conn->query("SELECT id, category_name FROM categories WHERE type = 'asset'");
$categories = $category_query->fetch_all(MYSQLI_ASSOC);

// Fetch offices
$office_query = $conn->query("SELECT id, office_name FROM offices");
$offices = $office_query->fetch_all(MYSQLI_ASSOC);

// Get next asset ID for QR code generation
$nextIdRes = $conn->query("SELECT AUTO_INCREMENT as next_id FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'assets' AND TABLE_SCHEMA = DATABASE()");
$nextAssetId = ($nextIdRes && $row = $nextIdRes->fetch_assoc()) ? $row['next_id'] : '0';

// Fetch units
$unit_query = $conn->query("SELECT id, unit_name FROM unit");
$units = $unit_query->fetch_all(MYSQLI_ASSOC);

?>

<!-- Add Asset Modal -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="add_asset.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssetModalLabel"><i class="bi bi-plus-circle"></i> Add New Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">



                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label> <span class="text-danger">*</span>
                    <textarea name="description" id="description" class="form-control" rows="2" required></textarea>
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
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="office_id" class="form-label">Office</label> <span class="text-danger">*</span>
                    <select name="office_id" id="office_id" class="form-select" required>
                        <option value="" disabled selected>Select Office</option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['office_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

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
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-info"><i class="bi bi-save"></i> Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>