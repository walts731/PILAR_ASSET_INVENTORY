<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Set office_id if not already set
if (!isset($_SESSION['office_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($office_id);
  if ($stmt->fetch()) {
    $_SESSION['office_id'] = $office_id;
  }
  $stmt->close();
}

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// Handle filter by office
$selected_office_id = $_GET['office_id'] ?? $_SESSION['office_id'];

// Fetch office list
$offices = [];
$office_stmt = $conn->prepare("SELECT id, office_name FROM offices");
$office_stmt->execute();
$office_result = $office_stmt->get_result();
while ($row = $office_result->fetch_assoc()) {
  $offices[] = $row;
}
$office_stmt->close();

// Fetch assets
$query = "SELECT id, asset_name, description, quantity, unit, value, acquisition_date FROM assets WHERE office_id = ? AND status = 'Available'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $selected_office_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Borrow Assets</title>
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
          <i class="bi bi-table"></i> List of Available Assets
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <form method="GET" class="d-inline">
            <select name="office_id" class="form-select form-select-sm" onchange="this.form.submit()">
              <?php foreach ($offices as $office): ?>
                <option value="<?= $office['id'] ?>" <?= ($selected_office_id == $office['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($office['office_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>

          <form id="borrowForm" action="process_borrow.php<?= isset($selected_office_id) ? '?office_id=' . $selected_office_id : ''; ?>" method="POST" class="d-inline">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="bi bi-check2-square"></i> Borrow Selected
            </button>
          </form>

          <a href="borrow_requests.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-eye"></i> View Borrow Requests
          </a>
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table id="borrowTable" class="table table-striped align-middle">
            <thead class="table-light">
              <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>Asset Name</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Value</th>
                <th>Acquired</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td>
                    <input type="checkbox" name="selected_assets[]" form="borrowForm" value="<?= $row['id'] . '|' . $selected_office_id ?>">
                  </td>
                  <td><?= htmlspecialchars($row['asset_name']) ?></td>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><?= $row['quantity'] ?></td>
                  <td><?= $row['unit'] ?></td>
                  <td>₱<?= number_format($row['value'], 2) ?></td>
                  <td><?= date('F j, Y', strtotime($row['acquisition_date'])) ?></td>
                  <td>
                    <a href="view_asset.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">
                      <i class="bi bi-eye"></i> View
                    </a>
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
    $('#borrowTable').DataTable();

    $('#selectAll').on('click', function () {
      $('input[name="selected_assets[]"]').prop('checked', this.checked);
    });
  });
</script>
</body>
</html>
