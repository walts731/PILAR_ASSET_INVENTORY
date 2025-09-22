<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['borrow_cart'])) {
    $_SESSION['borrow_cart'] = [];
}

$cart_items = $_SESSION['borrow_cart'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Borrow Cart</title>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="bi bi-cart3"></i> My Borrow Cart</div>
                    <a href="borrow.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus-circle"></i> Add More Items</a>
                </div>
                <div class="card-body">
                    <?php if (empty($cart_items)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x" style="font-size: 4rem; color: #6c757d;"></i>
                            <h4 class="mt-3">Your cart is empty.</h4>
                            <p class="text-muted">Add items from the borrow page to get started.</p>
                        </div>
                    <?php else: ?>
                        <form action="process_borrow_request.php" method="POST">
                            <div class="mb-3">
                                <label for="purpose" class="form-label">Purpose of Borrowing</label>
                                <textarea name="purpose" id="purpose" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Proposed Return Date</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                            </div>

                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Asset Name</th>
                                        <th style="width: 150px;">Quantity</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $asset_id => $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['asset_name']) ?></td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm quantity-input" 
                                                       name="quantities[<?= $asset_id ?>]" 
                                                       value="<?= $item['quantity'] ?>" 
                                                       min="1" 
                                                       max="<?= $item['max_quantity'] ?>" 
                                                       data-asset-id="<?= $asset_id ?>">
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-btn" data-asset-id="<?= $asset_id ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" id="clearCartBtn" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i> Clear Cart</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-square"></i> Submit Borrow Request</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Function to update cart via AJAX
        function updateCart(assetId, quantity) {
            $.post('cart_actions.php', { action: 'update', asset_id: assetId, quantity: quantity }, function(response) {
                if (response.status === 'success') {
                    if (response.removed) {
                        // Reload if the item was removed (quantity set to 0)
                        location.reload();
                    }
                } else {
                    alert('Error: ' + response.message);
                    location.reload(); // Reload to show original quantity
                }
            }, 'json');
        }

        // Update quantity on input change (with debounce)
        let debounceTimeout;
        $('.quantity-input').on('input', function() {
            clearTimeout(debounceTimeout);
            const input = $(this);
            const assetId = input.data('asset-id');
            const quantity = parseInt(input.val());
            const max = parseInt(input.attr('max'));

            if (quantity > max) {
                input.val(max); // Reset to max if exceeded
                return;
            }

            debounceTimeout = setTimeout(() => {
                updateCart(assetId, quantity);
            }, 500); // 500ms delay
        });

        // Remove item from cart
        $('.remove-btn').on('click', function() {
            const assetId = $(this).data('asset-id');
            if (confirm('Are you sure you want to remove this item?')) {
                $.post('cart_actions.php', { action: 'remove', asset_id: assetId }, function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        });

        // Clear entire cart
        $('#clearCartBtn').on('click', function() {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                $.post('cart_actions.php', { action: 'clear' }, function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        });
    });
    </script>
</body>
</html>
