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
    <div class="scrollable-nav px-3">
        <!-- Brand header -->
        <div class="sidebar-brand" aria-label="Application brand">
            <div class="brand-logo-wrap">
                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo">
            </div>
            <div class="brand-title">
                <strong><?= htmlspecialchars($system['system_title']) ?></strong>
                <span>Office Admin</span>
            </div>
        </div>
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

            <!-- Borrowing Menu -->
            <a class="<?= (in_array($page, ['borrow', 'inter_department_borrow', 'view_inter_dept_cart', 'inter_dept_borrow_requests'])) ? 'active' : '' ?>"
               data-bs-toggle="collapse" href="#borrowSubMenu" role="button"
               aria-expanded="<?= (in_array($page, ['borrow', 'inter_department_borrow', 'view_inter_dept_cart', 'inter_dept_borrow_requests'])) ? 'true' : 'false' ?>"
               aria-controls="borrowSubMenu">
                <i class="bi bi-arrow-left-right"></i> Borrowing
                <i class="bi bi-caret-down-fill float-end"></i>
            </a>
            <div class="collapse ps-4 <?= (in_array($page, ['borrow', 'inter_department_borrow', 'view_inter_dept_cart', 'inter_dept_borrow_requests'])) ? 'show' : '' ?>" id="borrowSubMenu">
                <a class="nav-link <?= ($page == 'borrow') ? 'active' : '' ?>" href="borrow.php">
                    <i class="bi bi-arrow-left-right"></i> Within Office
                </a>
                <a class="nav-link <?= ($page == 'inter_department_borrow' || $page == 'view_inter_dept_cart') ? 'active' : '' ?>" href="inter_department_borrow.php">
                    <i class="bi bi-building"></i> Inter-Department
                    <?php 
                    // Get count of pending requests for the current user
                    $pending_count = 0;
                    if (isset($_SESSION['inter_dept_cart'])) {
                        $pending_count = count($_SESSION['inter_dept_cart']);
                    }
                    if ($pending_count > 0): ?>
                        <span class="badge bg-danger float-end"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <a class="nav-link <?= ($page == 'inter_dept_borrow_requests') ? 'active' : '' ?>" href="inter_dept_borrow_requests.php">
                    <i class="bi bi-list-check"></i> My Requests
                </a>
            </div>
            
            <a href="usage.php" class="<?= ($page == 'usage') ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i> Usage
            </a>
            <a href="reports.php" class="<?= ($page == 'reports') ? 'active' : '' ?>"><i class="bi bi-bar-chart-line"></i> Reports</a>
            <a href="user.php" class="<?= ($page == 'user') ? 'active' : '' ?>"><i class="bi bi-person"></i> Users</a>
            
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
