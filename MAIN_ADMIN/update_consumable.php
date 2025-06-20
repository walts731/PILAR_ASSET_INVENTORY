<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Sanitize and validate
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 0;
  $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

  if ($id > 0 && $status !== '') {
    $sql = "UPDATE assets 
            SET quantity = $quantity, 
                status = '$status', 
                last_updated = NOW() 
            WHERE id = $id";

    if ($conn->query($sql)) {
      header("Location: admin_dashboard.php?tab=update=success");
      exit();
    } else {
      echo "Failed to update: " . $conn->error;
    }
  } else {
    echo "Missing or invalid parameters.";
  }
}
?>
