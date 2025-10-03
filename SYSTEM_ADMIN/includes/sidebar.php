<?php
require_once '../connect.php';

$system = [
    'logo' => '../img/default-logo.png',
    'system_title' => 'Inventory System'
];

// Fetch system settings
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}

// Get current page
$page = basename($_SERVER['PHP_SELF'], ".php");
?>

<!-- SIDEBAR STYLES -->
<style>
    .sidebar {
        height: 100vh;
        overflow: hidden;
    }

    .sidebar .scrollable-nav {
        overflow-y: auto;
        height: calc(100vh - 60px);
        padding-right: 8px;
        scrollbar-width: none;
    }

    .sidebar .scrollable-nav::-webkit-scrollbar {
        display: none;
    }

    .sidebar a {
        width: 100%;
        text-align: left;
        padding: 10px 15px;
        border-radius: 10px;
        margin: 5px 0;
        transition: all 0.3s ease;
        display: block;
        color: #000;
        text-decoration: none;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background-color: #e0e0e0;
        color: #000;
    }

    .sidebar { height: 100vh; overflow: hidden; }
    .sidebar .scrollable-nav { overflow-y: auto; height: calc(100vh - 60px); padding-right: 8px; scrollbar-width: none; }
    .sidebar .scrollable-nav::-webkit-scrollbar { display: none; }
    .sidebar a { width: 100%; text-align: left; padding: 10px 15px; border-radius: 10px; margin: 5px 0; transition: all 0.3s ease; display: block; color: #000; text-decoration: none; }
    .sidebar a:hover, .sidebar a.active { background-color: #e0e0e0; color: #000; }

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

    /* Header */
    .sidebar .sidebar-brand {
        text-align: center;
        padding: 16px 10px 6px;
    }

    .sidebar .brand-logo-wrap {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18), inset 0 0 0 6px rgba(255, 255, 255, 0.4);
        margin-bottom: 8px;
    }

    .sidebar .brand-logo-wrap img {
        width: 38px; height: 38px; object-fit: contain;
        filter: none;
    }

    .sidebar .brand-title {
        color: #fff;
        line-height: 1.1;
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

<!-- SIDEBAR HTML -->
<div class="sidebar d-flex flex-column justify-content-between">
    <!-- Scrollable top part -->
    <div class="scrollable-nav px-3">
        <!-- Brand header -->
        <div class="sidebar-brand" aria-label="Application brand">
            <div class="brand-logo-wrap">
                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo">
            </div>
            <div class="brand-title">
                <strong><?= htmlspecialchars($system['system_title']) ?></strong>
                <span>System Admin</span>
            </div>
        </div>
        <hr>

        <!-- Sidebar Navigation -->
        <nav class="nav flex-column">
            <a href="../SYSTEM_ADMIN/system_admin_dashboard.php" class="<?= ($page == 'system_admin_dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="../SYSTEM_ADMIN/manage_forms.php" class="<?= ($page == 'manage_forms') ? 'active' : '' ?>">
                <i class="bi bi-file-text"></i> Forms
            </a>

            <a href="../SYSTEM_ADMIN/manage_offices.php" class="<?= ($page == 'manage_offices') ? 'active' : '' ?>">
                <i class="bi bi-building"></i> Offices
            </a>

            <a href="../SYSTEM_ADMIN/manage_units.php" class="<?= ($page == 'manage_units') ? 'active' : '' ?>">
                <i class="bi bi-bounding-box"></i> Units
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
            <a href="../SYSTEM_ADMIN/manage_categories.php" class="<?= ($page == 'manage_categories') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="../SYSTEM_ADMIN/manage_tag_format.php" class="<?= ($page == 'manage_tag_format') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i> Manage Tag Format
            </a>
            <a href="../SYSTEM_ADMIN/simple_backup.php" class="<?= ($page == 'simple_backup') ? 'active' : '' ?>">
                <i class="bi bi-hdd"></i> Backup
            </a>
            <a href="../SYSTEM_ADMIN/drive_settings.php" class="<?= ($page == 'drive_settings') ? 'active' : '' ?>">
                <i class="bi bi-google"></i> Drive Backup
            </a>
            <?php endif; ?>
            <a href="../SYSTEM_ADMIN/manage_legal_documents.php" class="<?= ($page == 'manage_legal_documents') ? 'active' : '' ?>">
                <i class="bi bi-file-text-fill"></i> Legal Documents
            </a>
            <a href="../SYSTEM_ADMIN/par_ics_settings.php" class="<?= ($page == 'par_ics_settings') ? 'active' : '' ?>">
                <i class="bi bi-sliders"></i> PAR/ICS Settings
            </a>

            <a href="../SYSTEM_ADMIN/edit_system.php" class="<?= ($page == 'System') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> System
            </a>

            <a href="../SYSTEM_ADMIN/user_roles.php" class="<?= ($page == 'user_roles') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> User Roles
            </a>
    </div>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>