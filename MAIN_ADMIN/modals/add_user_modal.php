<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="add_user.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserLabel">
          <i class="bi bi-person-plus me-2"></i>Add New User
        </h5>
        <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="fullname" class="form-label">Full Name</label>
            <input type="text" class="form-control" name="fullname" id="fullname" required>
          </div>

          <div class="col-md-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="username" required>
          </div>

          <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" required>
          </div>

          <div class="col-md-6">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" name="role" id="role" required>
              <option value="user">User</option>
              <option value="admin">Admin</option>
              <option value="office_user">Office User</option>
              <option value="office_admin">Office Admin</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="password" class="form-label">Password (default from settings)</label>
            <div class="input-group">
              <?php
                $defPwd = '';
                $sysRes = $conn->query("SELECT default_user_password FROM system LIMIT 1");
                if ($sysRes && $sysRes->num_rows > 0) {
                  $sysRow = $sysRes->fetch_assoc();
                  $defPwd = $sysRow['default_user_password'] ?? '';
                }
              ?>
              <input type="password" class="form-control" name="password" id="password"
                value="<?= htmlspecialchars($defPwd) ?>"
                pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{12,}$"
                title="Strong password: min 12 chars, 1 uppercase, 1 number, 1 special character"
                required>
              <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)" title="Show/Hide">
                <i class="bi bi-eye-slash"></i>
              </button>
              <button type="button" class="btn btn-outline-success" id="copyPasswordBtn" title="Copy to clipboard">
                <i class="bi bi-clipboard"></i>
              </button>
            </div>
            <small class="text-muted">This defaults to the value set in User Management → Default Password. You may change it per user.</small>
          </div>

          <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" id="status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="office_id" class="form-label">Office</label>
            <select name="office_id" id="office_id" class="form-select" required>
              <?php
              $officeList = $conn->query("SELECT id, office_name FROM offices");
              while ($office = $officeList->fetch_assoc()):
              ?>
                <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['office_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Permissions</label>
            <div class="row g-2">
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="permissions[]" id="perm_fuel" value="fuel_inventory">
                  <label class="form-check-label" for="perm_fuel">Fuel Inventory</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="permissions[]" id="perm_restrict_user_mgmt" value="restrict_user_management">
                  <label class="form-check-label" for="perm_restrict_user_mgmt">Restrict access to User Management</label>
                </div>
                <small class="text-muted">When checked, this user will be blocked from opening the User Management page, even if their role normally allows it.</small>
              </div>
              <!-- Add more permission checkboxes here as needed -->
            </div>
            <small class="text-muted">Admins and Users implicitly have access by role for Fuel Inventory.</small>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Add</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>

</div>

<script>
  function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === "password") {
      field.type = "text";
      icon.classList.remove("bi-eye-slash");
      icon.classList.add("bi-eye");
    } else {
      field.type = "password";
      icon.classList.remove("bi-eye");
      icon.classList.add("bi-eye-slash");
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    const copyBtn = document.getElementById('copyPasswordBtn');
    if (copyBtn) {
      copyBtn.addEventListener('click', async function(){
        const el = document.getElementById('password');
        if (el && el.value) {
          try {
            await navigator.clipboard.writeText(el.value);
            copyBtn.classList.remove('btn-outline-success');
            copyBtn.classList.add('btn-success');
            setTimeout(()=>{
              copyBtn.classList.add('btn-outline-success');
              copyBtn.classList.remove('btn-success');
            }, 1000);
          } catch(e) {
            // ignore
          }
        }
      });
    }
  });
</script>
