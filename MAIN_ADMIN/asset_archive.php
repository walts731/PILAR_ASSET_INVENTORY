<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['office_id'])) {
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT office_id FROM users WHERE user_id = $user_id");
    if ($row = $result->fetch_assoc()) {
        $_SESSION['office_id'] = $row['office_id'];
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restore_id'])) {
        $restore_id = (int) $_POST['restore_id'];
        $res = $conn->query("SELECT * FROM assets_archive WHERE archive_id = $restore_id");
        if ($asset = $res->fetch_assoc()) {
            $insert = "INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type)
                VALUES (
                    '{$conn->real_escape_string($asset['asset_name'])}',
                    {$asset['category']},
                    '{$conn->real_escape_string($asset['description'])}',
                    {$asset['quantity']},
                    '{$conn->real_escape_string($asset['unit'])}',
                    '{$conn->real_escape_string($asset['status'])}',
                    '{$asset['acquisition_date']}',
                    {$asset['office_id']},
                    {$asset['red_tagged']},
                    '{$asset['last_updated']}',
                    {$asset['value']},
                    '{$conn->real_escape_string($asset['qr_code'])}',
                    '{$conn->real_escape_string($asset['type'])}'
                )";
            if ($conn->query($insert)) {
                $conn->query("DELETE FROM assets_archive WHERE archive_id = $restore_id");
                header("Location: asset_archive.php?restore=success");
                exit();
            }
        }
    }

    if (isset($_POST['restore_all']) && isset($_POST['type'])) {
        $type = $conn->real_escape_string($_POST['type']);
        $result = $conn->query("SELECT * FROM assets_archive WHERE type = '$type'");
        while ($asset = $result->fetch_assoc()) {
            $conn->query("INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type)
                VALUES (
                    '{$conn->real_escape_string($asset['asset_name'])}',
                    {$asset['category']},
                    '{$conn->real_escape_string($asset['description'])}',
                    {$asset['quantity']},
                    '{$conn->real_escape_string($asset['unit'])}',
                    '{$conn->real_escape_string($asset['status'])}',
                    '{$asset['acquisition_date']}',
                    {$asset['office_id']},
                    {$asset['red_tagged']},
                    '{$asset['last_updated']}',
                    {$asset['value']},
                    '{$conn->real_escape_string($asset['qr_code'])}',
                    '{$conn->real_escape_string($asset['type'])}'
                )");
        }
        $conn->query("DELETE FROM assets_archive WHERE type = '$type'");
        header("Location: asset_archive.php?restore_all=success");
        exit();
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = (int) $_POST['delete_id'];
        $conn->query("DELETE FROM assets_archive WHERE archive_id = $delete_id");
        header("Location: asset_archive.php?delete=success");
        exit();
    }

    if (isset($_POST['delete_all']) && isset($_POST['type'])) {
        $type = $conn->real_escape_string($_POST['type']);
        $conn->query("DELETE FROM assets_archive WHERE type = '$type'");
        header("Location: asset_archive.php?delete_all=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Assets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container py-4">
        <?php if (isset($_GET['restore'])): ?>
            <div class="alert alert-success">Asset restored successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['restore_all'])): ?>
            <div class="alert alert-success">All assets restored successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['delete'])): ?>
            <div class="alert alert-warning">Asset permanently deleted!</div>
        <?php endif; ?>
        <?php if (isset($_GET['delete_all'])): ?>
            <div class="alert alert-danger">All archived items permanently deleted!</div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="archiveTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="consumables-tab" data-bs-toggle="tab" data-bs-target="#consumables" type="button" role="tab">Consumables</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assets" type="button" role="tab">Assets</button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="archiveTabsContent">
            <?php
            $types = ['consumable' => 'Consumables', 'asset' => 'Assets'];
            foreach ($types as $type => $label):
                $tabId = $type === 'consumable' ? 'consumables' : 'assets';
                $isActive = $type === 'consumable' ? 'show active' : '';
            ?>
            <div class="tab-pane fade <?= $isActive ?>" id="<?= $tabId ?>" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><?= $label ?> Archive</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteAllModal<?= $type ?>"><i class="bi bi-trash3"></i> Delete All</button>
                            <button class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#restoreAllModal<?= $type ?>"><i class="bi bi-arrow-clockwise"></i> Restore All</button>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle archiveTable" id="table_<?= $type ?>">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Archived At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $q = $conn->query("SELECT a.*, c.category_name FROM assets_archive a LEFT JOIN categories c ON a.category = c.id WHERE a.type = '$type'");
                            while ($row = $q->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= $row['unit'] ?></td>
                                    <td><span class="badge bg-<?= $row['status'] === 'available' ? 'success' : 'secondary' ?>"><?= ucfirst($row['status']) ?></span></td>
                                    <td><?= date('F j, Y', strtotime($row['archived_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#restoreModal" data-id="<?= $row['archive_id'] ?>" data-name="<?= htmlspecialchars($row['asset_name']) ?>"><i class="bi bi-arrow-counterclockwise"></i> </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $row['archive_id'] ?>" data-name="<?= htmlspecialchars($row['asset_name']) ?>"><i class="bi bi-trash"></i> </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Restore Single Modal -->
<?php include 'modals/restore_modal.php'; ?>

<!-- Delete Single Modal -->
<?php include 'modals/delete_archived_modal.php'; ?>

<!-- Restore All Modal for Each Type -->
    <?php foreach ($types as $type => $label): ?>
    <div class="modal fade" id="restoreAllModal<?= $type ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST">
                <input type="hidden" name="restore_all" value="1">
                <input type="hidden" name="type" value="<?= $type ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Restore All Archived <?= $label ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to restore all archived <?= strtolower($label) ?>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Yes, Restore All</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<!-- Delete All Modal for Each Type -->
<?php foreach ($types as $type => $label): ?>
    <div class="modal fade" id="deleteAllModal<?= $type ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST">
                <input type="hidden" name="delete_all" value="1">
                <input type="hidden" name="type" value="<?= $type ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Delete All Archived <?= $label ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to permanently delete <strong>all</strong> archived <?= strtolower($label) ?>? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete All</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="js/dashboard.js"></script>
<script>
    $(document).ready(function () {
        $('.archiveTable').DataTable();

        // Restore Single
        const restoreModal = document.getElementById('restoreModal');
        restoreModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            restoreModal.querySelector('input[name="restore_id"]').value = id;
            restoreModal.querySelector('.asset-name').textContent = name;
        });

        // Delete Single
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            deleteModal.querySelector('input[name="delete_id"]').value = id;
            deleteModal.querySelector('.asset-name').textContent = name;
        });

        // Restore All (loop through types)
        ['consumable', 'asset'].forEach(function(type) {
            const restoreAllModal = document.getElementById('restoreAllModal' + type);
            if (restoreAllModal) {
                restoreAllModal.addEventListener('show.bs.modal', function () {
                    restoreAllModal.querySelector('input[name="type"]').value = type;
                });
            }

            const deleteAllModal = document.getElementById('deleteAllModal' + type);
            if (deleteAllModal) {
                deleteAllModal.addEventListener('show.bs.modal', function () {
                    deleteAllModal.querySelector('input[name="type"]').value = type;
                });
            }
        });
    });
</script>
</body>
</html>
