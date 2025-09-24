<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';
require_once __DIR__ . '/../includes/audit_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  header('Location: ../index.php');
  exit();
}

gdrive_ensure_tables($conn);

if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$settings = gdrive_get_settings($conn);
$connected = gdrive_is_connected($conn);

$msg = null; $err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf']) || !hash_equals($csrf, $_POST['csrf'])) {
    $err = 'Invalid CSRF token';
  } else {
    $data = [
      'client_id' => trim($_POST['client_id'] ?? ''),
      'client_secret' => trim($_POST['client_secret'] ?? ''),
      'redirect_uri' => trim($_POST['redirect_uri'] ?? ''),
      'folder_id' => trim($_POST['folder_id'] ?? ''),
    ];
    if (gdrive_save_settings($conn, $data)) {
      $msg = 'Settings saved.';
      $settings = gdrive_get_settings($conn);
    } else {
      $err = 'Failed to save settings.';
    }
  }
}

if (isset($_GET['connected']) && $_GET['connected'] === '1') {
  $msg = 'Google Drive connected successfully.';
  $connected = true;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Google Drive Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
<?php include 'includes/topbar.php'; ?>

<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0"><i class="bi bi-google"></i> Google Drive Backup</h4>
    <a class="btn btn-outline-secondary" href="system_admin_dashboard.php"><i class="bi bi-arrow-left"></i> Back</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Client ID</label>
            <input class="form-control" name="client_id" value="<?= htmlspecialchars($settings['client_id'] ?? '') ?>" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Client Secret</label>
            <input class="form-control" name="client_secret" value="<?= htmlspecialchars($settings['client_secret'] ?? '') ?>" />
          </div>
          <div class="col-md-8">
            <label class="form-label">Redirect URI</label>
            <input class="form-control" name="redirect_uri" value="<?= htmlspecialchars($settings['redirect_uri'] ?? '') ?>" />
            <div class="form-text">Example: <?= htmlspecialchars((isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/drive_oauth_callback.php') ?></div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Drive Folder ID (optional)</label>
            <input class="form-control" name="folder_id" value="<?= htmlspecialchars($settings['folder_id'] ?? '') ?>" />
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
          <?php if (gdrive_is_configured($conn)): ?>
            <a class="btn btn-success" href="drive_oauth_start.php"><i class="bi bi-link-45deg"></i> <?= $connected ? 'Reconnect' : 'Connect' ?></a>
          <?php else: ?>
            <button class="btn btn-success" type="button" disabled title="Save settings first"><i class="bi bi-link-45deg"></i> Connect</button>
          <?php endif; ?>
        </div>
        <div class="mt-2">
          Status: <?= $connected ? '<span class="badge text-bg-success">Connected</span>' : '<span class="badge text-bg-secondary">Not Connected</span>' ?>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
</body>
</html>
