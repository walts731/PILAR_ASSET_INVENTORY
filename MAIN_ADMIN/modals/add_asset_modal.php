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

<style>
.add-asset-modal .modal-dialog {
  max-width: 900px;
  margin: 1rem auto;
}

.add-asset-modal .modal-content {
  border: none;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.add-asset-modal .modal-header {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
  color: white;
  border-radius: 12px 12px 0 0;
  padding: 1rem 1.5rem;
}

.add-asset-modal .modal-title {
  font-weight: 600;
  font-size: 1.1rem;
}

.add-asset-modal .btn-close {
  filter: brightness(0) invert(1);
}

.add-asset-modal .nav-pills {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 0.25rem;
  margin-bottom: 1.5rem;
}

.add-asset-modal .nav-pills .nav-link {
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
  color: #6c757d;
  transition: all 0.3s ease;
}

.add-asset-modal .nav-pills .nav-link.active {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
}

.add-asset-modal .tab-content {
  min-height: 400px;
}

.add-asset-modal .form-floating label {
  font-weight: 500;
  color: #495057;
}

.add-asset-modal .form-control, .add-asset-modal .form-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.add-asset-modal .form-control:focus, .add-asset-modal .form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.add-asset-modal .field-group {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.add-asset-modal .field-group h6 {
  color: #495057;
  font-weight: 600;
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

.add-asset-modal .image-upload-area {
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.add-asset-modal .image-upload-area:hover {
  border-color: #0d6efd;
  background-color: #f8f9ff;
}

.add-asset-modal .image-upload-area.dragover {
  border-color: #0d6efd;
  background-color: #e7f1ff;
}

.add-asset-modal .preview-placeholder {
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  padding: 2rem 1rem;
  background-color: #f8f9fa;
  min-height: 150px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.add-asset-modal .preview-container {
  min-height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.add-asset-modal .progress-bar {
  background: linear-gradient(90deg, #0d6efd 0%, #0b5ed7 100%);
}

.add-asset-modal .btn-gradient {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
  border: none;
  color: white;
  font-weight: 500;
  border-radius: 8px;
  padding: 0.5rem 1.5rem;
  transition: all 0.3s ease;
}

.add-asset-modal .btn-gradient:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
  color: white;
}

.add-asset-modal .summary-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 8px;
  padding: 1rem;
  border-left: 4px solid #0d6efd;
}

@media (max-height: 700px) {
  .add-asset-modal .modal-body {
    max-height: calc(95vh - 140px);
    overflow-y: auto;
  }
}

@media (max-width: 768px) {
  .add-asset-modal .modal-dialog {
    margin: 0.5rem;
    max-width: calc(100% - 1rem);
  }
  
  .add-asset-modal .nav-pills .nav-link {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
  }
}
</style>

<!-- Add Asset Modal -->
<div class="modal fade add-asset-modal" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="add_asset.php" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="addAssetModalLabel">
          <i class="bi bi-plus-circle me-2"></i>Add New Asset
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Progress Bar -->
        <div class="progress mb-3" style="height: 4px;">
          <div class="progress-bar" role="progressbar" style="width: 33%"></div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills nav-justified" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basic-info" type="button" role="tab">
              <i class="bi bi-info-circle me-1"></i>Basic Info
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="details-tab" data-bs-toggle="pill" data-bs-target="#details-info" type="button" role="tab">
              <i class="bi bi-gear me-1"></i>Details
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="assignment-tab" data-bs-toggle="pill" data-bs-target="#assignment-info" type="button" role="tab">
              <i class="bi bi-person-check me-1"></i>Assignment
            </button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
          <!-- Basic Info Tab -->
          <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
            <div class="row g-3">
              <div class="col-12">
                <div class="form-floating">
                  <textarea name="description" id="description" class="form-control" rows="3" required placeholder="Asset Description"></textarea>
                  <label for="description">Description <span class="text-danger">*</span></label>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-floating">
                  <select name="type" id="type" class="form-select" required>
                    <option value="asset">Asset</option>
                    <option value="consumable">Consumable</option>
                  </select>
                  <label for="type">Type <span class="text-danger">*</span></label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <select name="category" id="category" class="form-select" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label for="category">Category <span class="text-danger">*</span></label>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-floating">
                  <input type="number" name="quantity" id="quantity" class="form-control" min="1" required placeholder="1">
                  <label for="quantity">Quantity <span class="text-danger">*</span></label>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-floating">
                  <select name="unit" id="unit" class="form-select" required>
                    <option value="" disabled selected>Select Unit</option>
                    <?php foreach ($units as $unit): ?>
                      <option value="<?= htmlspecialchars($unit['unit_name']) ?>">
                        <?= htmlspecialchars(ucfirst($unit['unit_name'])) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <label for="unit">Unit <span class="text-danger">*</span></label>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-floating">
                  <input type="number" name="value" id="value" class="form-control" step="0.01" min="0" required placeholder="0.00">
                  <label for="value">Value (₱) <span class="text-danger">*</span></label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <select name="status" id="status" class="form-select" required>
                    <option value="serviceable">Serviceable</option>
                    <option value="unserviceable">Unserviceable</option>
                  </select>
                  <label for="status">Status <span class="text-danger">*</span></label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <input type="date" name="acquisition_date" id="acquisition_date" class="form-control" value="<?= date('Y-m-d') ?>">
                  <label for="acquisition_date">Acquisition Date</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" name="supplier" id="supplier" class="form-control" placeholder="Supplier name">
                  <label for="supplier">Supplier</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Details Tab -->
          <div class="tab-pane fade" id="details-info" role="tabpanel">
            <div class="row g-3">
              <div class="col-12">
                <div class="field-group">
                  <h6><i class="bi bi-tag me-1"></i>Brand & Model Information</h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="brand" id="brand" class="form-control" placeholder="Brand">
                        <label for="brand">Brand</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="model" id="model" class="form-control" placeholder="Model">
                        <label for="model">Model</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="serial_no" id="serial_no" class="form-control" placeholder="Serial Number">
                        <label for="serial_no">Serial Number</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="code" id="code" class="form-control" placeholder="Asset Code">
                        <label for="code">Asset Code</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="field-group">
                  <h6><i class="bi bi-hash me-1"></i>Identification Numbers</h6>
                  <div class="row g-3">
                    <div class="col-md-6" id="propertyNoGroup">
                      <div class="form-floating">
                        <input type="text" name="property_no" id="property_no" class="form-control" placeholder="Property Number">
                        <label for="property_no">Property No.</label>
                      </div>
                    </div>
                    <div class="col-md-6 d-none" id="stockNoGroup">
                      <div class="form-floating">
                        <input type="text" name="stock_no" id="stock_no" class="form-control" value="<?= $stock_no ?>" readonly>
                        <label for="stock_no">Stock No.</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="inventory_tag" id="inventory_tag" class="form-control" placeholder="e.g., INV-0001">
                        <label for="inventory_tag">Inventory Tag</label>
                      </div>
                      <small class="text-muted">Providing Inventory Tag and Employee will auto-create MR details.</small>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Assignment Tab -->
          <div class="tab-pane fade" id="assignment-info" role="tabpanel">
            <div class="row g-3">
              <div class="col-12">
                <div class="field-group">
                  <h6><i class="bi bi-person-check me-1"></i>Assignment Information</h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input list="employeesList" name="employee_name" id="employee_name" class="form-control" placeholder="Type to search…">
                        <label for="employee_name">Employee (Person Accountable)</label>
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
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" name="end_user" id="end_user" class="form-control" placeholder="End user name">
                        <label for="end_user">End User</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="field-group">
                  <h6><i class="bi bi-cloud-upload me-1"></i>Asset Image</h6>
                  <div class="row g-3">
                    <!-- Upload Area (Left) -->
                    <div class="col-md-6">
                      <div class="image-upload-area" onclick="document.getElementById('asset_image').click()">
                        <i class="bi bi-cloud-upload display-4 text-muted mb-2"></i>
                        <p class="mb-2">Click to upload or drag and drop</p>
                        <small class="text-muted">JPG, PNG, GIF up to 10MB</small>
                        <input type="file" name="asset_image" id="asset_image" class="d-none" accept="image/*">
                      </div>
                    </div>
                    
                    <!-- Preview Area (Right) -->
                    <div class="col-md-6">
                      <div class="text-center h-100 d-flex flex-column justify-content-center">
                        <label class="form-label fw-semibold text-muted mb-2">Preview</label>
                        <div class="preview-container">
                          <img id="assetImagePreview" src="#" alt="Image Preview" class="img-thumbnail d-none" style="max-width: 100%; max-height: 200px; height: auto;">
                          <div id="previewPlaceholder" class="preview-placeholder">
                            <i class="bi bi-image display-4 text-muted mb-2"></i>
                            <p class="text-muted mb-0">No image selected</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="summary-card">
                  <h6 class="mb-3"><i class="bi bi-list-check me-1"></i>Asset Summary</h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <small class="text-muted">Description:</small>
                      <div id="summaryDescription" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                      <small class="text-muted">Type & Category:</small>
                      <div id="summaryTypeCategory" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-4">
                      <small class="text-muted">Quantity:</small>
                      <div id="summaryQuantity" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-4">
                      <small class="text-muted">Value:</small>
                      <div id="summaryValue" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-4">
                      <small class="text-muted">Assignee:</small>
                      <div id="summaryAssignee" class="fw-semibold">-</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="office_id" value="<?= $_SESSION['office_id'] ?>">

      <div class="modal-footer">
        <div class="d-flex justify-content-between align-items-center w-100">
          <div>
            <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
              <i class="bi bi-arrow-left me-1"></i>Previous
            </button>
          </div>
          <div>
            <small class="text-muted me-3">* Required fields</small>
            <button type="button" class="btn btn-gradient" id="nextBtn">
              Next <i class="bi bi-arrow-right ms-1"></i>
            </button>
            <button type="submit" class="btn btn-gradient" id="submitBtn" style="display: none;">
              <i class="bi bi-save me-1"></i>Save Asset
            </button>
            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>


<script src="js/add_asset_modal.js"></script>