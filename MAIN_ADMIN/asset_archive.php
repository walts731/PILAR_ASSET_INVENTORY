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

// Restore single asset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restore_id'])) {
        $restore_id = (int) $_POST['restore_id'];

        $result = $conn->query("SELECT * FROM assets_archive WHERE archive_id = $restore_id");
        if ($asset = $result->fetch_assoc()) {
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

    // Restore all
    if (isset($_POST['restore_all'])) {
        $result = $conn->query("SELECT * FROM assets_archive WHERE type = 'consumable'");
        while ($asset = $result->fetch_assoc()) {
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
            $conn->query($insert);
        }
        $conn->query("DELETE FROM assets_archive WHERE type = 'consumable'");
        header("Location: asset_archive.php?restore_all=success");
        exit();
    }

    // Delete single
    if (isset($_POST['delete_id'])) {
        $delete_id = (int) $_POST['delete_id'];
        $conn->query("DELETE FROM assets_archive WHERE archive_id = $delete_id");
        header("Location: asset_archive.php?delete=success");
        exit();
    }

    // Delete all
    if (isset($_POST['delete_all'])) {
        $conn->query("DELETE FROM assets_archive WHERE type = 'consumable'");
        header("Location: asset_archive.php?delete_all=success");
        exit();
    }
}
?>

<!-- HTML below -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Archived Consumables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="css/dashboard.css" />
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
            <div class="alert alert-danger">All archived consumables permanently deleted!</div>
        <?php endif; ?>

        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Archived Assets</h5>
                <div>
                    <button class="btn btn-sm btn-outline-danger me-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                        <i class="bi bi-trash3"></i> Delete All
                    </button>
                    <button class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#restoreAllModal">
                        <i class="bi bi-arrow-clockwise"></i> Restore All
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table id="archiveTable" class="table table-hover align-middle">
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
                        $result = $conn->query("SELECT a.*, c.category_name FROM assets_archive a LEFT JOIN categories c ON a.category = c.id WHERE a.type = 'consumable'");
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['unit'] ?></td>
                                <td><span class="badge bg-<?= $row['status'] === 'available' ? 'success' : 'secondary' ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td><?= date('F j, Y', strtotime($row['archived_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#restoreModal" data-id="<?= $row['archive_id'] ?>" data-name="<?= htmlspecialchars($row['asset_name']) ?>">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $row['archive_id'] ?>" data-name="<?= htmlspecialchars($row['asset_name']) ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<?php include 'modals/restore_modal.php'; ?>

<!-- Delete Single Modal -->
<?php include 'modals/delete_archived_modal.php'; ?>

<!-- Restore All Modal -->
<?php include 'modals/restore_all_modal.php'; ?>

<!-- Delete All Modal -->
<?php include 'modals/delete_all_archive_modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="js/dashboard.js"></script>
<script src="js/archive.js"></script>
</body>
</html>
