<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'office_admin') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'];
$pageTitle = 'Inter-Department Borrowing - PILAR Asset Inventory';

// Include header
require_once '../includes/header.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['inter_dept_cart'])) {
    $_SESSION['inter_dept_cart'] = [];
}
$cart_count = count($_SESSION['inter_dept_cart']);

// Get filters from GET request
$search_query = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Fetch categories for the filter dropdown
$categories = [];
try {
    $category_result = $conn->query("SELECT id, category_name FROM categories WHERE type = 'asset' ORDER BY category_name");
    if ($category_result) {
        while ($row = $category_result->fetch_assoc()) {
            $categories[] = $row;
        }
        $category_result->free();
    }
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Fetch available assets from other departments
$sql = "
    SELECT a.id, a.asset_name, a.description, a.quantity, a.unit, a.image, 
           c.category_name as category, o.office_name, o.id as office_id
    FROM assets a
    JOIN categories c ON a.category = c.id
    JOIN office o ON a.office_id = o.id
    WHERE a.status = 'available' 
    AND a.quantity > 0 
    AND a.office_id != ?
";

$params = [$office_id];
$types = 'i';

if (!empty($search_query)) {
    $sql .= " AND (a.asset_name LIKE ? OR a.description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($category_filter)) {
    $sql .= " AND a.category = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

$sql .= " ORDER BY o.office_name, c.category_name, a.asset_name";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $available_assets = $stmt->get_result();
    $stmt->close();
} else {
    error_log("Error preparing statement: " . $conn->error);
    $available_assets = [];
}

// Fetch office information for the current user
$office_info = [];
$office_result = $conn->prepare("SELECT * FROM office WHERE id = ?");
if ($office_result) {
    $office_result->bind_param('i', $office_id);
    $office_result->execute();
    $office_info = $office_result->get_result()->fetch_assoc();
    $office_result->close();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Inter-Department Borrowing</h1>
        <div>
            <a href="view_inter_dept_cart.php" class="btn btn-primary position-relative">
                <i class="fas fa-shopping-cart"></i> View Cart
                <?php if ($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $cart_count ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or description..." 
                           value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                <?= ($category_filter == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Available Assets -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Available Assets from Other Departments</h5>
        </div>
        <div class="card-body">
            <?php if (isset($available_assets) && $available_assets->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Asset Name</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Available Qty</th>
                                <th>Office</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($asset = $available_assets->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($asset['description']) ?></td>
                                    <td><?= htmlspecialchars($asset['category']) ?></td>
                                    <td><?= $asset['quantity'] . ' ' . htmlspecialchars($asset['unit']) ?></td>
                                    <td><?= htmlspecialchars($asset['office_name']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary add-to-cart" 
                                                data-asset-id="<?= $asset['id'] ?>"
                                                data-asset-name="<?= htmlspecialchars($asset['asset_name']) ?>"
                                                data-office-id="<?= $asset['office_id'] ?>"
                                                data-office-name="<?= htmlspecialchars($asset['office_name']) ?>">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No assets available for inter-department borrowing at the moment.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add to Cart Modal -->
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addToCartForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Asset</label>
                        <input type="text" class="form-control" id="assetName" readonly>
                        <input type="hidden" name="asset_id" id="assetId">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source Office</label>
                        <input type="text" class="form-control" id="sourceOffice" readonly>
                        <input type="hidden" name="source_office_id" id="sourceOfficeId">
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                        <div class="form-text">Maximum available: <span id="maxQuantity">1</span></div>
                    </div>
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose</label>
                        <textarea class="form-control" id="purpose" name="purpose" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="requested_return_date" class="form-label">Expected Return Date</label>
                        <input type="date" class="form-control" id="requested_return_date" name="requested_return_date" 
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Handle Add to Cart button click
    $('.add-to-cart').click(function() {
        const assetId = $(this).data('asset-id');
        const assetName = $(this).data('asset-name');
        const officeId = $(this).data('office-id');
        const officeName = $(this).data('office-name');
        const maxQuantity = $(this).closest('tr').find('td:eq(3)').text().split(' ')[0];
        
        $('#assetId').val(assetId);
        $('#assetName').val(assetName);
        $('#sourceOfficeId').val(officeId);
        $('#sourceOffice').val(officeName);
        $('#quantity').attr('max', maxQuantity);
        $('#maxQuantity').text(maxQuantity);
        
        // Reset form
        $('#addToCartForm')[0].reset();
        
        // Show modal
        $('#addToCartModal').modal('show');
    });

    // Handle form submission
    $('#addToCartForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            asset_id: $('#assetId').val(),
            asset_name: $('#assetName').val(),
            source_office_id: $('#sourceOfficeId').val(),
            source_office_name: $('#sourceOffice').val(),
            quantity: $('#quantity').val(),
            purpose: $('#purpose').val(),
            requested_return_date: $('#requested_return_date').val()
        };
        
        // Add to session cart
        $.ajax({
            url: 'add_to_inter_dept_cart.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#addToCartModal').modal('hide');
                    showAlert('success', 'Item added to cart successfully!');
                    // Update cart count
                    $('.cart-count').text(response.cart_count);
                } else {
                    showAlert('danger', response.message || 'Failed to add item to cart.');
                }
            },
            error: function() {
                showAlert('danger', 'An error occurred. Please try again.');
            }
        });
    });
    
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alert-placeholder').html(alertHtml);
    }
});
</script>
