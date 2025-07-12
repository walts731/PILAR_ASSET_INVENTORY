<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'])) {
  header("Location: users.php");
  exit();
}

$user_id = intval($_POST['user_id']);
$office_id = intval($_POST['office'] ?? $_SESSION['office_id']);

// Fetch the role of the user to be deactivated
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

// Prevent deactivation of super_admin only
if ($role === 'super_admin') {
  header("Location: users.php?deactivate=forbidden&office=$office_id");
  exit();
}

// Proceed to deactivate
$stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
  header("Location: user.php?deactivate=success&office=$office_id");
  exit();
} else {
  header("Location: user.php?deactivate=error&office=$office_id");
  exit();
}
?>
