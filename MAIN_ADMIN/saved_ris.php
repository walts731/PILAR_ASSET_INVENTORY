<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$form_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch all RIS forms
$sql = "SELECT f.id AS ris_id, f.ris_no, f.sai_no, f.division, f.responsibility_center, 
               f.responsibility_code, f.date, f.reason_for_transfer, f.created_at,
               o.office_name, f.requested_by_name, f.approved_by_name, f.issued_by_name, f.received_by_name
        FROM ris_form f
        LEFT JOIN offices o ON f.office_id = o.id
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);

$ris_forms = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $ris_forms[$row['ris_id']] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved RIS Records</title>
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
        <h4 class="fw-bold"><i class="bi bi-folder-check"></i> Saved RIS Records</h4>
      </div>

      <?php if (!empty($ris_forms)): ?>
        <div class="table-responsive">
          <table id="risTable" class="table">
            <thead class="text-center">
              <tr>
                <th>RIS No</th>
                <th>Office</th>
                <th>Date</th>
                <th>Requested By</th>
                <th>Approved By</th>
                <th>Issued By</th>
                <th>Received By</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ris_forms as $ris): ?>
                <tr>
                  <td class="text-center"><?php 
                    $officeDisplay = $ris['office_name'] ?: ($ris['division'] ?? '');
                    $risNoDisplay = preg_replace('/\{OFFICE\}|OFFICE/', $officeDisplay, $ris['ris_no'] ?? '');
                    echo htmlspecialchars($risNoDisplay);
                  ?></td>
                  <td><?php $officeDisplay = $ris['office_name'] ?: ($ris['division'] ?? ''); echo htmlspecialchars($officeDisplay !== '' ? $officeDisplay : 'N/A'); ?></td>
                  <td class="text-center"><?= date('F d, Y', strtotime($ris['date'])) ?></td>
                  <td><?= htmlspecialchars($ris['requested_by_name']) ?></td>
                  <td><?= htmlspecialchars($ris['approved_by_name']) ?></td>
                  <td><?= htmlspecialchars($ris['issued_by_name']) ?></td>
                  <td><?= htmlspecialchars($ris['received_by_name']) ?></td>
                  <td class="text-center">
                    <a href="view_ris.php?id=<?= $ris['ris_id'] ?>&form_id=<?= urlencode((string)$form_id) ?>" class="btn btn-sm btn-primary me-1">
                      <i class="bi bi-eye"></i> View
                    </a>
                    <a href="generate_ris_pdf.php?id=<?= $ris['ris_id'] ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Open PDF">
                      <i class="bi bi-filetype-pdf"></i> PDF
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-info">No RIS records found.</div>
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
      $('#risTable').DataTable({
        order: [[6, 'asc']] // sort by Date column
      });
    });
  </script>
</body>
</html>
