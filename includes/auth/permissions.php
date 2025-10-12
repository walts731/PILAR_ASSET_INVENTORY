<?php
/**
 * Checks if the current user has a specific permission
 * @param string $permissionName The name of the permission to check
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permissionName) {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Check if user has permission directly or through their role
    $query = "
        SELECT 1 
        FROM users u
        LEFT JOIN roles r ON u.role = r.name
        LEFT JOIN role_permissions rp ON r.id = rp.role_id
        LEFT JOIN permissions p ON rp.permission_id = p.id
        WHERE u.id = ? AND (p.name = ? OR u.role = 'SYSTEM_ADMIN')
        LIMIT 1
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $userId, $permissionName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Checks if the current user has any of the specified permissions
 * @param array $permissions Array of permission names
 * @return bool True if user has at least one of the permissions
 */
function hasAnyPermission($permissions) {
    foreach ($permissions as $permission) {
        if (hasPermission($permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Checks if the current user has all of the specified permissions
 * @param array $permissions Array of permission names
 * @return bool True if user has all the permissions
 */
function hasAllPermissions($permissions) {
    foreach ($permissions as $permission) {
        if (!hasPermission($permission)) {
            return false;
        }
    }
    return true;
}

/**
 * Checks if the current user has a specific role
 * @param string|array $roles Single role or array of roles to check
 * @return bool True if user has one of the specified roles
 */
function hasRole($roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($_SESSION['role'], $roles);
    }
    
    return $_SESSION['role'] === $roles;
}

/**
 * Renders content only if user has the specified permission
 * @param string $permission Permission name required to see the content
 * @param string $content Content to display if user has permission
 * @param string $altContent Alternative content to display if user doesn't have permission (optional)
 */
function renderIfAllowed($permission, $content, $altContent = '') {
    echo hasPermission($permission) ? $content : $altContent;
}

/**
 * Renders content only if user has any of the specified permissions
 * @param array $permissions Array of permission names
 * @param string $content Content to display if user has any of the permissions
 * @param string $altContent Alternative content to display (optional)
 */
function renderIfAnyAllowed($permissions, $content, $altContent = '') {
    echo hasAnyPermission($permissions) ? $content : $altContent;
}

/**
 * Renders content only if user has all of the specified permissions
 * @param array $permissions Array of permission names
 * @param string $content Content to display if user has all permissions
 * @param string $altContent Alternative content to display (optional)
 */
function renderIfAllAllowed($permissions, $content, $altContent = '') {
    echo hasAllPermissions($permissions) ? $content : $altContent;
}
?>
