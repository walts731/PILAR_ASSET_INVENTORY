<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'];
$pageTitle = 'Inter-Department Borrowing - PILAR Asset Inventory';
$sidebarActive = 'view_inter_dept_cart';

// Handle clear cart action
if (isset($_GET['clear_cart']) && $_GET['clear_cart'] == 1) {
    $_SESSION['inter_dept_cart'] = [];
    $_SESSION['success_message'] = 'Your box has been cleared successfully.';
    header('Location: view_inter_dept_cart.php');
    exit();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['inter_dept_cart'])) {
    $_SESSION['inter_dept_cart'] = [];
}

// Always use inter_dept_cart for this page
$cart = &$_SESSION['inter_dept_cart'];
$cart_key = 'inter_dept_cart';

// Handle item removal
if (isset($_POST['remove_item'], $_POST['item_key'])) {
    $item_key = $_POST['item_key'];
    if (isset($cart[$item_key])) {
        unset($cart[$item_key]);
        $_SESSION[$cart_key] = $cart;
        $_SESSION['success_message'] = 'Item removed successfully.';
        header('Location: view_inter_dept_cart.php');
        exit();
    }
}

// Handle submission
if (isset($_POST['submit_request'])) {
    if (empty($cart)) {
        $_SESSION['error_message'] = 'Your cart is empty.';
        header('Location: view_inter_dept_cart.php');
        exit();
    }
    
    try {
        $conn->begin_transaction();
        $requested_at = date('Y-m-d H:i:s');
        $requested_return_date = $_POST['requested_return_date'] ?? null;
        if (empty($requested_return_date)) {
            throw new Exception('Please fill in all required fields.');
        }

        foreach ($cart as $item_key => $item) {
            $asset_id = $item['asset_id'];
            $source_office_id = $item['source_office_id'];
            $quantity = $item['quantity'];

            // Check availability
            $check = $conn->prepare("SELECT quantity, status FROM assets WHERE id = ? AND office_id = ?");
            $check->bind_param("ii", $asset_id, $source_office_id);
            $check->execute();
            $asset = $check->get_result()->fetch_assoc();
            $check->close();

            if (!$asset || $asset['quantity'] < $quantity || $asset['status'] !== 'available') {
                throw new Exception("Asset {$item['asset_name']} is no longer available.");
            }

            // Insert request
            // Get purpose from cart item or use empty string if not set
            $purpose = $item['purpose'] ?? '';
            
            $insert = $conn->prepare("
                INSERT INTO borrow_requests ( 
                    user_id, requested_by_user_id, asset_id, office_id, source_office_id,
                    quantity, status, requested_at, due_date, purpose,
                    is_inter_department, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, 1, NOW(), NOW())
            ");
            $insert->bind_param(
                "iiiiiisss",
                $user_id, $user_id, $asset_id, $office_id, $source_office_id,
                $quantity, $requested_at, $requested_return_date, $purpose
            );
            $insert->execute();
            $request_id = $conn->insert_id;
            $insert->close();

            // Approvals
            $approval = $conn->prepare("
                INSERT INTO inter_department_approvals (request_id, approver_id, approval_type, status, created_at, updated_at)
                VALUES (?, (SELECT head_user_id FROM offices WHERE id = ?), 'office_head', 'pending', NOW(), NOW()),
                       (?, (SELECT head_user_id FROM offices WHERE id = ?), 'source_office', 'pending', NOW(), NOW())
            ");
            $approval->bind_param("iiii", $request_id, $office_id, $request_id, $source_office_id);
            $approval->execute();
            $approval->close();

            // Notification
            $notif_msg = "New inter-department borrow request for {$item['asset_name']} from {$item['source_office_name']}";
            // Get notification type ID for borrow requests
            $type_query = $conn->query("SELECT id FROM notification_types WHERE name = 'borrow_request' LIMIT 1");
            $type_row = $type_query->fetch_assoc();
            $type_id = $type_row ? $type_row['id'] : 1; // Default to 1 if type not found
            
            $notif = $conn->prepare("
                INSERT INTO notifications (
                    type_id, title, message, 
                    related_entity_type, related_entity_id, 
                    is_read, created_at, updated_at
                ) VALUES (?, 'New Borrow Request', ?, 'borrow_request', ?, 0, NOW(), NOW())
            ");
            $notif->bind_param("isi", $type_id, $notif_msg, $request_id);
            $notif->execute();
            $notif->close();
        }

        $conn->commit();
        $_SESSION[$cart_key] = [];
        $_SESSION['success_message'] = $is_borrow_cart
            ? 'Borrow request submitted successfully.'
            : 'Inter-department borrow request submitted successfully.';
        header("Location: " . ($is_borrow_cart ? 'borrow_requests.php' : 'inter_dept_borrow_requests.php'));
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = 'Error submitting request: ' . $e->getMessage();
        header('Location: view_inter_dept_cart.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container-fluid mt-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Inter-Department Borrowing Cart</h2>
            <a href="inter_department_borrow.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Assets
            </a>
            <a href="?clear_cart=1" class="btn btn-danger ms-2" onclick="return confirm('Are you sure you want to clear your box? This cannot be undone.');">
                <i class="bi bi-trash"></i> Clear Box
            </a>
        </div>

        <?php if (empty($cart)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-cart4 fs-1 text-muted mb-3"></i>
                    <h4>Your box is empty</h4>
                    <p class="text-muted">Add items from other departments to get started.</p>
                    <a href="inter_department_borrow.php" class="btn btn-primary">
                        <i class="bi bi-search"></i> Browse Assets
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-box-seam me-2"></i> Items in Your Cart
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Asset Name</th>
                                        <th>Source Office</th>
                                        <th>Quantity</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1; foreach ($cart as $key => $item): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($item['asset_name']) ?></td>
                                        <td><?= htmlspecialchars($item['source_office_name']) ?></td>
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmRemoveModal"
                                                    data-item-key="<?= htmlspecialchars($key) ?>"
                                                    data-item-name="<?= htmlspecialchars($item['asset_name']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-pencil-square me-2"></i> Request Details
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="3" placeholder="Enter purpose" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Requested Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="requested_return_date" class="form-control" required>
                                <small class="text-muted">Select the date you plan to return the asset.</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="submit_request" class="btn btn-success px-4">
                                <i class="bi bi-send"></i> Submit Request
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Confirm Remove Modal -->
<div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-labelledby="confirmRemoveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="removeItemForm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmRemoveLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Removal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove <strong id="removeItemName"></strong> from your cart?</p>
                    <input type="hidden" name="item_key" id="removeItemKey">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="remove_item" class="btn btn-danger">Remove</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('confirmRemoveModal');
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const itemKey = button.getAttribute('data-item-key');
        const itemName = button.getAttribute('data-item-name');
        document.getElementById('removeItemKey').value = itemKey;
        document.getElementById('removeItemName').textContent = itemName;
    });
});
</script>
</body>
</html>
