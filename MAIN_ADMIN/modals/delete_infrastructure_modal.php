<!-- Delete Infrastructure Modal -->
<div class="modal fade" id="deleteInfrastructureModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="delete_infrastructure.php" method="GET" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
          Delete Infrastructure Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="delete_infrastructure_id">
        <div class="alert alert-warning">
          <h6 class="alert-heading">Warning!</h6>
          <p class="mb-2">You are about to permanently delete this infrastructure record. This action cannot be undone.</p>
        </div>
        <p class="mb-3">Are you sure you want to delete the infrastructure record:</p>
        <div class="card bg-light">
          <div class="card-body py-2">
            <strong id="delete_infrastructure_name"></strong>
          </div>
        </div>
        <p class="mt-3 text-muted small">This will remove all associated data including images and appraisal information.</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-trash me-1"></i>Yes, Delete Record
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
      </div>
    </form>
  </div>
</div>
