<?php
session_start();
require_once '../connect.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Check if user has the required role (super_admin)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    $_SESSION['error'] = 'You do not have permission to access this page.';
    header('Location: system_admin_dashboard.php');
    exit();
}

// Additional permission check using the new permissions system
$has_permission = false;
$user_id = $_SESSION['user_id'];
$permission_check = $conn->prepare("
    SELECT 1 FROM users u
    LEFT JOIN user_permissions up ON u.id = up.user_id
    WHERE u.id = ? AND (u.role = 'super_admin' OR up.permission = 'manage_roles')
    LIMIT 1
");

if ($permission_check) {
    $permission_check->bind_param('i', $user_id);
    $permission_check->execute();
    $permission_check->store_result();
    $has_permission = $permission_check->num_rows > 0;
    $permission_check->close();
}

if (!$has_permission) {
    $_SESSION['error'] = 'You do not have permission to manage roles and permissions.';
    header('Location: system_admin_dashboard.php');
    exit();
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_permissions'])) {
        $roleId = $_POST['role_id'];
        $permissions = $_POST['permissions'] ?? [];
        
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Delete existing permissions for this role
            $stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->bind_param('i', $roleId);
            $stmt->execute();
            
            // Insert new permissions
            $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissions as $permissionId) {
                $stmt->bind_param('ii', $roleId, $permissionId);
                $stmt->execute();
            }
            
            $conn->commit();
            $success = 'Role permissions updated successfully!';
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Error updating role permissions: ' . $e->getMessage();
        }
    }
}

// First, check if OFFICE_ADMIN exists
$officeAdminCheck = $conn->query("SELECT * FROM roles WHERE name = 'OFFICE_ADMIN'");
if ($officeAdminCheck->num_rows === 0) {
    // OFFICE_ADMIN doesn't exist, let's add it
    $conn->query("INSERT INTO roles (name, description) VALUES ('OFFICE_ADMIN', 'Office Administrator with office-specific access')");
    error_log("Added OFFICE_ADMIN role to the database");
}

// Get all roles except MAIN_EMPLOYEE and MAIN_USER, but include OFFICE_ADMIN
$roles = [];
$query = "SELECT * FROM roles WHERE name NOT IN ('MAIN_EMPLOYEE', 'MAIN_USER') OR name = 'OFFICE_ADMIN' ORDER BY name";
$result = $conn->query($query);

// Debug: Log the query and results
error_log("Roles Query: " . $query);
if ($result) {
    $all_roles = [];
    while ($row = $result->fetch_assoc()) {
        $all_roles[] = $row['name'];
        $roles[] = $row;
    }
    error_log("Fetched roles: " . implode(", ", $all_roles));
} else {
    error_log("Error in roles query: " . $conn->error);
}

// Get all permissions grouped by category
$permissionsByCategory = getPermissionsByCategory($conn);

// Get permissions for a specific role
function getRolePermissions($conn, $roleId) {
    $rolePermissions = [];
    $stmt = $conn->prepare("
        SELECT p.name 
        FROM permissions p
        JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ?
    ");
    $stmt->bind_param('i', $roleId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rolePermissions[] = $row['name'];
    }
    return $rolePermissions;
}

// Get all permissions grouped by category
function getPermissionsByCategory($conn) {
    $permissions = [];
    $result = $conn->query("SELECT * FROM permissions ORDER BY name");
    
    if (!$result) {
        error_log("Error fetching permissions: " . $conn->error);
        return [];
    }
    
    // Define categories based on permission name patterns
    $categories = [
        'Dashboard' => ['view_dashboard'],
        'User Management' => ['view_users', 'view_users_'],
        'Role Management' => ['view_roles', 'view_roles_'],
        'Permission Management' => ['view_permissions', 'view_permissions_'],
        'Asset Management' => ['view_assets', 'view_assets_'],
        'Category Management' => ['view_categories', 'view_categories_'],
        'Status Management' => ['view_status', 'view_status_'],
        'Type Management' => ['view_types', 'view_types_']
    ];
    
    while ($row = $result->fetch_assoc()) {
        $category = 'Other';
        
        // Determine category based on permission name
        foreach ($categories as $catName => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($row['name'], $pattern) === 0) {
                    $category = $catName;
                    break 2;
                }
            }
        }
        
        if (!isset($permissions[$category])) {
            $permissions[$category] = [];
        }
        $permissions[$category][] = $row;
    }
    
    // Sort categories alphabetically
    ksort($permissions);
    
    return $permissions;
}

// Validate and sanitize role_id from query string
$selectedRoleId = null;
if (isset($_GET['role_id']) && is_numeric($_GET['role_id'])) {
    $roleId = (int)$_GET['role_id'];
    // Verify role exists in the fetched roles
    foreach ($roles as $role) {
        if ($role['id'] == $roleId) {
            $selectedRoleId = $roleId;
            break;
        }
    }
}

// Default to first role if no valid role_id provided
if ($selectedRoleId === null && !empty($roles)) {
    $selectedRoleId = $roles[0]['id'];
}

// Get role details and permissions for the selected role
$roleDetails = null;
if ($selectedRoleId) {
    // Get role details
    $stmt = $conn->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->bind_param('i', $selectedRoleId);
    $stmt->execute();
    $roleDetails = $stmt->get_result()->fetch_assoc();
}

// Get permissions for the selected role
$rolePermissions = $selectedRoleId ? getRolePermissions($conn, $selectedRoleId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Roles & Permissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        /* Sidebar and Main Content Layout */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .main {
            margin-left: 250px;
            min-height: 100vh;
            background-color: #f8f9fc;
            transition: all 0.3s;
            padding: 20px;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main {
                margin-left: 0;
                padding: 15px;
            }
        }
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .permission-item {
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        
        .permission-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        /* Search highlight */
        .highlight {
            background-color: #fff3cd;
            padding: 0.1rem 0.2rem;
            border-radius: 0.2rem;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }
        .card-header h5, .card-header h6 {
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 0;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 0.75rem 1.25rem;
            border-color: #e3e6f0;
            transition: all 0.2s;
        }
        .list-group-item:first-child {
            border-top: none;
        }
        .list-group-item:hover {
            background-color: #f8f9fc;
        }
        .list-group-item.active {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .permission-group {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }
        .permission-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }
        .permission-item:hover {
            background-color: #f8f9fa;
        }
        .permission-item:last-child {
            border-bottom: none;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">User Roles & Permissions</h1>
                <a href="user_management.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>User Management
                </a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <i class="bi <?= $_SESSION['message_type'] === 'success' ? 'bi-check-circle' : 'bi-info-circle' ?> me-2"></i>
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Roles List -->
                <div class="col-lg-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">Roles</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($roles as $role): ?>
                                <a href="?role_id=<?php echo $role['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $selectedRoleId == $role['id'] ? 'active' : ''; ?>">
                                    <span><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $role['name']))); ?></span>
                                    <?php if ($selectedRoleId == $role['id']): ?>
                                        <i class="bi bi-check-lg"></i>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Permissions Panel -->
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">
                                Permissions for 
                                <span class="text-primary">
                                    <?php 
                                    $roleName = '';
                                    foreach ($roles as $role) {
                                        if ($role['id'] == $selectedRoleId) {
                                            $roleName = $role['name'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars(ucwords(str_replace('_', ' ', $roleName)));
                                    ?>
                                </span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if ($selectedRoleId): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="role_id" value="<?php echo $selectedRoleId; ?>">
                                    
                                    <div class="permission-categories">
                                        <?php foreach ($permissionsByCategory as $category => $permissions): ?>
                                            <div class="card mb-4 category-card">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        <?php echo htmlspecialchars($category); ?>
                                                    </h6>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-sm btn-outline-primary select-category" data-category="<?php echo htmlspecialchars($category); ?>">
                                                            <i class="bi bi-check2-all me-1"></i> Select All
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary deselect-category" data-category="<?php echo htmlspecialchars($category); ?>">
                                                            <i class="bi bi-x-lg me-1"></i> Deselect All
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        <?php foreach ($permissions as $permission): ?>
                                                            <div class="col-md-6 permission-item" data-category="<?php echo htmlspecialchars($category); ?>" data-name="<?php echo htmlspecialchars(strtolower($permission['name'] . ' ' . $permission['description'])); ?>">
                                                                <div class="form-check">
                                                                    <input class="form-check-input permission-checkbox" 
                                                                           type="checkbox" 
                                                                           name="permissions[]" 
                                                                           value="<?php echo $permission['id']; ?>"
                                                                           id="perm_<?php echo $permission['id']; ?>"
                                                                           data-category="<?php echo htmlspecialchars($category); ?>"
                                                                           <?php echo in_array($permission['name'], $rolePermissions) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="perm_<?php echo $permission['id']; ?>">
                                                                        <strong><?php echo htmlspecialchars($permission['name']); ?></strong>
                                                                    </label>
                                                                    <?php if (!empty($permission['description'])): ?>
                                                                        <div class="form-text text-muted small">
                                                                            <?php echo htmlspecialchars($permission['description']); ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                        <button type="submit" name="update_permissions" class="btn btn-primary px-4">
                                            <i class="bi bi-save me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info d-flex align-items-center mb-0">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">No Role Selected</h6>
                                        <p class="mb-0">Please select a role from the list to view and edit its permissions.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            </div>
{{ ... }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Toggle all checkboxes in a permission group
        function togglePermissionGroup(checkbox, groupClass) {
            var checkboxes = document.querySelectorAll('.' + groupClass + ' .form-check-input');
            checkboxes.forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
        }
    </script>
</body>
</html>
