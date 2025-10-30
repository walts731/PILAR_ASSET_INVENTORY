<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($employee_id <= 0) {
    header('Location: employees.php');
    exit();
}

$employeeSql = $conn->prepare(
    "SELECT e.employee_id, e.employee_no, e.name, e.email, e.status, e.date_added, e.image,
            e.office_id, o.office_name,
            CASE 
              WHEN EXISTS (
                SELECT 1
                FROM mr_details m
                JOIN assets a2 ON a2.id = m.asset_id
                WHERE m.person_accountable = e.name
                  AND (a2.status IS NULL OR LOWER(a2.status) <> 'unserviceable')
              ) THEN 'uncleared'
              ELSE 'cleared'
            END AS clearance_status
     FROM employees e
     LEFT JOIN offices o ON e.office_id = o.id
     WHERE e.employee_id = ?
     LIMIT 1"
);

if (!$employeeSql) {
    header('Location: employees.php');
    exit();
}

$employeeSql->bind_param('i', $employee_id);
$employeeSql->execute();
$employeeResult = $employeeSql->get_result();

if ($employeeResult->num_rows === 0) {
    $employeeSql->close();
    header('Location: employees.php');
    exit();
}

$employee = $employeeResult->fetch_assoc();
$employeeSql->close();

$assets = [];
$assetTotals = [
    'count' => 0,
    'serviceable' => 0,
    'unserviceable' => 0,
    'total_value' => 0.0,
    'latest_update' => null,
];

$assetsSql = $conn->prepare(
    "SELECT a.id, a.description, a.status, a.serial_no, a.property_no, a.inventory_tag,
            a.value, a.quantity, a.unit, a.acquisition_date, a.last_updated,
            c.category_name, o.office_name
     FROM assets a
     LEFT JOIN categories c ON a.category = c.id
     LEFT JOIN offices o ON a.office_id = o.id
     WHERE a.employee_id = ? AND a.type = 'asset'
     ORDER BY a.description ASC"
);

if ($assetsSql) {
    $assetsSql->bind_param('i', $employee_id);
    $assetsSql->execute();
    $assetsResult = $assetsSql->get_result();

    while ($row = $assetsResult->fetch_assoc()) {
        $assets[] = $row;
        $assetTotals['count']++;

        $status = strtolower((string)($row['status'] ?? ''));
        if ($status === 'serviceable') {
            $assetTotals['serviceable']++;
        } elseif ($status === 'unserviceable') {
            $assetTotals['unserviceable']++;
        }

        $qty = isset($row['quantity']) ? (float)$row['quantity'] : 1.0;
        if ($qty <= 0) {
            $qty = 1.0;
        }
        $value = isset($row['value']) ? (float)$row['value'] : 0.0;
        $assetTotals['total_value'] += $value * $qty;

        if (!empty($row['last_updated'])) {
            $currentTs = strtotime($row['last_updated']);
            if ($currentTs && ($assetTotals['latest_update'] === null || $currentTs > $assetTotals['latest_update'])) {
                $assetTotals['latest_update'] = $currentTs;
            }
        }
    }
    $assetsSql->close();
}

function formatDateDisplay(?string $date): string
{
    if (empty($date)) {
        return 'N/A';
    }
    $ts = strtotime($date);
    return $ts ? date('F j, Y', $ts) : htmlspecialchars($date);
}

$employeeImage = '';
if (!empty($employee['image'])) {
    $imagePath = '../img/' . ltrim($employee['image'], '/');
    if (file_exists(__DIR__ . '/../img/' . ltrim($employee['image'], '/'))) {
        $employeeImage = $imagePath;
    }
}

if (empty($employeeImage)) {
    $employeeImage = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/person-circle.svg';
}

$latestUpdateDisplay = $assetTotals['latest_update'] ? date('F j, Y g:i A', $assetTotals['latest_update']) : 'N/A';
$totalValueDisplay = $assetTotals['total_value'] > 0 ? number_format($assetTotals['total_value'], 2) : '0.00';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details - <?= htmlspecialchars($employee['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        body {
            background-color: #f8f9fb;
            font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        .employee-header {
            background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%);
            color: #fff;
            border-radius: 20px;
            padding: 2rem 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(11, 94, 215, 0.25);
        }

        .employee-header::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            top: -60px;
            right: -40px;
        }

        .employee-header .badge {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
        }

        .employee-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.7);
            object-fit: cover;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.2);
        }

        .metric-card {
            border-radius: 16px;
            border: 0;
            box-shadow: 0 15px 35px rgba(8, 66, 152, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 45px rgba(8, 66, 152, 0.12);
        }

        .metric-card .metric-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .metric-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(11, 94, 215, 0.12);
            color: #0b5ed7;
        }

        .table thead th {
            border-top: 0;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05rem;
        }

        .status-pill {
            border-radius: 30px;
            padding: 0.35rem 0.85rem;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <div class="container-fluid mt-4">

            <div class="employee-header mb-4">
                <div class="row align-items-center g-4">
                    <div class="col-md-8">
                        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($employeeImage) ?>" alt="Employee Photo" class="employee-avatar">
                            </div>
                            <div>
                                <h1 class="h2 mb-1"><?= htmlspecialchars($employee['name']) ?></h1>
                                <p class="mb-3 opacity-75">
                                    <i class="bi bi-building me-2"></i><?= htmlspecialchars($employee['office_name'] ?? 'No office assigned') ?>
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge">
                                        <i class="bi bi-briefcase me-1"></i><?= ucfirst($employee['status']) ?>
                                    </span>
                                    <span class="badge <?= $employee['clearance_status'] === 'cleared' ? 'bg-success text-white' : 'bg-warning text-dark' ?>">
                                        <i class="bi bi-shield-check me-1"></i><?= ucfirst($employee['clearance_status']) ?> Clearance
                                    </span>
                                    <?php if (!empty($employee['email'])): ?>
                                        <span class="badge">
                                            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($employee['email']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="text-white-50 small">Date Added</div>
                        <div class="fs-5 fw-semibold mb-3"><?= formatDateDisplay($employee['date_added']) ?></div>
                        <div class="text-white-50 small">Latest Asset Update</div>
                        <div class="fs-6 fw-semibold"><?= htmlspecialchars($latestUpdateDisplay) ?></div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon"><i class="bi bi-box-seam"></i></div>
                                <span class="badge bg-primary-subtle text-primary">Total</span>
                            </div>
                            <div class="metric-value"><?= $assetTotals['count'] ?></div>
                            <div class="text-muted">Assets Assigned</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon"><i class="bi bi-check-circle"></i></div>
                                <span class="badge bg-success-subtle text-success">Healthy</span>
                            </div>
                            <div class="metric-value"><?= $assetTotals['serviceable'] ?></div>
                            <div class="text-muted">Serviceable Assets</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon"><i class="bi bi-exclamation-triangle"></i></div>
                                <span class="badge bg-danger-subtle text-danger">Attention</span>
                            </div>
                            <div class="metric-value"><?= $assetTotals['unserviceable'] ?></div>
                            <div class="text-muted">Unserviceable Assets</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="metric-icon"><i class="bi bi-currency-dollar"></i></div>
                                <span class="badge bg-indigo-100 text-dark">Value</span>
                            </div>
                            <div class="metric-value">₱<?= $totalValueDisplay ?></div>
                            <div class="text-muted">Total Asset Value</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Employee Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Employee Number</div>
                                <div class="fw-semibold fs-5 mb-2"><?= htmlspecialchars($employee['employee_no']) ?></div>
                                <div class="text-muted small">Office</div>
                                <div class="fw-semibold"><?= htmlspecialchars($employee['office_name'] ?? 'No office assigned') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Employment Status</div>
                                <div class="fw-semibold mb-2"><?= ucfirst($employee['status']) ?></div>
                                <div class="text-muted small">Clearance Status</div>
                                <div class="fw-semibold"><?= ucfirst($employee['clearance_status']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">Email Address</div>
                                <?php if (!empty($employee['email'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($employee['email']) ?>" class="fw-semibold d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-envelope"></i><?= htmlspecialchars($employee['email']) ?>
                                    </a>
                                <?php else: ?>
                                    <div class="fw-semibold">No email on record</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                
                                <div class="text-muted small mt-3">Date Added</div>
                                <div class="fw-semibold"><?= formatDateDisplay($employee['date_added']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-5">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Assigned Assets</h5>
                    <span class="badge bg-primary-subtle text-primary"><?= $assetTotals['count'] ?> total</span>
                </div>
                <div class="card-body">
                    <?php if (empty($assets)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inboxes display-5 mb-3"></i>
                            <p class="mb-0">This employee currently has no assigned assets.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Property No</th>
                                        <th>Inventory Tag</th>
                                        <th>Serial</th>
                                        <th>Qty</th>
                                        <th>Unit Cost</th>
                                        <th>Total Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assets as $asset): ?>
                                        <?php
                                            $qty = isset($asset['quantity']) ? (float)$asset['quantity'] : 1.0;
                                            if ($qty <= 0) {
                                                $qty = 1.0;
                                            }
                                            $value = isset($asset['value']) ? (float)$asset['value'] : 0.0;
                                            $rowTotal = $value * $qty;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($asset['description'] ?? 'Unnamed Asset') ?></div>
                                                <div class="small text-muted">Acquired <?= formatDateDisplay($asset['acquisition_date'] ?? null) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($asset['category_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                    $status = strtolower((string)($asset['status'] ?? '')); 
                                                    $badgeClass = 'bg-secondary';
                                                    if ($status === 'serviceable') {
                                                        $badgeClass = 'bg-success';
                                                    } elseif ($status === 'unserviceable') {
                                                        $badgeClass = 'bg-danger';
                                                    }
                                                ?>
                                                <span class="badge <?= $badgeClass ?> status-pill"><?= ucfirst($status ?: 'Unknown') ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($asset['property_no'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($asset['inventory_tag'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($asset['serial_no'] ?? '—') ?></td>
                                            <td><?= $qty ?></td>
                                            <td>₱<?= number_format($value, 2) ?></td>
                                            <td>₱<?= number_format($rowTotal, 2) ?></td>
                                            <td>
                                                <a href="view_asset_details.php?id=<?= (int)$asset['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
