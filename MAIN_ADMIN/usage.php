<?php
require_once '../connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// --- Office Filter ---
$selected_office = isset($_GET['office']) ? intval($_GET['office']) : 0;

// Fetch all offices for dropdown
$offices_query = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");

// --- Summary Query (Chart Data) ---
if ($selected_office) {
    // Specific office: show totals per asset
    $sql_summary = "
        SELECT a.description AS label, SUM(cl.quantity_consumed) AS total_consumed
        FROM consumption_log cl
        LEFT JOIN assets a ON cl.asset_id = a.id
        WHERE cl.office_id = $selected_office
        GROUP BY cl.asset_id
        ORDER BY total_consumed DESC
    ";
} else {
    // All offices: show totals per office
    $sql_summary = "
        SELECT o.office_name AS label, SUM(cl.quantity_consumed) AS total_consumed
        FROM consumption_log cl
        JOIN offices o ON cl.office_id = o.id
        GROUP BY o.id
        ORDER BY total_consumed DESC
    ";
}

$summary_result = $conn->query($sql_summary);

$labels = [];
$totals = [];
while ($row = $summary_result->fetch_assoc()) {
    $labels[] = $row['label'];
    $totals[] = $row['total_consumed'];
}

// --- Detailed Consumption Log ---
$sql_details = "
    SELECT cl.id, a.description, o.office_name, cl.quantity_consumed, 
           u1.fullname AS recipient, u2.fullname AS dispensed_by, 
           cl.consumption_date, cl.remarks
    FROM consumption_log cl
    LEFT JOIN assets a ON cl.asset_id = a.id
    JOIN offices o ON cl.office_id = o.id
    LEFT JOIN users u1 ON cl.recipient_user_id = u1.id
    LEFT JOIN users u2 ON cl.dispensed_by_user_id = u2.id
    " . ($selected_office ? "WHERE cl.office_id = $selected_office" : "") . "
    ORDER BY cl.consumption_date DESC
";
$details_result = $conn->query($sql_details);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Usage Reports - Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>

<?php include 'includes/sidebar.php'; ?>
<div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container-fluid mt-4">

        <!-- Filter Form -->
        <form method="get" class="row g-3 mb-4" id="officeFilterForm">
            <div class="col-md-4">
                <label for="office" class="form-label">Filter by Office</label>
                <select name="office" id="office" class="form-select" onchange="document.getElementById('officeFilterForm').submit();">
                    <option value="0">All Offices</option>
                    <?php while ($office = $offices_query->fetch_assoc()): ?>
                        <option value="<?= $office['id'] ?>" <?= ($selected_office == $office['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($office['office_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <!-- Chart -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <strong>
                    <?= $selected_office ? "Consumption for Selected Office" : "Consumption by Office (All)" ?>
                </strong>
            </div>
            <div class="card-body">
                <canvas id="consumptionChart" height="100"></canvas>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Detailed Consumption Log</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="consumptionTable" class="table ">
                        <thead class="table-light">
                            <tr>
                                <th>Asset</th>
                                <th>Office</th>
                                <th>Quantity</th>
                                <th>Dispensed By</th>
                                <th>Date</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $details_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['office_name']) ?></td>
                                    <td><?= $row['quantity_consumed'] ?></td>
                                    <td><?= htmlspecialchars($row['dispensed_by'] ?? '-') ?></td>
                                    <td><?= date("F j, Y", strtotime($row['consumption_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['remarks']) ?></td>
                                </tr>
                            <?php endwhile; ?>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Chart.js
    const ctx = document.getElementById('consumptionChart').getContext('2d');
    const consumptionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Total Consumed',
                data: <?= json_encode($totals) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // DataTable
    $(document).ready(function() {
        $('#consumptionTable').DataTable();
    });
</script>

</body>
</html>
