<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'];
$pageTitle = 'Inter-Department Borrowing Requests - PILAR Asset Inventory';

// Set active page for sidebar highlighting
$sidebarActive = 'inter_dept_borrow_requests';

// Include header with dark mode support (includes topbar and sidebar)
require_once '../includes/header.php';

// Handle request actions (approve/reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['request_id'])) {
        $action = $_POST['action'];
        $request_id = intval($_POST['request_id']);
        $comments = trim($_POST['comments'] ?? '');
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get request details
            $request_sql = "
                SELECT br.*, a.asset_name, 
                       so.office_name as source_office_name, 
                       do.office_name as dest_office_name,
                       u.fullname as requester_name
                FROM borrow_requests br
                JOIN assets a ON br.asset_id = a.id
                JOIN offices so ON br.source_office_id = so.id
                JOIN offices do ON br.office_id = do.id
                JOIN users u ON br.requested_by_user_id = u.id
                WHERE br.id = ? AND br.is_inter_department = 1
            ";
            
            $stmt = $conn->prepare($request_sql);
            $stmt->bind_param('i', $request_id);
            $stmt->execute();
            $request = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$request) {
                throw new Exception('Invalid borrow request.');
            }
            
            // Check if user has permission to approve/reject
            $can_approve = false;
            $approval_type = '';
            
            // Check if user is the head of the destination office
            $check_office_head = $conn->prepare("SELECT id FROM offices WHERE id = ? AND head_user_id = ?");
            $check_office_head->bind_param('ii', $office_id, $user_id);
            $check_office_head->execute();
            $is_office_head = $check_office_head->get_result()->num_rows > 0;
            $check_office_head->close();
            
            if ($is_office_head) {
                $can_approve = true;
                $approval_type = 'office_head';
            } else {
                // Check if user is the head of the source office
                $check_source_office = $conn->prepare("SELECT id FROM offices WHERE id = ? AND head_user_id = ?");
                $check_source_office->bind_param('ii', $request['source_office_id'], $user_id);
                $check_source_office->execute();
                $is_source_office_head = $check_source_office->get_result()->num_rows > 0;
                $check_source_office->close();
                
                if ($is_source_office_head) {
                    $can_approve = true;
                    $approval_type = 'source_office';
                }
            }
            
            if (!$can_approve) {
                throw new Exception('You do not have permission to approve/reject this request.');
            }
            
            // Update approval record
            $update_approval_sql = "
                UPDATE inter_department_approvals 
                SET status = ?, 
                    comments = ?,
                    updated_at = NOW()
                WHERE request_id = ? 
                AND approval_type = ?
                AND status = 'pending'
            ";
            
            $approval_status = ($action === 'approve') ? 'approved' : 'rejected';
            $update_approval = $conn->prepare($update_approval_sql);
            $update_approval->bind_param('ssis', $approval_status, $comments, $request_id, $approval_type);
            
            if (!$update_approval->execute()) {
                throw new Exception('Failed to update approval status: ' . $update_approval->error);
            }
            $update_approval->close();
            
            // Check if all approvals are done
            $check_approvals_sql = "
                SELECT 
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0) as rejected_count,
                    COUNT(*) as total_count
                FROM inter_department_approvals 
                WHERE request_id = ?
            ";
            
            $check_approvals = $conn->prepare($check_approvals_sql);
            $check_approvals->bind_param('i', $request_id);
            $check_approvals->execute();
            $approval_status = $check_approvals->get_result()->fetch_assoc();
            $check_approvals->close();
            
            $new_status = null;
            $notification_message = '';
            
            if ($approval_status['rejected_count'] > 0) {
                // If any approval is rejected, the request is rejected
                $new_status = 'rejected';
                $notification_message = "Your inter-department borrow request for {$request['asset_name']} has been rejected.";
            } elseif ($approval_status['approved_count'] == $approval_status['total_count']) {
                // If all approvals are done, update the request status
                $new_status = 'approved';
                $notification_message = "Your inter-department borrow request for {$request['asset_name']} has been approved.";
                
                // Update asset quantity (move from source to destination office)
                $update_asset_sql = "
                    UPDATE assets 
                    SET quantity = quantity - ?,
                        office_id = ?,
                        updated_at = NOW()
                    WHERE id = ? AND office_id = ? AND quantity >= ?
                ";
                
                $update_asset = $conn->prepare($update_asset_sql);
                $update_asset->bind_param('iiiii', 
                    $request['quantity'], 
                    $request['office_id'],
                    $request['asset_id'],
                    $request['source_office_id'],
                    $request['quantity']
                );
                
                if (!$update_asset->execute()) {
                    throw new Exception('Failed to update asset quantity: ' . $update_asset->error);
                }
                
                if ($update_asset->affected_rows === 0) {
                    throw new Exception('Insufficient quantity available for the asset.');
                }
                
                $update_asset->close();
            }
            
            // Update request status if needed
            if ($new_status) {
                $update_request_sql = "
                    UPDATE borrow_requests 
                    SET status = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ";
                
                $update_request = $conn->prepare($update_request_sql);
                $update_request->bind_param('si', $new_status, $request_id);
                
                if (!$update_request->execute()) {
                    throw new Exception('Failed to update request status: ' . $update_request->error);
                }
                $update_request->close();
                
                // Create notification for requester
                if (!empty($notification_message)) {
                    $notification_sql = "
                        INSERT INTO notifications (
                            user_id, title, message, type, related_id, related_type, is_read, created_at
                        ) VALUES (?, 'Borrow Request ' || ?, ?, 'borrow_request', ?, 'inter_dept_borrow', 0, NOW())
                    ";
                    
                    $notification_title = ucfirst($new_status);
                    $notification_stmt = $conn->prepare($notification_sql);
                    $notification_stmt->bind_param(
                        'isssi',
                        $request['requested_by_user_id'],
                        $notification_title,
                        $notification_message,
                        $request_id
                    );
                    
                    if (!$notification_stmt->execute()) {
                        error_log("Failed to create notification: " . $notification_stmt->error);
                        // Don't fail the whole process if notification fails
                    }
                    $notification_stmt->close();
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success_message'] = "Request has been " . $approval_status . " successfully.";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error_message'] = 'Error processing request: ' . $e->getMessage();
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get requests where the current user needs to take action
$pending_approval_requests = [];
$my_requests = [];

// Get requests where user is the office head of destination office
$office_head_sql = "
    SELECT br.*, a.asset_name, 
           so.office_name as source_office_name, 
           do.office_name as dest_office_name,
           u.fullname as requester_name,
           ia.status as office_head_approval,
           isa.status as source_office_approval
    FROM borrow_requests br
    JOIN assets a ON br.asset_id = a.id
    JOIN offices so ON br.source_office_id = so.id
    JOIN offices do ON br.office_id = do.id
    JOIN users u ON br.requested_by_user_id = u.id
    LEFT JOIN inter_department_approvals ia ON br.id = ia.request_id AND ia.approval_type = 'office_head'
    LEFT JOIN inter_department_approvals isa ON br.id = isa.request_id AND isa.approval_type = 'source_office'
    WHERE br.is_inter_department = 1
    AND br.status = 'pending_approval'
    AND (
        (do.head_user_id = ? AND (ia.status IS NULL OR ia.status = 'pending')) OR
        (so.head_user_id = ? AND (isa.status IS NULL OR isa.status = 'pending'))
    )
    ORDER BY br.requested_at DESC
";

$stmt = $conn->prepare($office_head_sql);
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$pending_approval_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get requests made by the current user
$my_requests_sql = "
    SELECT br.*, a.asset_name, 
           so.office_name as source_office_name, 
           do.office_name as dest_office_name,
           ia.status as office_head_approval,
           isa.status as source_office_approval,
           br.purpose as purpose
    FROM borrow_requests br
    JOIN assets a ON br.asset_id = a.id
    JOIN offices so ON br.source_office_id = so.id
    JOIN offices do ON br.office_id = do.id
    LEFT JOIN inter_department_approvals ia ON br.id = ia.request_id AND ia.approval_type = 'office_head'
    LEFT JOIN inter_department_approvals isa ON br.id = isa.request_id AND isa.approval_type = 'source_office'
    WHERE br.is_inter_department = 1
    AND br.requested_by_user_id = ?
    ORDER BY br.requested_at DESC
    LIMIT 50
";

$stmt = $conn->prepare($my_requests_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$my_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
        /* Card styles */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        
        .request-card {
            transition: all 0.2s ease-in-out;
            margin-bottom: 1rem;
        }
        
        .request-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 500;
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
            margin: 1.5rem 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
            padding-left: 1.5rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-badge {
            position: absolute;
            left: -0.5rem;
            top: 0.25rem;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #0d6efd;
            z-index: 1;
        }
        .timeline-panel {
            background: #fff;
            border-radius: 0.375rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .timeline-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .action-buttons .btn {
            min-width: 100px;
        }
        @media (max-width: 768px) {
            .action-buttons .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            .action-buttons .btn:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body class="<?php echo $darkModeClass; ?>">
    <?php include 'includes/sidebar.php' ?>

    <div class="main">
        <?php include 'includes/topbar.php' ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Inter-Department Borrow Requests</h1>

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
            <a href="inter_department_borrow.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Request
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Pending Approval Section -->
    <?php if (!empty($pending_approval_requests)): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Pending Your Approval</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request ID</th>
                                <th>Asset</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Requested By</th>
                                <th>Quantity</th>
                                <th>Requested On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_approval_requests as $request): 
                                $is_office_head = ($request['dest_office_name'] === $_SESSION['office_name']);
                                $approval_status = $is_office_head ? $request['office_head_approval'] : $request['source_office_approval'];
                                $can_approve = ($approval_status === 'pending' || $approval_status === null);
                            ?>
                                <tr>
                                    <td>#<?= $request['id'] ?></td>
                                    <td><?= htmlspecialchars($request['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($request['source_office_name']) ?></td>
                                    <td><?= htmlspecialchars($request['dest_office_name']) ?></td>
                                    <td><?= htmlspecialchars($request['requester_name']) ?></td>
                                    <td><?= $request['quantity'] ?></td>
                                    <td><?= date('M j, Y', strtotime($request['requested_at'])) ?></td>
                                    <td>
                                        <?php if ($can_approve): ?>
                                            <button type="button" class="btn btn-sm btn-success me-1 approve-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#approvalModal"
                                                    data-request-id="<?= $request['id'] ?>"
                                                    data-action="approve">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger reject-btn"
                                                    data-bs-toggle="modal" data-bs-target="#approvalModal"
                                                    data-request-id="<?= $request['id'] ?>"
                                                    data-action="reject">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <?= ucfirst($approval_status) ?>
                                            </span>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-info view-details" 
                                                data-bs-toggle="modal" data-bs-target="#detailsModal"
                                                data-request='<?= json_encode($request) ?>'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- My Requests Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>My Inter-Department Requests</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($my_requests)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request ID</th>
                                <th>Asset</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Requested On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_requests as $request): 
                                $status_class = '';
                                switch ($request['status']) {
                                    case 'approved':
                                        $status_class = 'success';
                                        break;
                                    case 'rejected':
                                        $status_class = 'danger';
                                        break;
                                    case 'pending_approval':
                                        $status_class = 'warning';
                                        break;
                                    default:
                                        $status_class = 'secondary';
                                }
                                
                                $office_head_approval = $request['office_head_approval'] ?? 'pending';
                                $source_office_approval = $request['source_office_approval'] ?? 'pending';
                            ?>
                                <tr>
                                    <td>#<?= $request['id'] ?></td>
                                    <td><?= htmlspecialchars($request['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($request['source_office_name']) ?></td>
                                    <td><?= htmlspecialchars($request['dest_office_name']) ?></td>
                                    <td><?= $request['quantity'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $status_class ?>">
                                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($request['requested_at'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info view-details" 
                                                data-bs-toggle="modal" data-bs-target="#detailsModal"
                                                data-request='<?= json_encode($request) ?>'>
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                        
                                        <?php if ($request['status'] === 'pending_approval'): ?>
                                            <button type="button" class="btn btn-sm btn-danger cancel-request"
                                                    data-request-id="<?= $request['id'] ?>">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <!-- Approval Status Row -->
                                <tr class="bg-light">
                                    <td colspan="8" class="p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    <strong>Office Head Approval:</strong> 
                                                    <span class="badge bg-<?= $office_head_approval === 'approved' ? 'success' : ($office_head_approval === 'rejected' ? 'danger' : 'warning') ?>">
                                                        <?= ucfirst($office_head_approval) ?>
                                                    </span>
                                                </small>
                                            </div>
                                            <div>
                                                <small class="text-muted">
                                                    <strong>Source Office Approval:</strong> 
                                                    <span class="badge bg-<?= $source_office_approval === 'approved' ? 'success' : ($source_office_approval === 'rejected' ? 'danger' : 'warning') ?>">
                                                        <?= ucfirst($source_office_approval) ?>
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5>No inter-department requests found</h5>
                    <p class="text-muted">You haven't made any inter-department borrow requests yet.</p>
                    <a href="inter_department_borrow.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                </div>
            <?php endif; ?>
        </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="approvalForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Approve/Reject Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="approvalRequestId">
                    <input type="hidden" name="action" id="approvalAction">
                    
                    <div class="mb-3">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                        <div class="form-text">Provide additional comments (optional)</div>
                    </div>
                    
                    <div id="approvalMessage" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="approvalSubmitBtn">
                        <i class="fas fa-check me-1"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestDetails">
                <!-- Details will be loaded via JavaScript -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

<script>
$(document).ready(function() {
    // Handle approval/reject buttons
    $('.approve-btn, .reject-btn').on('click', function() {
        const requestId = $(this).data('request-id');
        const action = $(this).data('action');
        
        $('#approvalRequestId').val(requestId);
        $('#approvalAction').val(action);
        
        // Update UI based on action
        if (action === 'approve') {
            $('#approvalSubmitBtn').removeClass('btn-danger').addClass('btn-success');
            $('#approvalSubmitBtn').html('<i class="fas fa-check me-1"></i> Approve');
        } else {
            $('#approvalSubmitBtn').removeClass('btn-success').addClass('btn-danger');
            $('#approvalSubmitBtn').html('<i class="fas fa-times me-1"></i> Reject');
        }
        
        // Clear previous messages
        $('#approvalMessage').addClass('d-none').removeClass('alert-success alert-danger').text('');
    });
    
    // Handle form submission
    $('#approvalForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitBtn = $('#approvalSubmitBtn');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        // Submit form via AJAX
        $.ajax({
            url: 'inter_dept_borrow_requests.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message and reload the page after a short delay
                    $('#approvalMessage')
                        .removeClass('d-none alert-danger')
                        .addClass('alert-success')
                        .html('<i class="fas fa-check-circle me-2"></i>' + response.message);
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    $('#approvalMessage')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .html('<i class="fas fa-exclamation-circle me-2"></i>' + (response.message || 'An error occurred.'));
                    
                    // Re-enable the button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                // Show error message
                $('#approvalMessage')
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .html('<i class="fas fa-exclamation-circle me-2"></i>An error occurred. Please try again.');
                
                // Re-enable the button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Handle view details button
    $('.view-details').on('click', function() {
        const request = $(this).data('request');
        console.log('Request data:', request);
        let detailsHtml = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Request Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Request ID:</th>
                            <td>#${request.id}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-${request.status === 'approved' ? 'success' : (request.status === 'rejected' ? 'danger' : 'warning')}">
                                    ${request.status.replace('_', ' ').charAt(0).toUpperCase() + request.status.slice(1).replace('_', ' ')}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Requested On:</th>
                            <td>${new Date(request.requested_at).toLocaleString()}</td>
                        </tr>
                        <tr>
                            <th>Expected Return:</th>
                            <td>${request.requested_return_date ? new Date(request.requested_return_date).toLocaleDateString() : 'Not specified'}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Asset Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Asset:</th>
                            <td>${escapeHtml(request.asset_name)}</td>
                        </tr>
                        <tr>
                            <th>Quantity:</th>
                            <td>${request.quantity}</td>
                        </tr>
                        <tr>
                            <th>Source Office:</th>
                            <td>${escapeHtml(request.source_office_name)}</td>
                        </tr>
                        <tr>
                            <th>Destination Office:</th>
                            <td>${escapeHtml(request.dest_office_name)}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h6>Purpose</h6>
                    <div class="border rounded p-3 mb-3">
                        ${request.purpose ? escapeHtml(request.purpose) : '<em class="text-muted">No purpose specified</em>'}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6>Approval Status</h6>
                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Office Head:</span>
                            <span class="badge bg-${request.office_head_approval === 'approved' ? 'success' : (request.office_head_approval === 'rejected' ? 'danger' : 'warning')}">
                                ${(request.office_head_approval || 'pending').charAt(0).toUpperCase() + (request.office_head_approval || 'pending').slice(1)}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Source Office:</span>
                            <span class="badge bg-${request.source_office_approval === 'approved' ? 'success' : (request.source_office_approval === 'rejected' ? 'danger' : 'warning')}">
                                ${(request.source_office_approval || 'pending').charAt(0).toUpperCase() + (request.source_office_approval || 'pending').slice(1)}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Timeline</h6>
                    <div class="border rounded p-3">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <p class="mb-0">Request submitted</p>
                                    <small class="text-muted">${new Date(request.requested_at).toLocaleString()}</small>
                                </div>
                            </div>
                            ${request.approved_at ? `
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <p class="mb-0">Request approved</p>
                                        <small class="text-muted">${new Date(request.approved_at).toLocaleString()}</small>
                                    </div>
                                </div>
                            ` : ''}
                            ${request.returned_at ? `
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <p class="mb-0">Asset returned</p>
                                        <small class="text-muted">${new Date(request.returned_at).toLocaleString()}</small>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#requestDetails').html(detailsHtml);
    });
    
    // Handle cancel request button
    $('.cancel-request').on('click', function() {
        const requestId = $(this).data('request-id');
        
        if (confirm('Are you sure you want to cancel this request? This action cannot be undone.')) {
            // Submit cancellation via AJAX
            $.ajax({
                url: 'cancel_inter_dept_request.php',
                type: 'POST',
                data: { request_id: requestId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        // Reload the page after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', response.message || 'Failed to cancel request.');
                    }
                },
                error: function() {
                    showAlert('danger', 'An error occurred. Please try again.');
                }
            });
        }
    });
    
    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.container-fluid').prepend(alertHtml);
    }
    });
    </script>

    <style>
    .timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 1rem;
}

.timeline-marker {
    position: absolute;
    left: -1.75rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background-color: #0d6efd;
    top: 0.25rem;
}

.timeline-content {
    padding-left: 1rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}
</style>
