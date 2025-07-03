<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'])) {
  header("Location: users.php");
  exit();
}

$user_id = intval($_POST['user_id']);
$office_id = intval($_POST['office'] ?? $_SESSION['office_id']);

$stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
  header("Location: user.php?activate=success&office=$office_id");
  exit();
} else {
  header("Location: user.php?activate=error&office=$office_id");
  exit();
}
?>
