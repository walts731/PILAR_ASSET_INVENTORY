<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$form_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch all IIRUP forms (latest first)
$sql = "SELECT f.id AS iirup_id,
               f.accountable_officer,
               f.designation,
               f.office,
               f.header_image,
               f.created_at
        FROM iirup_form f
        ORDER BY f.id DESC";
$result = $conn->query($sql);

$iirup_forms = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $iirup_forms[$row['iirup_id']] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved IIRUP Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <?php if ($form_id): ?>
    <base href="saved_iirup.php?id=<?= $form_id ?>">
  <?php endif; ?>
</head>
<body>
  <?php include 'includes/sidebar.php' ?>
  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><i class="bi bi-folder-check"></i> Saved IIRUP Records</h4>
        <?php if ($form_id): ?>
          <a href="forms.php?id=<?= $form_id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Forms
          </a>
        <?php endif; ?>
      </div>

      <?php if (!empty($iirup_forms)): ?>
        <div class="card">
          <div class="card-header">
            <i class="bi bi-list-check"></i> All Saved IIRUP
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
              <table id="iirupTable" class="table">
                <thead class="text-center">
                  <tr>
                    <th>Accountable Officer</th>
                    <th>Designation</th>
                    <th>Office</th>
                    <th>Date Created</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($iirup_forms as $iirup): ?>
                    <tr>
                      <td><?= htmlspecialchars($iirup['accountable_officer'] ?? '') ?></td>
                      <td><?= htmlspecialchars($iirup['designation'] ?? '') ?></td>
                      <td><?= htmlspecialchars($iirup['office'] ?? 'N/A') ?></td>
                      <td><?= $iirup['created_at'] ? date('F d, Y g:i A', strtotime($iirup['created_at'])) : 'N/A' ?></td>
                      <td class="text-center">
                        <a href="view_iirup.php?id=<?= $iirup['iirup_id'] ?>&form_id=<?= urlencode((string)$form_id) ?>" class="btn btn-sm btn-primary me-1">
                          <i class="bi bi-eye"></i> View
                        </a>
                        <a href="generate_iirup_pdf.php?id=<?= $iirup['iirup_id'] ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Open PDF">
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
        <div class="alert alert-info">No IIRUP records found.</div>
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
      $('#iirupTable').DataTable({
        order: [[0, 'desc']],
        columnDefs: [
          { orderable: false, targets: -1 } // Disable sorting on the last column (Action)
        ]
      });
      
      // Ensure all links have the form_id parameter if it exists
      if (<?= $form_id ? 'true' : 'false' ?>) {
        $('a[href^="view_iirup.php"]').each(function() {
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
