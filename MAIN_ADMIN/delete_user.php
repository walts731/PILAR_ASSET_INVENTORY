<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !isset($_GET['office'])) {
  header("Location: user.php");
  exit();
}

$user_id = intval($_GET['id']);
$office_id = intval($_GET['office']); // capture office ID

// Attempt deletion
try {
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    header("Location: user.php?office={$office_id}&delete=success");
  } else {
    header("Location: user.php?office={$office_id}&delete=locked");
  }
} catch (mysqli_sql_exception $e) {
  if ($e->getCode() == 1451) {
    // Foreign key constraint violation
    header("Location: user.php?office={$office_id}&delete=locked");
  } else {
    header("Location: user.php?office={$office_id}&delete=error");
  }
}
exit();
?>
