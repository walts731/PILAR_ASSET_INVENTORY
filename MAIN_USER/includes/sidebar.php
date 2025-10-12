<?php
// Ensure brand settings are available for the sidebar include
if (!isset($system) || !is_array($system)) {
  // Make sure we have a DB connection (use absolute path from this include file)
  if (!isset($conn) || !($conn instanceof mysqli)) {
    $connPath = __DIR__ . '/../../connect.php';
    if (file_exists($connPath)) {
      require_once $connPath;
    }
  }

  // Defaults
  $system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
  ];

  // Try to load from DB
  if (isset($conn) && $conn instanceof mysqli) {
    if ($res = $conn->query("SELECT logo, system_title FROM system LIMIT 1")) {
      if ($res && $res->num_rows > 0) {
        $system = $res->fetch_assoc();
      }
      $res->close();
    }
  }
}
?>
<!-- SIDEBAR STYLES -->
<style>
    /* Container */
    .sidebar {
        height: 100vh;
        overflow: hidden;
        background: linear-gradient(180deg, #0b5ed7 0%, #0a58ca 45%, #0948a6 100%);
        color: #eaf2ff;
        border-right: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    /* Scrollable area */
    .sidebar .scrollable-nav {
        overflow-y: auto;
        height: calc(100vh - 64px);
        padding-right: 8px;
        scrollbar-width: none;
    }

    .sidebar .scrollable-nav::-webkit-scrollbar { display: none; }

    .sidebar .sidebar-brand {
        text-align: center;
        padding: 16px 10px 6px;
    }

    .sidebar .brand-logo-wrap {
        width: 77px;
        height: 77px;
        border-radius: 50%;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.2), inset 0 0 0 8px rgba(255, 255, 255, 0.4);
        margin-bottom: 8px;
    }

    .sidebar .brand-logo-wrap img {
        width: 75px; height: 75px; object-fit: contain;
        filter: none;
    }

    .sidebar .brand-title strong {
        font-weight: 700;
        font-size: 0.98rem;
        letter-spacing: 0.3px;
        display: block;
        text-shadow: 0 1px 2px rgba(0,0,0,0.25);
    }
    .sidebar .brand-title span {
        display: block;
        font-size: 0.72rem;
        opacity: 0.9;
        color: #dfeaff;
        margin-top: 2px;
    }

    .sidebar hr {
        border-color: rgba(255, 255, 255, 0.2);
        opacity: 1;
        margin: 0.75rem 0 1rem;
    }

    /* Section label */
    .sidebar .nav-section {
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.75);
        padding: 4px 6px;
        margin: 8px 4px 4px;
        opacity: 0.9;
    }

    /* Links */
    .sidebar a {
        width: 100%;
        text-align: left;
        padding: 10px 14px;
        border-radius: 10px;
        margin: 4px 0;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.08s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #eaf2ff;
        text-decoration: none;
    }

    .sidebar a .bi { opacity: 0.95; font-size: 1.05rem; }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
    }

    .sidebar a:active { transform: scale(0.995); }

    .sidebar a.active {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
    }

    /* Submenu */
    .sidebar .collapse { border-left: 2px solid rgba(255, 255, 255, 0.18); margin-left: 6px; }
    .sidebar .collapse .nav-link {
        padding: 8px 12px;
        border-radius: 8px;
        color: #e9f2ff;
        margin: 3px 0;
    }
    /* Specific font styling for Inventory and Forms submenus */
    #inventorySubMenu .nav-link,
    #formsSubMenu .nav-link {
        font-size: 0.92rem;
        font-weight: 500;
        letter-spacing: 0.1px;
    }
    .sidebar .collapse .nav-link:hover { background: rgba(255, 255, 255, 0.12); }
    .sidebar .collapse .nav-link.active { background: rgba(255, 255, 255, 0.22); }

    /* Dropdown caret rotation */
    .sidebar a[aria-expanded="true"] .bi-caret-down-fill { transform: rotate(180deg); }
    .sidebar .bi-caret-down-fill { transition: transform 0.2s ease; }

    /* Logout area */
    .sidebar .border-top { border-top: 1px solid rgba(255, 255, 255, 0.2) !important; background: rgba(0, 0, 0, 0.05); }
    .sidebar .border-top .nav-link { color: #ffd9d9; }
    .sidebar .border-top .nav-link:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; border-radius: 8px; }

    /* Focus visibility */
    .sidebar a:focus { outline: 2px solid rgba(255, 255, 255, 0.35); outline-offset: 2px; }
</style>

<div class="sidebar d-flex flex-column justify-content-between">
    <div>
        <!-- Brand header -->
        <div class="sidebar-brand" aria-label="Application brand">
            <div class="brand-logo-wrap">
                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo">
            </div>
            <div class="brand-title">
                <strong><?= htmlspecialchars($system['system_title']) ?></strong>
                <span>User</span>
            </div>
        </div>
        <hr>
        <?php
        $page = basename($_SERVER['PHP_SELF'], ".php"); // detects current PHP filename
        ?>
        <nav class="nav flex-column">
            <a href="../MAIN_USER/user_dashboard.php" class="nav-link <?= ($page == 'user_dashboard') ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Inventory
            </a>
            <a href="settings.php" class="nav-link <?= ($page == 'settings') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
            <a href="../logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </nav>
    </div>
</div>