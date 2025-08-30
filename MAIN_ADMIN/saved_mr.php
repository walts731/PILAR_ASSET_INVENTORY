<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch MR details (only required columns)
$sql = "SELECT mr_id, inventory_tag, description, office_location, person_accountable, created_at
        FROM mr_details
        ORDER BY created_at DESC";
$result = $conn->query($sql);

$mr_records = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mr_records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved MR Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main">
  <?php include 'includes/topbar.php'; ?>

  <div class="container-fluid py-4">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-list-check"></i> Saved Property Tags Records</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($mr_records)): ?>
          <div class="table-responsive">
            <table id="mrTable" class="table align-middle">
              <thead class="text-center">
                <tr>
                  <th>Inventory Tag</th>
                  <th>Description</th>
                  <th>Office</th>
                  <th>Person Accountable</th>
                  <th>Date Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mr_records as $mr): ?>
                  <tr>
                    <td><?= htmlspecialchars($mr['inventory_tag']) ?></td>
                    <td><?= htmlspecialchars($mr['description']) ?></td>
                    <td><?= htmlspecialchars($mr['office_location']) ?></td>
                    <td><?= htmlspecialchars($mr['person_accountable']) ?></td>
                    <td><?= date('F d, Y', strtotime($mr['created_at'])) ?></td>
                    <td class="text-center">
                      <a href="view_mr.php?id=<?= urlencode($mr['mr_id']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info mb-0">No MR records found.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
<script>
  $(document).ready(function () {
    $('#mrTable').DataTable({
      order: [[4, 'desc']], // Sort by Date Created
      pageLength: 10
    });
  });
</script>
</body>
</html>
