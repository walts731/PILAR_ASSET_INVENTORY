<?php
session_start();
require_once "../connect.php";

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Fetch system settings for branding
$system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
];

if (isset($conn) && $conn instanceof mysqli) {
    $result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $system = $result->fetch_assoc();
    }
}

// Pagination and filtering
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'available';

// Build query
$where_conditions = ["a.type = 'asset'", "a.status IN ('serviceable', 'borrowed')"];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(a.description LIKE ? OR a.inventory_tag LIKE ? OR a.brand LIKE ? OR a.model LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

if ($category_filter > 0) {
    $where_conditions[] = "a.category = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if (!empty($status_filter)) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM assets a WHERE $where_clause";
$total_assets = 0;

if ($stmt = $conn->prepare($count_query)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total_assets = $result->fetch_assoc()['total'];
    $stmt->close();
}

$total_pages = ceil($total_assets / $limit);

// Get assets
$assets = [];
$assets_query = "
    SELECT a.*, c.category_name, o.office_name, e.name as employee_name
    FROM assets a
    LEFT JOIN categories c ON c.id = a.category
    LEFT JOIN offices o ON o.id = a.office_id
    LEFT JOIN employees e ON e.employee_id = a.employee_id
    WHERE $where_clause
    ORDER BY a.description ASC
    LIMIT ? OFFSET ?
";

if ($stmt = $conn->prepare($assets_query)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $assets = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Get categories for filter
$categories = [];
$cat_result = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
if ($cat_result) {
    $categories = $cat_result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Assets - <?= htmlspecialchars($system['system_title']) ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <style>
        :root {
            --primary-color: #0b5ed7;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .main-container {
            padding: 2rem 0;
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .asset-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .asset-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-available { background: var(--success-color); color: white; }
        .status-borrowed { background: var(--warning-color); color: white; }
        .status-maintenance { background: var(--danger-color); color: white; }
        .status-unserviceable { background: var(--danger-color); color: white; }

        .btn-borrow {
            background: linear-gradient(45deg, var(--success-color), #146c43);
            border: none;
            color: white;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-borrow:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(25, 135, 84, 0.3);
            color: white;
        }

        .btn-borrow:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .pagination {
            justify-content: center;
        }

        .page-link {
            border-radius: 10px;
            margin: 0 2px;
            border: none;
            color: var(--primary-color);
        }

        .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .asset-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }

        .no-image {
            width: 100%;
            height: 150px;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
            }
            
            .filter-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="guest_dashboard.php">
                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" width="32" height="32" class="me-2">
                <?= htmlspecialchars($system['system_title']) ?>
            </a>
            
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <a href="scan_qr.php" class="btn btn-outline-success me-2">
                    <i class="bi bi-qr-code-scan me-1"></i>QR Scanner
                </a>
                <a href="borrow.php" class="btn btn-outline-info me-2 position-relative">
                    <i class="bi bi-cart me-1"></i>Borrow Cart
                    <?php if (isset($_SESSION['borrow_cart']) && count($_SESSION['borrow_cart']) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= count($_SESSION['borrow_cart']) ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="../logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-container">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h2 class="text-white mb-2">
                <i class="bi bi-grid-3x3-gap me-2"></i>Browse Assets
            </h2>
            <p class="text-white-50">Explore available assets for borrowing</p>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Assets</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by name, tag, brand...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="serviceable" <?= $status_filter === 'serviceable' ? 'selected' : '' ?>>Serviceable</option>
                            <option value="borrowed" <?= $status_filter === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                           
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-white">
                Showing <?= count($assets) ?> of <?= $total_assets ?> assets
                <?php if (!empty($search)): ?>
                    for "<?= htmlspecialchars($search) ?>"
                <?php endif; ?>
            </span>
            <a href="browse_assets.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
            </a>
        </div>

        <!-- Assets Grid -->
        <div class="row">
            <?php if (empty($assets)): ?>
                <div class="col-12">
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3">No Assets Found</h5>
                            <p class="text-muted">Try adjusting your search criteria or filters.</p>
                            <a href="browse_assets.php" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i>View All Assets
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($assets as $asset): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card asset-card">
                            <div class="card-body p-3">
                                <!-- Asset Image -->
                                <?php if (!empty($asset['image'])): ?>
                                    <img src="../img/assets/<?= htmlspecialchars($asset['image']) ?>" 
                                         alt="Asset Image" class="asset-image mb-3">
                                <?php else: ?>
                                    <div class="no-image mb-3">
                                        <i class="bi bi-image" style="font-size: 2rem;"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Asset Info -->
                                <h6 class="card-title mb-2">
                                    <?= htmlspecialchars($asset['description']) ?>
                                </h6>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="status-badge status-<?= strtolower($asset['status']) ?>">
                                        <?= ucfirst($asset['status']) ?>
                                    </span>
                                    <small class="text-muted">
                                        #<?= htmlspecialchars($asset['inventory_tag'] ?? $asset['id']) ?>
                                    </small>
                                </div>

                                <div class="small text-muted mb-3">
                                    <div><strong>Category:</strong> <?= htmlspecialchars($asset['category_name'] ?? 'N/A') ?></div>
                                    <div><strong>Office:</strong> <?= htmlspecialchars($asset['office_name'] ?? 'N/A') ?></div>
                                    <?php if (!empty($asset['brand']) || !empty($asset['model'])): ?>
                                        <div><strong>Brand/Model:</strong> <?= htmlspecialchars(trim($asset['brand'] . ' ' . $asset['model'])) ?></div>
                                    <?php endif; ?>
                                    
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <?php if ($asset['status'] === 'serviceable'): ?>
                                        <button class="btn btn-borrow" onclick="requestBorrowing(<?= $asset['id'] ?>, '<?= htmlspecialchars($asset['description']) ?>')">
                                            <i class="bi bi-box-arrow-right me-1"></i>Request Borrowing
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-borrow" disabled>
                                            <i class="bi bi-x-circle me-1"></i>Not Available
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewDetails(<?= $asset['id'] ?>)">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Asset pagination" class="mt-4">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Request borrowing function
        function requestBorrowing(assetId, assetName) {
            // Show loading state
            const button = document.querySelector(`button[onclick*="${assetId}"]`);
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Adding...';
            }

            // Send AJAX request to add asset to borrow cart
            fetch('borrow_cart_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=add&asset_id=' + assetId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success state briefly
                    if (button) {
                        button.classList.remove('btn-borrow');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="bi bi-check-circle me-1"></i>Added to Cart!';
                    }

                    // Update cart count in navigation
                    const cartBadge = document.querySelector('.navbar .badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.count;
                        // Make sure the badge is visible if it wasn't before
                        cartBadge.style.display = 'inline-block';
                    }

                    // Redirect to borrow form after short delay
                    setTimeout(function() {
                        window.location.href = 'borrow.php';
                    }, 1000);
                } else {
                    alert(data.message || 'Failed to add asset to borrow cart');
                    // Reset button
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="bi bi-box-arrow-right me-1"></i>Request Borrowing';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding asset to borrow cart');
                // Reset button
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-box-arrow-right me-1"></i>Request Borrowing';
                }
            });
        }

        // View details function
        function viewDetails(assetId) {
            window.location.href = `view_asset_details.php?id=${assetId}`;
        }

        // Auto-submit form on filter change
        document.getElementById('category').addEventListener('change', function() {
            this.form.submit();
        });

        document.getElementById('status').addEventListener('change', function() {
            this.form.submit();
        });

        // Search on Enter key
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
