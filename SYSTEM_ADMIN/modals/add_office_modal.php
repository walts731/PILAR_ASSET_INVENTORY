<!-- Add Office Modal -->
<div class="modal fade" id="addOfficeModal" tabindex="-1" aria-labelledby="addOfficeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="add_office.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addOfficeLabel"><i class="bi bi-building me-2"></i>Add New Office</h5>
        <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="officeName" class="form-label">Office Name</label>
          <input type="text" class="form-control" name="office_name" id="officeName" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Create</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
