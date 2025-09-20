<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $id = (int)$_POST['id'];
  if ($id <= 0) {
    header("Location: inventory.php?delete=invalid");
    exit();
  }

  // Force delete: disable FK checks, delete, then re-enable
  $conn->query("SET FOREIGN_KEY_CHECKS=0");
  $delete_query = "DELETE FROM assets WHERE id = $id";
  $ok = $conn->query($delete_query);
  $conn->query("SET FOREIGN_KEY_CHECKS=1");

  if ($ok) {
    header("Location: inventory.php?delete=success");
    exit();
  } else {
    echo "Failed to force delete consumable: " . $conn->error;
  }
} else {
  echo "Invalid request.";
}
?>
