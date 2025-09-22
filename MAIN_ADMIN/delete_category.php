<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category_id'])) {
  $id = $_POST['category_id'];

  // Get category name before deletion
  $category_stmt = $conn->prepare("SELECT category_name FROM categories WHERE id = ?");
  $category_stmt->bind_param("i", $id);
  $category_stmt->execute();
  $category_result = $category_stmt->get_result();
  $category_data = $category_result->fetch_assoc();
  $category_stmt->close();

  $category_name = $category_data['category_name'] ?? 'Unknown Category';

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
    if ($stmt->execute()) {
      // Log category deletion
      logConfigActivity('Category', $category_name, 'DELETE', $id);
    } else {
      // Log deletion failure
      logErrorActivity('Categories', "Failed to delete category: {$category_name}");
    }
    $stmt->close();
  } else {
    // Log deletion failure - category in use
    logErrorActivity('Categories', "Failed to delete category: {$category_name} - Category is in use by {$used} assets");
  }
}
header("Location: inventory.php?category_deleted=success");
exit();
