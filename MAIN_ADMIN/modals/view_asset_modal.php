<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content shadow border border-dark">
      <div class="modal-body p-4" style="font-family: 'Courier New', Courier, monospace;">
        <div class="border border-2 border-dark rounded p-3">

          <!-- Header: Logo and QR -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <img id="municipalLogoImg" src="" alt="Municipal Logo" style="height: 70px;">
            <img id="viewQrCode" src="" alt="QR Code" style="height: 70px;">
          </div>

          <hr class="border-dark">

          <!-- Asset Image and Text Side-by-Side -->
          <div class="row">
            <!-- Left: Asset Image -->
            <div class="col-5 text-center">
              <label class="form-label fw-bold">Asset Image</label>
              <img id="viewAssetImage" src="" alt="Asset Image" class="img-fluid border border-dark rounded" style="max-height: 150px; object-fit: contain;">
            </div>

            <!-- Right: Asset Info -->
            <div class="col-7">
              <p class="mb-1"><strong>Office:</strong> <span id="viewOfficeName"></span></p>
              <p class="mb-1"><strong>Category:</strong> <span id="viewCategoryName"></span></p>
              <p class="mb-1"><strong>Type:</strong> <span id="viewType"></span></p>
              <p class="mb-1"><strong>Status:</strong> <span id="viewStatus"></span></p>
              <p class="mb-1"><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
              <p class="mb-1"><strong>Unit:</strong> <span id="viewUnit"></span></p>
            </div>
          </div>

          <hr class="border-dark">

          <!-- Bottom Description and Dates -->
          <div class="mt-3">
            <p class="mb-1"><strong>Description:</strong> <span id="viewDescription"></span></p>
            <p class="mb-1"><strong>Acquisition Date:</strong> <span id="viewAcquisitionDate"></span></p>
            <p class="mb-1"><strong>Last Updated:</strong> <span id="viewLastUpdated"></span></p>
            <p class="mb-1"><strong>Value:</strong> ₱ <span id="viewValue"></span></p>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
