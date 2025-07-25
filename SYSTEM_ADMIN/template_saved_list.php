<?php
require_once '../connect.php';

// Fetch template list
$sql = "
    SELECT 
        t.id,
        t.template_name,
        t.created_at,
        t.updated_at,
        u1.fullname AS created_by,
        u2.fullname AS updated_by
    FROM 
        report_templates t
    LEFT JOIN users u1 ON t.created_by = u1.id
    LEFT JOIN users u2 ON t.updated_by = u2.id
    ORDER BY t.created_at DESC
";
$result = $conn->query($sql);
?>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Saved Templates</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="templateTable" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Template Name</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Created By</th>
                        <th>Updated By</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['template_name']) ?></td>
                                <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <?= $row['updated_at'] ? date('F j, Y', strtotime($row['updated_at'])) : 'N/A' ?>
                                </td>
                                <td><?= htmlspecialchars($row['created_by'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['updated_by'] ?? 'N/A') ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?= $row['id'] ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="edit_template.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['template_name']) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No templates found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Modal -->
<?php include 'modals/view_template_modal.php'; ?>

<!-- Delete Confirmation Modal -->
<?php include 'modals/delete_template_modal.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deleteModal = document.getElementById('deleteModal');

        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const templateId = button.getAttribute('data-id');
            const templateName = button.getAttribute('data-name');

            const modalTemplateName = deleteModal.querySelector('#templateName');
            const deleteTemplateId = deleteModal.querySelector('#deleteTemplateId');

            modalTemplateName.textContent = templateName;
            deleteTemplateId.value = templateId;
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize DataTables
        $('#templateTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [], // disables default ordering on the first column
            columnDefs: [{
                    orderable: false,
                    targets: -1
                } // disable ordering on the "Actions" column
            ]
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const templateId = button.getAttribute('data-id');

            const modalBody = viewModal.querySelector('#viewModalContent');
            modalBody.innerHTML = '<div class="text-center p-3">Loading...</div>';

            fetch('view_template.php?id=' + templateId)
                .then(response => response.text())
                .then(data => {
                    modalBody.innerHTML = data;
                })
                .catch(error => {
                    modalBody.innerHTML = '<div class="text-danger text-center">Failed to load template.</div>';
                    console.error('Error loading template:', error);
                });
        });
    });
</script>