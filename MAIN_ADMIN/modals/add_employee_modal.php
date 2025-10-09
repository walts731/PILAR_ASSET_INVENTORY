<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="add_employee.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="addEmployeeLabel"><i class="bi bi-plus-circle"></i> Add Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <?php
          // Generate preview employee number
          $previewNo = "EMP0001";
          $res = $conn->query("SELECT employee_no FROM employees ORDER BY employee_id DESC LIMIT 1");
          if ($row = $res->fetch_assoc()) {
              $lastNo = intval(substr($row['employee_no'], 3));
              $previewNo = "EMP" . str_pad($lastNo + 1, 4, "0", STR_PAD_LEFT);
          }
          ?>
          <div class="col-md-6">
            <label class="form-label">Employee No</label>
            <input type="text" class="form-control" value="<?= $previewNo ?>" readonly>
            <!-- hidden input so it's not editable -->
            <input type="hidden" name="employee_no" value="<?= $previewNo ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com">
          </div>
          <div class="col-md-6">
            <label class="form-label">Office</label>
            <select name="office_id" class="form-select" required>
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
            <select name="status" class="form-select" required>
              <option value="permanent">Permanent</option>
              <option value="contractual">Contractual</option>
              <option value="resigned">Resigned</option>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Upload Image</label>
            <input type="file" name="image" id="employeeImage" class="form-control" accept="image/*">
            <div class="mt-2">
              <img id="previewImg" src="#" alt="Preview" class="img-thumbnail d-none" width="120">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Image preview
document.getElementById('employeeImage').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('previewImg');
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.classList.remove('d-none');
    }
    reader.readAsDataURL(file);
  } else {
    preview.src = "#";
    preview.classList.add('d-none');
  }
});
</script>
