<!-- Enhanced Delete Consumable Confirmation Modal -->
<div class="modal fade" id="deleteConsumableEnhancedModal" tabindex="-1" aria-labelledby="deleteConsumableEnhancedLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteConsumableEnhancedLabel">
          <i class="bi bi-exclamation-triangle me-2"></i>Confirm Consumable Deletion
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Warning Alert -->
        <div class="alert alert-warning d-flex align-items-center" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <div>
            <strong>Warning:</strong> This action will permanently delete the consumable and archive it for audit purposes. This action cannot be undone.
          </div>
        </div>

        <!-- Consumable Details Card -->
        <div class="card border-danger mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Consumable Details</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <p class="mb-2"><strong>Stock Number:</strong> <span id="deleteConsumableStockNo" class="text-primary"></span></p>
                <p class="mb-2"><strong>Description:</strong> <span id="deleteConsumableDescription"></span></p>
                <p class="mb-2"><strong>Category:</strong> <span id="deleteConsumableCategory"></span></p>
              </div>
              <div class="col-md-6">
                <p class="mb-2"><strong>Quantity on Hand:</strong> <span id="deleteConsumableQuantity" class="fw-bold"></span></p>
                <p class="mb-2"><strong>Unit:</strong> <span id="deleteConsumableUnit"></span></p>
                <p class="mb-2"><strong>Unit Value:</strong> ₱<span id="deleteConsumableValue"></span></p>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <p class="mb-2"><strong>Total Value:</strong> ₱<span id="deleteConsumableTotalValue" class="fw-bold text-success"></span></p>
                <p class="mb-2"><strong>Status:</strong> <span id="deleteConsumableStatus"></span></p>
              </div>
              <div class="col-md-6">
                <p class="mb-2"><strong>Office:</strong> <span id="deleteConsumableOffice"></span></p>
                <p class="mb-0"><strong>Last Updated:</strong> <span id="deleteConsumableLastUpdated"></span></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Archive Information -->
        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="bi bi-archive me-2"></i>
          <div>
            <strong>Archive Process:</strong> Before deletion, this consumable will be archived to the assets_archive table with a timestamp for audit trail and compliance purposes.
          </div>
        </div>

        <!-- Confirmation Text -->
        <div class="text-center">
          <p class="mb-0">Are you sure you want to delete <strong id="deleteConsumableConfirmName" class="text-danger"></strong>?</p>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
        <button type="button" id="confirmDeleteConsumableEnhanced" class="btn btn-danger">
          <i class="bi bi-trash me-1"></i>Yes, Delete Consumable
        </button>
      </div>
    </div>
  </div>
</div>
