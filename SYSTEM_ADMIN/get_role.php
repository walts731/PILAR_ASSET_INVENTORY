<?php
session_start();
require_once '../connect.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Unauthorized access.</div>';
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
    echo '<div class="alert alert-danger">You do not have permission to manage roles.</div>';
    exit();
}

// Get role ID from request
$role_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($role_id <= 0) {
    echo '<div class="alert alert-danger">Invalid role ID.</div>';
    exit();
}

// Get role details
$stmt = $conn->prepare("
    SELECT r.*, GROUP_CONCAT(rp.permission_id) as permission_ids
    FROM roles r
    LEFT JOIN role_permissions rp ON r.id = rp.role_id
    WHERE r.id = ?
    GROUP BY r.id
");
$stmt->bind_param('i', $role_id);
$stmt->execute();
$role = $stmt->get_result()->fetch_assoc();

if (!$role) {
    echo '<div class="alert alert-danger">Role not found.</div>';
    exit();
}

// Get role's permissions
$permission_ids = [];
if (!empty($role['permission_ids'])) {
    $permission_ids = array_map('intval', explode(',', $role['permission_ids']));
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

<div class="form-group">
    <label for="editRoleName">Role Name</label>
    <input type="text" class="form-control" id="editRoleName" name="name" value="<?php echo htmlspecialchars($role['name']); ?>" required>
</div>
<div class="form-group">
    <label for="editRoleDescription">Description</label>
    <textarea class="form-control" id="editRoleDescription" name="description" rows="2"><?php echo htmlspecialchars($role['description']); ?></textarea>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label for="editRoleColor">Color</label>
        <input type="color" class="form-control" id="editRoleColor" name="color" value="<?php echo htmlspecialchars($role['color']); ?>">
    </div>
    <div class="form-group col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" id="editIsHoisted" name="is_hoisted" <?php echo $role['is_hoisted'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="editIsHoisted">
                Display role members separately in member list
            </label>
        </div>
    </div>
</div>
<div class="form-group">
    <label for="editRolePosition">Position</label>
    <input type="number" class="form-control" id="editRolePosition" name="position" value="<?php echo (int)$role['position']; ?>">
    <small class="form-text text-muted">Higher numbers appear higher in the role list</small>
</div>
<div class="form-group">
    <label>Permissions</label>
    <div class="permissions-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
        <?php foreach ($permissions as $permission): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" 
                       value="<?php echo $permission['id']; ?>" 
                       id="edit_perm_<?php echo $permission['id']; ?>"
                       <?php echo in_array($permission['id'], $permission_ids) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="edit_perm_<?php echo $permission['id']; ?>">
                    <?php echo htmlspecialchars($permission['name']); ?>
                    <small class="text-muted"><?php echo htmlspecialchars($permission['description']); ?></small>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Handle form submission for editing role
$('#editRoleForm').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    var submitBtn = form.find('button[type="submit"]');
    
    // Disable submit button and show loading state
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
    
    $.ajax({
        url: 'update_role.php',
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
            var result = JSON.parse(response);
            if (result.success) {
                // Show success message and reload page after a short delay
                var alert = $('<div class="alert alert-success">' + result.message + '</div>');
                form.prepend(alert);
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                // Show error message
                var alert = $('<div class="alert alert-danger">' + result.message + '</div>');
                form.find('.alert').remove();
                form.prepend(alert);
                submitBtn.prop('disabled', false).html('Save Changes');
            }
        },
        error: function() {
            var alert = $('<div class="alert alert-danger">An error occurred while saving. Please try again.</div>');
            form.find('.alert').remove();
            form.prepend(alert);
            submitBtn.prop('disabled', false).html('Save Changes');
        }
    });
});
</script>
