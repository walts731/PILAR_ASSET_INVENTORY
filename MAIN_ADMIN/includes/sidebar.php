<?php
$system = [
    'logo' => '../img/default-logo.png', // fallback
    'system_title' => 'Inventory System'
];

$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}
?>
<div class="sidebar d-flex flex-column justify-content-between">
    <div>
        <h5 class="text-center d-flex align-items-center justify-content-center">
            <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo"
                style="width: 30px; height: 30px; margin-right: 10px;" />
            <?= htmlspecialchars($system['system_title']) ?>
        </h5>

        <hr />
        <?php
        $page = basename($_SERVER['PHP_SELF'], ".php"); // detects current PHP filename
        ?>
        <nav class="nav flex-column">
            <a href="../MAIN_ADMIN/admin_dashboard.php" class="nav-link <?= ($page == 'admin_dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="inventory.php" class="nav-link <?= ($page == 'inventory') ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Inventory
            </a>
            <a href="borrow.php" class="nav-link <?= ($page == 'borrow') ? 'active' : '' ?>">
                <i class="bi bi-arrow-left-right"></i> Borrow
            </a>
            <a href="reports.php" class="nav-link <?= ($page == 'reports') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>
            <a href="user.php" class="nav-link <?= ($page == 'user') ? 'active' : '' ?>">
                <i class="bi bi-person"></i> Users
            </a>
            <a href="forms.php" class="nav-link <?= ($page == 'templates') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> Forms
            </a>
            <a href="asset_archive.php" class="nav-link <?= ($page == 'asset_archive') ? 'active' : '' ?>">
                <i class="bi bi-archive"></i> Archive
            </a>
            <a href="about.php" class="nav-link <?= ($page == 'about') ? 'active' : '' ?>">
                <i class="bi bi-info-circle"></i> About
            </a>
            <a href="settings.php" class="nav-link <?= ($page == 'settings') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </nav>
    </div>
</div>

<!-- Logout Confirmation Modal -->
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
