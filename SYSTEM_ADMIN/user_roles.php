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

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">User Roles & Permissions</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Roles</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($roles as $role): ?>
                        <a href="?role_id=<?php echo $role['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $selectedRoleId == $role['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($role['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Permissions for 
                        <?php 
                        $roleName = '';
                        foreach ($roles as $role) {
                            if ($role['id'] == $selectedRoleId) {
                                $roleName = $role['name'];
                                break;
                            }
                        }
                        echo htmlspecialchars($roleName);
                        ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($selectedRoleId): ?>
                        <form method="post" action="">
                            <input type="hidden" name="role_id" value="<?php echo $selectedRoleId; ?>">
                            
                            <div class="row">
                                <?php foreach (array_chunk($permissions, 2) as $permissionChunk): ?>
                                    <div class="col-md-6">
                                        <?php foreach ($permissionChunk as $permission): ?>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?php echo $permission['id']; ?>"
                                                       id="perm_<?php echo $permission['id']; ?>"
                                                       <?php echo in_array($permission['id'], $rolePermissions) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="perm_<?php echo $permission['id']; ?>">
                                                    <strong><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($permission['name']))); ?></strong>
                                                    <small class="d-block text-muted">
                                                        <?php echo htmlspecialchars($permission['description']); ?>
                                                    </small>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" name="update_permissions" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Please select a role to view and edit its permissions.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
