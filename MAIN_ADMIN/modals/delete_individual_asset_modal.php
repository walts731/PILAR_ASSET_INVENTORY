<!-- Delete Individual Asset Modal -->
<div class="modal fade" id="deleteIndividualAssetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="delete_asset.php" method="GET" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bi bi-exclamation-triangle me-2"></i>Delete Individual Asset
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="deleteIndividualAssetId">
        <input type="hidden" name="office" id="deleteIndividualAssetOffice">
        
        <div class="alert alert-warning d-flex align-items-start" role="alert">
          <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1"></i>
          <div>
            <strong>Warning!</strong> This action will permanently delete this individual asset item.
          </div>
        </div>
        
        <p class="mb-3">Are you sure you want to delete this individual asset item:</p>
        <div class="card bg-light">
          <div class="card-body">
            <h6 class="card-title mb-2">
              <i class="bi bi-box-seam me-1"></i>
              <strong id="deleteIndividualAssetDescription"></strong>
            </h6>
            <div class="row g-2 small">
              <div class="col-6">
                <strong>Property No:</strong> <span id="deleteIndividualAssetPropertyNo"></span>
              </div>
              <div class="col-6">
                <strong>Inventory Tag:</strong> <span id="deleteIndividualAssetInventoryTag"></span>
              </div>
            </div>
            <small class="text-muted mt-2 d-block">
              <i class="bi bi-info-circle me-1"></i>
              This asset has no ICS/PAR records and can be safely deleted.
            </small>
          </div>
        </div>
        
        <div class="mt-3">
          <strong>This will:</strong>
          <ul class="mb-0">
            <li>Delete this individual asset item from assets table</li>
            <li>Remove any MR (Property Tag) records for this item</li>
            <li>Delete associated QR code and images</li>
            <li>Archive the data for audit purposes</li>
            <li>Decrease the quantity in the parent asset record</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-trash me-1"></i>Yes, Delete This Item
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
      </div>
    </form>
  </div>
</div>
