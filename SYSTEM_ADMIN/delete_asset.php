<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  $id = (int)$_GET['id'];

  // Archive first
  $archive_query = "INSERT INTO assets_archive 
    (id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type, archived_at)
    SELECT id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type, NOW()
    FROM assets WHERE id = $id";

  if ($conn->query($archive_query)) {
    $delete_query = "DELETE FROM assets WHERE id = $id";
    if ($conn->query($delete_query)) {
      header("Location: inventory.php?delete=success");
      exit();
    } else {
      echo "Failed to delete asset: " . $conn->error;
    }
  } else {
    echo "Failed to archive asset: " . $conn->error;
  }
} else {
  echo "Invalid request.";
}
?>
