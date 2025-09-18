<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow border border-dark">
      <div class="modal-body p-4" style="font-family: 'Courier New', Courier, monospace;">
        <div class="border border-2 border-dark rounded p-3">

          <!-- HEADER -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <img id="municipalLogoImg" src="" alt="Municipal Logo" style="height: 70px;">
            <div class="text-center flex-grow-1">
              <h6 class="m-0 text-uppercase fw-bold">Government Property</h6>
              <p class="m-0"><strong>Inventory Tag:</strong> <span id="viewInventoryTag"></span></p>
            </div>
            <img id="viewQrCode" src="" alt="QR Code" style="height: 70px;">
          </div>

          <hr class="border-dark">

          <!-- SECTION 1: DESCRIPTION -->
          <div class="mb-3">
            <h6 class="fw-bold text-decoration-underline">Description</h6>
            <p class="mb-1"><span id="viewDescription"></span></p>
          </div>

          <!-- SECTION 2: IMAGE + BASIC DETAILS -->
          <div class="row mb-3">
            <div class="col-5 text-center">
              <img id="viewAssetImage" src="" alt="Asset Image"
                   class="img-fluid border border-dark rounded"
                   style="max-height: 150px; object-fit: contain;">
            </div>
            <div class="col-7">
              <h6 class="fw-bold text-decoration-underline">Basic Information</h6>
              <p class="mb-1"><strong>Office:</strong> <span id="viewOfficeName"></span></p>
              <p class="mb-1"><strong>Category:</strong> <span id="viewCategoryName"></span></p>
              <p class="mb-1"><strong>Type:</strong> <span id="viewType"></span></p>
              <p class="mb-1"><strong>Status:</strong> <span id="viewStatus"></span></p>
              <p class="mb-1"><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
              <p class="mb-1"><strong>Unit:</strong> <span id="viewUnit"></span></p>
            </div>
          </div>

          <hr class="border-dark">

          <!-- SECTION 3: IDENTIFICATION NUMBERS -->
          <div class="mb-3">
            <h6 class="fw-bold text-decoration-underline">Identification</h6>
            <div class="row">
              <div class="col-6">
                <p class="mb-1"><strong>Serial No.:</strong> <span id="viewSerialNo"></span></p>
                <p class="mb-1"><strong>Code:</strong> <span id="viewCode"></span></p>
              </div>
              <div class="col-6">
                <p class="mb-1"><strong>Property No.:</strong> <span id="viewPropertyNo"></span></p>
              </div>
            </div>
          </div>

          <hr class="border-dark">

          <!-- SECTION 4: SPECIFICATIONS + ASSIGNMENT -->
          <div class="mb-3">
            <h6 class="fw-bold text-decoration-underline">Specifications & Assignment</h6>
            <div class="row">
              <div class="col-6">
                <p class="mb-1"><strong>Model:</strong> <span id="viewModel"></span></p>
                <p class="mb-1"><strong>Brand:</strong> <span id="viewBrand"></span></p>
              </div>
              <div class="col-6">
                <p class="mb-1"><strong>Person Accountable:</strong> <span id="viewEmployeeName"></span></p>
              </div>
            </div>
          </div>

          <hr class="border-dark">

          <!-- SECTION 5: VALUE + DATES -->
          <div>
            <h6 class="fw-bold text-decoration-underline">Valuation & Dates</h6>
            <div class="row">
              <div class="col-6">
                <p class="mb-1"><strong>Acquisition Date:</strong> <span id="viewAcquisitionDate"></span></p>
                <p class="mb-1"><strong>Last Updated:</strong> <span id="viewLastUpdated"></span></p>
              </div>
              <div class="col-6">
                <p class="mb-1"><strong>Unit Cost:</strong> ₱ <span id="viewValue"></span></p>
                <p class="mb-1"><strong>Total Value:</strong> ₱ <span id="viewTotalValue"></span></p>
              </div>
            </div>
          </div>

          <hr class="border-dark">

          <!-- SECTION 6: PER-ITEM DETAILS -->
          <div class="mt-3">
            <h6 class="fw-bold text-decoration-underline">Items</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 80px;">Item ID</th>
                    <th>Property No.</th>
                    <th>Inventory Tag</th>
                    <th>Serial No.</th>
                    <th>Status</th>
                    <th>QR Code</th>
                    <th>Date Acquired</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="viewItemsBody">
                  <!-- Filled by JS -->
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
