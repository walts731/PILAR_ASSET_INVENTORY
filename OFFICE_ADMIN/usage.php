<?php
require_once '../connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// --- Get the user's office from session ---
$office_id = $_SESSION['office_id'] ?? 0;

// --- Date Filters ---
$selected_year   = isset($_GET['year']) ? intval($_GET['year']) : 0;
$selected_month  = isset($_GET['month']) ? intval($_GET['month']) : 0;
$selected_day    = isset($_GET['day']) ? intval($_GET['day']) : 0;

$date_conditions = [];
if ($selected_year)  $date_conditions[] = "YEAR(cl.consumption_date) = $selected_year";
if ($selected_month) $date_conditions[] = "MONTH(cl.consumption_date) = $selected_month";
if ($selected_day)   $date_conditions[] = "DAY(cl.consumption_date) = $selected_day";
$date_sql = $date_conditions ? " AND " . implode(" AND ", $date_conditions) : "";

// --- Summary Query (Chart Data) ---
$sql_summary = "
    SELECT a.description AS label, SUM(cl.quantity_consumed) AS total_consumed
    FROM consumption_log cl
    LEFT JOIN assets a ON cl.asset_id = a.id
    WHERE cl.office_id = $office_id $date_sql
    GROUP BY cl.asset_id
    ORDER BY total_consumed DESC
";
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
    WHERE cl.office_id = $office_id $date_sql
    ORDER BY cl.consumption_date DESC
";
$details_result = $conn->query($sql_details);

// --- Fetch distinct years for filter ---
$years_result = $conn->query("SELECT DISTINCT YEAR(consumption_date) AS year FROM consumption_log WHERE office_id = $office_id ORDER BY year DESC");
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
    <style>
        .page-header { background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%); border: 1px solid #e9ecef; border-radius: .75rem; }
        .page-header .title { font-weight: 600; }
        .toolbar .btn { transition: transform .08s ease-in; }
        .toolbar .btn:hover { transform: translateY(-1px); }
        .card-hover:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.06) !important; }
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
                    <i class="bi bi-activity text-primary fs-4"></i>
                </div>
                <div>
                    <div class="h4 mb-0 title">Usage Reports</div>
                    <div class="text-muted small">Your office's consumables usage</div>
                </div>
            </div>
            <div class="toolbar d-flex align-items-center gap-2">
                <button id="toggleDensityUsageOffice" class="btn btn-outline-secondary btn-sm rounded-pill" title="Toggle compact density">
                    <i class="bi bi-arrows-vertical me-1"></i> Density
                </button>
            </div>
        </div>

        <!-- Date Filters Only -->
        <div class="row g-3 mb-4 align-items-end">
            <form method="get" class="col-md-9 row g-3">
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

            <div class="col-md-3">
                <form action="generate_report.php" method="post" class="w-100">
                    <input type="hidden" name="year" value="<?= $selected_year ?>">
                    <input type="hidden" name="month" value="<?= $selected_month ?>">
                    <input type="hidden" name="day" value="<?= $selected_day ?>">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-bar-chart-line"></i> Generate Report
                    </button>
                </form>
            </div>
        </div>

        <!-- Chart -->
        <div class="card mb-4 shadow-sm card-hover">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-bar-chart-line me-1"></i> Asset Consumption for Your Office</strong>
            </div>
            <div class="card-body">
                <canvas id="consumptionChart" height="100"></canvas>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="card shadow-sm card-hover">
            <div class="card-header d-flex justify-content-between align-items-center"><strong><i class="bi bi-list-check me-1"></i> Detailed Consumption Log</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="consumptionTable" class="table table-sm table-striped table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Asset</th>
                            <th>Quantity</th>
                            <th>Dispensed By</th>
                            <th>Recipient</th>
                            <th>Date</th>
                            <th>Remarks</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $details_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>
                                <td><?= $row['quantity_consumed'] ?></td>
                                <td><?= htmlspecialchars($row['dispensed_by'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['recipient'] ?? '-') ?></td>
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
<script src="js/dashboard.js"></script>

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
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    $(document).ready(function() {
        // Initialize DataTable with refined defaults
        const dt = $('#consumptionTable').DataTable({
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

        // Density toggle for compact spacing
        $('#toggleDensityUsageOffice').on('click', function() {
            $('#consumptionTable').toggleClass('table-sm');
        });
    });
</script>

</body>
</html>
