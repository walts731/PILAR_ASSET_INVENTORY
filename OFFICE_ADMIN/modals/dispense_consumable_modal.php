<div class="modal fade" id="dispenseConsumableModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="dispenseForm" action="process_dispense.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Dispense Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="asset_id" id="dispenseAssetId">
          <input type="hidden" name="recipient_user_id" value="<?= $_SESSION['user_id'] ?>">

          <h5>Item: <span id="dispenseItemName" class="fw-bold"></span></h5>
          <p>Current Stock: <span id="dispenseCurrentStock"></span></p>

          <div class="mb-3">
            <label for="quantity_consumed" class="form-label">Quantity to Dispense</label>
            <input type="number" class="form-control" name="quantity_consumed" id="quantity_consumed" required min="1">
          </div>

          <div class="mb-3">
            <label for="remarks" class="form-label">Remarks (Optional)</label>
            <textarea class="form-control" name="remarks" id="remarks" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm Dispense</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const dispenseModal = document.getElementById('dispenseConsumableModal');
  dispenseModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const assetId = button.getAttribute('data-id');
    const assetName = button.getAttribute('data-name');
    const currentStock = button.getAttribute('data-stock');

    // Populate modal fields
    const modal = this;
    modal.querySelector('#dispenseAssetId').value = assetId;
    modal.querySelector('#dispenseItemName').textContent = assetName;
    modal.querySelector('#dispenseCurrentStock').textContent = currentStock;
    modal.querySelector('#quantity_consumed').max = currentStock;
  });
});
</script>
