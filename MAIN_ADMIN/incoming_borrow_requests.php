<?php
require_once '../connect.php';
session_start();

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['super_admin', 'admin', 'office_admin'])) {
    header("Location: ../index.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$office_id = $_SESSION['office_id'];

// Base query to fetch pending borrow requests
$sql = "
    SELECT 
        br.id, 
        u.fullname as borrower_name,
        a.asset_name,
        br.quantity,
        br.requested_at,
        o.office_name
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN assets a ON br.asset_id = a.id
    JOIN offices o ON a.office_id = o.id
    WHERE br.status = 'pending'
";

$params = [];
$types = '';

// If user is an office_admin, only show requests for their office's assets
if ($user_role === 'office_admin') {
    $sql .= " AND a.office_id = ?";
    $params[] = $office_id;
    $types .= 'i';
}

$sql .= " ORDER BY br.requested_at ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incoming Borrow Requests</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <div class="container mt-4">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h4><i class="bi bi-inbox"></i> Incoming Borrow Requests</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Request ID</th>
                                    <th>Borrower</th>
                                    <th>Asset</th>
                                    <th>Office</th>
                                    <th>Quantity</th>
                                    <th>Requested On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($requests->num_rows > 0): ?>
                                    <?php while ($row = $requests->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['borrower_name']) ?></td>
                                            <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                            <td><?= htmlspecialchars($row['office_name']) ?></td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td><?= date('F j, Y, g:i a', strtotime($row['requested_at'])) ?></td>
                                            <td>
                                                <a href="approve_request.php?request_id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No pending borrow requests.</td>
                                    </tr>
                                <?php endif; ?>
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
    <script>
        $(document).ready(function() {
            $('#requestsTable').DataTable({
                "order": [[ 0, "desc" ]]
            });
        });
    </script>
</body>
</html>
