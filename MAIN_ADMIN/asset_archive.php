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
            // Build NULL-safe values
            $asset_name = isset($asset['asset_name']) ? "'" . $conn->real_escape_string($asset['asset_name']) . "'" : 'NULL';
            $category   = isset($asset['category']) && $asset['category'] !== '' ? (int)$asset['category'] : 'NULL';
            $description= isset($asset['description']) ? "'" . $conn->real_escape_string($asset['description']) . "'" : 'NULL';
            $quantity   = isset($asset['quantity']) && $asset['quantity'] !== '' ? (int)$asset['quantity'] : 0;
            $unit       = isset($asset['unit']) ? "'" . $conn->real_escape_string($asset['unit']) . "'" : 'NULL';
            $status     = isset($asset['status']) ? "'" . $conn->real_escape_string($asset['status']) . "'" : 'NULL';
            $acq_date   = isset($asset['acquisition_date']) && $asset['acquisition_date'] !== '' ? "'" . $conn->real_escape_string($asset['acquisition_date']) . "'" : 'NULL';
            $office_id  = isset($asset['office_id']) && $asset['office_id'] !== '' ? (int)$asset['office_id'] : 'NULL';
            $red_tagged = isset($asset['red_tagged']) && $asset['red_tagged'] !== '' ? (int)$asset['red_tagged'] : 0;
            $last_updated = isset($asset['last_updated']) && $asset['last_updated'] !== '' ? "'" . $conn->real_escape_string($asset['last_updated']) . "'" : 'NULL';
            $value      = isset($asset['value']) && $asset['value'] !== '' ? (float)$asset['value'] : 0;
            $qr_code    = isset($asset['qr_code']) ? "'" . $conn->real_escape_string($asset['qr_code']) . "'" : 'NULL';
            $type       = isset($asset['type']) ? "'" . $conn->real_escape_string($asset['type']) . "'" : "'asset'";

            $insert = "INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type)
                VALUES ($asset_name, $category, $description, $quantity, $unit, $status, $acq_date, $office_id, $red_tagged, $last_updated, $value, $qr_code, $type)";
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
            $asset_name = isset($asset['asset_name']) ? "'" . $conn->real_escape_string($asset['asset_name']) . "'" : 'NULL';
            $category   = isset($asset['category']) && $asset['category'] !== '' ? (int)$asset['category'] : 'NULL';
            $description= isset($asset['description']) ? "'" . $conn->real_escape_string($asset['description']) . "'" : 'NULL';
            $quantity   = isset($asset['quantity']) && $asset['quantity'] !== '' ? (int)$asset['quantity'] : 0;
            $unit       = isset($asset['unit']) ? "'" . $conn->real_escape_string($asset['unit']) . "'" : 'NULL';
            $status     = isset($asset['status']) ? "'" . $conn->real_escape_string($asset['status']) . "'" : 'NULL';
            $acq_date   = isset($asset['acquisition_date']) && $asset['acquisition_date'] !== '' ? "'" . $conn->real_escape_string($asset['acquisition_date']) . "'" : 'NULL';
            $office_id  = isset($asset['office_id']) && $asset['office_id'] !== '' ? (int)$asset['office_id'] : 'NULL';
            $red_tagged = isset($asset['red_tagged']) && $asset['red_tagged'] !== '' ? (int)$asset['red_tagged'] : 0;
            $last_updated = isset($asset['last_updated']) && $asset['last_updated'] !== '' ? "'" . $conn->real_escape_string($asset['last_updated']) . "'" : 'NULL';
            $value      = isset($asset['value']) && $asset['value'] !== '' ? (float)$asset['value'] : 0;
            $qr_code    = isset($asset['qr_code']) ? "'" . $conn->real_escape_string($asset['qr_code']) . "'" : 'NULL';
            $type       = isset($asset['type']) ? "'" . $conn->real_escape_string($asset['type']) . "'" : "'asset'";

            $conn->query("INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type)
                VALUES ($asset_name, $category, $description, $quantity, $unit, $status, $acq_date, $office_id, $red_tagged, $last_updated, $value, $qr_code, $type)");
        }
        $conn->query("DELETE FROM assets_archive WHERE type = '$type'");
        header("Location: asset_archive.php?restore_all=success");
        exit();
    }

    if (isset($_POST['delete_id'])) {
        $delete_id = (int) $_POST['delete_id'];
        // Start transaction to ensure insert-then-delete consistency
        $conn->begin_transaction();
        try {
            $res = $conn->query("SELECT * FROM assets_archive WHERE archive_id = $delete_id");
            if ($res && $asset = $res->fetch_assoc()) {
                if (isset($asset['type']) && $asset['type'] === 'asset') {
                    // Insert a copy into asset_new
                    $insertNew = sprintf(
                        "INSERT INTO asset_new (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type) VALUES ('%s', %d, '%s', %d, '%s', '%s', '%s', %d, %d, '%s', %f, '%s', '%s')",
                        $conn->real_escape_string($asset['asset_name']),
                        (int)$asset['category'],
                        $conn->real_escape_string($asset['description']),
                        (int)$asset['quantity'],
                        $conn->real_escape_string($asset['unit']),
                        $conn->real_escape_string($asset['status']),
                        $conn->real_escape_string($asset['acquisition_date']),
                        (int)$asset['office_id'],
                        (int)$asset['red_tagged'],
                        $conn->real_escape_string($asset['last_updated']),
                        (float)$asset['value'],
                        $conn->real_escape_string($asset['qr_code']),
                        $conn->real_escape_string($asset['type'])
                    );
                    $conn->query($insertNew);
                }
            }
            // Now delete from archive
            $conn->query("DELETE FROM assets_archive WHERE archive_id = $delete_id");
            $conn->commit();
            header("Location: asset_archive.php?delete=success");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: asset_archive.php?delete=failed");
            exit();
        }
    }

    if (isset($_POST['delete_all']) && isset($_POST['type'])) {
        $type = $conn->real_escape_string($_POST['type']);
        $conn->begin_transaction();
        try {
            // For type='asset', copy all to asset_new first
            if ($type === 'asset') {
                $result = $conn->query("SELECT * FROM assets_archive WHERE type = 'asset'");
                while ($asset = $result->fetch_assoc()) {
                    $insertNew = sprintf(
                        "INSERT INTO asset_new (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type) VALUES ('%s', %d, '%s', %d, '%s', '%s', '%s', %d, %d, '%s', %f, '%s', '%s')",
                        $conn->real_escape_string($asset['asset_name']),
                        (int)$asset['category'],
                        $conn->real_escape_string($asset['description']),
                        (int)$asset['quantity'],
                        $conn->real_escape_string($asset['unit']),
                        $conn->real_escape_string($asset['status']),
                        $conn->real_escape_string($asset['acquisition_date']),
                        (int)$asset['office_id'],
                        (int)$asset['red_tagged'],
                        $conn->real_escape_string($asset['last_updated']),
                        (float)$asset['value'],
                        $conn->real_escape_string($asset['qr_code']),
                        $conn->real_escape_string($asset['type'])
                    );
                    $conn->query($insertNew);
                }
            }
            // Delete all archived rows of the given type
            $conn->query("DELETE FROM assets_archive WHERE type = '$type'");
            $conn->commit();
            header("Location: asset_archive.php?delete_all=success");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: asset_archive.php?delete_all=failed");
            exit();
        }
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
    <style>
      .page-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%);
        border: 1px solid #e9ecef;
        border-radius: .75rem;
      }
      .page-header .title { font-weight: 600; }
      .toolbar .btn { transition: transform .08s ease-in; }
      .toolbar .btn:hover { transform: translateY(-1px); }
      .card-hover:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.06) !important; }
      .table thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
    </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container py-4">
        <?php
          // Build counts per type for badges
          $archiveCounts = ['consumable' => 0, 'asset' => 0];
          $cntRes = $conn->query("SELECT type, COUNT(*) AS cnt FROM assets_archive GROUP BY type");
          if ($cntRes) {
            while ($cr = $cntRes->fetch_assoc()) {
              $t = $cr['type'];
              if (isset($archiveCounts[$t])) $archiveCounts[$t] = (int)$cr['cnt'];
            }
          }
          $archiveTotal = array_sum($archiveCounts);
        ?>

        <!-- Page Header -->
        <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
              <i class="bi bi-archive text-primary fs-4"></i>
            </div>
            <div>
              <div class="h4 mb-0 title">Archived Assets</div>
              <div class="text-muted small">Restore, delete permanently, or review archived records</div>
            </div>
          </div>
          <div class="toolbar d-flex align-items-center gap-2">
            <span class="badge text-bg-secondary" title="Total archived items"><?= (int)$archiveTotal ?> total</span>
            <button id="toggleDensityArchive" class="btn btn-outline-secondary btn-sm rounded-pill" title="Toggle compact density">
              <i class="bi bi-arrows-vertical me-1"></i> Density
            </button>
          </div>
        </div>
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
                <button class="nav-link active" id="consumables-tab" data-bs-toggle="tab" data-bs-target="#consumables" type="button" role="tab">
                  <i class="bi bi-box-seam me-1"></i> Consumables
                  <span class="badge text-bg-secondary ms-1 align-middle"><?= (int)$archiveCounts['consumable'] ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assets" type="button" role="tab">
                  <i class="bi bi-hdd-stack me-1"></i> Assets
                  <span class="badge text-bg-secondary ms-1 align-middle"><?= (int)$archiveCounts['asset'] ?></span>
                </button>
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
                <div class="card shadow-sm card-hover">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                          <i class="bi bi-collection"></i>
                          <span><?= $label ?> Archive</span>
                          <span class="badge text-bg-secondary d-none d-sm-inline"><?= (int)$archiveCounts[$type] ?></span>
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteAllModal<?= $type ?>" title="Delete all archived <?= strtolower($label) ?>"><i class="bi bi-trash3"></i> Delete All</button>
                            <button class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#restoreAllModal<?= $type ?>" title="Restore all archived <?= strtolower($label) ?>"><i class="bi bi-arrow-clockwise"></i> Restore All</button>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-striped table-hover align-middle archiveTable" id="table_<?= $type ?>">
                            <thead class="table-light">
                                <tr>
                                    <th title="Asset name">Name</th>
                                    <th title="Category">Category</th>
                                    <th title="Description">Description</th>
                                    <th title="Quantity">Qty</th>
                                    <th title="Unit">Unit</th>
                                    <th title="Status">Status</th>
                                    <th title="Date archived">Archived At</th>
                                    <th title="Actions">Actions</th>
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
            // Set hidden input for archive_id
            const idInput = restoreModal.querySelector('input[name="restore_id"]');
            if (idInput) idInput.value = id;
            // Show asset name in the confirmation modal
            const nameEl = restoreModal.querySelector('#restoreAssetName');
            if (nameEl) nameEl.textContent = name;
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
