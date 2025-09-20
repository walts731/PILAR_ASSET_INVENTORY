<?php
// Simple view modal for consumables (non-asset specific fields)
?>
<div class="modal fade" id="viewConsumableModal" tabindex="-1" aria-labelledby="viewConsumableModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewConsumableModalLabel">
          <i class="bi bi-eye me-2"></i> Consumable Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-md-8">

            <div class="card shadow-sm mb-3">
              <div class="card-header py-2"><strong>General</strong></div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-12"><strong>Description:</strong> <span id="consDescription">—</span></div>
                  <div class="col-12"><strong>Office:</strong> <span id="consOffice">—</span></div>
                  <div class="col-12"><strong>Stock No. (Property No.):</strong> <span id="consPropertyNo">—</span></div>
                </div>
              </div>
            </div>

            <div class="card shadow-sm mb-3">
              <div class="card-header py-2"><strong>Inventory</strong></div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-md-4"><strong>On Hand:</strong> <span id="consQuantity">0</span></div>
                  <div class="col-md-4"><strong>Restocked (Last):</strong> <span id="consAddedStock">0</span></div>
                  <div class="col-md-4"><strong>Unit:</strong> <span id="consUnit">—</span></div>
                  <div class="col-md-4"><strong>Status:</strong> <span id="consStatus" class="badge bg-secondary">—</span></div>
                </div>
              </div>
            </div>

            <div class="card shadow-sm">
              <div class="card-header py-2"><strong>Cost</strong></div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-md-6"><strong>Unit Price:</strong> ₱<span id="consValue">0.00</span></div>
                  <div class="col-md-6"><strong>Total Value:</strong> ₱<span id="consTotalValue">0.00</span></div>
                  <div class="col-12"><strong>Last Updated:</strong> <span id="consLastUpdated">—</span></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="text-center">
              <img id="consImage" src="" alt="Consumable Image" class="img-fluid border rounded" style="max-height: 220px; object-fit: contain; display: none;" />
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
