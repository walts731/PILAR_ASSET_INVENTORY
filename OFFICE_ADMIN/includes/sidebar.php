<?php
// includes/sidebar.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: show PHP errors while debugging. Comment out in production.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure DB connection (path is relative to this sidebar file)
if (!isset($conn) || !$conn) {
    require_once __DIR__ . '/../connect.php';
}

// If user is not logged in, redirect to login (adjust path if needed)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../index.php'); // change if your login is elsewhere
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user's office_id and fullname (using bind_result for wide compatibility)
$fullname = '';
$db_office_id = null;
$stmt = $conn->prepare('SELECT office_id, fullname FROM users WHERE id = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($db_office_id, $db_fullname);
    if ($stmt->fetch()) {
        if (!isset($_SESSION['office_id']) || empty($_SESSION['office_id'])) {
            $_SESSION['office_id'] = $db_office_id;
        }
        $fullname = $db_fullname ?? '';
    }
    $stmt->close();
} else {
    error_log('Sidebar: prepare failed for users query: ' . $conn->error);
}

// Make sure we have $office_id variable for later use
$office_id = $_SESSION['office_id'] ?? null;

// Fetch system settings (simple query)
$system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
];
$res = $conn->query('SELECT logo, system_title FROM system LIMIT 1');
if ($res && $res->num_rows > 0) {
    $system = $res->fetch_assoc();
}

// Current page for active link highlighting
$page = basename($_SERVER['PHP_SELF'], '.php');

// Fetch categories that have assets in this office (only if $office_id is available)
$categories = [];
if (!empty($office_id)) {
    $categoryQuery = "
        SELECT c.id, c.category_name
        FROM categories c
        JOIN assets a ON a.category = c.id
        WHERE a.office_id = ? AND a.quantity > 0
        GROUP BY c.id
        ORDER BY c.category_name
    ";
    $stmt = $conn->prepare($categoryQuery);
    if ($stmt) {
        $stmt->bind_param('i', $office_id);
        $stmt->execute();
        // use bind_result loop for compatibility
        $stmt->bind_result($cat_id, $cat_name);
        while ($stmt->fetch()) {
            $categories[] = ['id' => $cat_id, 'category_name' => $cat_name];
        }
        $stmt->close();
    } else {
        error_log('Sidebar: prepare failed for categories query: ' . $conn->error);
    }
}

// Debug helper: an HTML comment you can view via "View Source"
// (safe to leave while debugging; remove or comment out in production)
echo "<!-- SIDEBAR DEBUG: user_id={$user_id} | office_id=" . htmlspecialchars($office_id ?? 'NULL') . " | fullname=" . htmlspecialchars($fullname) . " -->";
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
            <a href="usage.php" class="<?= ($page == 'usage') ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i> Usage
            </a>
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
