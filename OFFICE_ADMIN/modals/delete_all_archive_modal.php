<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <input type="hidden" name="delete_all" value="1">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete All Archived Consumables</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to permanently delete <strong>all</strong> archived consumable assets? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete All</button>
                </div>
            </div>
        </form>
    </div>
</div>