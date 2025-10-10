<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$form_id = isset($_GET['id']) ? intval($_GET['id']) : null;


// Fetch all ICS forms
$sql = "SELECT f.id AS ics_id, f.entity_name, f.fund_cluster, f.ics_no,
               f.received_from_name, f.received_from_position,
               f.received_by_name, f.received_by_position, f.created_at,
               o.office_name
        FROM ics_form f
        LEFT JOIN offices o ON f.office_id = o.id
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);


$ics_forms = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ics_forms[$row['ics_id']] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved ICS Records</title>
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
        <h4 class="fw-bold"><i class="bi bi-folder-check"></i> Saved ICS Records</h4>
      </div>

      <?php if (!empty($ics_forms)): ?>
        <div class="card">
          <div class="card-header">
            <i class="bi bi-list-check"></i> All Saved ICS
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
              <table id="icsTable" class="table">
                <thead class="text-center">
                  <tr>
                    <th>ICS No</th>
                    <th>Office</th>
                    <th>Date Created</th>
                    <th>Received From</th>
                    <th>Received By</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($ics_forms as $ics): ?>
                    <tr>
                      <td class="text-center"><?php 
                        $officeDisplay = $ics['office_name'] ?: ($ics['entity_name'] ?? '');
                        $icsNoDisplay = preg_replace('/\{OFFICE\}|OFFICE/', $officeDisplay, $ics['ics_no'] ?? '');
                        echo htmlspecialchars($icsNoDisplay);
                      ?></td>
                      <td><?php $officeDisplay = $ics['office_name'] ?: ($ics['entity_name'] ?? ''); echo htmlspecialchars($officeDisplay !== '' ? $officeDisplay : 'N/A'); ?></td>
                      <td class="text-center"><?= date('F d, Y', strtotime($ics['created_at'])) ?></td>
                      <td><?= htmlspecialchars($ics['received_from_name']) ?></td>
                      <td><?= htmlspecialchars($ics['received_by_name']) ?></td>
                      <td class="text-center">
                        <a href="view_ics.php?id=<?= $ics['ics_id'] ?>&form_id=<?php echo $form_id ?>" class="btn btn-sm btn-primary">
                          <i class="bi bi-eye"></i> View
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
        <div class="alert alert-info">No ICS records found.</div>
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
      $('#icsTable').DataTable({
        order: [[2, 'asc']] // sort by Date Created column (index adjusted after column removals)
      });
    });
  </script>
</body>
</html>
