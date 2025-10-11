<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Set office_id if not set
if (!isset($_SESSION['office_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($office_id);
  if ($stmt->fetch()) {
    $_SESSION['office_id'] = $office_id;
  }
  $stmt->close();

  // TABLES
  // SELECT `id`, `user_id`, `filename`, `generated_at` FROM `generated_reports` 
  // SELECT `id`, `username`, `fullname`, `email`, `password`, `role`, `status`, `created_at`, `reset_token`, `reset_token_expiry`, `office_id`, `profile_picture`, `session_timeout` FROM `users` 
  // SELECT `id`, `office_name`, `icon` FROM `offices` 
  // SELECT `id`, `category_name`, `type` FROM `categories` 
  // SELECT `id`, `asset_name`, `category`, `description`, `quantity`, `unit`, `status`, `acquisition_date`, `office_id`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type` FROM `assets` 
  // SELECT `id`, `template_name`, `header_html`, `subheader_html`, `footer_html`, `left_logo_path`, `right_logo_path`, `created_at`, `updated_at`, `created_by`, `updated_by` FROM `report_templates` 
}

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// Include audit helper for availability checks (and potential future logging)
require_once __DIR__ . '/../includes/audit_helper.php';

// Helper: check if a table exists
function table_exists($conn, $table_name) {
  try {
    $tbl = $conn->real_escape_string($table_name);
    $res = $conn->query("SHOW TABLES LIKE '{$tbl}'");
    return $res && $res->num_rows > 0;
  } catch (Exception $e) {
    return false;
  }
}

// Data for "At a Glance"
$metrics = [
  'active_users' => 0,
  'total_users' => 0,
  'failed_logins_24h' => 0,
  'errors_24h' => 0,
  'db_size_mb' => null,
  'last_backup' => null,
  'recent_audit' => []
];

// Total registered users
try {
  $res = $conn->query("SELECT COUNT(*) AS c FROM users");
  if ($res && ($row = $res->fetch_assoc())) { $metrics['total_users'] = (int)$row['c']; }
} catch (Exception $e) {}

// Active users currently logged in (approx): users whose latest action in audit_logs is LOGIN within last 30 minutes and not followed by LOGOUT
if (isAuditLoggingAvailable()) {
  try {
    $sql = "
      SELECT COUNT(DISTINCT al.user_id) AS c
      FROM audit_logs al
      WHERE al.action = 'LOGIN'
        AND al.created_at >= (NOW() - INTERVAL 30 MINUTE)
        AND al.user_id IS NOT NULL
        AND NOT EXISTS (
          SELECT 1 FROM audit_logs al2
          WHERE al2.user_id = al.user_id
            AND al2.created_at > al.created_at
            AND al2.action = 'LOGOUT'
        )
    ";
    $res = $conn->query($sql);
    if ($res && ($row = $res->fetch_assoc())) { $metrics['active_users'] = (int)$row['c']; }
  } catch (Exception $e) {}

  // Failed logins in last 24h
  try {
    $res = $conn->query("SELECT COUNT(*) AS c FROM audit_logs WHERE action='LOGIN_FAILED' AND created_at >= (NOW() - INTERVAL 1 DAY)");
    if ($res && ($row = $res->fetch_assoc())) { $metrics['failed_logins_24h'] = (int)$row['c']; }
  } catch (Exception $e) {}

  // Error entries in last 24h
  try {
    $res = $conn->query("SELECT COUNT(*) AS c FROM audit_logs WHERE action='ERROR' AND created_at >= (NOW() - INTERVAL 1 DAY)");
    if ($res && ($row = $res->fetch_assoc())) { $metrics['errors_24h'] = (int)$row['c']; }
  } catch (Exception $e) {}

  // Recent audit trail (latest 5)
  try {
    $res = $conn->query("SELECT username, action, module, details, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 5");
    if ($res) {
      while ($row = $res->fetch_assoc()) { $metrics['recent_audit'][] = $row; }
    }
  } catch (Exception $e) {}
}

// Database size (MB)
try {
  $res = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
  if ($res && ($row = $res->fetch_assoc())) { $metrics['db_size_mb'] = $row['size_mb']; }
} catch (Exception $e) {}

// Last backup info if a backups table exists
try {
  if (table_exists($conn, 'backups')) {
    $res = $conn->query("SELECT created_at AS last_backup, status AS last_backup_status, filename, storage FROM backups ORDER BY created_at DESC LIMIT 1");
    if ($res && ($row = $res->fetch_assoc())) {
      $metrics['last_backup'] = $row['last_backup'];
      $metrics['last_backup_status'] = $row['last_backup_status'] ?? null;
      $metrics['last_backup_filename'] = $row['filename'] ?? null;
      $metrics['last_backup_storage'] = $row['storage'] ?? null;
    }
  }
} catch (Exception $e) {}

// Calculate next scheduled backup (30 days after last_backup or from now)
if (!empty($metrics['last_backup'])) {
  $metrics['next_backup'] = date('Y-m-d H:i:s', strtotime($metrics['last_backup'] . ' +30 days'));
} else {
  $metrics['next_backup'] = date('Y-m-d H:i:s', strtotime('+30 days'));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title></title>
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

      <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center">
              <div class="me-3 text-primary"><i class="bi bi-people-fill fs-2"></i></div>
              <div>
                <div class="fw-semibold">Active Users (last 30m)</div>
                <div class="fs-4 fw-bold"><?php echo (int)$metrics['active_users']; ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center">
              <div class="me-3 text-info"><i class="bi bi-person-badge-fill fs-2"></i></div>
              <div>
                <div class="fw-semibold">Total Registered Users</div>
                <div class="fs-4 fw-bold"><?php echo (int)$metrics['total_users']; ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex align-items-center">
              <div class="me-3 text-danger"><i class="bi bi-shield-lock-fill fs-2"></i></div>
              <div>
                <div class="fw-semibold">Failed Logins (24h)</div>
                <div class="fs-4 fw-bold"><?php echo (int)$metrics['failed_logins_24h']; ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="me-2 text-info"><i class="bi bi-activity fs-4"></i></div>
                <div class="fw-semibold">System Health</div>
              </div>
              <div class="small text-muted">DB Size</div>
              <div class="fw-semibold mb-2"><?php echo $metrics['db_size_mb'] !== null ? $metrics['db_size_mb'] . ' MB' : 'N/A'; ?></div>
              <div class="small text-muted">Last Backup</div>
              <div class="fw-semibold mb-2" id="lastBackupText"><?php echo $metrics['last_backup'] ? date('M d, Y h:i A', strtotime($metrics['last_backup'])) : 'Not available'; ?></div>
              <?php if (!empty($metrics['last_backup_status'])): ?>
                <div class="small text-muted">Last Backup Status</div>
                <div class="mb-2">
                  <span class="badge text-bg-<?php echo ($metrics['last_backup_status']==='success') ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($metrics['last_backup_status']); ?></span>
                </div>
              <?php endif; ?>
              <div class="small text-muted">Next Scheduled Backup</div>
              <div class="fw-semibold mb-2" id="nextBackupText"><?php echo $metrics['next_backup'] ? date('M d, Y h:i A', strtotime($metrics['next_backup'])) : 'Not available'; ?></div>
              <?php if (!empty($metrics['last_backup_storage'])): ?>
                <div class="small text-muted">Cloud Sync</div>
                <div class="mb-2" id="cloudSyncText">
                  <?php if ($metrics['last_backup_storage'] === 'both'): ?>
                    <span class="badge text-bg-success">Uploaded</span>
                  <?php else: ?>
                    <span class="badge text-bg-secondary">Local only</span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div class="small text-muted">Cloud Sync</div>
                <div class="mb-2" id="cloudSyncText"><span class="badge text-bg-secondary">Local only</span></div>
              <?php endif; ?>
              <div class="small text-muted">Errors (24h)</div>
              <div class="fw-semibold text-<?php echo ((int)$metrics['errors_24h'] > 0) ? 'danger' : 'success'; ?>"><?php echo (int)$metrics['errors_24h']; ?></div>
              <hr class="my-3" />
              <div class="d-grid">
                <button class="btn btn-primary mb-2" id="localBackupBtn">
                  <span class="me-1"><i class="bi bi-hdd"></i></span> Backup Now
                </button>
                <div class="small text-muted mb-2" id="localBackupStatus" style="min-height:1rem;"></div>
                
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-12 col-lg-8">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
              <div class="d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Recent Audit Trail</span>
                <?php if (isAuditLoggingAvailable()) : ?>
                  <span class="badge text-bg-light">Latest 5</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-body">
              <?php if (!isAuditLoggingAvailable()) : ?>
                <div class="alert alert-warning mb-0">Audit logging table not found. Please run <code>create_audit_logs_table.sql</code> to enable this feature.</div>
              <?php elseif (empty($metrics['recent_audit'])) : ?>
                <div class="text-muted">No recent activities.</div>
              <?php else : ?>
                <ul class="list-group list-group-flush">
                  <?php foreach ($metrics['recent_audit'] as $log) : ?>
                    <li class="list-group-item px-0 d-flex align-items-start">
                      <div class="me-3 text-secondary"><i class="bi bi-dot fs-3 lh-1"></i></div>
                      <div class="flex-grow-1">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                          <span class="badge text-bg-secondary"><?php echo htmlspecialchars($log['action']); ?></span>
                          <span class="badge text-bg-light border"><?php echo htmlspecialchars($log['module']); ?></span>
                          <span class="text-muted small"><?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?></span>
                        </div>
                        <div class="mt-1 fw-semibold"><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></div>
                        <?php
                          $details_text = $log['details'] ?? '';
                          if ($details_text && $current_office_name) {
                            $details_text = str_replace('{OFFICE}', $current_office_name, $details_text);
                          }
                        ?>
                        <div class="text-muted small"><?php echo htmlspecialchars($details_text); ?></div>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Notes</div>
            <div class="card-body">
              <ul class="mb-0 small text-muted">
                <li><strong>Active Users</strong> is approximated from recent LOGIN events without subsequent LOGOUT.</li>
                <li><strong>Failed Logins</strong> and <strong>Errors</strong> are derived from <code>audit_logs</code>.</li>
                <li><strong>Last Backup</strong> appears if a <code>backups</code> table is available.</li>
              </ul>
            </div>
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
    (function(){
      const btn = document.getElementById('localBackupBtn');
      if (!btn) return;
      const statusEl = document.getElementById('localBackupStatus');
      const lastEl = document.getElementById('lastBackupText');
      const nextEl = document.getElementById('nextBackupText');

      function setLoading(on){
        if (on){
          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
          if (statusEl) statusEl.textContent = '';
        } else {
          btn.disabled = false;
          btn.innerHTML = '<span class="me-1"><i class="bi bi-hdd"></i></span> Backup Now';
        }
      }

      btn.addEventListener('click', function(){
        setLoading(true);
        fetch('auto_local_backup.php', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }})
          .then(r => r.json())
          .then(data => {
            if (data.success){
              if (statusEl) statusEl.textContent = 'Backup completed: ' + (data.filename || '');
              if (data.last_backup && lastEl){
                const d = new Date(data.last_backup.replace(' ', 'T'));
                lastEl.textContent = d.toLocaleString();
              }
              if (data.next_backup && nextEl){
                const d2 = new Date(data.next_backup.replace(' ', 'T'));
                nextEl.textContent = d2.toLocaleString();
              }
              const cloudEl = document.getElementById('cloudSyncText');
              if (cloudEl){
                if (data.cloud_sync === 'success') {
                  cloudEl.innerHTML = '<span class="badge text-bg-success">Uploaded</span>';
                } else if (data.cloud_sync === 'failed') {
                  cloudEl.innerHTML = '<span class="badge text-bg-danger">Upload failed</span>';
                } else {
                  cloudEl.innerHTML = '<span class="badge text-bg-secondary">Local only</span>';
                }
              }
            } else {
              if (statusEl) statusEl.textContent = 'Backup failed: ' + (data.message || 'Unknown error');
            }
          })
          .catch(err => {
            if (statusEl) statusEl.textContent = 'Backup failed: ' + err;
          })
          .finally(() => setLoading(false));
      });
    })();

    // Auto monthly local backup check (no extra config). Runs backup only if due.
    (function(){
      const lastEl = document.getElementById('lastBackupText');
      const nextEl = document.getElementById('nextBackupText');
      fetch('auto_local_backup.php?check=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(r => r.json())
        .then(data => {
          if (data && data.ran && data.last_backup) {
            const d = new Date((data.last_backup+'').replace(' ', 'T'));
            if (lastEl) lastEl.textContent = d.toLocaleString();
            if (data.next_backup){
              const d2 = new Date((data.next_backup+'').replace(' ', 'T'));
              if (nextEl) nextEl.textContent = d2.toLocaleString();
            }
          } else if (data && data.next_backup && nextEl) {
            const d2 = new Date((data.next_backup+'').replace(' ', 'T'));
            nextEl.textContent = d2.toLocaleString();
          }
        })
        .catch(() => {});
    })();
  </script>
</body>

</html>

