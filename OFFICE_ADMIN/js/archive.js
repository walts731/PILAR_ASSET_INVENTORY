$(document).ready(function() {
            $('#archiveTable').DataTable();

            $('#deleteModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const name = button.data('name');
                $('#delete_id').val(id);
                $('#deleteAssetName').text(name);
            });

            $('#restoreModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const name = button.data('name');
                $('#restore_id').val(id);
                $('#restoreAssetName').text(name);
            });
        });

$(document).ready(function () {
        $('#archiveTable').DataTable();

        // Restore modal population
        $('#restoreModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const name = button.data('name');
            $('#restore_id').val(id);
            $('#restoreAssetName').text(name);
        });

        // Delete modal population
        $('#deleteModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const name = button.data('name');
            $('#delete_id').val(id);
            $('#deleteAssetName').text(name);
        });
    });