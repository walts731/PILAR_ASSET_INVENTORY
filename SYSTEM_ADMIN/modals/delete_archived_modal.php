<div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST">
                <input type="hidden" name="delete_id" id="delete_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Permanent Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to permanently delete this asset?
                        <div class="text-danger mt-2 fw-bold" id="deleteAssetName"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Permanently</button>
                    </div>
                </div>
            </form>
        </div>
    </div>