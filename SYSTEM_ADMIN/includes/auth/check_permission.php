<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and PermissionManager
require_once __DIR__ . '../../../connect.php';
require_once __DIR__ . '/PermissionManager.php';

// Initialize PermissionManager
$permissionManager = new PermissionManager($conn);

/**
 * Require a specific permission
 * @param string $permission Permission name to check
 */
function requirePermission($permission) {
    global $permissionManager;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please log in to access this page.";
        header('Location: /login.php');
        exit();
    }
    
    // Check permission
    if (!$permissionManager->hasPermission($permission)) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        header('Location: /unauthorized.php');
        exit();
    }
}