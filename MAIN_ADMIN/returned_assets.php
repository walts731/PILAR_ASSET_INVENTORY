<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch returned assets
$query = "
  SELECT br.id, a.asset_name, a.description, a.unit, br.quantity, br.requested_at, br.approved_at, br.returned_at, br.return_remarks, o.office_name
  FROM borrow_requests br
  JOIN assets a ON br.asset_id = a.id
  JOIN offices o ON br.office_id = o.id
  WHERE br.user_id = ? AND br.status = 'returned'
  ORDER BY br.returned_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Returned Assets</title>
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
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div><i class="bi bi-arrow-counterclockwise"></i> Returned Assets</div>
        <a href="borrowed_assets.php" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-box-arrow-up"></i> Back to Borrowed
        </a>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table id="returnedAssetsTable" class="table table-striped align-middle">
            <thead class="table-light">
              <tr>
                <th>Asset Name</th>
                <th>Description</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Office</th>
                <th>Requested At</th>
                <th>Approved At</th>
                <th>Returned At</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['asset_name']) ?></td>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><?= htmlspecialchars($row['unit']) ?></td>
                  <td><?= (int)$row['quantity'] ?></td>
                  <td><?= htmlspecialchars($row['office_name']) ?></td>
                  <td><?= date('F j, Y h:i A', strtotime($row['requested_at'])) ?></td>
                  <td><?= $row['approved_at'] ? date('F j, Y h:i A', strtotime($row['approved_at'])) : 'N/A' ?></td>
                  <td><?= $row['returned_at'] ? date('F j, Y h:i A', strtotime($row['returned_at'])) : 'N/A' ?></td>
                  <td><?= htmlspecialchars($row['return_remarks'] ?? '—') ?></td>
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
    $('#returnedAssetsTable').DataTable();
  });
</script>
</body>
</html>
