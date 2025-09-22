<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'])) {
  header("Location: users.php");
  exit();
}

$user_id = intval($_POST['user_id']);
$office_id = intval($_POST['office'] ?? $_SESSION['office_id']);

// Get username for logging
$username_stmt = $conn->prepare("SELECT username, fullname FROM users WHERE id = ?");
$username_stmt->bind_param("i", $user_id);
$username_stmt->execute();
$username_result = $username_stmt->get_result();
$username_data = $username_result->fetch_assoc();
$username_stmt->close();

$target_username = $username_data['username'] ?? 'Unknown User';
$target_fullname = $username_data['fullname'] ?? 'Unknown Name';

$stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
  // Log user activation
  logUserManagementActivity('ACTIVATE', $target_username, $user_id, "Full Name: {$target_fullname}, Status changed to: active");
  
  header("Location: user.php?activate=success&office=$office_id");
  exit();
} else {
  // Log activation failure
  logErrorActivity('User Management', "Failed to activate user: {$target_username}");
  
  header("Location: user.php?activate=error&office=$office_id");
  exit();
}
?>
