<?php
require_once '../connect.php';
session_start();

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['super_admin', 'admin', 'office_admin'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['request_id'])) {
    $_SESSION['error_message'] = "No request ID specified.";
    header("Location: incoming_borrow_requests.php");
    exit();
}

$request_id = intval($_GET['request_id']);

// Fetch the main borrow request details
$stmt = $conn->prepare("
    SELECT br.*, u.fullname as borrower_name, a.asset_name, a.office_id as asset_office_id
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN assets a ON br.asset_id = a.id
    WHERE br.id = ? AND br.status = 'pending'
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request) {
    $_SESSION['error_message'] = "Request not found or already processed.";
    header("Location: incoming_borrow_requests.php");
    exit();
}

// Security check: ensure office_admin can only access requests for their office
if ($_SESSION['user_role'] === 'office_admin' && $request['asset_office_id'] != $_SESSION['office_id']) {
    $_SESSION['error_message'] = "You are not authorized to view this request.";
    header("Location: incoming_borrow_requests.php");
    exit();
}

// Fetch available individual asset items for this asset
$asset_id = $request['asset_id'];
$items_stmt = $conn->prepare("SELECT item_id, inventory_tag, serial_no FROM asset_items WHERE asset_id = ? AND status = 'available'");
$items_stmt->bind_param("i", $asset_id);
$items_stmt->execute();
$available_items = $items_stmt->get_result();
$items_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Borrow Request</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <div class="container mt-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4>Review Borrow Request</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Borrower:</strong> <?= htmlspecialchars($request['borrower_name']) ?></p>
                            <p><strong>Asset:</strong> <?= htmlspecialchars($request['asset_name']) ?></p>
                            <p><strong>Quantity Requested:</strong> <span class="badge bg-primary fs-6"><?= $request['quantity'] ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Purpose:</strong> <?= htmlspecialchars($request['purpose']) ?></p>
                            <p><strong>Requested On:</strong> <?= date('F j, Y', strtotime($request['requested_at'])) ?></p>
                            <p><strong>Return By:</strong> <?= date('F j, Y', strtotime($request['due_date'])) ?></p>
                        </div>
                    </div>
                    <hr>

                    <form action="process_approval.php" method="POST">
                        <input type="hidden" name="request_id" value="<?= $request_id ?>">
                        <input type="hidden" name="asset_id" value="<?= $asset_id ?>">
                        <input type="hidden" name="quantity_requested" value="<?= $request['quantity'] ?>">

                        <h5>Assign Specific Items</h5>
                        <p>Select <?= $request['quantity'] ?> item(s) from the list below to approve this request.</p>

                        <?php if ($available_items->num_rows < $request['quantity']): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                <strong>Insufficient Stock!</strong> Only <?= $available_items->num_rows ?> item(s) are available, but <?= $request['quantity'] ?> were requested. You cannot approve this request until stock is updated.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Select</th>
                                            <th>Inventory Tag</th>
                                            <th>Serial Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = $available_items->fetch_assoc()): ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected_items[]" value="<?= $item['item_id'] ?>" class="form-check-input item-checkbox"></td>
                                                <td><?= htmlspecialchars($item['inventory_tag']) ?></td>
                                                <td><?= htmlspecialchars($item['serial_no'] ?: 'N/A') ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <label for="remarks" class="form-label">Remarks (Optional, for rejection)</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="submit" name="decision" value="reject" class="btn btn-danger">Reject Request</button>
                            <button type="submit" name="decision" value="approve" class="btn btn-success" id="approveBtn" <?= ($available_items->num_rows < $request['quantity']) ? 'disabled' : '' ?>>Approve Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
    $(document).ready(function() {
        const quantityNeeded = <?= $request['quantity'] ?>;
        const approveBtn = $('#approveBtn');

        function validateSelection() {
            const selectedCount = $('.item-checkbox:checked').length;
            if (selectedCount !== quantityNeeded) {
                approveBtn.prop('disabled', true);
                approveBtn.text(`Select ${quantityNeeded} Items`);
            } else {
                approveBtn.prop('disabled', false);
                approveBtn.text('Approve Request');
            }
        }

        $('.item-checkbox').on('change', validateSelection);

        // Initial validation check
        validateSelection();
    });
    </script>
</body>
</html>
