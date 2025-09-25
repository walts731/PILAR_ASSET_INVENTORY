<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Set office_id if not set
if (!isset($_SESSION['office_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    if ($stmt->fetch()) {
        $_SESSION['office_id'] = $office_id;
    }
    $stmt->close();
}

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// Fetch infrastructure inventory data
$inventory = [];
$result = $conn->query("SELECT * FROM infrastructure_inventory ORDER BY inventory_id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inventory[] = $row;
    }
}
$infrastructure_total = count($inventory);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Infrastructure Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        .page-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%);
            border: 1px solid #e9ecef;
            border-radius: .75rem;
        }
        .page-header .title { font-weight: 600; }
        .toolbar .btn { transition: transform .08s ease-in; }
        .toolbar .btn:hover { transform: translateY(-1px); }
        .card-hover:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.06) !important; }
        /* Sticky table header for better readability */
        .table thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>
        <div class="container-fluid mt-4">
            <!-- Page Header -->
            <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
                        <i class="bi bi-building text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 title">Infrastructure Inventory</div>
                        <div class="text-muted small">Facilities and structures register</div>
                    </div>
                </div>
                <div class="toolbar d-flex gap-2">
                    <button class="btn btn-outline-info btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addInventoryModal" title="Add new infrastructure record">
                        <i class="bi bi-plus-circle me-1"></i> Add New
                    </button>
                </div>
            </div>

            <div class="card shadow-sm card-hover">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-table"></i>
                        <span>Listing</span>
                        <span class="badge text-bg-secondary"><?= $infrastructure_total ?> item<?= $infrastructure_total == 1 ? '' : 's' ?></span>
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <button id="toggleDensity" class="btn btn-outline-secondary btn-sm rounded-pill" title="Toggle compact density">
                            <i class="bi bi-arrows-vertical me-1"></i> Density
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="inventoryTable" class="table table-sm table-striped table-hover align-middle table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center align-middle" title="Classification or Type">Classification/<br>Type</th>
                                    <th class="text-center align-middle" title="Item Description">Item<br>description</th>
                                    <th class="text-center align-middle" title="Nature of Occupancy">Nature Occupancy<br>(schools, offices,<br>hospital, etc.)</th>
                                    <th class="text-center align-middle" title="Location">Location</th>
                                    <th class="text-center align-middle" title="Date Constructed/Acquired/Manufactured">Date Constructed/<br>Acquired/<br>Manufactured</th>
                                    <th class="text-center align-middle" title="Property No. or other reference">Property No./<br>Other reference</th>
                                    <th class="text-center align-middle" title="Actions">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['classification_type']) ?></td>
                                        <td><?= htmlspecialchars($item['item_description']) ?></td>
                                        <td><?= htmlspecialchars($item['nature_occupancy']) ?></td>
                                        <td><?= htmlspecialchars($item['location']) ?></td>
                                        <td><?= date("M-Y", strtotime($item['date_constructed_acquired_manufactured'])) ?></td>
                                        <td><?= htmlspecialchars($item['property_no_or_reference']) ?></td>
                                        <td class="text-center text-nowrap">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill view-btn" title="View details"
                                                data-id="<?= $item['inventory_id'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewInventoryModal">
                                                <i class="bi bi-eye me-1"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable once with refined defaults
            const dt = $('#inventoryTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [],
                language: {
                    search: 'Filter:',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                    paginate: { previous: 'Prev', next: 'Next' }
                }
            });

            // Density toggle (compact spacing)
            $('#toggleDensity').on('click', function() {
                $('#inventoryTable').toggleClass('table-sm');
            });

            // Handle view button click
            $('.view-btn').on('click', function() {
                let inventoryId = $(this).data('id');
                $('#inventoryDetails').html('<div class="text-center text-muted">Loading...</div>');
                $.ajax({
                    url: 'view_infrastructure.php',
                    type: 'GET',
                    data: { id: inventoryId },
                    success: function(response) {
                        $('#inventoryDetails').html(response);
                    },
                    error: function() {
                        $('#inventoryDetails').html('<div class="text-danger">Error loading details.</div>');
                    }
                });
            });
        });
    </script>

    <!-- View Inventory Modal -->
    <?php include 'view_infrastructure_modal.php'; ?>
    <!-- Add Inventory Modal -->
    <?php include 'modals/add_infrastructure_modal.php'; ?>
</body>
</html>