<?php
class PermissionManager {
    private $conn;
    private $permissions = [];
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadUserPermissions();
    }
    
    private function loadUserPermissions() {
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Check if permissions are already loaded in session
        if (isset($_SESSION['user_permissions'])) {
            $this->permissions = $_SESSION['user_permissions'];
            return;
        }
        
        // Load role-based permissions
        $query = "SELECT p.name 
                 FROM permissions p
                 JOIN role_permissions rp ON p.id = rp.permission_id
                 JOIN user_roles ur ON rp.role_id = ur.role_id
                 WHERE ur.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->permissions[$row['name']] = true;
        }
        
        // Load user-specific permissions (overrides)
        $query = "SELECT p.name 
                 FROM user_permissions up
                 JOIN permissions p ON up.permission = p.name
                 WHERE up.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->permissions[$row['name']] = true;
        }
        
        // Store in session for future requests
        $_SESSION['user_permissions'] = $this->permissions;
    }
    
    public function hasPermission($permission) {
        // Super admin has all permissions
        if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
            return true;
        }
        
        return isset($this->permissions[$permission]);
    }
    
    public function requirePermission($permission) {
        if (!$this->hasPermission($permission)) {
            $_SESSION['error'] = "You don't have permission to access this page.";
            header('Location: /unauthorized.php');
            exit();
        }
    }
}