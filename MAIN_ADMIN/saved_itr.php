<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$form_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch all ITR forms (latest first)
$sql = "SELECT f.itr_id,
               f.entity_name,
               f.from_accountable_officer,
               f.to_accountable_officer,
               f.itr_no,
               f.date,
               f.transfer_type,
               f.reason_for_transfer
        FROM itr_form f
        ORDER BY f.itr_id DESC";
$result = $conn->query($sql);

$itr_forms = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $itr_forms[$row['itr_id']] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved ITR Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <?php if ($form_id): ?>
    <base href="saved_itr.php?id=<?= $form_id ?>">
  <?php endif; ?>
</head>
<body>
  <?php include 'includes/sidebar.php' ?>
  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><i class="bi bi-folder-check"></i> Saved ITR Records</h4>
        <div>
          <?php if ($form_id): ?>
            <a href="forms.php?id=<?= $form_id ?>" class="btn btn-outline-secondary me-2">
              <i class="bi bi-arrow-left"></i> Back to Forms
            </a>
          <?php endif; ?>
          <a href="itr_form.php<?= $form_id ? '?id=' . $form_id : '' ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New ITR
          </a>
        </div>
      </div>

      <?php if (!empty($itr_forms)): ?>
        <div class="card">
          <div class="card-header">
            <i class="bi bi-list-check"></i> All Saved ITR Forms
          </div>
          <div class="card-body">
            <?php if (!empty($_SESSION['flash'])): ?>
              <?php
                $flash = $_SESSION['flash'];
                $type = isset($flash['type']) ? strtolower($flash['type']) : 'info';
                $allowed = ['primary','secondary','success','danger','warning','info','light','dark'];
                if (!in_array($type, $allowed, true)) { $type = 'info'; }
              ?>
              <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message'] ?? 'Action completed.') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <div class="table-responsive">
              <table id="itrTable" class="table">
                <thead class="text-center">
                  <tr>
                    <th>ITR No.</th>
                    <th>Entity Name</th>
                    <th>From Officer</th>
                    <th>To Officer</th>
                    <th>Transfer Type</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($itr_forms as $itr): ?>
                    <tr>
                      <td><?= htmlspecialchars($itr['itr_no'] ?? '') ?></td>
                      <td><?= htmlspecialchars($itr['entity_name'] ?? '') ?></td>
                      <td><?= htmlspecialchars($itr['from_accountable_officer'] ?? '') ?></td>
                      <td><?= htmlspecialchars($itr['to_accountable_officer'] ?? '') ?></td>
                      <td>
                        <span class="badge bg-info">
                          <?= htmlspecialchars($itr['transfer_type'] ?? 'N/A') ?>
                        </span>
                      </td>
                      <td><?= $itr['date'] ? date('M d, Y', strtotime($itr['date'])) : 'N/A' ?></td>
                      <td class="text-center">
                        <a href="view_itr.php?id=<?= $itr['itr_id'] ?><?= $form_id ? '&form_id=' . $form_id : '' ?>" class="btn btn-sm btn-primary">
                          <i class="bi bi-eye"></i> View
                        </a>
                        <a href="print_itr.php?id=<?= $itr['itr_id'] ?>" class="btn btn-sm btn-success" target="_blank">
                          <i class="bi bi-printer"></i> Print
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i> No ITR records found. 
          <a href="itr_form.php<?= $form_id ? '?id=' . $form_id : '' ?>" class="alert-link">Create your first ITR form</a>.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
  <script>
    $(document).ready(function () {
      $('#itrTable').DataTable({
        order: [[0, 'asc']], // Order by ITR ID (descending - latest first)
        columnDefs: [
          { orderable: false, targets: -1 } // Disable sorting on the last column (Action)
        ],
        pageLength: 25,
        responsive: true
      });
      
      // Ensure all links have the form_id parameter if it exists
      if (<?= $form_id ? 'true' : 'false' ?>) {
        $('a[href^="view_itr.php"]').each(function() {
          const $this = $(this);
          let href = $this.attr('href');
          if (href.indexOf('form_id=') === -1) {
            href += (href.indexOf('?') === -1 ? '?' : '&') + 'form_id=<?= $form_id ?>';
            $this.attr('href', href);
          }
        });
      }
    });
  </script>
</body>
</html>
