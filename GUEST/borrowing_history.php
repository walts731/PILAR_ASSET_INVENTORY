<?php
session_start();
require_once "../connect.php";
require_once "../includes/classes/GuestBorrowing.php";

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Initialize GuestBorrowing
$guestBorrowing = new GuestBorrowing($conn);

// Get guest email from session (assuming it's set during guest login)
$guestEmail = $_SESSION['guest_email'] ?? '';

// Get the guest's borrowing history
$borrowingHistory = $guestBorrowing->getGuestBorrowingHistory($guestEmail);

// Check for success message from form submission
$successMessage = '';
if (isset($_SESSION['borrow_success'])) {
    $successMessage = $_SESSION['borrow_success']['message'] . ' Request #' . $_SESSION['borrow_success']['request_number'];
    unset($_SESSION['borrow_success']);
}

// Get counts for summary
$statusCounts = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'active' => 0,
    'completed' => 0,
    'overdue' => 0,
    'total' => 0
];

if (!empty($borrowingHistory)) {
    foreach ($borrowingHistory as $request) {
        $status = strtolower($request['status']);
        $statusCounts['total']++;
        
        if (isset($statusCounts[$status])) {
            $statusCounts[$status]++;
        }
        
        // Count active requests (approved but not yet returned)
        if (in_array($status, ['approved', 'in_progress', 'ready_for_pickup', 'in_transit'])) {
            $statusCounts['active']++;
        }
        
        // Count completed requests
        if (in_array($status, ['returned', 'completed'])) {
            $statusCounts['completed']++;
        }
        
        // Count overdue requests
        if ($status === 'overdue' || 
            ($status === 'approved' && !empty($request['expected_return_date']) && 
             strtotime($request['expected_return_date']) < time())) {
            $statusCounts['overdue']++;
        }
    }
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing History - <?= htmlspecialchars($system['system_title']) ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0b5ed7;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
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

        .history-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending { background: var(--warning-color); color: white; }
        .status-approved { background: var(--info-color); color: white; }
        .status-borrowed { background: var(--primary-color); color: white; }
        .status-returned { background: var(--success-color); color: white; }
        .status-overdue { background: var(--danger-color); color: white; }

        .timeline-item {
            position: relative;
            padding-left: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-color);
        }

        .timeline-item.completed::before {
            background: var(--success-color);
        }

        .timeline-item.pending::before {
            background: var(--warning-color);
        }

        .empty-state {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 3rem 2rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
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
                <a href="browse_assets.php" class="btn btn-outline-info me-2">
                    <i class="bi bi-grid-3x3-gap me-1"></i>Browse
                </a>
                <a href="guest_dashboard.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-house me-1"></i>Dashboard
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
                <i class="bi bi-clock-history me-2"></i>Borrowing History
            </h2>
            <p class="text-white-50">Track your asset borrowing requests and returns</p>
        </div>

        <!-- Borrowing History -->
        <?php if (empty($borrowing_history)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #6c757d; margin-bottom: 1rem;"></i>
                <h4>No Borrowing History</h4>
                <p class="text-muted mb-4">You haven't made any borrowing requests yet.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="scan_qr.php" class="btn btn-primary">
                        <i class="bi bi-qr-code-scan me-1"></i>Scan QR Code
                    </a>
                    <a href="browse_assets.php" class="btn btn-outline-primary">
                        <i class="bi bi-grid-3x3-gap me-1"></i>Browse Assets
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($borrowing_history as $item): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card history-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($item['asset_name']) ?></h5>
                                        <p class="text-muted mb-0">
                                            <small><i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['asset_tag']) ?></small>
                                        </p>
                                    </div>
                                    <span class="status-badge status-<?= $item['status'] ?>">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <strong>Purpose:</strong> <?= htmlspecialchars($item['purpose']) ?>
                                </div>

                                <!-- Timeline -->
                                <div class="timeline">
                                    <div class="timeline-item completed">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Request Submitted</strong></span>
                                            <span class="text-muted"><?= date('M j, Y', strtotime($item['request_date'])) ?></span>
                                        </div>
                                    </div>

                                    <?php if ($item['status'] === 'approved' || $item['status'] === 'borrowed' || $item['status'] === 'returned'): ?>
                                        <div class="timeline-item completed mt-2">
                                            <div class="d-flex justify-content-between">
                                                <span><strong>Request Approved</strong></span>
                                                <span class="text-muted">Approved</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($item['borrowed_date']): ?>
                                        <div class="timeline-item completed mt-2">
                                            <div class="d-flex justify-content-between">
                                                <span><strong>Asset Borrowed</strong></span>
                                                <span class="text-muted"><?= date('M j, Y', strtotime($item['borrowed_date'])) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($item['return_date'] && !$item['actual_return']): ?>
                                        <div class="timeline-item pending mt-2">
                                            <div class="d-flex justify-content-between">
                                                <span><strong>Expected Return</strong></span>
                                                <span class="text-muted"><?= date('M j, Y', strtotime($item['return_date'])) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($item['actual_return']): ?>
                                        <div class="timeline-item completed mt-2">
                                            <div class="d-flex justify-content-between">
                                                <span><strong>Asset Returned</strong></span>
                                                <span class="text-muted"><?= date('M j, Y', strtotime($item['actual_return'])) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-3 d-flex gap-2">
                                    <?php if ($item['status'] === 'pending'): ?>
                                        <button class="btn btn-outline-danger btn-sm" onclick="cancelRequest(<?= $item['id'] ?>)">
                                            <i class="bi bi-x-circle me-1"></i>Cancel Request
                                        </button>
                                    <?php elseif ($item['status'] === 'approved'): ?>
                                        <span class="text-success small">
                                            <i class="bi bi-check-circle me-1"></i>Ready for pickup
                                        </span>
                                    <?php elseif ($item['status'] === 'borrowed' && !$item['actual_return']): ?>
                                        <span class="text-primary small">
                                            <i class="bi bi-clock me-1"></i>Currently borrowed
                                        </span>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-primary btn-sm ms-auto" onclick="viewDetails(<?= $item['id'] ?>)">
                                        <i class="bi bi-eye me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary Stats -->
            <div class="row mt-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-warning"><?= count(array_filter($borrowing_history, fn($item) => $item['status'] === 'pending')) ?></h4>
                            <p class="mb-0 small text-muted">Pending Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary"><?= count(array_filter($borrowing_history, fn($item) => $item['status'] === 'borrowed')) ?></h4>
                            <p class="mb-0 small text-muted">Currently Borrowed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success"><?= count(array_filter($borrowing_history, fn($item) => $item['status'] === 'returned')) ?></h4>
                            <p class="mb-0 small text-muted">Returned</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info"><?= count($borrowing_history) ?></h4>
                            <p class="mb-0 small text-muted">Total Requests</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function cancelRequest(requestId) {
            if (confirm('Are you sure you want to cancel this borrowing request?')) {
                // Here you would send an AJAX request to cancel the request
                alert('Request cancelled successfully.');
                location.reload();
            }
        }

        function viewDetails(requestId) {
            // Here you would show detailed information about the request
            alert('Detailed view would be implemented here.');
        }
    </script>
</body>
</html>
