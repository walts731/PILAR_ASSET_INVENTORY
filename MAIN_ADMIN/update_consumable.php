<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Sanitize and validate
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 0;
  $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
  $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
  $description = isset($_GET['description']) ? $conn->real_escape_string(trim($_GET['description'])) : '';
  $unit = isset($_GET['unit']) ? $conn->real_escape_string(trim($_GET['unit'])) : '';

  // Validate required fields
  if ($id > 0 && $status !== '' && $name !== '' && $category > 0 && $unit !== '') {
    $sql = "
      UPDATE assets 
      SET 
        category = $category,
        description = '$description',
        unit = '$unit',
        quantity = $quantity,
        status = '$status',
        last_updated = NOW()
      WHERE id = $id
    ";

    if ($conn->query($sql)) {
      header("Location: inventory.php?update=success");
      exit();
    } else {
      echo "Failed to update: " . $conn->error;
    }
  } else {
    echo "Missing or invalid parameters.";
  }
}
?>
