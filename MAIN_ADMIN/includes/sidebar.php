<div class="sidebar d-flex flex-column justify-content-between">
    <div>
        <h5 class="text-center d-flex align-items-center justify-content-center">
            <img src="../img/logo.jpg" alt="Logo" style="width: 30px; height: 30px; margin-right: 10px;" />
            Pilar Inventory
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
            <a href="../logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </nav>
    </div>
</div>
