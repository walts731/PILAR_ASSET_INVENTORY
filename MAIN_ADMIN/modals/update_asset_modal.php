<!-- Update Asset Modal -->
<div class="modal fade" id="updateAssetModal" tabindex="-1" aria-labelledby="updateAssetLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="update_asset.php" method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateAssetLabel">Update Asset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="asset_id">
        <input type="hidden" name="office" id="edit_asset_office">

        <div class="row g-3">
          <!-- Description -->
          <div class="col-md-12">
            <label for="edit_asset_description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="edit_asset_description" rows="3" required></textarea>
          </div>

          <!-- Category -->
          <div class="col-md-6">
            <label for="edit_asset_category" class="form-label">Category</label>
            <select name="category" id="edit_asset_category" class="form-select" required>
              <option value="">Select Category</option>
              <?php
              $catRes = $conn->query("SELECT id, category_name FROM categories");
              while ($cat = $catRes->fetch_assoc()):
              ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Unit (Dynamic from units table) -->
          <div class="col-md-6">
            <label for="edit_asset_unit" class="form-label">Unit</label>
            <select class="form-select" name="unit" id="edit_asset_unit" required>
              <option value="">Select Unit</option>
              <?php
              $unitRes = $conn->query("SELECT unit_name FROM unit");
              while ($unit = $unitRes->fetch_assoc()):
              ?>
                <option value="<?= htmlspecialchars($unit['unit_name']) ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Quantity -->
          <div class="col-md-6">
            <label for="edit_asset_quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" id="edit_asset_quantity" required>
          </div>

          <!-- Status -->
          <div class="col-md-6">
            <label for="edit_asset_status" class="form-label">Status</label>
            <select name="status" id="edit_asset_status" class="form-select" required>
              <option value="available">Available</option>
              <option value="borrowed">Borrowed</option>
              <option value="unavailable">Unavailable</option>
            </select>
          </div>

          <!-- Image Upload and Preview -->
          <div class="col-md-6">
            <label for="edit_asset_image" class="form-label">Change Image</label>
            <input type="file" class="form-control" name="image" id="edit_asset_image" accept="image/*">
          </div>

          <div class="col-md-6 text-center">
            <label class="form-label d-block">Current Image</label>
            <img id="edit_asset_preview" src="#" alt="Current Image" class="img-fluid border rounded" style="max-height: 200px; object-fit: contain;">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Save Changes</button>
      </div>
    </form>
  </div>
</div>
