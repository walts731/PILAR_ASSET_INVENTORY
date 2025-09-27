<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConsumableModal" tabindex="-1" aria-labelledby="deleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteConsumableForm" action="delete_consumable.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="deleteConsumableId">
        <!-- Preserve office filter on redirect -->
        <input type="hidden" name="office" id="deleteConsumableOffice" value="">
        <p>Are you sure you want to delete <strong id="deleteConsumableName"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
