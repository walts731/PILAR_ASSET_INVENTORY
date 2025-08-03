<!-- Update Consumable Modal -->
<div class="modal fade" id="updateConsumableModal" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="update_consumable.php" method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateLabel">Update Consumable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="consumable_id">

        <div class="row g-3">
          <div class="col-md-12">
            <label for="edit_description" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
          </div>

          <div class="col-md-6">
            <label for="edit_category" class="form-label">Category</label>
            <select name="category" id="edit_category" class="form-select" required>
              <option value="">Select Category</option>
              <?php
              $catRes = $conn->query("SELECT id, category_name FROM categories");
              while ($cat = $catRes->fetch_assoc()):
              ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label for="edit_unit" class="form-label">Unit</label>
            <select class="form-select" name="unit" id="edit_unit" required>
              <option value="">Select Unit</option>
              <?php
              $query = "SELECT unit_name FROM unit ORDER BY unit_name ASC";
              $result = $conn->query($query);
              while ($row = $result->fetch_assoc()):
              ?>
                <option value="<?= htmlspecialchars($row['unit_name']) ?>"><?= htmlspecialchars($row['unit_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label for="edit_quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" id="edit_quantity" required>
          </div>

          <div class="col-md-6">
            <label for="edit_status" class="form-label">Status</label>
            <select name="status" id="edit_status" class="form-select" required>
              <option value="available">Available</option>
              <option value="unavailable">Unavailable</option>
            </select>
          </div>
        </div>

        <div class="row g-3 mt-2">
          <div class="col-md-6">
            <label for="edit_consumable_image" class="form-label">Change Image</label>
            <input type="file" class="form-control" name="image" id="edit_consumable_image" accept="image/*">
          </div>

          <div class="col-md-6 text-center">
            <label class="form-label d-block">Current Image</label>
            <img id="edit_consumable_preview" src="#" alt="Current Image"
              class="img-fluid border rounded"
              style="max-height: 200px; object-fit: contain;"
              onerror="this.src='placeholder.jpg';">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Save Changes</button>
      </div>
    </form>
  </div>
</div>
