<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="edit_employee.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="editEmployeeLabel"><i class="bi bi-pencil-square"></i> Edit Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" name="employee_id" id="editEmployeeId">

          <div class="col-md-6">
            <label class="form-label">Employee No</label>
            <input type="text" name="employee_no" id="editEmployeeNo" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" id="editEmployeeName" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Office</label>
            <select name="office_id" id="editOfficeId" class="form-select" required>
              <option value="">Select Office</option>
              <?php
              $officeRes = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name");
              while ($o = $officeRes->fetch_assoc()) {
                echo "<option value='{$o['id']}'>" . htmlspecialchars($o['office_name']) . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" id="editStatus" class="form-select" required>
              <option value="permanent">Permanent</option>
              <option value="contractual">Contractual</option>
              <option value="resigned">Resigned</option>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Upload New Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Leave blank to keep current image.</small>
            <div class="mt-2">
              <img id="currentImagePreview" src="" alt="Current Image" width="70" class="rounded-circle border">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>