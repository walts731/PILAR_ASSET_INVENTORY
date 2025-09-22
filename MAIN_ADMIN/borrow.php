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
$sql = "
    SELECT a.id, a.asset_name, a.description, a.quantity, a.unit, a.image, 
           c.category, c.id as category_id
    FROM assets a
    JOIN category c ON a.category = c.id
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrow Assets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .asset-card {
            transition: transform 0.2s;
        }
        .asset-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <div class="container mt-4">
            <div id="alert-placeholder"></div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Available Assets for Borrowing</h3>
                <a href="view_cart.php" class="btn btn-primary position-relative">
                    <i class="bi bi-cart3"></i> View Cart
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $cart_count ?>
                    </span>
                </a>
            </div>

            <!-- Filter and Search Form -->
            <div class="card shadow-sm mb-4">
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

            <!-- Asset Grid -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php if ($available_assets->num_rows > 0): ?>
                    <?php while ($row = $available_assets->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm asset-card">
                                <img src="../img/assets/<?= htmlspecialchars($row['image'] ?: 'default.png') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['asset_name']) ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($row['asset_name']) ?></h5>
                                    <p class="card-text text-muted small"><?= htmlspecialchars($row['description']) ?></p>
                                    <p class="card-text">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($row['category']) ?></span>
                                        <span class="badge bg-info">Available: <?= $row['quantity'] ?></span>
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <form class="add-to-cart-form">
                                        <input type="hidden" name="asset_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="asset_name" value="<?= htmlspecialchars($row['asset_name']) ?>">
                                        <input type="hidden" name="max_quantity" value="<?= $row['quantity'] ?>">
                                        <div class="input-group">
                                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?= $row['quantity'] ?>">
                                            <button type="submit" class="btn btn-outline-primary">Add to Cart</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-search" style="font-size: 4rem; color: #6c757d;"></i>
                            <h4 class="mt-3">No Assets Found</h4>
                            <p class="text-muted">No available assets match your search criteria.</p>
                            <a href="borrow.php" class="btn btn-primary">Clear Filters</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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
                    $('#cart-badge').text(response.cart_count);
                    // Show success alert
                    showAlert(response.message, 'success');
                } else {
                    showAlert(response.message, 'danger');
                }
            }, 'json');
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
</body>
</html>