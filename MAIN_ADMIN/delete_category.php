<?php
require_once '../connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category_id'])) {
  $id = $_POST['category_id'];

  // Check if category is used
  $check = $conn->prepare("SELECT COUNT(*) FROM assets WHERE category = ?");
  $check->bind_param("i", $id);
  $check->execute();
  $check->bind_result($used);
  $check->fetch();
  $check->close();

  if ($used == 0) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }
}
header("Location: inventory.php?category_deleted=success");
exit();
