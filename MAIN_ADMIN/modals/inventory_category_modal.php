<!-- Asset Modal -->
<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-sm">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
          <img id="municipalLogoImg" src="" alt="Municipal Logo" style="height: 40px;" />
          <h5 class="modal-title" id="viewAssetModalLabel">Asset Details</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body pt-3">
        <!-- General -->
        <div class="card shadow-sm mb-3">
          <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <strong>General</strong>
            <span id="viewStatus" class="badge bg-secondary">—</span>
          </div>
          <div class="card-body">
            <div class="mb-2"><strong>Description:</strong> <span id="viewDescription">—</span></div>
            <div class="row g-2">
              <div class="col-md-6"><strong>Office:</strong> <span id="viewOfficeName">—</span></div>
              <div class="col-md-6"><strong>Category:</strong> <span id="viewCategoryName">—</span></div>
              <div class="col-md-6"><strong>Type:</strong> <span id="viewType">—</span></div>
            </div>
          </div>
        </div>

        <!-- Inventory -->
        <div class="card shadow-sm mb-3">
          <div class="card-header py-2"><strong>Inventory</strong></div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-md-4"><strong>Quantity:</strong> <span id="viewQuantity">0</span></div>
              <div class="col-md-4"><strong>Unit:</strong> <span id="viewUnit">—</span></div>
            </div>
          </div>
        </div>

        <!-- Valuation & Dates -->
        <div class="card shadow-sm mb-3">
          <div class="card-header py-2"><strong>Valuation & Dates</strong></div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-md-6"><strong>Unit Cost:</strong> ₱ <span id="viewValue">0.00</span></div>
              <div class="col-md-6"><strong>Total Value:</strong> ₱ <span id="viewTotalValue">0.00</span></div>
              <div class="col-md-6"><strong>Acquired:</strong> <span id="viewAcquisitionDate">—</span></div>
              <div class="col-md-6"><strong>Last Updated:</strong> <span id="viewLastUpdated">—</span></div>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <div class="card shadow-sm">
          <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <strong>Items</strong>
            <small class="text-muted">Per-Item details</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Property No.</th>
                    <th>Inventory Tag</th>
                    <th>Serial No.</th>
                    <th>Status</th>
                    <th>Date Acquired</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="viewItemsBody"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
