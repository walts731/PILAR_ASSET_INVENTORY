<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_quantity'])) {
  $request_id = intval($_POST['request_id']);
  $new_quantity = intval($_POST['new_quantity']);

  // Validate new quantity is positive
  if ($new_quantity <= 0) {
    $_SESSION['error_message'] = "Quantity must be greater than 0.";
    header("Location: borrow_requests.php");
    exit();
  }

  // Check if request belongs to user and is still pending
  $check_stmt = $conn->prepare("
    SELECT br.status, a.quantity as max_quantity, a.asset_name
    FROM borrow_requests br
    JOIN assets a ON br.asset_id = a.id
    WHERE br.id = ? AND br.user_id = ?
  ");
  $check_stmt->bind_param("ii", $request_id, $user_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  
  if ($check_result->num_rows === 0) {
    $_SESSION['error_message'] = "Request not found or unauthorized.";
    header("Location: borrow_requests.php");
    exit();
  }
  
  $request_data = $check_result->fetch_assoc();
  $check_stmt->close();
  
  if ($request_data['status'] !== 'pending') {
    $_SESSION['error_message'] = "Can only edit pending requests.";
    header("Location: borrow_requests.php");
    exit();
  }
  
  if ($new_quantity > $request_data['max_quantity']) {
    $_SESSION['error_message'] = "Requested quantity ({$new_quantity}) exceeds available quantity ({$request_data['max_quantity']}) for {$request_data['asset_name']}.";
    header("Location: borrow_requests.php");
    exit();
  }

  $stmt = $conn->prepare("
    UPDATE borrow_requests
    SET quantity = ?
    WHERE id = ? AND user_id = ? AND status = 'pending'
  ");
  $stmt->bind_param("iii", $new_quantity, $request_id, $user_id);
  
  if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['success_message'] = "Quantity updated successfully.";
  } else {
    $_SESSION['error_message'] = "Failed to update quantity.";
  }
  $stmt->close();
  header("Location: borrow_requests.php");
  exit();
}

// Handle cancel selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_requests'])) {
  $selected_ids = $_POST['selected_requests'];
  
  if (empty($selected_ids)) {
    $_SESSION['error_message'] = "No requests selected for cancellation.";
    header("Location: borrow_requests.php");
    exit();
  }
  
  // Validate all selected IDs are integers
  $selected_ids = array_map('intval', $selected_ids);
  $selected_ids = array_filter($selected_ids, function($id) { return $id > 0; });
  
  if (empty($selected_ids)) {
    $_SESSION['error_message'] = "Invalid request IDs provided.";
    header("Location: borrow_requests.php");
    exit();
  }
  
  $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
  $types = str_repeat('i', count($selected_ids));

  // Only allow cancellation of pending requests that belong to the user
  $stmt = $conn->prepare("DELETE FROM borrow_requests WHERE id IN ($placeholders) AND user_id = ? AND status = 'pending'");
  $params = array_merge($selected_ids, [$user_id]);
  $stmt->bind_param($types . 'i', ...$params);
  $stmt->execute();
  
  $cancelled_count = $stmt->affected_rows;
  $stmt->close();

  if ($cancelled_count > 0) {
    $_SESSION['success_message'] = "$cancelled_count borrow request(s) have been cancelled.";
  } else {
    $_SESSION['error_message'] = "No pending requests were found to cancel.";
  }
  header("Location: borrow_requests.php");
  exit();
}

// Debug: Check user_id and session
error_log("Debug: User ID from session = " . $user_id);
error_log("Debug: Session data = " . print_r($_SESSION, true));

// First, let's check if there are ANY borrow requests in the database
$check_all = $conn->query("SELECT COUNT(*) as total FROM borrow_requests");
$total_requests = $check_all->fetch_assoc()['total'];
error_log("Debug: Total borrow requests in database = " . $total_requests);

// Check if there are requests for this specific user_id
$check_user = $conn->prepare("SELECT COUNT(*) as user_total FROM borrow_requests WHERE user_id = ?");
$check_user->bind_param("i", $user_id);
$check_user->execute();
$user_total = $check_user->get_result()->fetch_assoc()['user_total'];
error_log("Debug: Borrow requests for user $user_id = " . $user_total);

// Let's also check what user_ids exist in borrow_requests
$check_users = $conn->query("SELECT DISTINCT user_id FROM borrow_requests");
$existing_users = [];
while ($row = $check_users->fetch_assoc()) {
    $existing_users[] = $row['user_id'];
}
error_log("Debug: User IDs in borrow_requests table = " . implode(', ', $existing_users));

// Fetch requests with max quantity
$stmt = $conn->prepare("
  SELECT br.id, a.asset_name, o.office_name, br.quantity, br.status, br.requested_at, a.quantity AS max_quantity
  FROM borrow_requests br
  JOIN assets a ON br.asset_id = a.id
  JOIN offices o ON br.office_id = o.id
  WHERE br.user_id = ?
  ORDER BY br.requested_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Debug: Check if any results found
$row_count = $result->num_rows;
error_log("Debug: Found $row_count borrow requests for user $user_id after JOIN query");

// Store all rows in an array for display
$borrow_requests = [];
if ($row_count > 0) {
    while ($row = $result->fetch_assoc()) {
        $borrow_requests[] = $row;
    }
    // Debug: Show sample data
    if (!empty($borrow_requests)) {
        error_log("Debug: Sample row data = " . print_r($borrow_requests[0], true));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Borrow Requests</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main">
  <?php include 'includes/topbar.php'; ?>

  <div class="container mt-4">
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Debug Information -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <strong>Debug Info:</strong><br>
      User ID: <?= $user_id ?><br>
      Total requests in DB: <?= $total_requests ?><br>
      Requests for this user: <?= $user_total ?><br>
      User IDs in DB: <?= implode(', ', $existing_users) ?><br>
      Query result count: <?= $row_count ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="card shadow-sm">
      <form method="POST">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div><i class="bi bi-clock-history"></i> My Borrow Requests</div>
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel selected requests?')">
            <i class="bi bi-x-circle"></i> Cancel Selected
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="requestsTable" class="table table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th><input type="checkbox" id="checkAll"></th>
                  <th>Asset</th>
                  <th>Office</th>
                  <th>Status</th>
                  <th>Quantity</th>
                  <th>Requested At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($row_count > 0): ?>
                  <?php foreach ($borrow_requests as $row): ?>
                    <tr>
                      <td><input type="checkbox" name="selected_requests[]" value="<?= $row['id'] ?>"></td>
                      <td><?= htmlspecialchars($row['asset_name']) ?></td>
                      <td><?= htmlspecialchars($row['office_name']) ?></td>
                      <td>
                        <?php
                          $badge = match($row['status']) {
                            'pending' => 'warning',
                            'borrowed' => 'success',
                            'rejected' => 'danger',
                            'returned' => 'info',
                            default => 'secondary'
                          };
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span>
                      </td>
                      <td><?= $row['quantity'] ?></td>
                      <td><?= date('F j, Y g:i A', strtotime($row['requested_at'])) ?></td>
                      <td>
                        <?php if ($row['status'] === 'pending'): ?>
                          <button type="button"
                                  class="btn btn-sm btn-outline-primary edit-btn"
                                  data-bs-toggle="modal"
                                  data-bs-target="#editModal"
                                  data-id="<?= $row['id'] ?>"
                                  data-quantity="<?= $row['quantity'] ?>"
                                  data-max="<?= $row['max_quantity'] ?>">
                            <i class="bi bi-pencil"></i> Edit
                          </button>
                        <?php elseif ($row['status'] === 'borrowed'): ?>
                          <button type="button"
                                  class="btn btn-sm btn-outline-success return-btn"
                                  data-bs-toggle="modal"
                                  data-bs-target="#returnModal"
                                  data-request-id="<?= $row['id'] ?>"
                                  data-asset-name="<?= htmlspecialchars($row['asset_name']) ?>">
                            <i class="bi bi-arrow-return-left"></i> Return
                          </button>
                        <?php else: ?>
                          <span class="text-muted small">N/A</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <div class="text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        <h5>No Borrow Requests Found</h5>
                        <p>You haven't made any borrow requests yet.</p>
                        <a href="borrow.php" class="btn btn-primary btn-sm">
                          <i class="bi bi-plus-circle"></i> Make a Borrow Request
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Quantity Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Quantity</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="request_id" id="editRequestId">
          <input type="number" name="new_quantity" id="editQuantity" class="form-control" min="1" required>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_quantity" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Return Confirmation Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="returnForm" method="POST" action="process_return.php">
        <div class="modal-header">
          <h5 class="modal-title" id="returnModalLabel">Return Asset</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="request_id" id="returnRequestId">
          <p>You are about to return: <strong id="returnAssetName"></strong></p>
          
          <div class="mb-3">
            <label for="returnCondition" class="form-label">Condition</label>
            <select class="form-select" id="returnCondition" name="condition" required>
              <option value="good">Good</option>
              <option value="damaged">Damaged</option>
              <option value="needs_repair">Needs Repair</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="returnNotes" class="form-label">Notes (Optional)</label>
            <textarea class="form-control" id="returnNotes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm Return</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    // Only initialize DataTable if there are rows with data
    <?php if ($row_count > 0): ?>
      $('#requestsTable').DataTable();
    <?php endif; ?>

    // Check all functionality - only if checkbox exists
    $('#checkAll').on('change', function () {
      $('input[name="selected_requests[]"]').prop('checked', this.checked);
    });

    // Fill edit modal with correct data - only bind if edit buttons exist
    $('.edit-btn').on('click', function () {
      const id = $(this).data('id');
      const quantity = $(this).data('quantity');
      const max = $(this).data('max');

      $('#editRequestId').val(id);
      $('#editQuantity').val(quantity).attr('max', max);
    });

    // Fill return modal with data
    $('.return-btn').on('click', function() {
      const requestId = $(this).data('request-id');
      const assetName = $(this).data('asset-name');
      
      $('#returnRequestId').val(requestId);
      $('#returnAssetName').text(assetName);
    });
    
    // Handle return form submission
    $('#returnForm').on('submit', function(e) {
      e.preventDefault();
      
      const form = $(this);
      const submitBtn = form.find('button[type="submit"]');
      const originalBtnText = submitBtn.html();
      
      // Show loading state
      submitBtn.prop('disabled', true).html(
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
      );
      
      // Submit form via AJAX
      $.ajax({
        url: 'process_return.php',
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            // Show success message and reload the page
            const successAlert = `
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                ${response.message || 'Item returned successfully'}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`;
            $('.container.mt-4').prepend(successAlert);
            
            // Close the modal and reload the page after a short delay
            $('#returnModal').modal('hide');
            setTimeout(() => window.location.reload(), 1000);
          } else {
            // Show error message
            alert(response.message || 'An error occurred while processing the return');
            submitBtn.prop('disabled', false).html(originalBtnText);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error:', error);
          alert('An error occurred while processing your request. Please try again.');
          submitBtn.prop('disabled', false).html(originalBtnText);
        }
      });
    });
  });
</script>
</body>
</html>
