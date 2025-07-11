<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Wider modal -->
      <form action="update_user.php" method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserLabel"><i class="bi bi-person-gear me-2"></i>Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="user_id" id="editUserId">

          <div class="row g-3">
            <!-- Left Column -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="editFullname" class="form-label">Full Name</label>
                <input type="text" class="form-control" name="fullname" id="editFullname" required>
              </div>

              <div class="mb-3">
                <label for="editUsername" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="editUsername" required>
              </div>

              <div class="mb-3">
                <label for="editEmail" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="editEmail" required>
              </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="editRole" class="form-label">Role</label>
                <select class="form-select" name="role" id="editRole" required>
                  <option value="admin">Admin</option>
                  <option value="user">User</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="editStatus" class="form-label">Status</label>
                <select class="form-select" name="status" id="editStatus" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-info">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>