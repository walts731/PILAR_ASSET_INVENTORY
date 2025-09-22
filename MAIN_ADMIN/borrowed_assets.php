<?php
require_once '../connect.php';
session_start();

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['super_admin', 'admin', 'office_admin'])) {
    header("Location: ../index.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$office_id = $_SESSION['office_id'];

// Base query to fetch all individual 'in-use' items
$sql = "
    SELECT
        ai.item_id,
        ai.inventory_tag,
        ai.serial_no,
        a.asset_name,
        br.id as request_id,
        br.due_date,
        br.approved_at,
        u.fullname as borrower_name,
        o.office_name
    FROM asset_items ai
    JOIN assets a ON ai.asset_id = a.id
    JOIN borrow_request_items bri ON ai.item_id = bri.asset_item_id
    JOIN borrow_requests br ON bri.borrow_request_id = br.id
    JOIN users u ON br.user_id = u.id
    JOIN offices o ON a.office_id = o.id
    WHERE ai.status = 'in-use'
";

$params = [];
$types = '';

if ($user_role === 'office_admin') {
    $sql .= " AND a.office_id = ?";
    $params[] = $office_id;
    $types .= 'i';
}

$sql .= " ORDER BY br.due_date ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$borrowed_items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Borrowed Assets</title>
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
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4><i class="bi bi-box-arrow-up"></i> Manage Borrowed Assets</h4>
                </div>
                <div class="card-body">
                    <table id="borrowedTable" class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tag ID</th>
                                <th>Serial No.</th>
                                <th>Asset Name</th>
                                <th>Borrower</th>
                                <th>Office</th>
                                <th>Date Approved</th>
                                <th>Due Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $borrowed_items->fetch_assoc()): ?>
                                <?php 
                                    $is_overdue = strtotime($item['due_date']) < time();
                                ?>
                                <tr class="<?= $is_overdue ? 'table-danger' : '' ?>">
                                    <td><?= htmlspecialchars($item['inventory_tag']) ?></td>
                                    <td><?= htmlspecialchars($item['serial_no'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($item['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($item['borrower_name']) ?></td>
                                    <td><?= htmlspecialchars($item['office_name']) ?></td>
                                    <td><?= date('M j, Y', strtotime($item['approved_at'])) ?></td>
                                    <td>
                                        <?= date('M j, Y', strtotime($item['due_date'])) ?>
                                        <?php if ($is_overdue): ?>
                                            <span class="badge bg-danger">Overdue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary return-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#returnModal" 
                                                data-item-id="<?= $item['item_id'] ?>"
                                                data-asset-name="<?= htmlspecialchars($item['asset_name']) ?>">
                                            <i class="bi bi-arrow-return-left"></i> Return
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

    <!-- Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="process_return.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Return Asset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to return: <strong id="assetNameInModal"></strong></p>
                        <input type="hidden" name="item_id" id="itemIdInModal">
                        <div class="mb-3">
                            <label for="condition" class="form-label">Condition on Return</label>
                            <select name="condition" id="condition" class="form-select" required>
                                <option value="good">Good</option>
                                <option value="damaged">Damaged</option>
                                <option value="needs_repair">Needs Repair</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks (Optional)</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#borrowedTable').DataTable({
                "order": [[ 6, "asc" ]]
            });

            // Populate modal with data
            $('.return-btn').on('click', function() {
                const itemId = $(this).data('item-id');
                const assetName = $(this).data('asset-name');
                $('#itemIdInModal').val(itemId);
                $('#assetNameInModal').text(assetName);
            });
        });
    </script>
</body>
</html>
