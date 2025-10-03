<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Set page title
$pageTitle = 'Borrow Assets - PILAR Asset Inventory';

// Include header with dark mode support
require_once '../includes/header.php';

// Initialize cart count for borrowing
if (!isset($_SESSION['borrow_cart'])) {
    $_SESSION['borrow_cart'] = [];
}
$cart_count = count($_SESSION['borrow_cart']);
// Get filters from GET request
$search_query = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Fetch categories for the filter dropdown with count of available assets
$categories = [];
$category_counts = [];

try {
    // First, get all categories
    $category_result = $conn->query("SELECT id, category FROM category ORDER BY category");
    if ($category_result) {
        while ($row = $category_result->fetch_assoc()) {
            $categories[] = $row;
        }
        $category_result->free();

        // Get count of available items per category
        $count_sql = "SELECT c.id, COUNT(a.id) as item_count 
                     FROM category c 
                     LEFT JOIN assets a ON c.id = a.category 
                     WHERE a.status = 'available' AND a.quantity > 0 
                     GROUP BY c.id";
        $count_result = $conn->query($count_sql);
        if ($count_result) {
            while ($row = $count_result->fetch_assoc()) {
                $category_counts[$row['id']] = $row['item_count'];
            }
            $count_result->free();
        }
    } else {
        error_log("Error fetching categories: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Exception when fetching categories: " . $e->getMessage());
}

// Base query for available assets
// Debug: Show the current SQL query
$debug_sql = "
    SELECT a.id, a.asset_name, a.description, a.quantity, a.unit, a.image, a.status, a.type,
           c.category, c.id as category_id
    FROM assets a
    LEFT JOIN category c ON a.category = c.id
    WHERE a.status = 'available' AND a.quantity > 0
";

$debug_result = $conn->query($debug_sql);
if ($debug_result) {
    error_log("Debug - Available Assets Query: " . $debug_sql);
    error_log("Debug - Found " . $debug_result->num_rows . " available assets");
    while ($debug_row = $debug_result->fetch_assoc()) {
        error_log(sprintf(
            "Asset ID %d: %s (Status: %s, Type: %s, Qty: %d, Category: %s)",
            $debug_row['id'],
            $debug_row['asset_name'],
            $debug_row['status'],
            $debug_row['type'],
            $debug_row['quantity'],
            $debug_row['category'] ?? 'NULL'
        ));
    }
    $debug_result->free();
}

// Original query
$sql = "
    SELECT a.id, a.asset_name, a.description, a.quantity, a.unit, a.image, 
           c.category, c.id as category_id
    FROM assets a
    LEFT JOIN category c ON a.category = c.id
    WHERE a.status = 'available' AND a.quantity > 0
";

$params = [];
$types = '';

if (!empty($search_query)) {
    $sql .= " AND (a.asset_name LIKE ? OR a.description LIKE ?)";
    $search_term = "%{$search_query}%";
    array_push($params, $search_term, $search_term);
    $types .= 'ss';
}

if (!empty($category_filter)) {
    $sql .= " AND a.category = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

$sql .= " ORDER BY a.asset_name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$available_assets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $darkModeClass; ?>">
<head>
    <meta charset="UTF-8">
    <title>Borrow Assets - PILAR Asset Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Layout styles */
        .wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }
        
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            overflow-y: auto;
            background-color: #f8f9fa;
            transition: all 0.3s;
        }
        
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        
        /* Page-specific dark mode styles */
        .dark-mode .asset-card {
            background-color: var(--dark-card);
            border-color: var(--dark-border);
        }
        
        .dark-mode .asset-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* Dark mode layout styles */
        .dark-mode .sidebar {
            background-color: #1a1a1a;
            border-right: 1px solid #333;
        }
        
        .dark-mode .main-content {
            background-color: #121212;
        }
        
        .dark-mode .sidebar a {
            color: #e0e0e0;
        }
        
        .dark-mode .sidebar a:hover,
        .dark-mode .sidebar a.active {
            background-color: #333;
            color: #fff;
        }
        
        .dark-mode .card {
            background-color: var(--dark-card);
            border-color: var(--dark-border);
        }
        
        .dark-mode .card-header {
            background-color: rgba(0, 0, 0, 0.1);
            border-bottom-color: var(--dark-border);
        }
        
        .dark-mode .form-control,
        .dark-mode .form-select {
            background-color: var(--dark-input-bg);
            border-color: var(--dark-border);
            color: var(--dark-input-text);
        }
        
        .dark-mode .btn-outline-secondary {
            color: var(--dark-text);
            border-color: var(--dark-border);
        }
        
        .dark-mode .btn-outline-secondary:hover {
            background-color: var(--dark-hover);
            color: var(--dark-text);
        }
        
        .dark-mode .text-muted {
            color: #adb5bd !important;
        }
        
        /* Original styles */
        .asset-card {
            transition: transform 0.2s;
            height: 100%;
        }
        
        .asset-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
        }
        
        /* Ensure proper spacing in dark mode */
        .dark-mode .card-body {
            background-color: var(--dark-card);
        }
        
        /* Fix for dropdown menus */
        .dark-mode .dropdown-menu {
            background-color: var(--dark-dropdown);
            border-color: var(--dark-border);
        }
        
        .dark-mode .dropdown-item {
            color: var(--dark-text);
        }
        
        .dark-mode .dropdown-item:hover,
        .dark-mode .dropdown-item:focus {
            background-color: var(--dark-hover);
            color: var(--dark-text);
        }
        
        /* Fix for alerts */
        .dark-mode .alert {
            background-color: var(--dark-card);
            border-color: var(--dark-border);
            color: var(--dark-text);
        }
    </style>
</head>
<body class="<?php echo $darkModeClass; ?>">
    <div class="wrapper d-flex">
        <?php 
        // Set active page for sidebar highlighting
        $sidebarActive = 'borrow';
        include 'includes/sidebar.php'; 
        ?>

        <div class="main-content" style="flex: 1; margin-left: 250px;">
        <?php 
        include 'includes/topbar.php';
        
        // Display success/error messages if any
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">';
            echo $_SESSION['success'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">';
            echo $_SESSION['error'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="container mt-4">
            <div id="alert-placeholder"></div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Available Assets for Borrowing</h3>
                <a href="view_inter_dept_cart.php" class="btn btn-primary position-relative">
                    <i class="bi bi-cart3"></i> View Box
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $cart_count ? $cart_count : '' ?>
                    </span>
                </a>
            </div>

            <!-- Filter and Search Form -->
            <div class="card shadow-sm mb-4">
{{ ... }}
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Search by asset name or description..." value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select" id="categoryFilter" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <?php if (isset($category['id']) && isset($category['category'])): 
                                            $item_count = $category_counts[$category['id']] ?? 0;
                                            if ($item_count > 0 || !empty($category_filter) && $category_filter == $category['id']): 
                                        ?>
                                            <option value="<?= htmlspecialchars($category['id']) ?>" 
                                                <?= (isset($category_filter) && $category_filter == $category['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['category']) ?> 
                                                (<?= $item_count ?>)
                                            </option>
                                        <?php 
                                            endif;
                                        endif; 
                                        ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No categories available</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                        <?php if (!empty($search_query) || !empty($category_filter)): ?>
                            <div class="col-md-2">
                                <a href="borrow.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Borrowing Cart -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Borrowing Box</h5>
                    <?php if (!empty($_SESSION['borrow_cart'])): ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($_SESSION['borrow_cart'] as $asset_id => $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($item['asset_name']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $item['quantity'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button class="btn btn-primary w-100" id="checkout-btn" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                            Proceed to Checkout
                        </button>
                    <?php else: ?>
                        <p class="text-muted">Your box is empty</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Asset Grid -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php 
                error_log("Rendering assets - Found " . $available_assets->num_rows . " assets to display");
                if ($available_assets->num_rows > 0): 
                    while ($row = $available_assets->fetch_assoc()): 
                        error_log(sprintf(
                            "Rendering Asset ID %d: %s (Qty: %d, Category: %s)",
                            $row['id'],
                            $row['asset_name'],
                            $row['quantity'],
                            $row['category'] ?? 'NULL'
                        ));
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm asset-card">
                        <img src="../img/assets/<?= htmlspecialchars($row['image'] ?: 'default.png') ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($row['asset_name']) ?>" 
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['asset_name']) ?></h5>
                            <p class="card-text text-muted">
                                <i class="bi bi-tag"></i> <?= htmlspecialchars($row['category'] ?? 'Uncategorized') ?><br>
                                <i class="bi bi-box"></i> Qty: <?= (int)$row['quantity'] ?>
                            </p>
                            <form class="add-to-cart-form" method="post">
                                <input type="hidden" name="asset_id" value="<?= (int)$row['id'] ?>">
                                <input type="hidden" name="asset_name" value="<?= htmlspecialchars($row['asset_name']) ?>">
                                <input type="hidden" name="max_quantity" value="<?= (int)$row['quantity'] ?>">
                                <div class="input-group mb-2">
                                    <input type="number" 
                                           name="quantity" 
                                           class="form-control" 
                                           value="1" 
                                           min="1" 
                                           max="<?= (int)$row['quantity'] ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile; 
                else: 
                    // Log if no assets were found
                    error_log("No assets found matching the criteria");
                    
                    // Check if there are any assets in the database at all
                    $check_assets = $conn->query("SELECT COUNT(*) as total FROM assets");
                    $total_assets = $check_assets ? $check_assets->fetch_assoc()['total'] : 0;
                    error_log("Total assets in database: " . $total_assets);
                    
                    // Check how many have status = 'available' and quantity > 0
                    $check_available = $conn->query("SELECT COUNT(*) as available FROM assets WHERE status = 'available' AND quantity > 0");
                    $available_count = $check_available ? $check_available->fetch_assoc()['available'] : 0;
                    error_log("Available assets (status='available' AND quantity>0): " . $available_count);
                ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-search" style="font-size: 4rem; color: #6c757d;"></i>
                        <h4 class="mt-3">No Assets Found</h4>
                        <p class="text-muted">No available assets match your search criteria.</p>
                        <?php if ($total_assets === 0): ?>
                            <p class="text-muted">There are currently no assets in the system.</p>
                        <?php elseif ($available_count === 0): ?>
                            <p class="text-muted">All assets are currently unavailable or out of stock.</p>
                        <?php endif; ?>
                        <a href="borrow.php" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-left"></i> Back to All Assets
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Handle Add to Cart form submission
        $('.add-to-cart-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serialize() + '&action=add';

            $.post('cart_actions.php', formData, function(response) {
                if (response.status === 'success') {
                    // Update cart badge
                    const cartBadge = $('#cart-badge');
                    cartBadge.text(response.cart_count > 0 ? response.cart_count : '');
                    // Show success alert
                    showAlert(response.message, 'success');
                } else {
                    showAlert(response.message, 'danger');
                }
            }).fail(function(xhr, status, error) {
                showAlert('Error: ' + error, 'danger');
            });
        });

        // Function to show dynamic alerts
        function showAlert(message, type) {
            const alertPlaceholder = $('#alert-placeholder');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = [
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">`,
                `   <div>${message}</div>`,
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>'
            ].join('');

            alertPlaceholder.append(wrapper);

            // Automatically dismiss the alert after 5 seconds
            setTimeout(() => {
                $(wrapper).fadeOut(500, function() { $(this).remove(); });
            }, 5000);
        }
    });
    </script>

    <?php 
    // Include the footer with dark mode support and necessary scripts
    require_once '../includes/footer.php'; 
    ?>
    <!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">Submit Borrow Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="checkoutForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] = bin2hex(random_bytes(32)) ?>">
                    
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose of Borrowing</label>
                        <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Expected Return Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" 
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6>Items to Borrow:</h6>
                        <ul id="cart-items-list" class="mb-0">
                            <!-- Cart items will be populated by JavaScript -->
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Add this to your existing script section
$(document).ready(function() {
    // Handle checkout button click
    $('#checkout-btn').on('click', function() {
        if (Object.keys(<?= json_encode($_SESSION['borrow_cart'] ?? []) ?>).length === 0) {
            showAlert('Your cart is empty', 'warning');
            return false;
        }
        
        // Update cart items list in modal
        const itemsList = $('#cart-items-list');
        itemsList.empty();
        
        <?php foreach ($_SESSION['borrow_cart'] ?? [] as $item): ?>
            itemsList.append(`<li>${<?= json_encode($item['quantity'] ?? 0) ?>}x ${<?= json_encode($item['asset_name'] ?? '') ?>}</li>`);
        <?php endforeach; ?>
    });
    
    // Handle form submission
    $('#checkoutForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Submitting...
        `);
        
        // Submit the form
        $.post('submit_borrow_request.php', formData, function(response) {
            if (response.status === 'success') {
                // Show success message
                showAlert('Borrow request submitted successfully!', 'success');
                
                // Close modal and redirect or refresh
                $('#checkoutModal').modal('hide');
                window.location.href = 'borrow_requests.php?success=' + response.request_id;
            } else {
                showAlert(response.message || 'An error occurred', 'danger');
                submitBtn.prop('disabled', false).html(originalText);
            }
        }, 'json').fail(function(xhr, status, error) {
            showAlert('Error: ' + error, 'danger');
            submitBtn.prop('disabled', false).html(originalText);
        });
    });
});
</script>
</body>