<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

// Restrict Office Admin to update only status of consumables in their office
$office_id = $_SESSION['office_id'] ?? null;
if (!$office_id) {
  $_SESSION['error_message'] = 'Office not set in session.';
  header('Location: inventory.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['error_message'] = 'Invalid request method.';
  header('Location: inventory.php');
  exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? trim(strtolower($_POST['status'])) : '';

$allowed = ['available', 'unavailable'];
if ($id <= 0 || !in_array($status, $allowed, true)) {
  $_SESSION['error_message'] = 'Invalid consumable ID or status.';
  header('Location: inventory.php');
  exit();
}

// Update only status for a consumable that belongs to this office
$stmt = $conn->prepare("UPDATE assets SET status = ?, last_updated = NOW() WHERE id = ? AND type = 'consumable' AND office_id = ?");
if (!$stmt) {
  $_SESSION['error_message'] = 'Database error: ' . $conn->error;
  header('Location: inventory.php');
  exit();
}

$stmt->bind_param('sii', $status, $id, $office_id);
if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    $_SESSION['success_message'] = 'Consumable status updated successfully.';
  } else {
    // No row updated could mean wrong office, wrong type, or same status
    $_SESSION['error_message'] = 'No changes applied. Check consumable ownership or status.';
  }
} else {
  $_SESSION['error_message'] = 'Failed to update status: ' . $stmt->error;
}
$stmt->close();

header('Location: inventory.php#consumables');
exit();
