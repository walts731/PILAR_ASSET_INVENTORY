<!-- Update Consumable Modal -->
<div class="modal fade" id="updateConsumableModal" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form action="update_consumable.php" method="POST" enctype="multipart/form-data" class="modal-content needs-validation" novalidate>
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-pencil-square text-info"></i>
          <div>
            <h5 class="modal-title mb-0" id="updateLabel">Update Consumable</h5>
            <small class="text-muted">Edit details, status, and image. Fields marked with * are required.</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body py-3">
        <input type="hidden" name="id" id="consumable_id">
        <input type="hidden" name="existing_image" id="edit_existing_image">
        <input type="hidden" name="office" id="updateConsumableOffice">
        <div class="row g-3">
          <!-- Left: Compact details form -->
          <div class="col-12 col-md-7">
            <div class="row g-2">
              <div class="col-12">
                <label for="edit_description" class="form-label small mb-1">Description <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-sm" name="description" id="edit_description" rows="3" placeholder="Short, clear description" required></textarea>
                <div class="form-text small">Keep it concise so others can identify this item quickly.</div>
                <div class="invalid-feedback">Description is required.</div>
              </div>
              <div class="col-6">
                <label for="edit_unit" class="form-label small mb-1">Unit <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="unit" id="edit_unit" required>
                  <option value="">Select unit</option>
                  <?php
                  $query = "SELECT unit_name FROM unit ORDER BY unit_name ASC";
                  $result = $conn->query($query);
                  while ($row = $result->fetch_assoc()):
                  ?>
                    <option value="<?= htmlspecialchars($row['unit_name']) ?>"><?= htmlspecialchars($row['unit_name']) ?></option>
                  <?php endwhile; ?>
                </select>
                <div class="invalid-feedback">Please select a unit.</div>
              </div>
              <div class="col-6">
                <label for="edit_quantity" class="form-label small mb-1">Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control form-control-sm" name="quantity" id="edit_quantity" inputmode="numeric" min="0" step="1" placeholder="0" required>
                <div class="form-text small">Whole numbers only.</div>
                <div class="invalid-feedback">Enter a valid quantity (0 or more).</div>
              </div>
              <div class="col-12 col-sm-6">
                <label for="edit_status" class="form-label small mb-1">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-select-sm" required>
                  <option value="available">Available</option>
                  <option value="unavailable">Unavailable</option>
                </select>
                <div class="form-text small">Set to unavailable if it should not be issued.</div>
                <div class="invalid-feedback">Please select a status.</div>
              </div>
            </div>
          </div>

          <!-- Right: Compact image section -->
          <div class="col-12 col-md-5">
            <div class="row g-2 align-items-start">
              <div class="col-12">
                <label for="edit_consumable_image" class="form-label small mb-1">Image</label>
                <input type="file" class="form-control form-control-sm" name="image" id="edit_consumable_image" accept="image/png, image/jpeg, image/jpg, image/webp">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" value="1" id="remove_consumable_image" name="remove_image">
                  <label class="form-check-label" for="remove_consumable_image">Remove current image</label>
                </div>
                <div class="form-text small">JPG, PNG, WebP • Max 3 MB</div>
              </div>
              <div class="col-12 text-center">
                <div class="border rounded bg-light p-2">
                  <img id="edit_consumable_preview" src="#" alt="Current Image" class="img-fluid"
                       style="max-height: 160px; object-fit: contain;"
                       onerror="this.onerror=null; this.src='../img/1.png';">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between align-items-center py-2">
        <div class="text-muted small" id="updateConsumableHelper">Review before saving.</div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary">
            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true" id="updateConsumableSpinner"></span>
            Save
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
