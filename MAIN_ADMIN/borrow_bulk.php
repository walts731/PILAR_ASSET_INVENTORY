<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if (!isset($_GET['ids'])) {
  die("No assets selected.");
}

$ids = explode(',', $_GET['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

// Example: Update status to "borrowed"
$stmt = $conn->prepare("UPDATE assets SET status = 'borrowed' WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$stmt->close();

$office = $_GET['office'] ?? '';
header("Location: inventory.php?bulk=success&office=" . urlencode($office));
exit();

