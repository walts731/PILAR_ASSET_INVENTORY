<?php
// Start the session if not already started
session_start();

// Include database connection and audit helper
require_once 'connect.php';
require_once 'includes/audit_helper.php';

// Log logout activity before destroying session
if (isset($_SESSION['user_id'])) {
    logAuthActivity('LOGOUT', "User logged out successfully");
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit;
?>
