<!-- Employee Assets Modal -->
  <div class="modal fade" id="assetsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-box"></i> Assets MR to <span id="employeeName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Asset Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Serial No</th>
                <th>Property No</th>
                <th>Action</th>
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