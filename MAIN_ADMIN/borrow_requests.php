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

  $stmt = $conn->prepare("
    UPDATE borrow_requests
    SET quantity = ?
    WHERE id = ? AND user_id = ?
  ");
  $stmt->bind_param("iii", $new_quantity, $request_id, $user_id);
  $stmt->execute();
  $_SESSION['success_message'] = "Quantity updated successfully.";
  header("Location: borrow_requests.php");
  exit();
}

// Handle cancel selected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_requests'])) {
  $selected_ids = $_POST['selected_requests'];
  $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
  $types = str_repeat('i', count($selected_ids));

  $stmt = $conn->prepare("DELETE FROM borrow_requests WHERE id IN ($placeholders) AND user_id = ?");
  $params = array_merge($selected_ids, [$user_id]);
  $stmt->bind_param($types . 'i', ...$params);
  $stmt->execute();

  $_SESSION['success_message'] = "Selected borrow requests have been cancelled.";
  header("Location: borrow_requests.php");
  exit();
}

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
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><input type="checkbox" name="selected_requests[]" value="<?= $row['id'] ?>"></td>
                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                    <td><?= htmlspecialchars($row['office_name']) ?></td>
                    <td>
                      <?php
                        $badge = match($row['status']) {
                          'pending' => 'warning',
                          'approved' => 'success',
                          'declined' => 'danger',
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
                      <?php else: ?>
                        <span class="text-muted small">N/A</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    $('#requestsTable').DataTable();

    $('#checkAll').on('change', function () {
      $('input[name="selected_requests[]"]').prop('checked', this.checked);
    });

    // Fill modal with correct data
    $('.edit-btn').on('click', function () {
      const id = $(this).data('id');
      const quantity = $(this).data('quantity');
      const max = $(this).data('max');

      $('#editRequestId').val(id);
      $('#editQuantity').val(quantity).attr('max', max);
    });
  });
</script>
</body>
</html>
