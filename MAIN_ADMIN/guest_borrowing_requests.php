<?php
require_once '../connect.php';
require_once '../includes/classes/GuestBorrowing.php';

// Check if user is logged in and has admin privileges
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = 'Guest Borrowing Requests';
$guestBorrowing = new GuestBorrowing($conn);

// Handle actions (approve, reject, update status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['request_id'])) {
        $request_id = intval($_POST['request_id']);
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
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $request_id);
        exit();
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($status_filter)) {
    $where_conditions[] = "r.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(r.request_number LIKE ? OR r.guest_name LIKE ? OR r.guest_email LIKE ? OR r.guest_contact LIKE ? OR r.guest_organization LIKE ? OR a.asset_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, array_fill(0, 6, $search_param));
    $types .= str_repeat('s', 6);
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(DISTINCT r.id) as total 
               FROM guest_borrowing_requests r
               LEFT JOIN guest_borrowing_items ri ON r.id = ri.request_id
               LEFT JOIN assets a ON ri.asset_id = a.id
               $where_clause";

$total_requests = 0;
if ($stmt = $conn->prepare($count_query)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total_requests = $result->fetch_assoc()['total'];
    $stmt->close();
}

$total_pages = ceil($total_requests / $limit);

// Get requests
$requests_query = "
    SELECT r.*, 
           COUNT(ri.id) as item_count,
           GROUP_CONCAT(DISTINCT a.asset_name SEPARATOR ', ') as asset_names,
           u.username as approved_by_name
    FROM guest_borrowing_requests r
    LEFT JOIN guest_borrowing_items ri ON r.id = ri.request_id
    LEFT JOIN assets a ON ri.asset_id = a.id
    LEFT JOIN users u ON r.approved_by = u.id
    $where_clause
    GROUP BY r.id
    ORDER BY r.request_date DESC
    LIMIT ? OFFSET ?
";

$requests = [];
if ($stmt = $conn->prepare($requests_query)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Guest Borrowing Requests</h1>
        <div>
            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                <i class="fas fa-download fa-sm text-white-50"></i> Export
            </a>
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

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="ready_for_pickup" <?= $status_filter === 'ready_for_pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                        <option value="in_transit" <?= $status_filter === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="returned" <?= $status_filter === 'returned' ? 'selected' : '' ?>>Returned</option>
                        <option value="overdue" <?= $status_filter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Search by request #, name, email, or asset..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Borrowing Requests</h6>
            <div>
                <span class="badge bg-secondary">Total: <?= $total_requests ?></span>
                <?php if (!empty($status_filter)): ?>
                    <span class="badge bg-info">Filtered: <?= count($requests) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Guest</th>
                            <th>Organization</th>
                            <th>Items</th>
                            <th>Request Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No requests found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>
                                        <a href="guest_borrowing_request_details.php?id=<?= $request['id'] ?>">
                                            <?= htmlspecialchars($request['request_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($request['guest_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($request['guest_email']) ?></small>
                                        <?php if (!empty($request['guest_contact'])): ?>
                                            <div><small class="text-muted"><?= htmlspecialchars($request['guest_contact']) ?></small></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($request['guest_organization']) ? htmlspecialchars($request['guest_organization']) : 'N/A' ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $request['item_count'] ?> items</span>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($request['asset_names']) ?>">
                                            <?= htmlspecialchars($request['asset_names']) ?>
                                        </div>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($request['request_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($request['expected_return_date'])) ?></td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'pending' => 'warning',
                                            'approved' => 'primary',
                                            'rejected' => 'danger',
                                            'in_progress' => 'info',
                                            'ready_for_pickup' => 'success',
                                            'in_transit' => 'info',
                                            'completed' => 'success',
                                            'returned' => 'success',
                                            'overdue' => 'danger',
                                            'damaged' => 'danger',
                                            'lost' => 'dark'
                                        ][$request['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $status_class ?>">
                                            <?= ucwords(str_replace('_', ' ', $request['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="guest_borrowing_request_details.php?id=<?= $request['id'] ?>" 
                                           class="btn btn-sm btn-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success approve-btn" 
                                                    data-id="<?= $request['id'] ?>"
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger reject-btn" 
                                                    data-id="<?= $request['id'] ?>"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">First</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        $start_page = max(1, $end_page - 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">Next</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">Last</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="request_id" id="approve_request_id" value="">
                
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
                <input type="hidden" name="request_id" id="reject_request_id" value="">
                
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
                <input type="hidden" name="request_id" id="status_request_id" value="">
                
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
                            <option value="in_progress">In Progress</option>
                            <option value="ready_for_pickup">Ready for Pickup</option>
                            <option value="in_transit">In Transit</option>
                            <option value="completed">Completed</option>
                            <option value="returned">Returned</option>
                            <option value="overdue">Overdue</option>
                            <option value="damaged">Damaged</option>
                            <option value="lost">Lost</option>
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

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="export_guest_borrowing_requests.php" target="_blank">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exportModalLabel">Export Guest Borrowing Requests</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="export_format">Format</label>
                        <select class="form-control" id="export_format" name="format" required>
                            <option value="csv">CSV (Excel)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="export_status">Status</label>
                        <select class="form-control" id="export_status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="in_progress">In Progress</option>
                            <option value="ready_for_pickup">Ready for Pickup</option>
                            <option value="in_transit">In Transit</option>
                            <option value="completed">Completed</option>
                            <option value="returned">Returned</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Document ready
$(document).ready(function() {
    // Initialize DataTable
    $('#dataTable').DataTable({
        paging: false,
        searching: false,
        info: false,
        order: [[4, 'desc']] // Sort by request date by default
    });
    
    // Handle approve button click
    $('.approve-btn').on('click', function() {
        var requestId = $(this).data('id');
        $('#approve_request_id').val(requestId);
        $('#approveModal').modal('show');
    });
    
    // Handle reject button click
    $('.reject-btn').on('click', function() {
        var requestId = $(this).data('id');
        $('#reject_request_id').val(requestId);
        $('#rejectModal').modal('show');
    });
    
    // Handle status update button click
    $('.status-update-btn').on('click', function() {
        var requestId = $(this).data('id');
        var currentStatus = $(this).data('status');
        $('#status_request_id').val(requestId);
        $('#status').val(currentStatus);
        $('#statusModal').modal('show');
    });
    
    // Form validation for reject form
    $('#rejectForm').on('submit', function(e) {
        if ($('#reject_reason').val().trim() === '') {
            e.preventDefault();
            alert('Please provide a reason for rejection.');
            $('#reject_reason').focus();
        }
    });
    
    // Set default dates for export
    var today = new Date();
    var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    var lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    $('#start_date').val(formatDate(firstDay));
    $('#end_date').val(formatDate(lastDay));
    
    // Format date as YYYY-MM-DD
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }
});
</script>
