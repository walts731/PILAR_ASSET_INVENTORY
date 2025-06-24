<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="add_user.php" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addUserLabel"><i class="bi bi-person-plus me-2"></i>Add New User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="fullname" class="form-label">Full Name</label>
          <input type="text" class="form-control" name="fullname" id="fullname" required>
        </div>

        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" name="username" id="username" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" name="email" id="email" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" name="password" id="password" required>
        </div>

        <div class="mb-3">
          <label for="role" class="form-label">Role</label>
          <select class="form-select" name="role" id="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="status" class="form-label">Status</label>
          <select class="form-select" name="status" id="status" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="mb-3">
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

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Create</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
