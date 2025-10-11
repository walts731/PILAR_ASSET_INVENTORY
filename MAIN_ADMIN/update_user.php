<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['user_id'];
  $fullname = $_POST['fullname'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $role = $_POST['role'];
  $status = $_POST['status'];

  // Fetch current role of the target user and block updates for global roles
  $currentRole = null;
  $chk = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
  $chk->bind_param("i", $id);
  $chk->execute();
  $chk->bind_result($currentRole);
  $chk->fetch();
  $chk->close();

  if (in_array($currentRole, ['user', 'admin'], true)) {
    header("Location: user.php?update=forbidden");
    exit();
  }

  $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, role = ?, status = ? WHERE id = ?");
  $stmt->bind_param("sssssi", $fullname, $username, $email, $role, $status, $id);
  $stmt->execute();
  $stmt->close();

  header("Location: user.php?update=success");
  exit();
}
?>
