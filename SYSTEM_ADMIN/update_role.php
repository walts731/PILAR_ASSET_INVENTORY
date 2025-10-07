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

// Define system roles that cannot be modified
$system_roles = ['SYSTEM_ADMIN', 'MAIN_ADMIN', 'MAIN_EMPLOYEE', 'MAIN_USER'];

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
            if (in_array($role['name'], $system_roles)) {
                $response['message'] = 'System roles cannot be modified.';
                break;
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update role details
                $stmt = $conn->prepare("UPDATE roles SET name = ?, description = ?, color = ?, is_hoisted = ?, position = ? WHERE id = ?");
                $stmt->bind_param('sssiii', $name, $description, $color, $is_hoisted, $position, $role_id);
                $result = $stmt->execute();
                
                if (!$result) {
                    throw new Exception('Failed to update role: ' . $conn->error);
                }
                
                // Get role name for permission updates
                $role_stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
                $role_stmt->bind_param('i', $role_id);
                $role_stmt->execute();
                $role_result = $role_stmt->get_result();
                $role_data = $role_result->fetch_assoc();
                $role_name = $role_data['name'];
                
                // Update role permissions
                $delete_stmt = $conn->prepare("DELETE FROM role_permissions WHERE role = ?");
                $delete_stmt->bind_param('s', $role_name);
                $delete_stmt->execute();
                
                if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                    $insert_stmt = $conn->prepare("INSERT INTO role_permissions (role, permission_id) VALUES (?, ?)");
                    
                    foreach ($_POST['permissions'] as $permission_id) {
                        $permission_id = (int)$permission_id;
                        if ($permission_id > 0) {
                            $insert_stmt->bind_param('si', $role_name, $permission_id);
                            $insert_stmt->execute();
                        }
                    }
                    $insert_stmt->close();
                }
                
                $conn->commit();
                $response = ['success' => true, 'message' => 'Role updated successfully.'];
                
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Error: ' . $e->getMessage();
            }
            break;
            
        case 'delete_role':
            $role_id = (int)($_POST['role_id'] ?? 0);
            
            if ($role_id <= 0) {
                $response['message'] = 'Invalid role ID.';
                break;
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Check if role exists and get its name
                $stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
                $stmt->bind_param('i', $role_id);
                $stmt->execute();
                $role = $stmt->get_result()->fetch_assoc();
                
                if (!$role) {
                    throw new Exception('Role not found.');
                }
                
                $role_name = $role['name'];
                
                // Prevent deleting system roles
                if (in_array($role_name, $system_roles)) {
                    throw new Exception('System roles cannot be deleted.');
                }
                
                // Check if any users have this role
                $user_check = $conn->prepare("SELECT COUNT(*) as user_count FROM users WHERE role = ?");
                $user_check->bind_param('s', $role_name);
                $user_check->execute();
                $user_count = $user_check->get_result()->fetch_assoc()['user_count'];
                $user_check->close();
                
                if ($user_count > 0) {
                    throw new Exception('Cannot delete role: There are users assigned to this role.');
                }
                
                // Delete role permissions first
                $delete_perms = $conn->prepare("DELETE FROM role_permissions WHERE role = ?");
                $delete_perms->bind_param('s', $role_name);
                $delete_perms->execute();
                $delete_perms->close();
                
                // Delete the role
                $delete_role = $conn->prepare("DELETE FROM roles WHERE id = ?");
                $delete_role->bind_param('i', $role_id);
                $delete_role->execute();
                
                if ($delete_role->affected_rows === 0) {
                    throw new Exception('Failed to delete role.');
                }
                
                $delete_role->close();
                $conn->commit();
                $response = ['success' => true, 'message' => 'Role deleted successfully.'];
                
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = $e->getMessage();
            }
            break;
            
        default:
            $response['message'] = 'Invalid action.';
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
