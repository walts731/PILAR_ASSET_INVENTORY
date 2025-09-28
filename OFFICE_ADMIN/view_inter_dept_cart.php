<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'];
$pageTitle = 'Inter-Department Borrowing Cart - PILAR Asset Inventory';

// Include header
require_once '../includes/header.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['inter_dept_cart'])) {
    $_SESSION['inter_dept_cart'] = [];
}
$cart = &$_SESSION['inter_dept_cart'];
$cart_count = count($cart);

// Handle item removal
if (isset($_POST['remove_item']) && isset($_POST['item_key'])) {
    $item_key = $_POST['item_key'];
    if (isset($cart[$item_key])) {
        unset($cart[$item_key]);
        $_SESSION['success_message'] = 'Item removed from cart successfully.';
        header('Location: view_inter_dept_cart.php');
        exit();
    }
}

// Handle cart submission
if (isset($_POST['submit_request'])) {
    if (empty($cart)) {
        $_SESSION['error_message'] = 'Your cart is empty.';
        header('Location: view_inter_dept_cart.php');
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $requested_at = date('Y-m-d H:i:s');
        $requested_return_date = $_POST['requested_return_date'] ?? null;
        $purpose = trim($_POST['purpose'] ?? '');
        
        // Validate required fields
        if (empty($requested_return_date) || empty($purpose)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        // Create a borrow request for each item in the cart
        foreach ($cart as $item_key => $item) {
            $asset_id = $item['asset_id'];
            $source_office_id = $item['source_office_id'];
            $quantity = $item['quantity'];
            
            // Check if the asset is still available
            $check_sql = "SELECT quantity, status FROM assets WHERE id = ? AND office_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('ii', $asset_id, $source_office_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();
            
            if (!$check_result || $check_result['status'] !== 'available' || $check_result['quantity'] < $quantity) {
                throw new Exception("Asset {$item['asset_name']} is no longer available in the requested quantity.");
            }
            
            // Insert into borrow_requests table
            $insert_sql = "
                INSERT INTO borrow_requests (
                    user_id, requested_by_user_id, asset_id, office_id, source_office_id, 
                    quantity, status, purpose, requested_at, requested_return_date, 
                    is_inter_department, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending_approval', ?, ?, ?, 1, NOW(), NOW())";
                
            $insert_stmt = $conn->prepare($insert_sql);
            $pending_status = 'pending_approval';
            $insert_stmt->bind_param(
                'iiiiissss', 
                $user_id, $user_id, $asset_id, $office_id, $source_office_id,
                $quantity, $purpose, $requested_at, $requested_return_date
            );
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to create borrow request: " . $insert_stmt->error);
            }
            
            $request_id = $conn->insert_id;
            $insert_stmt->close();
            
            // Create approval records
            // 1. Office head approval (current office)
            $approval_sql = "
                INSERT INTO inter_department_approvals (
                    request_id, approver_id, approval_type, status, created_at, updated_at
                ) VALUES (?, 
                    (SELECT head_user_id FROM office WHERE id = ?), 
                    'office_head', 'pending', NOW(), NOW()
                )";
                
            $approval_stmt = $conn->prepare($approval_sql);
            $approval_stmt->bind_param('ii', $request_id, $office_id);
            if (!$approval_stmt->execute()) {
                throw new Exception("Failed to create office head approval record: " . $approval_stmt->error);
            }
            $approval_stmt->close();
            
            // 2. Source office approval
            $approval_sql = "
                INSERT INTO inter_department_approvals (
                    request_id, approver_id, approval_type, status, created_at, updated_at
                ) VALUES (?, 
                    (SELECT head_user_id FROM office WHERE id = ?), 
                    'source_office', 'pending', NOW(), NOW()
                )";
                
            $approval_stmt = $conn->prepare($approval_sql);
            $approval_stmt->bind_param('ii', $request_id, $source_office_id);
            if (!$approval_stmt->execute()) {
                throw new Exception("Failed to create source office approval record: " . $approval_stmt->error);
            }
            $approval_stmt->close();
            
            // Create notification for office head
            $notification_message = "New inter-department borrow request for {$item['asset_name']} from {$item['source_office_name']}";
            $notification_sql = "
                INSERT INTO notifications (
                    user_id, title, message, type, related_id, related_type, is_read, created_at
                ) VALUES (
                    (SELECT head_user_id FROM office WHERE id = ?), 
                    'New Borrow Request', 
                    ?, 
                    'borrow_request', 
                    ?, 
                    'inter_dept_borrow', 
                    0, 
                    NOW()
                )";
                
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->bind_param('isi', $office_id, $notification_message, $request_id);
            if (!$notification_stmt->execute()) {
                error_log("Failed to create notification: " . $notification_stmt->error);
                // Don't fail the whole process if notification fails
            }
            $notification_stmt->close();
        }
        
        // If we got here, everything was successful
        $conn->commit();
        
        // Clear the cart
        $_SESSION['inter_dept_cart'] = [];
        
        $_SESSION['success_message'] = 'Your inter-department borrow request has been submitted successfully.';
        header('Location: inter_dept_borrow_requests.php');
        exit();
        
    } catch (Exception $e) {
        // Something went wrong, roll back
        $conn->rollback();
        $_SESSION['error_message'] = 'Error submitting request: ' . $e->getMessage();
        header('Location: view_inter_dept_cart.php');
        exit();
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Inter-Department Borrowing Cart</h1>
        <div>
            <a href="inter_department_borrow.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Assets
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Add items from other departments to get started.</p>
                <a href="inter_department_borrow.php" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Browse Assets
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Items in Cart (<?= $cart_count ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Asset</th>
                                        <th>Source Office</th>
                                        <th>Quantity</th>
                                        <th>Purpose</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $key => $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" 
                                                             alt="<?= htmlspecialchars($item['asset_name']) ?>" 
                                                             class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fas fa-box text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($item['asset_name']) ?></h6>
                                                        <small class="text-muted">ID: <?= $item['asset_id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($item['source_office_name']) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= !empty($item['purpose']) ? htmlspecialchars(substr($item['purpose'], 0, 30)) . '...' : 'N/A' ?></td>
                                            <td>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to remove this item from your cart?');">
                                                    <input type="hidden" name="item_key" value="<?= $key ?>">
                                                    <button type="submit" name="remove_item" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Request Summary</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="submitRequestForm">
                            <div class="mb-3">
                                <label for="purpose" class="form-label">Purpose of Borrowing</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="requested_return_date" class="form-label">Expected Return Date</label>
                                <input type="date" class="form-control" id="requested_return_date" 
                                       name="requested_return_date" 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                            
                            <hr>
                            
                            <div class="d-grid">
                                <button type="submit" name="submit_request" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Form validation
    $('#submitRequestForm').on('submit', function(e) {
        const purpose = $('#purpose').val().trim();
        const returnDate = $('#requested_return_date').val();
        
        if (!purpose) {
            e.preventDefault();
            showAlert('danger', 'Please enter the purpose of borrowing.');
            return false;
        }
        
        if (!returnDate) {
            e.preventDefault();
            showAlert('danger', 'Please select an expected return date.');
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');
        
        return true;
    });
    
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.container-fluid').prepend(alertHtml);
    }
});
</script>
