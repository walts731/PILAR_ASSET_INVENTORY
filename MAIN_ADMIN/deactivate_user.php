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

// Fetch user details for logging and validation
$stmt = $conn->prepare("SELECT username, fullname, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

$target_username = $user_data['username'] ?? 'Unknown User';
$target_fullname = $user_data['fullname'] ?? 'Unknown Name';
$role = $user_data['role'] ?? 'Unknown Role';

// Prevent deactivation of admin
if ($role === 'admin') {
  header("Location: users.php?deactivate=forbidden&office=$office_id");
  exit();
}

// Proceed to deactivate if not admin
$stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
  // Log user deactivation
  logUserManagementActivity('DEACTIVATE', $target_username, $user_id, "Full Name: {$target_fullname}, Status changed to: inactive");
  
  header("Location: user.php?deactivate=success&office=$office_id");
  exit();
} else {
  // Log deactivation failure
  logErrorActivity('User Management', "Failed to deactivate user: {$target_username}");
  
  header("Location: user.php?deactivate=error&office=$office_id");
  exit();
}
?>
