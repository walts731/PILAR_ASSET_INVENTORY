<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="restore_id" id="restore_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Restoration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to restore this asset?
                    <div class="text-success mt-2 fw-bold" id="restoreAssetName"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Yes, Restore</button>
                </div>
            </div>
        </form>
    </div>
</div>