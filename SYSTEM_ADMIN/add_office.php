<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['office_name']);

  if (!empty($name)) {
    // Check if office already exists
    $check = $conn->prepare("SELECT id FROM offices WHERE office_name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      // Duplicate office found
      header("Location: user.php?office_add=duplicate");
      exit();
    }

    // Proceed to insert
    $stmt = $conn->prepare("INSERT INTO offices (office_name) VALUES (?)");
    $stmt->bind_param("s", $name);
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
