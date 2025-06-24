<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['office_name']);
  $icon = trim($_POST['icon']);

  if (!empty($name)) {
    $stmt = $conn->prepare("INSERT INTO offices (office_name, icon) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $icon);
    if ($stmt->execute()) {
      header("Location: user.php?office_add=success");
      exit();
    } else {
      header("Location: user.php?office_add=error");
      exit();
    }
  } else {
    header("Location: user.php?office_add=empty");
    exit();
  }
}
?>
