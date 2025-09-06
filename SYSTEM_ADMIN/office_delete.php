<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $office_id = intval($_POST['id']);

  // ✅ Check if this office has linked assets
  $stmt = $conn->prepare("SELECT COUNT(*) as total FROM assets WHERE office_id = ?");
  $stmt->bind_param("i", $office_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  if ($row['total'] > 0) {
    $_SESSION['message'] = "❌ Cannot delete this office because it has asset records.";
    $_SESSION['message_type'] = "danger";
    header("Location: manage_offices.php");
    exit();
  }

  // ✅ If no assets, safe to delete
  $stmt = $conn->prepare("DELETE FROM offices WHERE id = ?");
  $stmt->bind_param("i", $office_id);
  if ($stmt->execute()) {
    $_SESSION['message'] = "✅ Office deleted successfully.";
    $_SESSION['message_type'] = "success";
  } else {
    $_SESSION['message'] = "⚠️ Error deleting office.";
    $_SESSION['message_type'] = "warning";
  }
  $stmt->close();
}

header("Location: manage_offices.php");
exit();
