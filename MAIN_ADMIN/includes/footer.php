<?php
require_once '../connect.php';

// Fetch system settings (logo and title) similar to sidebar
$system = [
  'logo' => '../img/default-logo.png',
  'system_title' => 'Inventory System'
];

$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
  $system = $result->fetch_assoc();
}

$currentYear = date('Y');

// Detect latest backup time (DB table if exists, else filesystem fallback)
$lastBackup = null;

// Check if a 'backups' table exists
$tblCheck = $conn->query("SHOW TABLES LIKE 'backups'");
if ($tblCheck && $tblCheck->num_rows > 0) {
  $bk = $conn->query("SELECT MAX(created_at) AS last_backup FROM backups");
  if ($bk && $bk->num_rows > 0) {
    $rowBk = $bk->fetch_assoc();
    if (!empty($rowBk['last_backup'])) {
      $lastBackup = date('M d, Y h:i A', strtotime($rowBk['last_backup']));
    }
  }
}
// Filesystem fallback: look in generated_backups/*.sql
if ($lastBackup === null) {
  $backupDir = realpath(__DIR__ . '/../generated_backups');
  if ($backupDir && is_dir($backupDir)) {
    $files = glob($backupDir . DIRECTORY_SEPARATOR . '*.sql');
    if ($files) {
      usort($files, function ($a, $b) {
        return filemtime($b) <=> filemtime($a);
      });
      $latest = $files[0] ?? null;
      if ($latest) {
        $lastBackup = date('M d, Y h:i A', filemtime($latest));
      }
    }
  }
}

// Fetch Privacy Policy and Terms from legal_documents
$privacy_policy = '';
$terms_of_service = '';

$checkTable = $conn->query("SHOW TABLES LIKE 'legal_documents'");
if ($checkTable && $checkTable->num_rows > 0) {
  $stmt = $conn->prepare("
    SELECT document_type, content 
    FROM legal_documents 
    WHERE is_active = 1
    ORDER BY version DESC, last_updated DESC
  ");
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    if ($row['document_type'] === 'privacy_policy' && empty($privacy_policy)) {
      $privacy_policy = $row['content'];
    } elseif ($row['document_type'] === 'terms_of_service' && empty($terms_of_service)) {
      $terms_of_service = $row['content'];
    }
  }
}



?>

<style>
  /* Professional App Footer */
  .app-footer {
    background: #ffffff;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.04);
    padding: 12px 16px;
    /* Standard footer (non-sticky). Let layout place it at the bottom. */
    position: static;
    z-index: 100;
    width: 100%;
    flex-shrink: 0;
  }

  .app-footer .brand {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .app-footer .brand-logo {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
  }

  .app-footer .brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .app-footer .brand-title {
    font-weight: 600;
    color: #1f2d3d;
  }

  .app-footer .muted {
    color: #6c7a89;
    font-size: 0.925rem;
  }

  .app-footer .links a {
    color: #3a7ae0;
    text-decoration: none;
    font-weight: 500;
  }

  .app-footer .links a:hover {
    text-decoration: underline;
  }

  .app-footer .icons i {
    color: #6c7a89;
    font-size: 1.1rem;
  }

  .app-footer .icons a {
    padding: 6px;
    border-radius: 8px;
  }

  .app-footer .icons a:hover {
    background: rgba(0, 0, 0, 0.06);
  }

  /* Dark mode support */
  .dark-mode .app-footer {
    background: #1f1f1f !important;
    box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.6);
  }

  .dark-mode .app-footer .brand-title {
    color: #e8edf5 !important;
  }

  .dark-mode .app-footer .muted {
    color: #b7c1cd !important;
  }

  .dark-mode .app-footer .links a {
    color: #8cb6ff !important;
  }

  .dark-mode .app-footer .icons a:hover {
    background: rgba(0, 0, 0, 0.06);
  }

  /* Footer functional chips */
  .app-footer .status {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .app-footer .chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f5f7fb;
    color: #334e68;
    border: 1px solid rgba(0, 0, 0, 0.06);
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 500;
  }

  .app-footer .chip i {
    font-size: 1rem;
    color: inherit;
  }

  .app-footer .chip.success {
    background: #e6f7ee;
    color: #127c45;
    border-color: rgba(18, 124, 69, 0.2);
  }

  .app-footer .chip.danger {
    background: #fdecec;
    color: #9d1c24;
    border-color: rgba(157, 28, 36, 0.2);
  }

  .dark-mode .app-footer .chip {
    background: #2a2f36;
    color: #d7e2f0;
    border-color: #3a3f46;
  }

  .dark-mode .app-footer .chip.success {
    background: #23342a;
    color: #8fe3b4;
    border-color: #2d5a40;
  }

  .dark-mode .app-footer .chip.danger {
    background: #3a2729;
    color: #ffb3ba;
    border-color: #5a2d33;
  }
</style>

<footer class="app-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
  <div class="brand">
    <span class="brand-logo">
      <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" />
    </span>
    <span class="brand-title"><?= htmlspecialchars($system['system_title']) ?></span>
    <span class="ms-2 muted">© <?= $currentYear ?> • All rights reserved</span>
  </div>

  <!-- Functional status area -->
  <div class="status">
    <span class="chip" id="footerClock" title="Current time">
      <i class="bi bi-clock"></i>
      <span class="value">--:-- --</span>
    </span>
    <span class="chip" id="footerNetStatus" title="Network status">
      <i class="bi bi-wifi"></i>
      <span class="value">Checking…</span>
    </span>
    <span class="chip" title="Last backup time">
      <i class="bi bi-hdd-stack"></i>
      <span class="value">Backup: <?= htmlspecialchars($lastBackup ?? 'N/A') ?></span>
    </span>
  </div>

  <div class="d-flex align-items-center gap-3">
    <div class="links d-none d-sm-flex align-items-center gap-3">
      <a href="#" title="Privacy Policy" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy</a>
      <a href="#" title="Terms of Service" data-bs-toggle="modal" data-bs-target="#termsModal">Terms</a>
      <a href="#" title="Help & Support" data-bs-toggle="modal" data-bs-target="#helpModal">Help</a>
    </div>
    <div class="icons d-flex align-items-center gap-1">
      <a href="#" title="Notifications" data-bs-toggle="tooltip"><i class="bi bi-bell"></i></a>
      <a href="settings.php" title="Settings" data-bs-toggle="tooltip"><i class="bi bi-gear"></i></a>
      <a href="#" title="Back to top" data-bs-toggle="tooltip" onclick="window.scrollTo({top:0, behavior:'smooth'})"><i class="bi bi-arrow-up-circle"></i></a>
    </div>
  </div>
</footer>

<!-- Help & Shortcuts Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="helpModalLabel">Help & Shortcuts</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <strong>Keyboard Shortcuts</strong>
          <ul class="mt-2 mb-0">
            <li>Space — Start/Stop scanner (on Scan QR)</li>
            <li>Ctrl+R — Reset scanner</li>
            <li>Ctrl+C — Switch camera</li>
            <li>Alt+T — Toggle theme (dark/light)</li>
            <li>/ — Focus search (if available)</li>
          </ul>
        </div>
        <div class="mb-2"><strong>Contact Support</strong></div>
        <div class="text-muted">For assistance, contact your system administrator.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($privacy_policy)): ?>
          <?= $privacy_policy ?>
        <?php else: ?>
          <p class="text-muted">No Privacy Policy has been published yet.</p>
        <?php endif; ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Terms of Service Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalLabel">Terms of Service</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($terms_of_service)): ?>
          <?= $terms_of_service ?>
        <?php else: ?>
          <p class="text-muted">No Terms of Service has been published yet.</p>
        <?php endif; ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function() {
    // Live clock
    function updateClock() {
      var el = document.querySelector('#footerClock .value');
      if (!el) return;
      var now = new Date();
      el.textContent = now.toLocaleString();
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Network status
    function setNetStatus() {
      var wrap = document.getElementById('footerNetStatus');
      if (!wrap) return;
      var val = wrap.querySelector('.value');
      var icon = wrap.querySelector('i');
      var online = navigator.onLine;
      wrap.classList.toggle('success', online);
      wrap.classList.toggle('danger', !online);
      val.textContent = online ? 'Online' : 'Offline';
      if (icon) icon.className = online ? 'bi bi-wifi' : 'bi bi-wifi-off';
    }
    setNetStatus();
    window.addEventListener('online', setNetStatus);
    window.addEventListener('offline', setNetStatus);

    // Tooltips (if Bootstrap loaded)
    if (window.bootstrap && typeof bootstrap.Tooltip === 'function') {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }

    // Theme toggle shortcut (Alt+T)
    document.addEventListener('keydown', function(e) {
      if (e.altKey && (e.key === 't' || e.key === 'T')) {
        var themeToggle = document.getElementById('themeToggle');
        if (themeToggle) themeToggle.click();
      }
    });
  })();
</script>