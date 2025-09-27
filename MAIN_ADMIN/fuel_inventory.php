<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch system settings for title/logo if needed
$system = [
  'logo' => '../img/default-logo.png',
  'system_title' => 'Inventory System'
];
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
  $system = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fuel Inventory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="css/dashboard.css" rel="stylesheet" />
  <style>
    .page-header { background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%); border: 1px solid #e9ecef; border-radius: .75rem; }
    .page-header .title { font-weight: 600; }
  </style>
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container-fluid px-0 mb-3">
      <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
            <i class="bi bi-fuel-pump text-primary fs-4"></i>
          </div>
          <div>
            <div class="h4 mb-0 title">Fuel Inventory</div>
            <div class="text-muted small">Manage fuel stocks, receipts, and usage</div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="alert alert-info d-flex align-items-start" role="alert">
        <div>
          <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Placeholder Page</div>
          <div class="small mb-0">This is a starter page for Fuel Inventory. You can expand it with tables and forms (e.g., fuel receipts, issuances, and balance tracking).</div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <p class="text-muted mb-0">Coming soon: fuel stock list, transactions, and reports.</p>
        </div>
      </div>
    </div>

    <?php include 'includes/footer.php'; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
