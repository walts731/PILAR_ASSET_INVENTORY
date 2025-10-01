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

// Fetch inventory categories that have at least one asset record (type='asset', quantity > 0)
$categories = [];
$categorySql = "
    SELECT c.id, c.category_name
    FROM categories c
    WHERE EXISTS (
        SELECT 1 FROM assets a
        WHERE a.category = c.id AND a.type = 'asset' AND a.quantity > 0
    )
    ORDER BY c.category_name ASC
";
$categoryResult = $conn->query($categorySql);
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

$form_categories = [];
// Load individual forms and display their titles in the sidebar
$formCatResult = $conn->query("SELECT id, form_title FROM forms WHERE form_title IS NOT NULL AND form_title != '' ORDER BY form_title ASC");
if ($formCatResult && $formCatResult->num_rows > 0) {
    while ($row = $formCatResult->fetch_assoc()) {
        $form_categories[] = $row;
    }
}

// Determine if Forms dropdown should be active
$formActive = ($page == 'forms' && isset($_GET['id']));

// Session and permission check for Fuel-Only users
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isFuelOnlyUser = false;
$role = $_SESSION['role'] ?? null;
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($role === 'user' && $userId > 0) {
    if ($permStmt = $conn->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = 'fuel_inventory' LIMIT 1")) {
        $permStmt->bind_param('i', $userId);
        $permStmt->execute();
        $permStmt->store_result();
        $isFuelOnlyUser = $permStmt->num_rows > 0;
        $permStmt->close();
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
                <span>Main Admin</span>
            </div>
        </div>
        <hr>

        <!-- Sidebar Navigation -->
        <nav class="nav flex-column">
            <?php if ($isFuelOnlyUser): ?>
                <div class="nav-section">Fuel</div>
                <a href="fuel_inventory.php" class="<?= ($page == 'fuel_inventory') ? 'active' : '' ?>">
                    <i class="bi bi-fuel-pump"></i> Fuel Inventory
                </a>
            <?php else: ?>
            <a href="../MAIN_ADMIN/admin_dashboard.php" class="<?= ($page == 'admin_dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="nav-section">Inventory</div>
            <!-- Inventory dropdown -->
            <a class="<?= ($page == 'inventory' || $page == 'inventory_category' || $page == 'infrastructure_inventory') ? 'active' : '' ?>"
                data-bs-toggle="collapse" href="#inventorySubMenu" role="button"
                aria-expanded="<?= ($page == 'inventory' || $page == 'inventory_category' || $page == 'infrastructure_inventory') ? 'true' : 'false' ?>"
                aria-controls="inventorySubMenu">
                <i class="bi bi-box-seam"></i> Inventory
                <i class="bi bi-caret-down-fill float-end"></i>
            </a>
            <div class="collapse ps-4 <?= ($page == 'inventory' || $page == 'inventory_category' || $page == 'infrastructure_inventory') ? 'show' : '' ?>"
                id="inventorySubMenu">
                <a class="nav-link <?= ($page == 'inventory') ? 'active' : '' ?>" href="inventory.php">All</a>

                <?php foreach ($categories as $cat): ?>
                    <a class="nav-link <?= (isset($_GET['category']) && $_GET['category'] == $cat['id'] && $page == 'inventory_category') ? 'active' : '' ?>"
                        href="inventory_category.php?category=<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </a>
                <?php endforeach; ?>

                <!-- Static Infrastructure Inventory -->
                <a class="nav-link <?= ($page == 'infrastructure_inventory') ? 'active' : '' ?>" href="infrastructure_inventory.php">
                    Infrastructure
                </a>
            </div>

            <div class="nav-section">Forms</div>
            <!-- Forms dropdown -->
            <a class="<?= $formActive ? 'active' : '' ?>"
                data-bs-toggle="collapse" href="#formsSubMenu" role="button"
                aria-expanded="<?= $formActive ? 'true' : 'false' ?>"
                aria-controls="formsSubMenu">
                <i class="bi bi-file-earmark-text"></i> Forms
                <i class="bi bi-caret-down-fill float-end"></i>
            </a>
            <div class="collapse ps-4 <?= $formActive ? 'show' : '' ?>" id="formsSubMenu">
                <?php foreach ($form_categories as $category): ?>
                    <a class="nav-link <?= (isset($_GET['id']) && $_GET['id'] == $category['id']) ? 'active' : '' ?>"
                        href="forms.php?id=<?= $category['id'] ?>">
                        <?= htmlspecialchars($category['form_title']) ?>
                    </a>
                <?php endforeach; ?>
                <a class="nav-link <?= ($page == 'Saved Property Tags') ? 'active' : '' ?>" href="saved_mr.php">
                    Property Tags
                </a>
                <a class="nav-link <?= ($page == 'Saved Red Tags') ? 'active' : '' ?>" href="red_tag.php">
                    Red Tags
                </a>
            </div>

            <div class="nav-section">Fuel</div>
            <a href="fuel_inventory.php" class="<?= ($page == 'fuel_inventory') ? 'active' : '' ?>">
                <i class="bi bi-fuel-pump"></i> Fuel Inventory
            </a>

            <div class="nav-section">Operations</div>
            <a href="borrowing.php" class="<?= ($page == 'borrow') ? 'active' : '' ?>">
                <i class="bi bi-arrow-left-right"></i> Borrow
            </a>
            <a href="reports.php" class="<?= ($page == 'reports') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>
            <a href="usage.php" class="<?= ($page == 'usage') ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i> Usage
            </a>
            <div class="nav-section">Administration</div>
            <a href="logs.php" class="<?= ($page == 'logs') ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i> Audit Trail
            </a>
            <a href="employees.php" class="<?= ($page == 'employees') ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> Employees
            </a>

            <a href="user.php" class="<?= ($page == 'user') ? 'active' : '' ?>">
                <i class="bi bi-person"></i> Users
            </a>
            <a href="asset_archive.php" class="<?= ($page == 'asset_archive') ? 'active' : '' ?>">
                <i class="bi bi-archive"></i> Archive
            </a>
            <a href="about.php" class="<?= ($page == 'about') ? 'active' : '' ?>">
                <i class="bi bi-info-circle"></i> About
            </a>
            <a href="settings.php" class="<?= ($page == 'settings') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
            <?php endif; ?>
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