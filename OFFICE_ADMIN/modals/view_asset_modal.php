<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow-sm border-0">
      <div class="modal-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%);">
        <div class="d-flex align-items-center gap-2">
          <img id="municipalLogoImg" src="" alt="Logo" class="rounded" style="height: 40px; width: 40px; object-fit: contain;">
          <div>
            <h5 class="modal-title mb-0">Asset Details</h5>
            <small class="text-muted">Inventory Tag: <span id="viewInventoryTag"></span></small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span id="viewStatus" class="badge bg-secondary"></span>
          <img id="viewQrCode" src="" alt="QR Code" class="border rounded" style="height: 40px; width: 40px; object-fit: contain;">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>

      <div class="modal-body p-3">
        <div class="row g-3">
          <div class="col-md-5">
            <div class="card h-100">
              <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <img id="viewAssetImage" src="" alt="Asset Image" class="img-fluid rounded border" style="max-height: 220px; object-fit: contain;">
                <div class="mt-2 small text-muted text-center">Preview</div>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="fw-semibold mb-2">Description</h6>
                <p class="mb-3" id="viewDescription"></p>

                <div class="row g-2">
                  <div class="col-6">
                    <div class="small text-muted">Office</div>
                    <div id="viewOfficeName"></div>
                  </div>
                  <div class="col-6">
                    <div class="small text-muted">Category</div>
                    <div id="viewCategoryName"></div>
                  </div>
                  <div class="col-4">
                    <div class="small text-muted">Type</div>
                    <div id="viewType"></div>
                  </div>
                  <div class="col-4">
                    <div class="small text-muted">Quantity</div>
                    <div id="viewQuantity"></div>
                  </div>
                  <div class="col-4">
                    <div class="small text-muted">Unit</div>
                    <div id="viewUnit"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="fw-semibold mb-2">Identification</h6>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="small text-muted">Serial No.</div>
                    <div id="viewSerialNo"></div>
                  </div>
                  <div class="col-6">
                    <div class="small text-muted">Code</div>
                    <div id="viewCode"></div>
                  </div>
                  <div class="col-12">
                    <div class="small text-muted">Property No.</div>
                    <div id="viewPropertyNo"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="fw-semibold mb-2">Assignment & Specs</h6>
                <div class="row g-2">
                  <div class="col-12">
                    <div class="small text-muted">Person Accountable</div>
                    <div id="viewEmployeeName"></div>
                  </div>
                  <div class="col-6">
                    <div class="small text-muted">Model</div>
                    <div id="viewModel"></div>
                  </div>
                  <div class="col-6">
                    <div class="small text-muted">Brand</div>
                    <div id="viewBrand"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-md-12">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="fw-semibold mb-2">Valuation & Dates</h6>
                <div class="row g-2">
                  <div class="col-4">
                    <div class="small text-muted">Unit Cost (₱)</div>
                    <div id="viewValue"></div>
                  </div>
                  <div class="col-4">
                    <div class="small text-muted">Total Value (₱)</div>
                    <div id="viewTotalValue"></div>
                  </div>
                  <div class="col-4">
                    <div class="small text-muted">Acquired</div>
                    <div id="viewAcquisitionDate"></div>
                  </div>
                  <div class="col-4">
                    <div class="small text-muted">Last Updated</div>
                    <div id="viewLastUpdated"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
