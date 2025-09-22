<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}

// Fetch user's full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// Pagination and filtering
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$module_filter = isset($_GET['module']) ? $_GET['module'] : '';
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(username LIKE ? OR details LIKE ? OR module LIKE ? OR action LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $param_types .= 'ssss';
}

if (!empty($module_filter)) {
    $where_conditions[] = "module = ?";
    $params[] = $module_filter;
    $param_types .= 's';
}

if (!empty($action_filter)) {
    $where_conditions[] = "action = ?";
    $params[] = $action_filter;
    $param_types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM audit_logs {$where_clause}";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

// Fetch audit logs with pagination
$logs_query = "
    SELECT 
        id,
        user_id,
        username,
        action,
        module,
        details,
        affected_table,
        affected_id,
        ip_address,
        created_at
    FROM audit_logs 
    {$where_clause}
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
";

$logs_stmt = $conn->prepare($logs_query);
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

if (!empty($params)) {
    $logs_stmt->bind_param($param_types, ...$params);
}
$logs_stmt->execute();
$logs_result = $logs_stmt->get_result();

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_today,
        COUNT(DISTINCT user_id) as active_users,
        SUM(CASE WHEN action = 'LOGIN_FAILED' THEN 1 ELSE 0 END) as failed_attempts,
        SUM(CASE WHEN action IN ('ERROR', 'WARNING') THEN 1 ELSE 0 END) as alerts
    FROM audit_logs 
    WHERE DATE(created_at) = CURDATE()
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get unique modules and actions for filters
$modules_query = "SELECT DISTINCT module FROM audit_logs ORDER BY module";
$modules_result = $conn->query($modules_query);

$actions_query = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
$actions_result = $conn->query($actions_query);

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, ['Date & Time', 'User', 'Action', 'Module', 'Details', 'IP Address']);
    
    // Reset result pointer and fetch all logs for export
    $export_query = "
        SELECT 
            created_at,
            username,
            action,
            module,
            details,
            ip_address
        FROM audit_logs 
        {$where_clause}
        ORDER BY created_at DESC
    ";
    
    $export_stmt = $conn->prepare($export_query);
    if (!empty($params)) {
        // Remove limit and offset params for export
        $export_params = array_slice($params, 0, -2);
        $export_param_types = substr($param_types, 0, -2);
        if (!empty($export_params)) {
            $export_stmt->bind_param($export_param_types, ...$export_params);
        }
    }
    $export_stmt->execute();
    $export_result = $export_stmt->get_result();
    
    while ($row = $export_result->fetch_assoc()) {
        fputcsv($output, [
            date('M d, Y H:i:s', strtotime($row['created_at'])),
            $row['username'],
            $row['action'],
            $row['module'],
            $row['details'],
            $row['ip_address']
        ]);
    }
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Audit Trail - <?= htmlspecialchars($system['system_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
    <?php include 'includes/sidebar.php' ?>

    <div class="main">
        <?php include 'includes/topbar.php' ?>

        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-journal-text"></i> Audit Trail
                    </h1>
                    <p class="text-muted mb-0">System activity logs and user actions (<?= number_format($total_records) ?> total records)</p>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search logs...">
                        </div>
                        <div class="col-md-2">
                            <label for="module" class="form-label">Module</label>
                            <select class="form-select" id="module" name="module">
                                <option value="">All Modules</option>
                                <?php while ($module = $modules_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($module['module']) ?>" <?= $module_filter === $module['module'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($module['module']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="action" class="form-label">Action</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">All Actions</option>
                                <?php while ($action = $actions_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($action['action']) ?>" <?= $action_filter === $action['action'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($action['action']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Logs Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> System Activity Logs
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-outline-success btn-sm rounded-pill">
                            <i class="bi bi-download"></i> Export CSV
                        </a>
                        <a href="logs.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="logsTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($logs_result->num_rows > 0): ?>
                                    <?php while ($log = $logs_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-person-circle text-primary me-2"></i>
                                                    <span><?= htmlspecialchars($log['username']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_class = 'bg-secondary';
                                                switch (strtoupper($log['action'])) {
                                                    case 'CREATE':
                                                        $badge_class = 'bg-success';
                                                        break;
                                                    case 'UPDATE':
                                                        $badge_class = 'bg-warning';
                                                        break;
                                                    case 'DELETE':
                                                        $badge_class = 'bg-danger';
                                                        break;
                                                    case 'LOGIN':
                                                        $badge_class = 'bg-info';
                                                        break;
                                                    case 'LOGOUT':
                                                        $badge_class = 'bg-dark';
                                                        break;
                                                    case 'GENERATE':
                                                    case 'PRINT':
                                                        $badge_class = 'bg-primary';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $badge_class ?> rounded-pill">
                                                    <?= htmlspecialchars($log['action']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?= htmlspecialchars($log['module']) ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted" title="<?= htmlspecialchars($log['details']) ?>">
                                                    <?= htmlspecialchars(strlen($log['details']) > 50 ? substr($log['details'], 0, 50) . '...' : $log['details']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($log['ip_address']) ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                <p class="mb-0">No audit logs found</p>
                                                <small>Try adjusting your search criteria or check if the audit_logs table exists.</small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_records > $limit): ?>
                        <nav aria-label="Logs pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php
                                $total_pages = ceil($total_records / $limit);
                                $query_params = $_GET;
                                
                                // Previous button
                                if ($page > 1):
                                    $query_params['page'] = $page - 1;
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query($query_params) ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                // Page numbers
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                    $query_params['page'] = $i;
                                ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query($query_params) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php
                                // Next button
                                if ($page < $total_pages):
                                    $query_params['page'] = $page + 1;
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query($query_params) ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Actions Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_today']) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-activity text-primary" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Active Users Today
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['active_users']) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Failed Attempts
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['failed_attempts']) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-shield-exclamation text-info" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        System Alerts
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['alerts']) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables only if we have data
            if ($('#logsTable tbody tr').length > 0 && !$('#logsTable tbody tr td[colspan="6"]').length) {
                $('#logsTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']], // Sort by date/time descending
                    paging: false, // Disable DataTables pagination since we use server-side pagination
                    searching: false, // Disable DataTables search since we use server-side search
                    info: false, // Disable info since we show custom pagination info
                    language: {
                        emptyTable: "No log entries available"
                    },
                    columnDefs: [
                        { orderable: false, targets: [4] }, // Disable sorting on Details column
                        { width: "15%", targets: 0 }, // Date & Time
                        { width: "15%", targets: 1 }, // User
                        { width: "10%", targets: 2 }, // Action
                        { width: "12%", targets: 3 }, // Module
                        { width: "35%", targets: 4 }, // Details
                        { width: "13%", targets: 5 }  // IP Address
                    ]
                });
            }

            // Handle CSV export
            $('a[href*="export=csv"]').on('click', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                window.open(url, '_blank');
            });

            // Auto-submit form on filter change
            $('#module, #action').on('change', function() {
                $(this).closest('form').submit();
            });

            // Clear filters functionality
            if ($('.btn-clear-filters').length === 0) {
                $('.card-body form').append(`
                    <div class="col-12 mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-clear-filters">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </button>
                    </div>
                `);
            }

            $(document).on('click', '.btn-clear-filters', function() {
                window.location.href = 'logs.php';
            });
        });
    </script>

    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        .text-xs {
            font-size: 0.7rem;
        }
    </style>
</body>

</html>
