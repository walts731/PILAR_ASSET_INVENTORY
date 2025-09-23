<?php
require_once __DIR__ . '/../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
  header('Location: ../index.php');
  exit();
}

// CSRF helpers
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// Ensure backups table exists
require_once __DIR__ . '/../includes/backup_helper.php';
ensure_backups_table($conn);

// Fetch backups
$backups = [];
$res = $conn->query("SELECT id, filename, path, size_bytes, storage, status, triggered_by, error_message, created_at FROM backups ORDER BY created_at DESC");
if ($res) {
  while ($row = $res->fetch_assoc()) { $backups[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Backups</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container-fluid py-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0"><i class="bi bi-hdd-network me-2"></i>Backups</h4>
        <a class="btn btn-outline-secondary" href="system_admin_dashboard.php"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <?php if (empty($backups)) : ?>
            <div class="text-muted">No backups found.</div>
          <?php else : ?>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Created</th>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Storage</th>
                    <th>Status</th>
                    <th>Triggered</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($backups as $b): ?>
                    <tr>
                      <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($b['created_at']))); ?></td>
                      <td class="text-break"><?php echo htmlspecialchars($b['filename']); ?></td>
                      <td><?php echo $b['size_bytes'] ? number_format($b['size_bytes']/1024/1024,2) . ' MB' : 'N/A'; ?></td>
                      <td><span class="badge text-bg-light border"><?php echo htmlspecialchars($b['storage']); ?></span></td>
                      <td>
                        <?php if ($b['status'] === 'success'): ?>
                          <span class="badge text-bg-success">success</span>
                        <?php else: ?>
                          <span class="badge text-bg-danger">failed</span>
                        <?php endif; ?>
                      </td>
                      <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($b['triggered_by']); ?></span></td>
                      <td>
                        <div class="btn-group" role="group">
                          <a class="btn btn-sm btn-primary" href="download_backup.php?id=<?php echo (int)$b['id']; ?>">
                            <i class="bi bi-download"></i>
                          </a>
                          <form method="post" action="delete_backup.php" onsubmit="return confirm('Delete this backup file and record?');">
                            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>" />
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>" />
                            <button class="btn btn-sm btn-outline-danger" type="submit">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        </div>
                        <?php if (!empty($b['error_message'])): ?>
                          <div class="small text-danger mt-1">Error: <?php echo htmlspecialchars($b['error_message']); ?></div>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
