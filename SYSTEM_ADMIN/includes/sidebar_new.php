<?php
require_once '../connect.php';
require_once __DIR__ . '/../includes/auth/permissions.php';

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

// Define menu items with permissions
$menuItems = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'bi-speedometer2',
        'url' => 'system_admin_dashboard.php',
        'permission' => 'view_dashboard'
    ],
    'forms' => [
        'title' => 'Forms',
        'icon' => 'bi-file-text',
        'url' => 'manage_forms.php',
        'permission' => 'manage_forms'
    ],
    'offices' => [
        'title' => 'Offices',
        'icon' => 'bi-building',
        'url' => 'manage_offices.php',
        'permission' => 'manage_offices'
    ],
    'units' => [
        'title' => 'Units',
        'icon' => 'bi-bounding-box',
        'url' => 'manage_units.php',
        'permission' => 'manage_units'
    ],
    'categories' => [
        'title' => 'Categories',
        'icon' => 'bi-tags',
        'url' => 'manage_categories.php',
        'permission' => 'manage_categories',
        'roles' => ['super_admin', 'SYSTEM_ADMIN']
    ],
    'tag_formats' => [
        'title' => 'Tag Formats',
        'icon' => 'bi-tag-fill',
        'url' => 'manage_tag_format.php',
        'permission' => 'manage_tag_formats',
        'roles' => ['super_admin']
    ],
    'backup' => [
        'title' => 'Backup',
        'icon' => 'bi-hdd',
        'url' => 'simple_backup.php',
        'permission' => 'manage_backup',
        'roles' => ['super_admin']
    ],
    'drive' => [
        'title' => 'Drive Backup',
        'icon' => 'bi-google',
        'url' => 'drive_settings.php',
        'permission' => 'manage_drive_backup',
        'roles' => ['super_admin']
    ],
    'legal_docs' => [
        'title' => 'Legal Documents',
        'icon' => 'bi-file-text-fill',
        'url' => 'manage_legal_documents.php',
        'permission' => 'manage_legal_documents'
    ],
    'par_settings' => [
        'title' => 'PAR/ICS Settings',
        'icon' => 'bi-sliders',
        'url' => 'par_ics_settings.php',
        'permission' => 'manage_par_settings'
    ],
    'system' => [
        'title' => 'System',
        'icon' => 'bi-gear',
        'url' => 'edit_system.php',
        'permission' => 'system_settings',
        'roles' => ['super_admin', 'SYSTEM_ADMIN']
    ],
    'user_roles' => [
        'title' => 'User Roles',
        'icon' => 'bi-people',
        'url' => 'user_roles.php',
        'permission' => 'manage_roles',
        'roles' => ['super_admin', 'SYSTEM_ADMIN']
    ]
];

// Group menu items by sections
$menuSections = [
    'main' => [
        'title' => 'Main Navigation',
        'items' => ['dashboard', 'forms', 'offices', 'units']
    ],
    'inventory' => [
        'title' => 'Inventory Management',
        'items' => ['categories', 'tag_formats']
    ],
    'system' => [
        'title' => 'System',
        'items' => ['legal_docs', 'par_settings', 'backup', 'drive', 'system', 'user_roles']
    ]
];
?>

<!-- SIDEBAR STYLES -->
<style>
    .sidebar {
        height: 100vh;
        overflow: hidden;
        background: linear-gradient(180deg, #0b5ed7 0%, #0a58ca 45%, #0948a6 100%);
        color: #eaf2ff;
        border-right: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    .sidebar .scrollable-nav {
        overflow-y: auto;
        height: calc(100vh - 64px);
        padding-right: 8px;
        scrollbar-width: none;
    }

    .sidebar .scrollable-nav::-webkit-scrollbar {
        display: none;
    }

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
        width: 38px;
        height: 38px;
        object-fit: contain;
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

    .sidebar .nav-section {
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.75);
        padding: 4px 6px;
        margin: 8px 4px 4px;
        opacity: 0.9;
    }

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

    .sidebar a .bi {
        opacity: 0.95;
        font-size: 1.05rem;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        text-decoration: none;
    }

    .sidebar a:active {
        transform: scale(0.995);
    }

    .sidebar a.active {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
    }

    .sidebar .collapse {
        border-left: 2px solid rgba(255, 255, 255, 0.18);
        margin-left: 6px;
    }

    .sidebar .collapse .nav-link {
        padding: 8px 12px;
        border-radius: 8px;
        color: #e9f2ff;
        margin: 3px 0;
    }

    .sidebar .collapse .nav-link:hover {
        background: rgba(255, 255, 255, 0.12);
    }

    .sidebar .collapse .nav-link.active {
        background: rgba(255, 255, 255, 0.22);
    }

    .sidebar a[aria-expanded="true"] .bi-caret-down-fill {
        transform: rotate(180deg);
    }

    .sidebar .bi-caret-down-fill {
        transition: transform 0.2s ease;
    }

    .sidebar .border-top {
        border-top: 1px solid rgba(255, 255, 255, 0.2) !important;
        background: rgba(0, 0, 0, 0.05);
    }

    .sidebar .border-top .nav-link {
        color: #ffd9d9;
    }

    .sidebar .border-top .nav-link:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border-radius: 8px;
    }

    .sidebar a:focus {
        outline: 2px solid rgba(255, 255, 255, 0.35);
        outline-offset: 2px;
    }

    .badge-counter {
        position: absolute;
        transform: scale(0.7);
        transform-origin: top right;
        right: 0.5rem;
        margin-top: -0.5rem;
    }
</style>

<!-- SIDEBAR HTML -->
<div class="sidebar d-flex flex-column">
    <!-- Scrollable top part -->
    <div class="scrollable-nav px-3">
        <!-- Brand header -->
        <div class="sidebar-brand">
            <div class="brand-logo-wrap">
                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo">
            </div>
            <div class="brand-title">
                <strong><?= htmlspecialchars($system['system_title']) ?></strong>
                <span>System Admin</span>
            </div>
        </div>
        <hr>

        <!-- Navigation Menu -->
        <nav class="nav flex-column">
            <?php foreach ($menuSections as $sectionId => $section): 
                // Filter items that should be visible to the user
                $visibleItems = [];
                foreach ($section['items'] as $itemId) {
                    if (!isset($menuItems[$itemId])) continue;
                    
                    $item = $menuItems[$itemId];
                    
                    // Check role-based access
                    if (isset($item['roles']) && !hasRole($item['roles'])) {
                        continue;
                    }
                    
                    // Check permission
                    if (isset($item['permission']) && !hasPermission($item['permission'])) {
                        continue;
                    }
                    
                    $visibleItems[$itemId] = $item;
                }
                
                if (empty($visibleItems)) continue;
            ?>
                <?php if (count($menuSections) > 1): ?>
                    <div class="nav-section"><?= htmlspecialchars($section['title']) ?></div>
                <?php endif; ?>
                
                <?php foreach ($visibleItems as $itemId => $item): 
                    $isActive = ($page === str_replace('.php', '', $item['url']));
                ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" 
                       class="nav-link <?= $isActive ? 'active' : '' ?>"
                       title="<?= htmlspecialchars($item['title']) ?>">
                        <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
                        <span><?= htmlspecialchars($item['title']) ?></span>
                        
                        <?php if (isset($item['badge'])): 
                            $badge = $item['badge'];
                            if (hasPermission($badge['permission'])): 
                                $count = 0;
                                if (is_callable($badge['count'])) {
                                    $count = $badge['count']();
                                } elseif (is_string($badge['count'])) {
                                    $result = $conn->query($badge['count']);
                                    if ($result && $row = $result->fetch_assoc()) {
                                        $count = $row['count'] ?? 0;
                                    }
                                } elseif (is_numeric($badge['count'])) {
                                    $count = $badge['count'];
                                }
                                
                                if ($count > 0): ?>
                                    <span class="badge badge-danger badge-counter"><?= $count > 9 ? '9+' : $count ?></span>
                                <?php endif; 
                            endif; 
                        endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Bottom user info -->
    <div class="border-top px-3 py-2">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <img src="<?= !empty($_SESSION['avatar']) ? '../uploads/avatars/'.htmlspecialchars($_SESSION['avatar']) : '../img/undraw_profile.svg' ?>" 
                     alt="User" class="rounded-circle me-2" width="32" height="32">
            </div>
            <div class="flex-grow-1 ms-2">
                <div class="text-white small fw-bold"><?= htmlspecialchars($_SESSION['fullname'] ?? 'User') ?></div>
                <div class="text-white-50 small" style="font-size: 0.7rem;">
                    <?= htmlspecialchars(ucfirst(strtolower(str_replace('_', ' ', $_SESSION['role'] ?? 'User')))) ?>
                </div>
            </div>
            <a href="#" class="text-white-50" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
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
