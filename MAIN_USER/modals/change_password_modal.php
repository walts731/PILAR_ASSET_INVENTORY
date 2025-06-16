<div class="modal fade" id="managePasswordModal" tabindex="-1" aria-labelledby="managePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_password.php" method="POST" class="modal-content needs-validation" novalidate>
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">Password must be at least 6 characters and include uppercase, lowercase, number, and symbol.</p>
        <div class="mb-3">
          <label for="currentPassword" class="form-label">Current Password</label>
          <input type="password" class="form-control" id="currentPassword" name="current_password" required>
        </div>
        <div class="mb-3">
          <label for="newPassword" class="form-label">New Password</label>
          <input type="password" class="form-control" id="newPassword" name="new_password"
                 pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{6,}" required>
        </div>
        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="showPasswordToggle">
          <label class="form-check-label" for="showPasswordToggle">Show Password</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Password</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>