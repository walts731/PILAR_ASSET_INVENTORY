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