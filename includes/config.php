<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$db_host = 'localhost';
$db_name = 'inventory_pilar';
$db_user = 'root'; // Default XAMPP username
$db_pass = '';     // Default XAMPP password

// Create database connection
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for full Unicode support
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log the error (in a production environment, log to a file instead of displaying)
    error_log("Database connection error: " . $e->getMessage());
    
    // Display a user-friendly error message
    die("<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; background-color: #f8d7da; color: #721c24;'>
            <h2>Database Connection Error</h2>
            <p>We're sorry, but we're experiencing technical difficulties. Please try again later.</p>
            <p><small>Technical details for administrator: " . htmlspecialchars($e->getMessage()) . "</small></p>
        </div>");
}

// Include and initialize PermissionManager
require_once __DIR__ . '/auth/PermissionManager.php';
$permissionManager = new PermissionManager($conn);

// Helper function for checking permissions in views
function can($permission) {
    global $permissionManager;
    return $permissionManager->hasPermission($permission);
}

// Set default timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (enable in development, disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base URL for easy reference
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/PILAR_ASSET_INVENTORY');

// Function to safely redirect
function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

// Function to check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Function to require specific role
function requireRole($role) {
    if (!hasRole($role)) {
        http_response_code(403);
        die("<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; background-color: #f8d7da; color: #721c24;'>
                <h2>Access Denied</h2>
                <p>You don't have permission to access this page.</p>
                <p><a href='" . BASE_URL . "'>Return to Home</a></p>
            </div>");
    }
}

// Function to set flash message
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to get and clear flash message
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Function to display flash messages
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $alertClass = 'alert-' . ($flash['type'] === 'error' ? 'danger' : $flash['type']);
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($flash['message']);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
    }
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return "<input type='hidden' name='csrf_token' value='" . csrf_token() . "'>";
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
