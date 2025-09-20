<!-- Employee Assets Modal -->
<div class="modal fade" id="assetsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i class="bi bi-person-badge"></i>
          Employee Information & MR Assets
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Employee Info Section -->
        <div class="border rounded p-3 mb-3 bg-light">
          <div class="row g-3 align-items-center">
            <div class="col-auto">
              <img id="empInfoImage" src="" alt="Employee Image" class="rounded-circle border" style="width: 70px; height: 70px; object-fit: cover;" />
            </div>
            <div class="col">
              <div class="fw-bold fs-5" id="empInfoName">—</div>
              <div class="text-muted">
                <span class="me-3">Emp No: <span id="empInfoNo">—</span></span>
                <span class="me-3">Office: <span id="empInfoOffice">—</span></span>
                <span>Status: <span class="badge" id="empInfoStatusBadge">—</span></span>
                <span class="ms-2">Clearance: <span class="badge" id="empInfoClearanceBadge">—</span></span>
                <span class="ms-3">Date Joined: <span id="empInfoDateJoined">—</span></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Assets Table -->
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>Description</th>
                <th>Status</th>
                <th>Serial No</th>
                <th>Property No</th>
                <th>Inventory Tag</th>
                <th style="width: 120px;">Action</th>
              </tr>
            </thead>
            <tbody id="assetsTableBody">
              <tr>
                <td colspan="6" class="text-center text-muted">Select an employee to view assets.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>