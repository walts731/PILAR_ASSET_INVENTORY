<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Set office_id if not already set
if (!isset($_SESSION['office_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    if ($stmt->fetch()) {
        $_SESSION['office_id'] = $office_id;
    }
    $stmt->close();
}

$user_id = $_SESSION['user_id'];
$selected_office_id = $_GET['office_id'] ?? $_SESSION['office_id'];

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// Fetch office list
$offices = [];
$office_stmt = $conn->prepare("SELECT id, office_name FROM offices");
$office_stmt->execute();
$office_result = $office_stmt->get_result();
while ($row = $office_result->fetch_assoc()) {
    $offices[] = $row;
}
$office_stmt->close();

// Fetch available assets
$stmt = $conn->prepare("SELECT id, asset_name, description, quantity, unit, value, acquisition_date FROM assets WHERE office_id = ? AND status = 'Available'");
$stmt->bind_param("i", $selected_office_id);
$stmt->execute();
$available_assets = $stmt->get_result();
$stmt->close();

// Fetch borrowed assets
$borrowed_query = "
SELECT a.asset_name, a.description, a.unit, br.status, br.requested_at, br.approved_at, u.fullname AS borrower, o.office_name
FROM borrow_requests br
JOIN assets a ON br.asset_id = a.id
JOIN users u ON br.user_id = u.id
JOIN offices o ON br.office_id = o.id
WHERE br.status IN ('approved', 'borrowed')
ORDER BY br.approved_at DESC
";
$borrowed_assets = $conn->query($borrowed_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Borrow Assets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <div class="container mt-4">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <?php unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <?php unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs" id="assetTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button" role="tab">Available Assets</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="borrowed-tab" data-bs-toggle="tab" data-bs-target="#borrowed" type="button" role="tab">Borrowed Assets</button>
                </li>
            </ul>

            <div class="tab-content mt-3" id="assetTabsContent">
                <!-- Available Assets -->
                <div class="tab-pane fade show active" id="available" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div><i class="bi bi-table"></i> List of Available Assets</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <form method="GET" class="d-inline">
                                    <select name="office_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <?php foreach ($offices as $office): ?>
                                            <option value="<?= $office['id'] ?>" <?= ($selected_office_id == $office['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($office['office_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                                <a href="borrow_requests.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                                    <i class="bi bi-journal-check"></i> Borrow Requests
                                </a>
                                <a href="borrowed_assets.php" class="btn btn-outline-info btn-sm rounded-pill">
                                    <i class="bi bi-box-arrow-up"></i> Borrowed Assets
                                </a>
                                <a href="incoming_borrow_requests.php" class="btn btn-outline-dark btn-sm rounded-pill">
                                    <i class="bi bi-inbox"></i> Incoming Borrow Requests
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="process_borrow.php?office_id=<?= $selected_office_id ?>" method="POST">
                                <div class="mb-3 text-end">
                                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="bi bi-check2-square"></i> Borrow Selected
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table id="availableAssetsTable" class="table table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th><input type="checkbox" id="selectAll"></th>
                                                <th>Asset Name</th>
                                                <th>Description</th>
                                                <th>Qty</th>
                                                <th>Unit</th>
                                                <th>Value</th>
                                                <th>Acquired</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $available_assets->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="selected_assets[]" value="<?= $row['id'] . '|' . $selected_office_id ?>" class="asset-checkbox" data-asset-id="<?= $row['id'] ?>">
                                                    </td>
                                                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                                    <td><?= $row['quantity'] ?></td>
                                                    <td><?= $row['unit'] ?></td>
                                                    <td>₱<?= number_format($row['value'], 2) ?></td>
                                                    <td><?= date('F j, Y', strtotime($row['acquisition_date'])) ?></td>
                                                    <td>
                                                        <input type="number" name="quantities[<?= $row['id'] ?>]" class="form-control form-control-sm quantity-input" min="1" max="<?= $row['quantity'] ?>" placeholder="Qty" disabled>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Borrowed Assets -->
                <div class="tab-pane fade" id="borrowed" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <i class="bi bi-box-arrow-up"></i> Borrowed Assets (Across All Offices)
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="borrowedAssetsTable" class="table table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Asset</th>
                                            <th>Description</th>
                                            <th>Unit</th>
                                            <th>Status</th>
                                            <th>Requested At</th>
                                            <th>Approved At</th>
                                            <th>Borrower</th>
                                            <th>From Office</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $borrowed_assets->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                                <td><?= htmlspecialchars($row['description']) ?></td>
                                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                                <td><span class="badge bg-<?= $row['status'] === 'borrowed' ? 'success' : 'warning' ?>"><?= ucfirst($row['status']) ?></span></td>
                                                <td><?= date('F j, Y h:i A', strtotime($row['requested_at'])) ?></td>
                                                <td><?= $row['approved_at'] ? date('F j, Y h:i A', strtotime($row['approved_at'])) : 'N/A' ?></td>
                                                <td><?= htmlspecialchars($row['borrower']) ?></td>
                                                <td><?= htmlspecialchars($row['office_name']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#availableAssetsTable').DataTable();
            $('#borrowedAssetsTable').DataTable();

            $('#selectAll').on('click', function() {
                $('.asset-checkbox').prop('checked', this.checked).trigger('change');
            });

            $('.asset-checkbox').on('change', function() {
                const assetId = this.dataset.assetId;
                const qtyInput = document.querySelector(`input[name="quantities[${assetId}]"]`);
                if (this.checked) {
                    qtyInput.disabled = false;
                    qtyInput.required = true;
                } else {
                    qtyInput.disabled = true;
                    qtyInput.required = false;
                    qtyInput.value = '';
                }
            });
        });
    </script>
</body>

</html>