<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch Red Tag details with related asset and user information
$sql = "SELECT rt.id, rt.red_tag_number, rt.description, rt.item_location, 
               rt.removal_reason, rt.action, rt.status, rt.date_received,
               u.fullname as tagged_by_name,
               a.property_no, a.office_id
        FROM red_tags rt
        LEFT JOIN users u ON rt.tagged_by = u.id
        LEFT JOIN assets a ON rt.asset_id = a.id
        ORDER BY rt.date_received DESC";
$result = $conn->query($sql);

$red_tag_records = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $red_tag_records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Red Tag Records</title>
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
        <h5 class="mb-0 fw-bold"><i class="bi bi-tag-fill text-danger"></i> Red Tag Records</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($red_tag_records)): ?>
          <div class="table-responsive">
            <table id="redTagTable" class="table align-middle">
              <thead class="text-center">
                <tr>
                  <th>Red Tag Number</th>
                  <th>Property Number</th>
                  <th>Description</th>
                  <th>Location</th>
                  <th>Removal Reason</th>
                  <th>Action</th>
                  <th>Tagged By</th>
                  <th>Date Created</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($red_tag_records as $red_tag): ?>
                  <tr>
                    <td class="fw-bold text-danger"><?= htmlspecialchars($red_tag['red_tag_number']) ?></td>
                    <td><?= htmlspecialchars($red_tag['property_no'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($red_tag['description']) ?></td>
                    <td><?= htmlspecialchars($red_tag['item_location']) ?></td>
                    <td>
                      <span class="badge bg-warning text-dark"><?= htmlspecialchars($red_tag['removal_reason']) ?></span>
                    </td>
                    <td>
                      <span class="badge bg-info text-dark"><?= htmlspecialchars($red_tag['action']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($red_tag['tagged_by_name'] ?? 'N/A') ?></td>
                    <td><?= date('F d, Y', strtotime($red_tag['date_received'])) ?></td>
                    <td class="text-center">
                      <a href="print_red_tag.php?id=<?= urlencode($red_tag['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle"></i> No Red Tag records found.
          </div>
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
    $('#redTagTable').DataTable({
      order: [[6, 'desc']], // Sort by Date Created
      pageLength: 10,
      columnDefs: [
        { orderable: false, targets: [7] } // Disable sorting on Action column
      ]
    });
  });
</script>
</body>
</html>
