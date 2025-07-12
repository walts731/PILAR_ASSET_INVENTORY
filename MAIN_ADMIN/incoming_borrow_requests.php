<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$office_id = $_SESSION['office_id'];
$user_id = $_SESSION['user_id'];

// Fetch incoming borrow requests for assets owned by this office
$query = "
  SELECT br.id AS request_id, u.fullname AS requester, a.asset_name, a.description, a.unit, br.status, br.requested_at, o.office_name
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
        <div>
          <i class="bi bi-inbox"></i> Incoming Borrow Requests
        </div>
        
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-light">
              <tr>
                <th>Requester</th>
                <th>Office</th>
                <th>Asset</th>
                <th>Description</th>
                <th>Unit</th>
                <th>Requested At</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['requester']) ?></td>
                  <td><?= htmlspecialchars($row['office_name']) ?></td>
                  <td><?= htmlspecialchars($row['asset_name']) ?></td>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><?= htmlspecialchars($row['unit']) ?></td>
                  <td><?= date('F j, Y h:i A', strtotime($row['requested_at'])) ?></td>
                  <td>
                    <span class="badge bg-<?php
                      echo $row['status'] === 'accepted' ? 'success' :
                          ($row['status'] === 'rejected' ? 'danger' : 'warning');
                    ?>">
                      <?= ucfirst($row['status']) ?>
                    </span>
                  </td>
                  <td>
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
                    <?php else: ?>
                      <span class="text-muted small fst-italic">No actions</span>
                    <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    $('.table').DataTable();
  });
</script>
</body>
</html>
