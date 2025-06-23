<div class="modal fade" id="confirmDeleteUserModal" tabindex="-1" aria-labelledby="confirmDeleteUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form id="deleteUserForm" method="GET" action="delete_user.php" class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="confirmDeleteUserLabel">
            <i class="bi bi-exclamation-triangle me-2"></i> Confirm Deletion
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete <strong id="deleteUserName"></strong>? This action cannot be undone.
          <input type="hidden" name="id" id="deleteUserId">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Yes, Delete</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>