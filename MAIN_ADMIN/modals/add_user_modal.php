<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="add_user.php" class="modal-content" onsubmit="return validatePasswordMatch();">
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
            </select>
          </div>

          <div class="col-md-6">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control" name="password" id="password"
                pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$"
                title="Must be at least 8 characters, with 1 uppercase letter, 1 number, and 1 special character"
                required>
              <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)">
                <i class="bi bi-eye-slash"></i>
              </button>
            </div>
            <small class="text-muted">Min. 8 chars, 1 uppercase, 1 number, 1 special character</small>
          </div>

          <div class="col-md-6">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="confirm_password" required>
              <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password', this)">
                <i class="bi bi-eye-slash"></i>
              </button>
            </div>
            <small id="passwordMatchMsg" class="text-danger d-none">Passwords do not match</small>
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
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Create</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  function validatePasswordMatch() {
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const message = document.getElementById('passwordMatchMsg');

    if (password.value !== confirm.value) {
      message.classList.remove('d-none');
      confirm.focus();
      return false;
    } else {
      message.classList.add('d-none');
      return true;
    }
  }

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
</script>
