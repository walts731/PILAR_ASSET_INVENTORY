<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

// Page meta
$page = 'borrow';

// Fetch active borrowed records (borrow_requests)
$stmt = $conn->prepare("SELECT 
                          br.id AS borrow_id,
                          br.quantity,
                          br.approved_at,
                          NULL AS due_date,
                          a.id AS asset_id,
                          a.description AS asset_description,
                          a.unit,
                          o.office_name,
                          u.fullname AS borrower_name
                        FROM borrow_requests br
                        JOIN assets a ON a.id = br.asset_id
                        JOIN offices o ON o.id = br.office_id
                        JOIN users u ON u.id = br.user_id
                        WHERE br.status = 'borrowed'
                        ORDER BY br.approved_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$count = is_array($rows) ? count($rows) : 0;
$stmt->close();

// If no live data, provide sample rows for testing the table UI
if (empty($rows)) {
  $now = date('Y-m-d H:i:s');
  $rows = [
    [
      'borrow_id' => 0,
      'quantity' => 1,
      'approved_at' => $now,
      'due_date' => null,
      'asset_id' => 0,
      'asset_description' => 'Sample Laptop Dell XPS 15',
      'unit' => 'unit',
      'office_name' => 'Office A',
      'borrower_name' => 'John Doe',
    ],
    [
      'borrow_id' => 0,
      'quantity' => 3,
      'approved_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
      'due_date' => null,
      'asset_id' => 0,
      'asset_description' => 'Sample Projector Epson EB-S41',
      'unit' => 'pcs',
      'office_name' => 'Office B',
      'borrower_name' => 'Jane Smith',
    ],
    [
      'borrow_id' => 0,
      'quantity' => 5,
      'approved_at' => date('Y-m-d H:i:s', strtotime('-5 days 3 hours')),
      'due_date' => null,
      'asset_id' => 0,
      'asset_description' => 'Sample Tablet Samsung Galaxy Tab',
      'unit' => 'pcs',
      'office_name' => 'Office C',
      'borrower_name' => 'Alice Johnson',
    ],
  ];
  $count = count($rows);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrowed Assets</title>
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
        <h4 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Borrowed Assets</h4>
        <span class="badge bg-primary"><?= (int)$count ?> items</span>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table id="borrowedAssetsTable" class="table table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Date & Time</th>
                  <th>Borrow Item</th>
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
                      <td><small class="text-muted"><?= $r['approved_at'] ? date('M j, Y g:i A', strtotime($r['approved_at'])) : '—' ?></small></td>
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
                      <td><small class="text-muted"><?= $r['due_date'] ? date('M j, Y g:i A', strtotime($r['due_date'])) : '—' ?></small></td>
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
