<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$form_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch all PAR forms
$sql = "SELECT f.id AS par_id, f.entity_name, f.fund_cluster, f.par_no,
               f.position_office_left, f.position_office_right,
               f.received_by_name, f.issued_by_name,
               f.date_received_left, f.date_received_right, f.created_at,
               o.office_name
        FROM par_form f
        LEFT JOIN offices o ON f.office_id = o.id
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);

$par_forms = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $par_forms[$row['par_id']] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved PAR Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><i class="bi bi-folder-check"></i> Saved PAR Records</h4>
      </div>

      <?php if (!empty($par_forms)): ?>
        <div class="card">
          <div class="card-header">
            <i class="bi bi-list-check"></i> All Saved PAR
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
              <table id="parTable" class="table">
                <thead class="text-center">
                  <tr>
                    <th>PAR No</th>
                    <th>Office</th>
                    <th>Date Created</th>
                    <th>Received By</th>
                    <th>Issued By</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($par_forms as $par): ?>
                    <tr>
                      <td class="text-center"><?php 
                        $officeDisplay = $par['office_name'] ?: ($par['entity_name'] ?? ''); 
                        $parNoDisplay = preg_replace('/\{OFFICE\}|OFFICE/', $officeDisplay, $par['par_no'] ?? '');
                        echo htmlspecialchars($parNoDisplay);
                      ?></td>
                      <td><?php $officeDisplay = $par['office_name'] ?: ($par['entity_name'] ?? ''); echo htmlspecialchars($officeDisplay !== '' ? $officeDisplay : 'N/A'); ?></td>
                      <td class="text-center"><?= date('F d, Y', strtotime($par['created_at'])) ?></td>
                      <td class="text-center">
                        <div><strong><?= htmlspecialchars($par['received_by_name'] ?? '') ?></strong></div>
                      </td>
                      <td class="text-center">
                        <div><strong><?= htmlspecialchars($par['issued_by_name'] ?? '') ?></strong></div>
                      </td>
                      <td class="text-center">
                        <a href="view_par.php?id=<?= $par['par_id'] ?>&form_id=<?= urlencode((string)$form_id) ?>" class="btn btn-sm btn-primary me-1">
                          <i class="bi bi-eye"></i> View
                        </a>
                        <a href="generate_par_pdf.php?id=<?= $par['par_id'] ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Open PDF">
                          <i class="bi bi-filetype-pdf"></i> PDF
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
        <div class="alert alert-info">No PAR records found.</div>
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
      $('#parTable').DataTable({
        order: [[2, 'desc']]
      });
    });
  </script>
</body>
</html>
