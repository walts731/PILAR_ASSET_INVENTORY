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
    /* Wrap header text */
    #inventoryTable th {
        white-space: normal; /* allow text to wrap */
        vertical-align: middle;
        text-align: center;
    }

    /* Reduce header and row padding */
    #inventoryTable th,
    #inventoryTable td {
        padding: 0.3rem 0.5rem;
        font-size: 0.75rem; /* even smaller font */
    }

    /* Ensure table scrolls horizontally */
    .table-responsive {
        overflow-x: auto;
    }
</style>


</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">

        <?php include 'includes/topbar.php'; ?>

        <div class="container-fluid mt-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Infrastructure Inventory</h5>
                    <!-- Optional: Add New button -->
                    <button class="btn btn-outline-info btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="bi bi-plus-circle"></i> Add New
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="inventoryTable" class="table table-striped table-bordered">
                            <thead>
    <tr>
        <th>Classification/<br>Type</th>
        <th>Item<br>description</th>
        <th>Nature Occupancy<br>(schools, offices,<br>hospital, etc.)</th>
        <th>Location</th>
        <th>Date Constructed/<br>Acquired/<br>Manufactured</th>
        <th>Property No./<br>Other reference</th>
        <th colspan="2" class="text-center">Valuation</th>
        <th>Date of<br>Appraisal</th>
        <th>Remarks</th>
    </tr>
    <tr>
        <th colspan="6"></th> <!-- empty cells under previous columns -->
        <th>Acquisition Cost/<br>Insurable Interest</th>
        <th>Market/Appraisal/<br>Insurable Interest</th>
        <th colspan="2"></th> <!-- empty under Date of Appraisal & Remarks -->
    </tr>
</thead>


                            <tbody>
                                <?php foreach ($inventory as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['inventory_id']) ?></td>
                                        <td><?= htmlspecialchars($item['classification_type']) ?></td>
                                        <td><?= htmlspecialchars($item['item_description']) ?></td>
                                        <td><?= htmlspecialchars($item['nature_occupancy']) ?></td>
                                        <td><?= htmlspecialchars($item['location']) ?></td>
                                        <td><?= htmlspecialchars($item['date_constructed_acquired_manufactured']) ?></td>
                                        <td><?= htmlspecialchars($item['property_no_or_reference']) ?></td>
                                        <td><?= htmlspecialchars(number_format($item['acquisition_cost'], 2)) ?></td>
                                        <td><?= htmlspecialchars(number_format($item['market_appraisal_insurable_interest'], 2)) ?></td>
                                        <td><?= htmlspecialchars($item['date_of_appraisal']) ?></td>
                                        <td><?= htmlspecialchars($item['remarks']) ?></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                                <?php if (!empty($item['image_' . $i])): ?>
                                                    <img src="<?= htmlspecialchars($item['image_' . $i]) ?>" alt="Image <?= $i ?>" style="width:50px; height:50px; object-fit:cover; margin-right:3px;">
                                                <?php endif; ?>
                                            <?php endfor; ?>
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
            $('#inventoryTable').DataTable();
        });
    </script>

</body>

</html>