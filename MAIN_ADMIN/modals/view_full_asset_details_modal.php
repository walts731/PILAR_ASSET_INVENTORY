<!-- View Full Asset Modal -->
<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="viewAssetModalLabel">Asset Full Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Asset Name:</label>
            <p id="view_asset_name" class="form-control-plaintext"></p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Category:</label>
            <p id="view_category" class="form-control-plaintext"></p>
          </div>
          <div class="col-md-12">
            <label class="form-label fw-bold">Description:</label>
            <p id="view_description" class="form-control-plaintext"></p>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Quantity:</label>
            <p id="view_quantity" class="form-control-plaintext"></p>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Unit:</label>
            <p id="view_unit" class="form-control-plaintext"></p>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Status:</label>
            <p id="view_status" class="form-control-plaintext"></p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Estimated Value:</label>
            <p id="view_value" class="form-control-plaintext"></p>
          </div>
          <div class="col-md-6 text-center">
            <label class="form-label fw-bold">QR Code:</label>
            <div id="view_qr">
              <!-- QR image will go here -->
              <img src="" id="qr_image" alt="QR Code" class="img-fluid border p-2">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
