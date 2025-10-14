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
        
        // Load from session if available
        if (isset($_SESSION['user_permissions'])) {
            $this->permissions = $_SESSION['user_permissions'];
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $this->permissions = [];
        
        // Get user's role
        $role = $this->getUserRole($userId);
        
        // If user is system admin, grant all permissions
        if ($role === 'SYSTEM_ADMIN') {
            $this->grantAllPermissions();
            return;
        }
        
        // Load role-based permissions
        $this->loadRolePermissions($role);
        
        // Load user-specific permissions (overrides)
        $this->loadUserSpecificPermissions($userId);
        
        // Store in session for future requests
        $_SESSION['user_permissions'] = $this->permissions;
    }
    
    private function getUserRole($userId) {
        $stmt = $this->conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user ? $user['role'] : null;
    }
    
    private function grantAllPermissions() {
        $result = $this->conn->query("SELECT name FROM permissions");
        while ($row = $result->fetch_assoc()) {
            $this->permissions[$row['name']] = true;
        }
    }
    
    private function loadRolePermissions($role) {
        $stmt = $this->conn->prepare("
            SELECT p.name 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN roles r ON rp.role_id = r.id
            WHERE r.name = ?
        ");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->permissions[$row['name']] = true;
        }
    }
    
    private function loadUserSpecificPermissions($userId) {
        $stmt = $this->conn->prepare("
            SELECT p.name 
            FROM user_permissions up
            JOIN permissions p ON up.permission = p.name
            WHERE up.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $this->permissions[$row['name']] = true;
        }
    }
    
    public function hasPermission($permission) {
        // System admin has all permissions
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'SYSTEM_ADMIN') {
            return true;
        }
        return isset($this->permissions[$permission]);
    }
    
    public function requirePermission($permission) {
        if (!$this->hasPermission($permission)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.'
                ]);
            } else {
                $_SESSION['error'] = "You don't have permission to access this page.";
                header('Location: /unauthorized.php');
            }
            exit();
        }
    }
}