<!-- Delete No Property Tag Asset Modal -->
<div class="modal fade" id="deleteNoPropertyTagModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-exclamation-triangle text-warning me-2"></i>
          Delete Asset Without Property Tag
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Important:</strong> This action will permanently delete the asset and archive it for record keeping.
        </div>
        
        <p class="mb-3">Are you sure you want to delete the following asset?</p>
        
        <div class="card bg-light">
          <div class="card-body">
            <h6 class="card-title mb-2">
              <i class="bi bi-box me-2"></i>
              <span id="deleteNoPropertyAssetName">Asset Name</span>
            </h6>
            <div class="row">
              <div class="col-sm-6">
                <small class="text-muted">Category:</small><br>
                <span id="deleteNoPropertyAssetCategory">Category</span>
              </div>
              <div class="col-sm-6">
                <small class="text-muted">Value:</small><br>
                <span class="text-success fw-medium">₱<span id="deleteNoPropertyAssetValue">0.00</span></span>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-sm-6">
                <small class="text-muted">Quantity:</small><br>
                <span id="deleteNoPropertyAssetQty">0</span> <span id="deleteNoPropertyAssetUnit">pcs</span>
              </div>
              <div class="col-sm-6">
                <small class="text-muted">ICS/PAR No:</small><br>
                <span id="deleteNoPropertyAssetNumber">N/A</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-3">
          <small class="text-muted">
            <i class="bi bi-shield-check me-1"></i>
            The asset will be archived before deletion to maintain audit trail.
          </small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="confirmDeleteNoPropertyTag">
          <i class="bi bi-trash me-2"></i>Yes, Delete Asset
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-2"></i>Cancel
        </button>
      </div>
    </div>
  </div>
</div>
