<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['user_id'];
  $fullname = $_POST['fullname'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $role = $_POST['role'];
  $status = $_POST['status'];

  $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, role = ?, status = ? WHERE id = ?");
  $stmt->bind_param("sssssi", $fullname, $username, $email, $role, $status, $id);
  $stmt->execute();
  $stmt->close();

  header("Location: user.php?update=success");
  exit();
}
?>
