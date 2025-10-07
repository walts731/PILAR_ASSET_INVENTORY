<?php
session_start();
require_once '../connect.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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
    echo json_encode(['success' => false, 'message' => 'You do not have permission to manage roles.']);
    exit();
}

$response = ['success' => false, 'message' => ''];

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_role':
            $role_id = (int)($_POST['role_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $color = isset($_POST['color']) ? '#' . ltrim($_POST['color'], '#') : '#99AAB5';
            $is_hoisted = isset($_POST['is_hoisted']) ? 1 : 0;
            $position = (int)($_POST['position'] ?? 0);
            
            if (empty($name)) {
                $response['message'] = 'Role name is required.';
                break;
            }
            
            // Check if role exists and is not a system role
            $stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
            $stmt->bind_param('i', $role_id);
            $stmt->execute();
            $role = $stmt->get_result()->fetch_assoc();
            
            if (!$role) {
                $response['message'] = 'Role not found.';
                break;
            }
            
            // Prevent modifying system roles
            $system_roles = ['SYSTEM_ADMIN', 'MAIN_ADMIN', 'MAIN_EMPLOYEE', 'MAIN_USER'];
            if (in_array($role['name'], $system_roles)) {
                $response['message'] = 'System roles cannot be modified.';
                break;
            }
            
            // Update role
            $stmt = $conn->prepare("
                UPDATE roles 
                SET name = ?, description = ?, color = ?, is_hoisted = ?, position = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ") or die($conn->error);
            $stmt->bind_param('sssiii', $name, $description, $color, $is_hoisted, $position, $role_id);
            
            if ($stmt->execute()) {
                // Update permissions
                $conn->query("DELETE FROM role_permissions WHERE role_id = $role_id");
                
                if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                    $permission_stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    foreach ($_POST['permissions'] as $permission_id) {
                        $permission_id = (int)$permission_id;
                        $permission_stmt->bind_param('ii', $role_id, $permission_id);
                        $permission_stmt->execute();
                    }
                    $permission_stmt->close();
                }
                
                $response['success'] = true;
                $response['message'] = 'Role updated successfully!';
            } else {
                $response['message'] = 'Error updating role: ' . $conn->error;
            }
            break;
            
        case 'delete_role':
            $role_id = (int)($_POST['role_id'] ?? 0);
            
            if ($role_id <= 0) {
                $response['message'] = 'Invalid role ID.';
                break;
            }
            
            // Check if role exists and is not a system role
            $stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
            $stmt->bind_param('i', $role_id);
            $stmt->execute();
            $role = $stmt->get_result()->fetch_assoc();
            
            if (!$role) {
                $response['message'] = 'Role not found.';
                break;
            }
            
            // Prevent deleting system roles
            $system_roles = ['SYSTEM_ADMIN', 'MAIN_ADMIN', 'MAIN_EMPLOYEE', 'MAIN_USER'];
            if (in_array($role['name'], $system_roles)) {
                $response['message'] = 'System roles cannot be deleted.';
                break;
            }
            
            // Check if role is assigned to any users
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_roles WHERE role_id = ?");
            $stmt->bind_param('i', $role_id);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            
            if ($count > 0) {
                $response['message'] = 'Cannot delete role: It is assigned to ' . $count . ' user(s).';
                break;
            }
            
            // Delete role permissions
            $conn->query("DELETE FROM role_permissions WHERE role_id = $role_id");
            
            // Delete role
            $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->bind_param('i', $role_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Role deleted successfully!';
            } else {
                $response['message'] = 'Error deleting role: ' . $conn->error;
            }
            break;
            
        default:
            $response['message'] = 'Invalid action.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
