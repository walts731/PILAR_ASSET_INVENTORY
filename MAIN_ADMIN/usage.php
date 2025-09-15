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
$selected_year   = isset($_GET['year']) ? intval($_GET['year']) : 0;
$selected_month  = isset($_GET['month']) ? intval($_GET['month']) : 0;
$selected_day    = isset($_GET['day']) ? intval($_GET['day']) : 0;

// Fetch all offices for dropdown
$offices_query = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");

// --- Build date filter ---
$date_conditions = [];
if ($selected_year)  $date_conditions[] = "YEAR(cl.consumption_date) = $selected_year";
if ($selected_month) $date_conditions[] = "MONTH(cl.consumption_date) = $selected_month";
if ($selected_day)   $date_conditions[] = "DAY(cl.consumption_date) = $selected_day";

$date_sql = $date_conditions ? " AND " . implode(" AND ", $date_conditions) : "";

// --- Summary Query (Chart Data) ---
if ($selected_office) {
    // Specific office: totals per asset
    $sql_summary = "
        SELECT a.description AS label, SUM(cl.quantity_consumed) AS total_consumed
        FROM consumption_log cl
        LEFT JOIN assets a ON cl.asset_id = a.id
        WHERE cl.office_id = $selected_office $date_sql
        GROUP BY cl.asset_id
        ORDER BY total_consumed DESC
    ";
} else {
    // All offices: totals per office
    $sql_summary = "
        SELECT o.office_name AS label, SUM(cl.quantity_consumed) AS total_consumed
        FROM consumption_log cl
        JOIN offices o ON cl.office_id = o.id
        WHERE 1=1 $date_sql
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
    WHERE 1=1
    " . ($selected_office ? " AND cl.office_id = $selected_office" : "") . $date_sql . "
    ORDER BY cl.consumption_date DESC
";
$details_result = $conn->query($sql_details);

// --- For year dropdown: fetch distinct years ---
$years_result = $conn->query("SELECT DISTINCT YEAR(consumption_date) AS year FROM consumption_log ORDER BY year DESC");
$years = [];
while ($y = $years_result->fetch_assoc()) {
    $years[] = $y['year'];
}
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

            <div class="row g-3 mb-4 align-items-end">
                <!-- Filters Form -->
                <form method="get" class="col-md-9 row g-3" id="officeFilterForm">
                    <div class="col-md-3">
                        <label for="office" class="form-label">Filter by Office</label>
                        <select name="office" id="office" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Offices</option>
                            <?php while ($office = $offices_query->fetch_assoc()): ?>
                                <option value="<?= $office['id'] ?>" <?= ($selected_office == $office['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($office['office_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="year" class="form-label">Year</label>
                        <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Years</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= ($selected_year == $year ? 'selected' : '') ?>><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="month" class="form-label">Month</label>
                        <select name="month" id="month" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Months</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($selected_month == $m ? 'selected' : '') ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="day" class="form-label">Day</label>
                        <select name="day" id="day" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Days</option>
                            <?php for ($d = 1; $d <= 31; $d++): ?>
                                <option value="<?= $d ?>" <?= ($selected_day == $d ? 'selected' : '') ?>><?= $d ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>

                <!-- Generate Report Button (Outside Form) -->
                <div class="col-md-3 d-flex">
                    <form action="generate_report.php" method="post" class="w-100">
                        <input type="hidden" name="office" value="<?= $selected_office ?>">
                        <input type="hidden" name="year" value="<?= $selected_year ?>">
                        <input type="hidden" name="month" value="<?= $selected_month ?>">
                        <input type="hidden" name="day" value="<?= $selected_day ?>">
                        <button type="submit" class="btn btn-primary w-100 align-self-end">
                            <i class="bi bi-bar-chart-line"></i> Generate Report
                        </button>
                    </form>
                </div>

            </div>

            <!-- Chart -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><strong><?= $selected_office ? "Consumption for Selected Office" : "Consumption by Office (All)" ?></strong></div>
                <div class="card-body"><canvas id="consumptionChart" height="100"></canvas></div>
            </div>

            <!-- Detailed Table -->
            <div class="card shadow-sm">
                <div class="card-header"><strong>Detailed Consumption Log</strong></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="consumptionTable" class="table">
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
        const ctx = document.getElementById('consumptionChart').getContext('2d');
        new Chart(ctx, {
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
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        $(document).ready(function() {
            $('#consumptionTable').DataTable();
        });

        document.getElementById('generateReportBtn').addEventListener('click', function() {
            document.getElementById('officeFilterForm').submit();
        });
    </script>

</body>

</html>