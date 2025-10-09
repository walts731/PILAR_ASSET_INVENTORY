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

// Get all roles
$roles = [];
$result = $conn->query("SELECT * FROM roles ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
}

// Get all permissions
$permissions = [];
$result = $conn->query("SELECT * FROM permissions ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row;
    }
}

// Get permissions for a specific role
function getRolePermissions($conn, $roleName) {
    $rolePermissions = [];
    $stmt = $conn->prepare("
        SELECT p.name 
        FROM permissions p
        JOIN user_permissions up ON p.name = up.permission
        JOIN users u ON up.user_id = u.id
        WHERE u.role = ?
    ");
    $stmt->bind_param('s', $roleName);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rolePermissions[] = $row['name'];
    }
    return $rolePermissions;
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

// Get permissions for the selected role
$roleName = '';
if ($selectedRoleId) {
    // Find the role name by ID
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $selectedRoleId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $roleName = $row['role'];
    }
}
$rolePermissions = $roleName ? getRolePermissions($conn, $roleName) : [];
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
        .main {
            min-height: 100vh;
            background-color: #f8f9fc;
            transition: all 0.3s;
        }
        @media (max-width: 991.98px) {
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
                                    
                                    <div class="row g-4">
                                        <?php 
                                        // Group permissions by their prefix (e.g., 'manage_', 'view_', etc.)
                                        $groupedPermissions = [];
                                        foreach ($permissions as $permission) {
                                            $prefix = strtok($permission['name'], '_') . '_';
                                            $groupedPermissions[$prefix][] = $permission;
                                        }
                                        
                                        foreach ($groupedPermissions as $prefix => $permissionGroup): 
                                            $groupName = ucwords(str_replace('_', ' ', rtrim($prefix, '_'))) . ' Permissions';
                                        ?>
                                            <div class="col-12 col-md-6">
                                                <div class="permission-group">
                                                    <h6 class="font-weight-bold text-primary mb-3">
                                                        <i class="bi bi-shield-lock me-2"></i><?php echo $groupName; ?>
                                                    </h6>
                                                    <div class="permission-list">
                                                        <?php foreach ($permissionGroup as $permission): ?>
                                                            <div class="permission-item">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" 
                                                                            name="permissions[]" 
                                                                            value="<?php echo $permission['id']; ?>"
                                                                            id="perm_<?php echo $permission['id']; ?>"
                                                                            <?php echo in_array($permission['id'], $rolePermissions) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label w-100" for="perm_<?php echo $permission['id']; ?>">
                                                                        <div class="font-weight-medium">
                                                                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $permission['name']))); ?>
                                                                        </div>
                                                                        <div class="small text-muted">
                                                                            <?php echo htmlspecialchars($permission['description']); ?>
                                                                        </div>
                                                                    </label>
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
