<?php
require_once '../connect.php';
require_once '../includes/classes/GuestBorrowing.php';

// Check if user is logged in and has admin privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request ID.';
    header("Location: guest_borrowing_requests.php");
    exit();
}

$request_id = intval($_GET['id']);
$guestBorrowing = new GuestBorrowing($conn);
$request = $guestBorrowing->getRequestDetails($request_id);

if (!$request) {
    $_SESSION['error'] = 'Request not found.';
    header("Location: guest_borrowing_requests.php");
    exit();
}

// Handle actions (approve, reject, update status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $admin_id = $_SESSION['user_id'];
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : '';
        
        try {
            switch ($_POST['action']) {
                case 'approve':
                    $result = $guestBorrowing->updateRequestStatus($request_id, 'approved', $admin_id, $notes);
                    if ($result) {
                        $_SESSION['success'] = 'Request approved successfully.';
                    }
                    break;
                    
                case 'reject':
                    if (empty($notes)) {
                        throw new Exception('Please provide a reason for rejection.');
                    }
                    $result = $guestBorrowing->updateRequestStatus($request_id, 'rejected', $admin_id, $notes);
                    if ($result) {
                        $_SESSION['success'] = 'Request rejected successfully.';
                    }
                    break;
                    
                case 'update_status':
                    $status = $_POST['status'];
                    $valid_statuses = ['in_progress', 'ready_for_pickup', 'in_transit', 'completed', 'returned', 'overdue', 'damaged', 'lost'];
                    
                    if (!in_array($status, $valid_statuses)) {
                        throw new Exception('Invalid status provided.');
                    }
                    
                    $result = $guestBorrowing->updateRequestStatus($request_id, $status, $admin_id, $notes);
                    if ($result) {
                        $_SESSION['success'] = 'Request status updated successfully.';
                    }
                    break;
                    
                case 'add_note':
                    if (!empty($notes)) {
                        $result = $guestBorrowing->addNote($request_id, $admin_id, $notes);
                        if ($result) {
                            $_SESSION['success'] = 'Note added successfully.';
                        }
                    }
                    break;
            }
            
            // Refresh the request data
            $request = $guestBorrowing->getRequestDetails($request_id);
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        // Redirect to clear the POST data
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $request_id);
        exit();
    }
}

$page_title = 'Guest Borrowing Request #' . $request['request_number'];

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <a href="guest_borrowing_requests.php" class="text-decoration-none text-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>
            </a>
            Guest Borrowing Request
        </h1>
        <div class="d-flex">
            <a href="#" class="btn btn-primary mr-2" data-toggle="modal" data-target="#printModal">
                <i class="fas fa-print fa-sm text-white-50"></i> Print
            </a>
            <?php if ($request['status'] === 'pending'): ?>
                <button class="btn btn-success mr-2" data-toggle="modal" data-target="#approveModal">
                    <i class="fas fa-check fa-sm text-white-50"></i> Approve
                </button>
                <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                    <i class="fas fa-times fa-sm text-white-50"></i> Reject
                </button>
            <?php else: ?>
                <button class="btn btn-info" data-toggle="modal" data-target="#statusModal">
                    <i class="fas fa-sync-alt fa-sm text-white-50"></i> Update Status
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Request Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Request Details</h6>
                    <span class="badge badge-<?= getStatusBadgeClass($request['status']) ?>">
                        <?= ucwords(str_replace('_', ' ', $request['status'])) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Request Information</h5>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th class="w-50">Request #:</th>
                                    <td><?= htmlspecialchars($request['request_number']) ?></td>
                                </tr>
                                <tr>
                                    <th>Request Date:</th>
                                    <td><?= date('F j, Y g:i A', strtotime($request['request_date'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Needed By:</th>
                                    <td><?= date('F j, Y', strtotime($request['needed_by_date'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Expected Return:</th>
                                    <td><?= date('F j, Y', strtotime($request['expected_return_date'])) ?></td>
                                </tr>
                                <?php if ($request['actual_return_date']): ?>
                                <tr>
                                    <th>Actual Return:</th>
                                    <td><?= date('F j, Y g:i A', strtotime($request['actual_return_date'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Guest Information</h5>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th class="w-50">Name:</th>
                                    <td><?= htmlspecialchars($request['guest_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($request['guest_email']) ?></td>
                                </tr>
                                <?php if (!empty($request['guest_contact'])): ?>
                                <tr>
                                    <th>Contact:</th>
                                    <td><?= htmlspecialchars($request['guest_contact']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($request['guest_organization'])): ?>
                                <tr>
                                    <th>Organization:</th>
                                    <td><?= htmlspecialchars($request['guest_organization']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Purpose</h5>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($request['purpose'])) ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($request['rejection_reason'])): ?>
                    <div class="mb-4">
                        <h5 class="font-weight-bold text-danger">Rejection Reason</h5>
                        <div class="border border-danger rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($request['rejection_reason'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($request['notes'])): ?>
                    <div class="mb-4">
                        <h5 class="font-weight-bold">Admin Notes</h5>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($request['notes'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Borrowed Items (<?= count($request['items']) ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Inventory #</th>
                                    <th>Condition</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($request['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($item['asset_name']) ?></div>
                                        <small class="text-muted">
                                            <?= !empty($item['brand']) ? htmlspecialchars($item['brand']) : '' ?>
                                            <?= !empty($item['model']) ? ' - ' . htmlspecialchars($item['model']) : '' ?>
                                        </small>
                                    </td>
                                    <td><?= !empty($item['inventory_tag']) ? htmlspecialchars($item['inventory_tag']) : 'N/A' ?></td>
                                    <td>
                                        <?php if (!empty($item['condition_before'])): ?>
                                            <span class="badge bg-info"><?= ucfirst($item['condition_before']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'returned' || $request['status'] === 'completed'): ?>
                                            <?php if (!empty($item['condition_after'])): ?>
                                                <span class="badge bg-<?= $item['condition_after'] === 'good' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($item['condition_after']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not checked</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending return</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($request['history'] as $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1">
                                        <?= ucwords(str_replace('_', ' ', $history['action'])) ?>
                                        <?php if ($history['performed_by_name']): ?>
                                            <small class="text-muted">by <?= htmlspecialchars($history['performed_by_name']) ?></small>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted"><?= date('M j, Y g:i A', strtotime($history['performed_at'])) ?></small>
                                </div>
                                <?php if (!empty($history['details'])): ?>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($history['details'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Add Note -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Note</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="add_note">
                        <div class="form-group">
                            <textarea class="form-control" name="notes" rows="3" placeholder="Add a note about this request..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Note</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Request Status</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="status-indicator status-<?= $request['status'] ?> mb-3">
                            <i class="fas <?= getStatusIcon($request['status']) ?> fa-3x"></i>
                        </div>
                        <h4 class="mb-1"><?= ucwords(str_replace('_', ' ', $request['status'])) ?></h4>
                        <?php if ($request['approved_by_name']): ?>
                            <p class="text-muted mb-0">Approved by <?= htmlspecialchars($request['approved_by_name']) ?></p>
                            <p class="text-muted"><?= date('M j, Y g:i A', strtotime($request['approved_at'])) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="progress mb-4">
                        <?php 
                        $progress = [
                            'pending' => 20,
                            'approved' => 40,
                            'in_progress' => 60,
                            'ready_for_pickup' => 80,
                            'completed' => 100,
                            'returned' => 100,
                            'rejected' => 0,
                            'cancelled' => 0,
                            'overdue' => 100,
                            'damaged' => 100,
                            'lost' => 100
                        ][$request['status']];
                        ?>
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= getStatusBadgeClass($request['status']) ?>" 
                             role="progressbar" style="width: <?= $progress ?>%" 
                             aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                            <?= $progress ?>%
                        </div>
                    </div>
                    
                    <div class="timeline-status">
                        <div class="timeline-step <?= in_array($request['status'], ['pending', 'approved', 'in_progress', 'ready_for_pickup', 'completed', 'returned', 'overdue', 'damaged', 'lost']) ? 'active' : '' ?>">
                            <div class="timeline-step-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="timeline-step-label">Requested</div>
                            <div class="timeline-step-date">
                                <?= date('M j, Y', strtotime($request['request_date'])) ?>
                            </div>
                        </div>
                        
                        <?php if ($request['status'] !== 'rejected' && $request['status'] !== 'cancelled'): ?>
                            <div class="timeline-step <?= in_array($request['status'], ['approved', 'in_progress', 'ready_for_pickup', 'completed', 'returned', 'overdue', 'damaged', 'lost']) ? 'active' : '' ?>">
                                <div class="timeline-step-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-step-label">Approved</div>
                                <?php if ($request['approved_at']): ?>
                                    <div class="timeline-step-date">
                                        <?= date('M j, Y', strtotime($request['approved_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="timeline-step <?= in_array($request['status'], ['in_progress', 'ready_for_pickup', 'completed', 'returned', 'overdue', 'damaged', 'lost']) ? 'active' : '' ?>">
                                <div class="timeline-step-icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="timeline-step-label">In Progress</div>
                            </div>
                            
                            <div class="timeline-step <?= in_array($request['status'], ['ready_for_pickup', 'completed', 'returned', 'overdue', 'damaged', 'lost']) ? 'active' : '' ?>">
                                <div class="timeline-step-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="timeline-step-label">Ready for Pickup</div>
                            </div>
                            
                            <div class="timeline-step <?= in_array($request['status'], ['completed', 'returned', 'overdue', 'damaged', 'lost']) ? 'active' : '' ?>">
                                <div class="timeline-step-icon">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div class="timeline-step-label">Completed</div>
                                <?php if ($request['actual_return_date']): ?>
                                    <div class="timeline-step-date">
                                        <?= date('M j, Y', strtotime($request['actual_return_date'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="timeline-step <?= $request['status'] === 'rejected' || $request['status'] === 'cancelled' ? 'active' : '' ?>">
                                <div class="timeline-step-icon">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="timeline-step-label">
                                    <?= $request['status'] === 'rejected' ? 'Rejected' : 'Cancelled' ?>
                                </div>
                                <?php if ($request['status'] === 'rejected' && $request['rejection_reason']): ?>
                                    <div class="timeline-step-note">
                                        <?= nl2br(htmlspecialchars(mb_strimwidth($request['rejection_reason'], 0, 50, '...'))) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if ($request['status'] === 'pending'): ?>
                            <button type="button" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#approveModal">
                                <i class="fas fa-check-circle text-success mr-2"></i> Approve Request
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times-circle text-danger mr-2"></i> Reject Request
                            </button>
                        <?php elseif (in_array($request['status'], ['approved', 'in_progress', 'ready_for_pickup'])): ?>
                            <button type="button" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#statusModal">
                                <i class="fas fa-sync-alt text-primary mr-2"></i> Update Status
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#returnModal">
                                <i class="fas fa-undo-alt text-info mr-2"></i> Mark as Returned
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] === 'overdue'): ?>
                            <button type="button" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#markReturnedModal">
                                <i class="fas fa-check-circle text-success mr-2"></i> Mark as Returned
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#reportModal">
                                <i class="fas fa-flag text-warning mr-2"></i> Report as Lost/Damaged
                            </button>
                        <?php endif; ?>
                        
                        <a href="mailto:<?= htmlspecialchars($request['guest_email']) ?>?subject=Regarding%20Borrowing%20Request%20%23<?= urlencode($request['request_number']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope text-primary mr-2"></i> Email Guest
                        </a>
                        
                        <?php if (!empty($request['guest_contact'])): ?>
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $request['guest_contact']) ?>" 
                               class="list-group-item list-group-item-action">
                                <i class="fas fa-phone text-success mr-2"></i> Call Guest
                            </a>
                        <?php endif; ?>
                        
                        <a href="#" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#printModal">
                            <i class="fas fa-print text-secondary mr-2"></i> Print Request
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Guest Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Guest Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-icon" style="width: 80px; height: 80px; background: #4e73df; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                            <span style="color: white; font-size: 2rem;">
                                <?= strtoupper(substr($request['guest_name'], 0, 1)) ?>
                            </span>
                        </div>
                        <h5 class="font-weight-bold mb-1"><?= htmlspecialchars($request['guest_name']) ?></h5>
                        <p class="text-muted mb-1">
                            <?= !empty($request['guest_organization']) ? htmlspecialchars($request['guest_organization']) : 'No organization provided' ?>
                        </p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope fa-fw text-gray-400 mr-2"></i>
                                <a href="mailto:<?= htmlspecialchars($request['guest_email']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($request['guest_email']) ?>
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!empty($request['guest_contact'])): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone fa-fw text-gray-400 mr-2"></i>
                                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $request['guest_contact']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($request['guest_contact']) ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($request['guest_organization'])): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building fa-fw text-gray-400 mr-2"></i>
                                <span><?= htmlspecialchars($request['guest_organization']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar fa-fw text-gray-400 mr-2"></i>
                                <span>Member since <?= date('M Y', strtotime($request['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="#" class="btn btn-sm btn-primary btn-block">
                            <i class="fas fa-history fa-sm text-white-50"></i> View All Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="approve">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveModalLabel">Approve Borrowing Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this borrowing request?</p>
                    <div class="form-group">
                        <label for="approve_notes">Notes (Optional)</label>
                        <textarea class="form-control" id="approve_notes" name="notes" rows="3" placeholder="Add any notes or instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="" id="rejectForm">
                <input type="hidden" name="action" value="reject">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Borrowing Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this borrowing request?</p>
                    <div class="form-group">
                        <label for="reject_reason">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_reason" name="notes" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="update_status">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="statusModalLabel">Update Request Status</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="in_progress" <?= $request['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="ready_for_pickup" <?= $request['status'] === 'ready_for_pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                            <option value="in_transit" <?= $request['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                            <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="returned" <?= $request['status'] === 'returned' ? 'selected' : '' ?>>Returned</option>
                            <option value="overdue" <?= $request['status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                            <option value="damaged" <?= $request['status'] === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                            <option value="lost" <?= $request['status'] === 'lost' ? 'selected' : '' ?>>Lost</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status_notes">Notes</label>
                        <textarea class="form-control" id="status_notes" name="notes" rows="3" placeholder="Add any notes or comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printModalLabel">Print Borrowing Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="printableArea" class="p-4">
                    <div class="text-center mb-4">
                        <h4>Borrowing Request #<?= $request['request_number'] ?></h4>
                        <p class="mb-0"><?= date('F j, Y') ?></p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Request Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Request #:</th>
                                    <td><?= $request['request_number'] ?></td>
                                </tr>
                                <tr>
                                    <th>Request Date:</th>
                                    <td><?= date('F j, Y', strtotime($request['request_date'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-<?= getStatusBadgeClass($request['status']) ?>">
                                            <?= ucwords(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Guest Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td><?= $request['guest_name'] ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= $request['guest_email'] ?></td>
                                </tr>
                                <?php if (!empty($request['guest_contact'])): ?>
                                <tr>
                                    <th>Contact:</th>
                                    <td><?= $request['guest_contact'] ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($request['guest_organization'])): ?>
                                <tr>
                                    <th>Organization:</th>
                                    <td><?= $request['guest_organization'] ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <h5>Borrowing Details</h5>
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Item</th>
                                <th>Inventory #</th>
                                <th>Condition</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($request['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?= $item['asset_name'] ?></div>
                                    <small class="text-muted">
                                        <?= !empty($item['brand']) ? $item['brand'] : '' ?>
                                        <?= !empty($item['model']) ? ' - ' . $item['model'] : '' ?>
                                    </small>
                                </td>
                                <td><?= !empty($item['inventory_tag']) ? $item['inventory_tag'] : 'N/A' ?></td>
                                <td>
                                    <?php if (!empty($item['condition_before'])): ?>
                                        <span class="badge bg-info"><?= ucfirst($item['condition_before']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Not specified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        <h5>Purpose</h5>
                        <div class="border rounded p-3">
                            <?= nl2br($request['purpose']) ?>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="border-top pt-3">
                                <p class="mb-1">_________________________</p>
                                <p class="mb-0">Guest's Signature</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-top pt-3">
                                <p class="mb-1">_________________________</p>
                                <p class="mb-0">Authorized Personnel</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printDiv('printableArea')">
                    <i class="fas fa-print fa-sm text-white-50"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Returned Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="returned">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="returnModalLabel">Mark as Returned</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this request as returned?</p>
                    
                    <div class="form-group">
                        <label for="return_condition">Condition of Returned Items</label>
                        <select class="form-control" id="return_condition" name="item_condition" required>
                            <option value="good">Good</option>
                            <option value="damaged">Damaged</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="return_notes">Notes</label>
                        <textarea class="form-control" id="return_notes" name="notes" rows="3" placeholder="Add any notes about the return..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Returned</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Report Lost/Damaged Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="update_status">
                
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="reportModalLabel">Report Lost or Damaged Item</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="report_type">Report Type</label>
                        <select class="form-control" id="report_type" name="status" required>
                            <option value="damaged">Item Damaged</option>
                            <option value="lost">Item Lost</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="report_notes">Details</label>
                        <textarea class="form-control" id="report_notes" name="notes" rows="4" required 
                                  placeholder="Please provide details about the damage or circumstances of the loss..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="notify_guest" name="notify_guest" checked>
                            <label class="custom-control-label" for="notify_guest">Notify guest about this report</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
/* Status indicators */
.status-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto;
}

.status-pending { background-color: #f6c23e; color: white; }
.status-approved { background-color: #1cc88a; color: white; }
.status-rejected { background-color: #e74a3b; color: white; }
.status-in_progress { background-color: #36b9cc; color: white; }
.status-ready_for_pickup { background-color: #4e73df; color: white; }
.status-completed { background-color: #1cc88a; color: white; }
-status-returned { background-color: #6f42c1; color: white; }
.status-overdue { background-color: #fd7e14; color: white; }
.status-damaged { background-color: #e74a3b; color: white; }
.status-lost { background-color: #5a5c69; color: white; }

/* Timeline */
.timeline {
    position: relative;
    padding-left: 2rem;
    margin: 2rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    background: #4e73df;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #4e73df;
}

.timeline-content {
    position: relative;
    padding: 0.5rem 0 0 1rem;
}

.timeline-step {
    position: relative;
    padding-left: 3rem;
    margin-bottom: 1.5rem;
}

.timeline-step.active .timeline-step-icon {
    background-color: #4e73df;
    color: white;
    border-color: #4e73df;
}

.timeline-step-icon {
    position: absolute;
    left: 0;
    width: 2.5rem;
    height: 2.5rem;
    border: 2px solid #e3e6f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: #b7b9cc;
    font-size: 1.1rem;
}

.timeline-step-label {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-step-date {
    font-size: 0.8rem;
    color: #858796;
}

/* Status badges */
.badge-pending { background-color: #f6c23e; }
.badge-approved { background-color: #1cc88a; }
.badge-rejected { background-color: #e74a3b; }
.badge-in_progress { background-color: #36b9cc; }
.badge-ready_for_pickup { background-color: #4e73df; }
.badge-completed { background-color: #1cc88a; }
.badge-returned { background-color: #6f42c1; }
.badge-overdue { background-color: #fd7e14; }
.badge-damaged { background-color: #e74a3b; }
.badge-lost { background-color: #5a5c69; }

/* Print styles */
@media print {
    body * {
        visibility: hidden;
    }
    #printableArea, #printableArea * {
        visibility: visible;
    }
    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<script>
// Print function
function printDiv(divName) {
    var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;
    
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    
    // Reload the page to restore functionality
    location.reload();
}

// Document ready
$(document).ready(function() {
    // Handle reject form validation
    $('#rejectForm').on('submit', function(e) {
        if ($('#reject_reason').val().trim() === '') {
            e.preventDefault();
            alert('Please provide a reason for rejection.');
            $('#reject_reason').focus();
        }
    });
    
    // Handle status update button click
    $('.status-update-btn').on('click', function() {
        var status = $(this).data('status');
        $('#status').val(status);
        $('#statusModal').modal('show');
    });
    
    // Handle return button click
    $('.return-btn').on('click', function() {
        $('#returnModal').modal('show');
    });
    
    // Handle report button click
    $('.report-btn').on('click', function() {
        $('#reportModal').modal('show');
    });
});
</script>

<?php
// Helper function to get status badge class
function getStatusBadgeClass($status) {
    $status_classes = [
        'pending' => 'warning',
        'approved' => 'primary',
        'rejected' => 'danger',
        'in_progress' => 'info',
        'ready_for_pickup' => 'primary',
        'in_transit' => 'info',
        'completed' => 'success',
        'returned' => 'success',
        'overdue' => 'warning',
        'damaged' => 'danger',
        'lost' => 'dark'
    ];
    
    return $status_classes[$status] ?? 'secondary';
}

// Helper function to get status icon
function getStatusIcon($status) {
    $status_icons = [
        'pending' => 'fa-clock',
        'approved' => 'fa-check-circle',
        'rejected' => 'fa-times-circle',
        'in_progress' => 'fa-spinner',
        'ready_for_pickup' => 'fa-box-open',
        'in_transit' => 'fa-truck',
        'completed' => 'fa-check-double',
        'returned' => 'fa-undo',
        'overdue' => 'fa-exclamation-triangle',
        'damaged' => 'fa-exclamation-triangle',
        'lost' => 'fa-question-circle',
        'cancelled' => 'fa-ban'
    ];
    
    return $status_icons[$status] ?? 'fa-question-circle';
}
?>
