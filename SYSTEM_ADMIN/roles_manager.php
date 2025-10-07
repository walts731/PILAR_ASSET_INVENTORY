<?php
session_start();
require_once '../connect.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Check if user has permission to manage roles
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
    $_SESSION['error'] = 'You do not have permission to manage roles.';
    header('Location: system_admin_dashboard.php');
    exit();
}

// Handle form submissions
$success = $error = '';

// Create new role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_role') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $color = isset($_POST['color']) ? '#' . ltrim($_POST['color'], '#') : '#99AAB5';
    $is_hoisted = isset($_POST['is_hoisted']) ? 1 : 0;
    
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO roles (name, description, color, is_hoisted) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $name, $description, $color, $is_hoisted);
        
        if ($stmt->execute()) {
            $role_id = $conn->insert_id;
            
            // Handle permission assignments
            if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                $permission_stmt = $conn->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($_POST['permissions'] as $permission_id) {
                    $permission_id = (int)$permission_id;
                    $permission_stmt->bind_param('ii', $role_id, $permission_id);
                    $permission_stmt->execute();
                }
                $permission_stmt->close();
            }
            
            $success = "Role '{$name}' created successfully!";
        } else {
            $error = "Error creating role: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Role name is required.";
    }
}

// Get all roles with their permissions
$roles = [];
$result = $conn->query("
    SELECT r.*, GROUP_CONCAT(p.name) as permission_names, 
           GROUP_CONCAT(p.id) as permission_ids
    FROM roles r
    LEFT JOIN role_permissions rp ON r.name = rp.role
    LEFT JOIN permissions p ON rp.permission_id = p.id
    GROUP BY r.id
    ORDER BY r.position DESC, r.name ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['permission_names'] = $row['permission_names'] ? explode(',', $row['permission_names']) : [];
        $row['permission_ids'] = $row['permission_ids'] ? array_map('intval', explode(',', $row['permission_ids'])) : [];
        $roles[] = $row;
    }
}

// Get all available permissions
$permissions = [];
$result = $conn->query("SELECT * FROM permissions ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row;
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Role Management</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRoleModal">
            <i class="fas fa-plus"></i> Create Role
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Roles</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="rolesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Members</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td>
                                    <span class="role-badge" style="background-color: <?php echo htmlspecialchars($role['color']); ?>; color: white; padding: 2px 8px; border-radius: 4px;">
                                        <?php echo htmlspecialchars($role['name']); ?>
                                    </span>
                                    <?php if ($role['is_hoisted']): ?>
                                        <span class="badge badge-info">Hoisted</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($role['description']); ?></td>
                                <td>
                                    <?php if (!empty($role['permission_names'])): ?>
                                        <div class="permission-tags">
                                            <?php foreach ($role['permission_names'] as $permission): ?>
                                                <span class="badge badge-secondary"><?php echo htmlspecialchars($permission); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No permissions</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_roles WHERE role_id = ?");
                                    $stmt->bind_param('i', $role['id']);
                                    $stmt->execute();
                                    $count = $stmt->get_result()->fetch_assoc()['count'];
                                    echo $count . ' member' . ($count != 1 ? 's' : '');
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-role" data-id="<?php echo $role['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if (!in_array($role['name'], ['SYSTEM_ADMIN', 'MAIN_ADMIN', 'MAIN_EMPLOYEE', 'MAIN_USER'])): ?>
                                        <button class="btn btn-sm btn-danger delete-role" data-id="<?php echo $role['id']; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="create_role">
                <div class="modal-header">
                    <h5 class="modal-title" id="createRoleModalLabel">Create New Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="roleName">Role Name</label>
                        <input type="text" class="form-control" id="roleName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="roleDescription">Description</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="roleColor">Color</label>
                            <input type="color" class="form-control" id="roleColor" name="color" value="#99AAB5">
                        </div>
                        <div class="form-group col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isHoisted" name="is_hoisted">
                                <label class="form-check-label" for="isHoisted">
                                    Display role members separately in member list
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="permissions-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                            <?php foreach ($permissions as $permission): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" id="perm_<?php echo $permission['id']; ?>">
                                    <label class="form-check-label" for="perm_<?php echo $permission['id']; ?>">
                                        <?php echo htmlspecialchars($permission['name']); ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($permission['description']); ?></small>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editRoleForm" method="POST" action="">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="role_id" id="editRoleId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading role details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Role Confirmation Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                <p>All users with this role will lose the associated permissions.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteRoleForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_role">
                    <input type="hidden" name="role_id" id="deleteRoleId">
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Page level plugins -->
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#rolesTable').DataTable({
        "columnDefs": [
            { "orderable": false, "targets": [4] } // Disable sorting on actions column
        ],
        "order": [[0, 'asc']] // Sort by role name by default
    });

    // Handle edit role button click
    $('.edit-role').on('click', function() {
        var roleId = $(this).data('id');
        $('#editRoleId').val(roleId);
        
        // Load role data via AJAX
        $.get('get_role.php', { id: roleId }, function(data) {
            $('#editRoleModal .modal-body').html(data);
            $('#editRoleModal').modal('show');
        });
    });

    // Handle delete role button click
    $('.delete-role').on('click', function() {
        var roleId = $(this).data('id');
        $('#deleteRoleId').val(roleId);
        $('#deleteRoleModal').modal('show');
    });

    // Handle form submission for delete role
    $('#deleteRoleForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        
        $.ajax({
            url: 'update_role.php',
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('An error occurred while processing your request.');
            }
        });
    });
});
</script>
