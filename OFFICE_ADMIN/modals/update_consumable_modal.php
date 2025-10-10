<!-- Update Consumable Status Modal -->
<div class="modal fade" id="updateConsumableModal" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_consumable.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateLabel">Update Consumable Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="consumable_id">

        <div class="mb-3">
          <label for="edit_status" class="form-label">Status</label>
          <select name="status" id="edit_status" class="form-select" required>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
          </select>
        </div>

        <div class="alert alert-info mb-0">
          Only status can be updated by Office Admin. Other details are managed by Main Admin.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-info">Save</button>
      </div>
    </form>
  </div>
</div>
