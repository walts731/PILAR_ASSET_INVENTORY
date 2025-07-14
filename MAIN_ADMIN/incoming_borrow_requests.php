<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$office_id = $_SESSION['office_id'];
$user_id = $_SESSION['user_id'];

$query = "
  SELECT br.id AS request_id, u.fullname AS requester, a.asset_name, a.description, a.unit,
         br.quantity, br.status, br.requested_at, br.returned_at, br.return_remarks, o.office_name
  FROM borrow_requests br
  JOIN assets a ON br.asset_id = a.id
  JOIN users u ON br.user_id = u.id
  JOIN offices o ON br.office_id = o.id
  WHERE a.office_id = ?
  ORDER BY br.requested_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Incoming Borrow Requests</title>
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
    <div class="card shadow-sm">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><i class="bi bi-inbox"></i> Incoming Borrow Requests</div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped align-middle" id="incomingTable">
            <thead class="table-light">
              <tr>
                <th>Requester</th>
                <th>Office</th>
                <th>Asset</th>
                <th>Description</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Requested At</th>
                <th>Status</th>
                <th>Returned At</th>
                <th>Remarks</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr data-request-id="<?= $row['request_id'] ?>">
                  <td><?= htmlspecialchars($row['requester']) ?></td>
                  <td><?= htmlspecialchars($row['office_name']) ?></td>
                  <td><?= htmlspecialchars($row['asset_name']) ?></td>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><?= htmlspecialchars($row['unit']) ?></td>
                  <td><?= intval($row['quantity']) ?></td>
                  <td><?= date('F j, Y h:i A', strtotime($row['requested_at'])) ?></td>
                  <td>
                    <span class="badge bg-<?php
                      echo $row['status'] === 'approved' ? 'success' :
                          ($row['status'] === 'rejected' ? 'danger' :
                          ($row['status'] === 'returned' ? 'secondary' : 'warning'));
                    ?>">
                      <?= ucfirst($row['status']) ?>
                    </span>
                  </td>
                  <td><?= $row['returned_at'] ? date('F j, Y h:i A', strtotime($row['returned_at'])) : '—' ?></td>
                  <td><?= htmlspecialchars($row['return_remarks'] ?? '') ?></td>
                  <td>
                    <div class="action-buttons" id="buttons-<?= $row['request_id'] ?>">
                      <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST" action="process_borrow_decision.php" class="d-flex gap-1">
                          <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                          <button name="action" value="accept" class="btn btn-success btn-sm" title="Accept">
                            <i class="bi bi-check-circle"></i>
                          </button>
                          <button name="action" value="reject" class="btn btn-danger btn-sm" title="Reject">
                            <i class="bi bi-x-circle"></i>
                          </button>
                        </form>
                      <?php elseif ($row['status'] === 'returned'): ?>
                        <button type="button" class="btn btn-info btn-sm view-btn" data-bs-toggle="modal"
                                data-bs-target="#viewModal"
                                data-request='<?= json_encode($row) ?>'>
                          <i class="bi bi-eye"></i>
                        </button>
                      <?php else: ?>
                        <button class="btn btn-secondary btn-sm edit-btn" title="Edit Status" data-request-id="<?= $row['request_id'] ?>">
                          <i class="bi bi-pencil-square"></i>
                        </button>
                      <?php endif; ?>
                    </div>

                    <!-- Hidden editable form -->
                    <div class="edit-form d-none" id="edit-form-<?= $row['request_id'] ?>">
                      <form method="POST" action="process_borrow_decision.php" class="d-flex gap-1 mt-1">
                        <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                        <button name="action" value="accept" class="btn btn-success btn-sm">
                          <i class="bi bi-check-circle"></i>
                        </button>
                        <button name="action" value="reject" class="btn btn-danger btn-sm">
                          <i class="bi bi-x-circle"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm cancel-edit" data-request-id="<?= $row['request_id'] ?>">
                          <i class="bi bi-x-lg"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Viewing Returned Details -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalLabel"><i class="bi bi-eye"></i> Returned Asset Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Requester:</strong> <span id="viewRequester"></span></p>
        <p><strong>Asset:</strong> <span id="viewAsset"></span></p>
        <p><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
        <p><strong>Remarks:</strong> <span id="viewRemarks"></span></p>
        <p><strong>Returned At:</strong> <span id="viewReturnedAt"></span></p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    $('#incomingTable').DataTable();

    $('.edit-btn').on('click', function () {
      const requestId = $(this).data('request-id');
      $('#buttons-' + requestId).hide();
      $('#edit-form-' + requestId).removeClass('d-none');
    });

    $('.cancel-edit').on('click', function () {
      const requestId = $(this).data('request-id');
      $('#edit-form-' + requestId).addClass('d-none');
      $('#buttons-' + requestId).show();
    });

    $('.view-btn').on('click', function () {
      const data = $(this).data('request');
      $('#viewRequester').text(data.requester);
      $('#viewAsset').text(data.asset_name + ' - ' + data.description);
      $('#viewQuantity').text(data.quantity);
      $('#viewRemarks').text(data.return_remarks || '—');
      $('#viewReturnedAt').text(data.returned_at ? new Date(data.returned_at).toLocaleString() : '—');
    });
  });
</script>
</body>
</html>
