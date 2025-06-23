<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
  header("Location: user.php");
  exit();
}

$user_id = $_GET['id'];

// Attempt deletion
try {
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    header("Location: user.php?delete=success");
  } else {
    // Possibly locked or doesn't exist
    header("Location: user.php?delete=locked");
  }
} catch (mysqli_sql_exception $e) {
  // Foreign key constraint violation
  if ($e->getCode() == 1451) {
    header("Location: user.php?delete=locked");
  } else {
    header("Location: user.php?delete=error");
  }
}
exit();
?>
