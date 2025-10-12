<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

// Page meta
$page = 'borrow';

// Status filter (default to 'borrowed')
$statusOptions = ['pending', 'approved', 'borrowed', 'returned', 'declined', 'cancelled', 'all'];
$status = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : 'borrowed';
if (!in_array($status, $statusOptions, true)) {
  $status = 'borrowed';
}

// Determine ordering column based on status
switch ($status) {
  case 'pending':
    $orderField = 'br.created_at';
    break;
  case 'approved':
  case 'borrowed':
    $orderField = 'br.approved_at';
    break;
  case 'returned':
    $orderField = 'br.returned_at';
    break;
  default:
    $orderField = 'br.created_at';
}

// Build base SQL
$baseSql = "SELECT 
              br.id AS borrow_id,
              br.quantity,
              br.approved_at,
              br.due_date,
              br.returned_at,
              br.created_at,
              br.status,
              a.id AS asset_id,
              a.description AS asset_description,
              a.unit,
              o.office_name,
              u.fullname AS borrower_name
            FROM borrow_requests br
            JOIN assets a ON a.id = br.asset_id
            JOIN offices o ON o.id = br.office_id
            JOIN users u ON u.id = br.user_id";

if ($status !== 'all') {
  $sql = $baseSql . " WHERE br.status = ? ORDER BY " . $orderField . " DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s', $status);
} else {
  $sql = $baseSql . " ORDER BY " . $orderField . " DESC";
  $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$count = is_array($rows) ? count($rows) : 0;
$stmt->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrowing Log</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Borrowing Log</h4>
        <div class="d-flex align-items-center gap-2">
          <form method="get" class="d-flex align-items-center">
            <label for="status" class="me-2 fw-semibold">Status:</label>
            <select name="status" id="status" class="form-select form-select-sm" onchange="this.form.submit()">
              <?php foreach ($statusOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>" <?= $status === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <span class="badge bg-primary"><?= (int)$count ?> <?= $status === 'all' ? 'items' : htmlspecialchars($status) . ' items' ?></span>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table id="borrowedAssetsTable" class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Date & Time</th>
                  <th>Item</th>
                  <th>Quantity & Unit</th>
                  <th>Office</th>
                  <th>Borrower</th>
                  <th>Date of Return & Time</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($rows)): ?>
                  <?php foreach ($rows as $r): ?>
                    <tr>
                      <?php
                        // Determine primary event date based on status
                        $rowStatus = strtolower($r['status'] ?? '');
                        $primaryDate = null;
                        if ($rowStatus === 'pending') {
                          $primaryDate = $r['created_at'] ?? null;
                        } elseif ($rowStatus === 'approved' || $rowStatus === 'borrowed') {
                          $primaryDate = $r['approved_at'] ?? null;
                        } elseif ($rowStatus === 'returned') {
                          $primaryDate = $r['returned_at'] ?? null;
                        } else {
                          $primaryDate = $r['created_at'] ?? null;
                        }
                      ?>
                      <td><small class="text-muted"><?= $primaryDate ? date('M j, Y g:i A', strtotime($primaryDate)) : '—' ?></small></td>
                      <td><?= htmlspecialchars($r['asset_description']) ?></td>
                      <td><span class="badge bg-light text-dark"><?= (int)$r['quantity'] ?> <?= htmlspecialchars($r['unit'] ?? '') ?></span></td>
                      <td>
                        <div class="text-truncate" style="max-width:160px" title="<?= htmlspecialchars($r['office_name'] ?? '—') ?>">
                          <?= htmlspecialchars($r['office_name'] ?? '—') ?>
                        </div>
                      </td>
                      <td>
                        <div class="text-truncate" style="max-width:200px" title="<?= htmlspecialchars($r['borrower_name'] ?? '—') ?>">
                          <?= htmlspecialchars($r['borrower_name'] ?? '—') ?>
                        </div>
                      </td>
                      <?php
                        // Show returned_at if available, otherwise due_date, otherwise em dash
                        $returnOrDue = $r['returned_at'] ?? null;
                        if (!$returnOrDue) {
                          $returnOrDue = $r['due_date'] ?? null;
                        }
                      ?>
                      <td><small class="text-muted"><?= $returnOrDue ? date('M j, Y g:i A', strtotime($returnOrDue)) : '—' ?></small></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
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
  <script src="js/dashboard.js"></script>
  <script>
    $(function() {
      $('#borrowedAssetsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']], // Date & Time
        language: {
          search: 'Search borrowed records:',
          lengthMenu: 'Show _MENU_ entries',
          info: 'Showing _START_ to _END_ of _TOTAL_ borrowed records',
          emptyTable: 'No borrowed records found',
          zeroRecords: 'No matching records found'
        },
        columnDefs: []
      });
    });
  </script>
</body>
</html>
