<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  header('Location: ../index.php');
  exit();
}

// CSRF
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// Google Drive status
gdrive_ensure_tables($conn);
$gdConfigured = gdrive_is_configured($conn);
$gdConnected = gdrive_is_connected($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Simple Backup</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container-fluid py-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0"><i class="bi bi-database-down me-2"></i> Backup</h4>
        
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-download me-2"></i>Download Backup (SQL)</div>
            <div class="card-body">
              <p class="text-muted">Generate and download a full SQL backup of the current database without any external tools.</p>
              <a class="btn btn-primary" href="download_simple_backup.php"><i class="bi bi-cloud-download me-1"></i> Download .sql</a>
              <div class="small text-muted mt-2">This may take a while depending on database size.</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-google me-2"></i>Google Drive Sync</div>
            <div class="card-body">
              <p class="text-muted">Optionally connect a Google account to automatically sync backup .sql files in the <code>/backups/</code> folder to Google Drive. This uses OAuth2 and stores only the refresh token securely in the database.</p>
              <div class="mb-2">
                <span class="me-2">Configured:</span>
                <span class="badge text-bg-<?= $gdConfigured ? 'success' : 'secondary' ?>"><?= $gdConfigured ? 'Yes' : 'No' ?></span>
              </div>
              <div class="mb-3">
                <span class="me-2">Connected:</span>
                <span class="badge text-bg-<?= $gdConnected ? 'success' : 'secondary' ?>"><?= $gdConnected ? 'Yes' : 'No' ?></span>
              </div>
              <a class="btn btn-outline-primary" href="drive_settings.php">
                <i class="bi bi-gear"></i> Configure Google Drive
              </a>
              <div class="small text-muted mt-2">When connected, each backup performed by the system will attempt to upload to your configured Drive folder, and results will be logged in audit logs.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
