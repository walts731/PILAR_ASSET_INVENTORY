<!-- View Asset Modal -->
<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content shadow border border-dark">
      <div class="modal-body p-4" style="font-family: 'Courier New', Courier, monospace;">
        <div class="border border-2 border-dark rounded p-3">

          <!-- Header: Logo - Office - QR -->
          <div class="row align-items-center mb-4 text-center">
            <div class="col-4 text-start">
              <img id="municipalLogoImg" src="" alt="Municipal Logo" style="height: 80px;">
            </div>
            <div class="col-4">
              <h5 class="mb-0 fw-bold text-uppercase" id="viewOfficeName"></h5>
              <small class="text-muted" id="viewCategoryName"></small>
            </div>
            <div class="col-4 text-end">
              <img id="viewQrCode" src="" alt="QR Code" style="height: 80px; object-fit: contain;">
            </div>
          </div>

          <hr class="border-dark">

          <!-- Description -->
          <div class="mb-3">
            <strong>Description:</strong>
            <span id="viewDescription" class="ms-1"></span>
          </div>

          <!-- Type & Status -->
          <div class="row mb-2">
            <div class="col-6"><strong>Type:</strong> <span id="viewType"></span></div>
            <div class="col-6"><strong>Status:</strong> <span id="viewStatus"></span></div>
          </div>

          <!-- Quantity & Unit -->
          <div class="row mb-2">
            <div class="col-6"><strong>Quantity:</strong> <span id="viewQuantity"></span> <span id="viewUnit"></span></div>
            <div class="col-6"><strong>Value:</strong> ₱<span id="viewValue"></span></div>
          </div>

          <!-- Dates -->
          <div class="row mb-2">
            <div class="col-6"><strong>Acquired On:</strong> <span id="viewAcquisitionDate"></span></div>
            <div class="col-6"><strong>Last Updated:</strong> <span id="viewLastUpdated"></span></div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
