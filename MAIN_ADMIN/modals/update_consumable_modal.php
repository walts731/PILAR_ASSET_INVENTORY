<!-- Update Consumable Modal -->
<div class="modal fade" id="updateConsumableModal" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_consumable.php" method="GET" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateLabel">Update Consumable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="consumable_id">
        <div class="mb-3">
          <label for="edit_quantity" class="form-label">Quantity</label>
          <input type="number" class="form-control" name="quantity" id="edit_quantity" required>
        </div>
        <div class="mb-3">
          <label for="edit_status" class="form-label">Status</label>
          <select name="status" id="edit_status" class="form-select" required>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Save Changes</button>
      </div>
    </form>
  </div>
</div>
