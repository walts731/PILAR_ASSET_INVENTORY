<?php
require_once '../connect.php';

// Ensure office_id is set
if (!isset($_SESSION['office_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    if ($stmt->fetch()) {
        $_SESSION['office_id'] = $office_id;
    }
    $stmt->close();
}

$office_id = $_SESSION['office_id'];

// Fetch system settings
$system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
];
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}

// Current page
$page = basename($_SERVER['PHP_SELF'], ".php");

// Fetch categories that have assets in this office
$categories = [];
$categoryQuery = "
    SELECT c.id, c.category_name
    FROM categories c
    JOIN assets a ON a.category = c.id
    WHERE a.office_id = ? AND a.quantity > 0
    GROUP BY c.id
    ORDER BY c.category_name
";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$stmt->close();
?>

<!-- SIDEBAR STYLES -->
<style>
    .sidebar { height: 100vh; overflow: hidden; }
    .sidebar .scrollable-nav { overflow-y: auto; height: calc(100vh - 60px); padding-right: 8px; scrollbar-width: none; }
    .sidebar .scrollable-nav::-webkit-scrollbar { display: none; }
    .sidebar a { width: 100%; text-align: left; padding: 10px 15px; border-radius: 10px; margin: 5px 0; transition: all 0.3s ease; display: block; color: #000; text-decoration: none; }
    .sidebar a:hover, .sidebar a.active { background-color: #e0e0e0; color: #000; }
</style>

<!-- SIDEBAR HTML -->
<div class="sidebar d-flex flex-column justify-content-between">
    <div class="scrollable-nav px-3">
        <!-- Logo -->
        <h5 class="text-center d-flex align-items-center justify-content-center mt-3">
            <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" style="width:30px; height:30px; margin-right:10px;">
            <?= htmlspecialchars($system['system_title']) ?>
        </h5>
        <hr>

        <!-- Navigation -->
        <nav class="nav flex-column">
            <a href="../OFFICE_ADMIN/admin_dashboard.php" class="<?= ($page == 'admin_dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <!-- Inventory dropdown -->
            <a class="<?= ($page == 'inventory' || $page == 'inventory_category' || $page == 'infrastructure_inventory') ? 'active' : '' ?>"
               data-bs-toggle="collapse" href="#inventorySubMenu" role="button"
               aria-expanded="<?= ($page == 'inventory' || $page == 'inventory_category' || $page == 'infrastructure_inventory') ? 'true' : 'false' ?>"
               aria-controls="inventorySubMenu">
                <i class="bi bi-box-seam"></i> Inventory
                <i class="bi bi-caret-down-fill float-end"></i>
            </a>
            <div class="collapse ps-4 <?= ($page == 'inventory' || $page == 'inventory_category' || $page == 'infrastructure_inventory') ? 'show' : '' ?>" id="inventorySubMenu">
                <a class="nav-link <?= ($page == 'inventory') ? 'active' : '' ?>" href="inventory.php">All</a>

                <?php foreach ($categories as $cat): ?>
                    <a class="nav-link <?= (isset($_GET['category']) && $_GET['category'] == $cat['id'] && $page == 'inventory_category') ? 'active' : '' ?>"
                       href="inventory_category.php?category=<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </a>
                <?php endforeach; ?>

                
            </div>

            <a href="borrow.php" class="<?= ($page == 'borrow') ? 'active' : '' ?>"><i class="bi bi-arrow-left-right"></i> Borrow</a>
            <a href="reports.php" class="<?= ($page == 'reports') ? 'active' : '' ?>"><i class="bi bi-bar-chart-line"></i> Reports</a>
            <a href="user.php" class="<?= ($page == 'user') ? 'active' : '' ?>"><i class="bi bi-person"></i> Users</a>
            <a href="asset_archive.php" class="<?= ($page == 'asset_archive') ? 'active' : '' ?>"><i class="bi bi-archive"></i> Archive</a>
            <a href="about.php" class="<?= ($page == 'about') ? 'active' : '' ?>"><i class="bi bi-info-circle"></i> About</a>
            <a href="settings.php" class="<?= ($page == 'settings') ? 'active' : '' ?>"><i class="bi bi-gear"></i> Settings</a>
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
            <div class="modal-body">Are you sure you want to log out?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>
