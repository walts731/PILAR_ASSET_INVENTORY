<!-- Update Asset Modal -->
<div class="modal fade" id="updateAssetModal" tabindex="-1" aria-labelledby="updateAssetLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Wider modal -->
    <form action="update_asset.php" method="GET" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateAssetLabel">Update Asset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="asset_id">
        <input type="hidden" name="office" id="edit_asset_office">

        <div class="row g-3">
          <div class="col-md-6">
            <label for="edit_asset_name" class="form-label">Name</label>
            <input type="text" class="form-control" name="asset_name" id="edit_asset_name" required>
          </div>

          <div class="col-md-6">
            <label for="edit_asset_category" class="form-label">Category</label>
            <select name="category" id="edit_asset_category" class="form-select" required>
              <?php
              $catRes = $conn->query("SELECT id, category_name FROM categories");
              while ($cat = $catRes->fetch_assoc()):
              ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label for="edit_asset_unit" class="form-label">Unit</label>
            <select class="form-select" name="unit" id="edit_asset_unit" required>
              <option value="">Select Unit</option>
              <option value="pcs">pcs</option>
              <option value="box">box</option>
              <option value="pack">pack</option>
              <option value="bottle">bottle</option>
              <option value="liters">liters</option>
              <option value="kg">kg</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="edit_asset_quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" id="edit_asset_quantity" required>
          </div>

          <div class="col-md-6">
            <label for="edit_asset_status" class="form-label">Status</label>
            <select name="status" id="edit_asset_status" class="form-select" required>
              <option value="available">Available</option>
              <option value="borrowed">Borrowed</option>
              <option value="unavailable">Unavailable</option>
            </select>
          </div>

          <div class="col-md-12">
            <label for="edit_asset_description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="edit_asset_description" rows="3"></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Save Changes</button>
      </div>
    </form>
  </div>
</div>
