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
?>

<style>
  /* Professional App Footer */
  .app-footer {
    background: #ffffff;
    border-top: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 -2px 12px rgba(0,0,0,0.04);
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
    width: 28px; height: 28px; border-radius: 6px; background: #fff; display: inline-flex;
    align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.12);
  }
  .app-footer .brand-logo img { width: 100%; height: 100%; object-fit: cover; }
  .app-footer .brand-title { font-weight: 600; color: #1f2d3d; }
  .app-footer .muted { color: #6c7a89; font-size: 0.925rem; }

  .app-footer .links a {
    color: #3a7ae0; text-decoration: none; font-weight: 500;
  }
  .app-footer .links a:hover { text-decoration: underline; }

  .app-footer .icons i { color: #6c7a89; font-size: 1.1rem; }
  .app-footer .icons a { padding: 6px; border-radius: 8px; }
  .app-footer .icons a:hover { background: rgba(0,0,0,0.06); }

  /* Dark mode support */
  .dark-mode .app-footer {
    background: #1f1f1f !important;
    border-top: 1px solid #333 !important;
    box-shadow: 0 -2px 12px rgba(0,0,0,0.6);
  }
  .dark-mode .app-footer .brand-title { color: #e8edf5 !important; }
  .dark-mode .app-footer .muted { color: #b7c1cd !important; }
  .dark-mode .app-footer .links a { color: #8cb6ff !important; }
  .dark-mode .app-footer .icons i { color: #cfd6df !important; }
</style>

<footer class="app-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
  <div class="brand">
    <span class="brand-logo">
      <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" />
    </span>
    <span class="brand-title"><?= htmlspecialchars($system['system_title']) ?></span>
    <span class="ms-2 muted">© <?= $currentYear ?> • All rights reserved</span>
  </div>

  <div class="links d-flex align-items-center gap-3">
    <a href="#" title="Privacy Policy">Privacy</a>
    <a href="#" title="Terms of Service">Terms</a>
    <a href="#" title="Help & Support">Help</a>
  </div>

  <div class="icons d-flex align-items-center gap-1">
    <a href="#" title="Notifications"><i class="bi bi-bell"></i></a>
    <a href="#" title="Settings"><i class="bi bi-gear"></i></a>
    <a href="#" title="Back to top" onclick="window.scrollTo({top:0, behavior:'smooth'})"><i class="bi bi-arrow-up-circle"></i></a>
  </div>
</footer>
