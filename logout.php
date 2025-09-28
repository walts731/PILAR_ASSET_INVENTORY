<?php
// Start the session if not already started
session_start();

// Include database connection and helpers
require_once 'connect.php';
require_once 'includes/audit_helper.php';
require_once 'includes/remember_me_helper.php';

// Handle remember me token cleanup
$logout_all_devices = isset($_GET['all']) && $_GET['all'] === '1';

// Log logout activity before destroying session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Unknown';
    
    if ($logout_all_devices) {
        // Delete all remember tokens for this user (logout from all devices)
        deleteAllUserTokens($conn, $user_id);
        logAuthActivity('LOGOUT_ALL_DEVICES', "User '{$username}' logged out from all devices", $user_id, $username);
    } else {
        // Only delete current remember token if it exists
        if (isset($_COOKIE['remember_token'])) {
            deleteRememberToken($conn, $_COOKIE['remember_token']);
        }
        logAuthActivity('LOGOUT', "User '{$username}' logged out successfully", $user_id, $username);
    }
}

// Clear remember me cookie
clearRememberCookie();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page with optional message
$redirect_url = "index.php";
if ($logout_all_devices) {
    $redirect_url .= "?message=logged_out_all";
}

header("Location: " . $redirect_url);
exit;
?>
