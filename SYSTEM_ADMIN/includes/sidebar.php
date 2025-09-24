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
</style>

<!-- SIDEBAR HTML -->
<div class="sidebar d-flex flex-column justify-content-between">
    <!-- Scrollable top part -->
    <div class="scrollable-nav px-3">
        <!-- Logo and title -->
        <h5 class="text-center d-flex align-items-center justify-content-center mt-3">
            <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo"
                style="width: 30px; height: 30px; margin-right: 10px;" />
            <?= htmlspecialchars($system['system_title']) ?>
        </h5>
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
            <a href="../SYSTEM_ADMIN/tag_formats.php" class="<?= ($page == 'tag_formats') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i> Tag Formats
            </a>
            <a href="../SYSTEM_ADMIN/simple_backup.php" class="<?= ($page == 'simple_backup') ? 'active' : '' ?>">
                <i class="bi bi-hdd"></i> Backup
            </a>
            <a href="../SYSTEM_ADMIN/drive_settings.php" class="<?= ($page == 'drive_settings') ? 'active' : '' ?>">
                <i class="bi bi-google"></i> Drive Backup
            </a>
            <?php endif; ?>

            <a href="../SYSTEM_ADMIN/edit_system.php" class="<?= ($page == 'System') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> System
            </a>

        </nav>
    </div>

    <!-- Logout -->
    <div class="p-3 border-top">
        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="bi bi-box-arrow-right"></i> Logout
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