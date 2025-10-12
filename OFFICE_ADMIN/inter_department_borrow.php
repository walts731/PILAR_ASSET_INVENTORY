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

// Include header with dark mode support
require_once '../includes/header.php';

// Set active page for sidebar highlighting
$sidebarActive = 'inter_department_borrow';

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
$category_result = $conn->query("SELECT id, category_name FROM categories WHERE type = 'asset' ORDER BY category_name");
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Build the query to get available assets from other offices
$query = "SELECT a.*, o.office_name, o.id as office_id, c.category_name 
          FROM assets a 
          JOIN offices o ON a.office_id = o.id 
          LEFT JOIN categories c ON a.category = c.id
          WHERE a.office_id != ? 
          AND a.status = 'available' 
          AND a.quantity > 0";

$params = [$office_id];
$types = "i";

// Add search filter
if (!empty($search_query)) {
    $query .= " AND (a.asset_name LIKE ? OR a.description LIKE ? OR a.serial_no LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Add category filter
if (!empty($category_filter)) {
    $query .= " AND a.category = ?";
    $params[] = $category_filter;
    $types .= "i";
}

$query .= " ORDER BY o.office_name, a.asset_name";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $assets = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $assets = [];
    $_SESSION['error_message'] = 'Error preparing database query: ' . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        .asset-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            height: 100%;
        }
        .asset-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .asset-img-container {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            overflow: hidden;
        }
        .asset-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.6rem;
            padding: 0.2rem 0.4rem;
        }
        .search-form .form-control {
            border-radius: 20px;
            padding: 0.5rem 1rem;
        }
        .search-form .btn {
            border-radius: 20px;
            padding: 0.5rem 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php' ?>

    <div class="main">
        <?php include 'includes/topbar.php' ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Inter-Department Borrowing</h1>
                <div>
                    <a href="view_inter_dept_cart.php" class="btn btn-primary position-relative">
                        <i class="fas fa-box-open"></i> View Box
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
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="search" placeholder="Search assets..." value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
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
            <?php include 'modals/view_asset_modal.php'; ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Assets from Other Departments</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($assets)): ?>
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
                                    <?php foreach ($assets as $asset): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                                            <td><?= htmlspecialchars($asset['description']) ?></td>
                                            <td><?= htmlspecialchars($asset['category_name'] ?? 'N/A') ?></td>
                                            <td><?= $asset['quantity'] ?></td>
                                            <td><?= htmlspecialchars($asset['office_name']) ?></td>
                                            <td class="text-nowrap">
                                                <button class="btn btn-sm btn-info view-asset" 
                                                        data-bs-toggle="tooltip" 
                                                        title="View Details"
                                                        data-asset-id="<?= $asset['id'] ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary add-to-cart" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#addToCartModal"
                                                        data-asset-id="<?= $asset['id'] ?>"
                                                        data-asset-name="<?= htmlspecialchars($asset['asset_name']) ?>"
                                                        data-office-id="<?= $asset['office_id'] ?>"
                                                        data-office-name="<?= htmlspecialchars($asset['office_name']) ?>"
                                                        data-max-qty="<?= $asset['quantity'] ?>">
                                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            No assets available for inter-department borrowing at the moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


<style>
/* Main content area */
.main-content {
    flex: 1;
    min-height: 100vh;
    overflow-x: hidden;
    background: #f8f9fa;
    transition: all 0.3s;
    margin-left: 250px; /* Match sidebar width */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0 !important;
    }
    
    .sidebar-collapsed .main-content {
        margin-left: 60px !important;
    }
}
</style>

<!-- View Asset Details Modal -->
<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asset Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded here via JavaScript -->
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading asset details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add to Cart Modal -->
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addToCartForm" method="POST" action="add_to_inter_dept_cart.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add to Box</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">
                            Asset
                            <input type="text" class="form-control mt-1" id="assetNameDisplay" readonly>
                            <input type="hidden" name="asset_id" id="assetId">
                            <input type="hidden" name="asset_name" id="assetName">
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            Source Office
                            <input type="text" class="form-control mt-1" id="sourceOffice" readonly>
                            <input type="hidden" name="source_office_id" id="sourceOfficeId">
                            <input type="hidden" name="source_office_name" id="sourceOfficeName">
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            Quantity <span class="text-danger">*</span>
                            <input type="number" class="form-control mt-1" id="quantity" name="quantity" min="1" value="1" required>
                            <small class="form-text text-muted">Maximum available: <span id="maxQuantity">0</span></small>
                        </label>
                    </div>
                    <input type="hidden" name="purpose" id="purpose" value="">
                    <input type="hidden" name="requested_return_date" id="requestedReturnDate" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Box</button>
                </div>
            </form>
        </div>
    </div>
</div>

        <style>
        /* Main content area */
        .main-content {
            flex: 1;
            min-height: 100vh;
            overflow-x: hidden;
            background: #f8f9fa;
            transition: all 0.3s;
            margin-left: 250px; /* Match sidebar width */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
            
            .sidebar-collapsed .main-content {
                margin-left: 60px !important;
            }
        }
        </style>

        <!-- jQuery and Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        
        <script>
        $(document).ready(function() {
            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('.data-table')) {
                $('.data-table').DataTable().destroy();
            }
            
            $('.data-table').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'desc']]
            });

            // View asset details
            $(document).on('click', '.view-asset', function(e) {
                e.preventDefault();
                const assetId = $(this).data('asset-id');
                const modal = $('#viewAssetModal');
                
                console.log('View asset clicked, ID:', assetId);
                
                // Show loading state
                modal.modal('show');
                modal.find('.modal-body').html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading asset details...</p>
                    </div>
                `);
                
                // Fetch asset details via AJAX with absolute URL
                const url = new URL('get_asset_details.php', window.location.origin + '/PILAR_ASSET_INVENTORY/OFFICE_ADMIN/');
                url.searchParams.append('id', assetId);
                
                console.log('Fetching asset from:', url.toString());
                
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(response => {
                    console.log('AJAX Response:', response);
                    
                    if (response && response.status === 'success' && response.data) {
                        const asset = response.data;
                        console.log('Asset data:', asset);
                        
                        // Create modal content
                        const modalBody = `
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6>Asset Name</h6>
                                        <p>${asset.asset_name || 'N/A'}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6>Description</h6>
                                        <p>${asset.description || 'N/A'}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6>Category</h6>
                                        <p>${asset.category_name || 'N/A'}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6>Status</h6>
                                        <p>${asset.status || 'N/A'}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6>Quantity</h6>
                                        <p>${asset.quantity || '0'} ${asset.unit || ''}</p>
                                    </div>
                                    <div class="mb-3">
                                        <h6>Serial Number</h6>
                                        <p>${asset.serial_number || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                            ${asset.image_path ? `
                                <div class="text-center mt-3">
                                    <img src="../${asset.image_path}" alt="Asset Image" class="img-fluid" style="max-height: 200px;">
                                </div>` 
                                : ''
                            }
                        `;
                        
                        modal.find('.modal-body').html(modalBody);
                    } else {
                        throw new Error(response?.message || 'Failed to load asset details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modal.find('.modal-body').html(`
                        <div class="alert alert-danger">
                            <h5>Error</h5>
                            <p>${error.message}</p>
                            <button class="btn btn-sm btn-secondary mt-2" onclick="$('#viewAssetModal').modal('hide')">Close</button>
                        </div>
                    `);
                });
            });

            // Add to cart functionality
            $(document).on('click', '.add-to-cart', function(e) {
                e.preventDefault();
                const assetId = $(this).data('asset-id');
                const assetName = $(this).data('asset-name');
                const officeName = $(this).data('office-name');
                const officeId = $(this).data('office-id');
                const maxQuantity = $(this).data('max-qty') || 1;
                
                console.log('Office Data:', { officeName, officeId, maxQuantity }); // Debug log
                
                // Set form values
                $('#assetId').val(assetId);
                $('#assetName').val(assetName);
                $('#assetNameDisplay').val(assetName);
                $('#sourceOffice').val(officeName);
                $('#sourceOfficeId').val(officeId);
                $('#sourceOfficeName').val(officeName);
                $('#quantity').attr('max', maxQuantity).val(1);
                $('#maxQuantity').text(maxQuantity);
                
                // Show modal with form
                $('#addToCartModal').modal('show');
            });

            // Form submission
            $('#addToCartForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    type: 'POST',
                    url: 'add_to_inter_dept_cart.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update cart count
                            $('.cart-count').text(response.cart_count);
                            
                            // Show success message
                            const alert = `
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    ${response.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                            $('.container-fluid').prepend(alert);
                            
                            // Hide modal
                            $('#addToCartModal').modal('hide');
                            
                            // Reset form
                            $('#addToCartForm')[0].reset();
                    } else {
                        // Show error message
                        const alert = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ${response.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        $('.container-fluid').prepend(alert);
                    }
                },
                error: function() {
                    // Show error message
                    const alert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            An error occurred. Please try again.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.container-fluid').prepend(alert);
                }
            });
        });
    });
    </script>
</body>
</html>
