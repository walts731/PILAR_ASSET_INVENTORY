<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $id = (int)$_POST['id'];

  // Archive data first
  $archive_query = "INSERT INTO assets_archive 
    (id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type, archived_at)
    SELECT id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code, type, NOW()
    FROM assets WHERE id = $id";

  if ($conn->query($archive_query)) {
    // Now delete from assets
    $delete_query = "DELETE FROM assets WHERE id = $id";
    if ($conn->query($delete_query)) {
      header("Location: inventory.php?delete=success");
      exit();
    } else {
      echo "Failed to delete consumable: " . $conn->error;
    }
  } else {
    echo "Failed to archive consumable: " . $conn->error;
  }
} else {
  echo "Invalid request.";
}
?>
