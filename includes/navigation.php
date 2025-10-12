<?php
require_once __DIR__ . '/auth/permissions.php';

/**
 * Generates navigation items based on user permissions
 * @return array Array of navigation items
 */
function getNavigationItems() {
    $navItems = [
        [
            'title' => 'Dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'url' => 'dashboard.php',
            'permission' => 'view_dashboard'
        ],
        [
            'title' => 'Asset Management',
            'icon' => 'fas fa-fw fa-boxes',
            'url' => '#',
            'permission' => 'manage_assets',
            'children' => [
                [
                    'title' => 'All Assets',
                    'url' => 'assets/list.php',
                    'permission' => 'view_assets'
                ],
                [
                    'title' => 'Add New Asset',
                    'url' => 'assets/add.php',
                    'permission' => 'add_asset'
                ],
                [
                    'title' => 'Asset Categories',
                    'url' => 'assets/categories.php',
                    'permission' => 'manage_categories'
                ]
            ]
        ],
        [
            'title' => 'Borrowing',
            'icon' => 'fas fa-fw fa-exchange-alt',
            'url' => 'borrowing/requests.php',
            'permission' => 'borrow_assets',
            'badge' => [
                'query' => "SELECT COUNT(*) as count FROM borrow_requests WHERE status = 'pending'",
                'permission' => 'approve_borrow_requests'
            ]
        ],
        [
            'title' => 'Reports',
            'icon' => 'fas fa-fw fa-chart-bar',
            'url' => 'reports/index.php',
            'permission' => 'view_reports'
        ],
        [
            'title' => 'User Management',
            'icon' => 'fas fa-fw fa-users',
            'url' => 'users/list.php',
            'permission' => 'manage_users',
            'roles' => ['SYSTEM_ADMIN', 'MAIN_ADMIN']
        ],
        [
            'title' => 'System Settings',
            'icon' => 'fas fa-fw fa-cog',
            'url' => 'settings/index.php',
            'permission' => 'system_settings',
            'roles' => ['SYSTEM_ADMIN']
        ]
    ];

    // Filter navigation items based on permissions and roles
    return array_filter($navItems, function($item) {
        // Check if item has specific role requirements
        if (isset($item['roles'])) {
            if (!hasRole($item['roles'])) {
                return false;
            }
        }
        
        // Check permission if specified
        if (isset($item['permission'])) {
            if (!hasPermission($item['permission'])) {
                return false;
            }
        }
        
        // Process children recursively
        if (isset($item['children'])) {
            $item['children'] = array_filter($item['children'], function($child) {
                if (isset($child['permission']) && !hasPermission($child['permission'])) {
                    return false;
                }
                return true;
            });
            
            // Don't show parent if no children are visible
            if (empty($item['children'])) {
                return false;
            }
        }
        
        return true;
    });
}

/**
 * Renders the sidebar navigation
 */
function renderSidebar() {
    $navItems = getNavigationItems();
    
    echo '<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">';
    
    // Sidebar - Brand
    echo '<a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Asset Manager</div>
          </a>';
    
    // Divider
    echo '<hr class="sidebar-divider my-0">';
    
    // Navigation Items
    foreach ($navItems as $item) {
        $hasChildren = !empty($item['children']);
        $isActive = (basename($_SERVER['PHP_SELF']) === basename($item['url'])) || 
                   (isset($item['children']) && in_array(basename($_SERVER['PHP_SELF']), 
                       array_map('basename', array_column($item['children'], 'url'))));
        
        echo '<li class="nav-item' . ($isActive ? ' active' : '') . '">';
        
        if ($hasChildren) {
            echo '<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse' . 
                 preg_replace('/[^a-zA-Z0-9]/', '', $item['title']) . '" ' . 
                 'aria-expanded="true" aria-controls="collapse' . preg_replace('/[^a-zA-Z0-9]/', '', $item['title']) . '">';
        } else {
            echo '<a class="nav-link" href="' . $item['url'] . '">';
        }
        
        echo '<i class="' . ($item['icon'] ?? 'fas fa-fw fa-circle') . '"></i>';
        echo '<span>' . htmlspecialchars($item['title']) . '</span>';
        
        // Badge (e.g., for notification counts)
        if (isset($item['badge'])) {
            if (hasPermission($item['badge']['permission'])) {
                global $conn;
                $result = $conn->query($item['badge']['query']);
                if ($result && $row = $result->fetch_assoc()) {
                    echo '<span class="badge badge-danger badge-counter">' . $row['count'] . '</span>';
                }
            }
        }
        
        if ($hasChildren) {
            echo '<i class="fas fa-fw fa-caret-down"></i>';
        }
        
        echo '</a>';
        
        // Render children if any
        if ($hasChildren) {
            $show = $isActive ? 'show' : '';
            echo '<div id="collapse' . preg_replace('/[^a-zA-Z0-9]/', '', $item['title']) . '" ' . 
                 'class="collapse' . ($isActive ? ' show' : '') . '" aria-labelledby="heading' . 
                 preg_replace('/[^a-zA-Z0-9]/', '', $item['title']) . '" data-parent="#accordionSidebar">';
            echo '<div class="bg-white py-2 collapse-inner rounded">';
            
            foreach ($item['children'] as $child) {
                $isChildActive = (basename($_SERVER['PHP_SELF']) === basename($child['url']));
                echo '<a class="collapse-item' . ($isChildActive ? ' active' : '') . '" href="' . 
                     $child['url'] . '">' . htmlspecialchars($child['title']) . '</a>';
            }
            
            echo '</div></div>';
        }
        
        echo '</li>';
    }
    
    // Sidebar Toggler
    echo '<div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
          </div>';
    
    echo '</ul>';
}
?>
