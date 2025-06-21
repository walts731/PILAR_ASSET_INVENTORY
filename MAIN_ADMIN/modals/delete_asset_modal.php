<!-- Delete Asset Modal -->
<div class="modal fade" id="deleteAssetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="delete_asset.php" method="GET" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Asset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="delete_asset_id">
        <p>Are you sure you want to delete the asset: <strong id="delete_asset_name"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
