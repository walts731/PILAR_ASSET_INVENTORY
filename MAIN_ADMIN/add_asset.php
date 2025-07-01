<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Sanitize and escape input values
$asset_name   = mysqli_real_escape_string($conn, $_POST['asset_name']);
$category     = (int)$_POST['category'];
$description  = mysqli_real_escape_string($conn, $_POST['description']);
$quantity     = (int)$_POST['quantity'];
$unit         = mysqli_real_escape_string($conn, $_POST['unit']);
$value        = (float)$_POST['value'];
$status       = mysqli_real_escape_string($conn, $_POST['status']);
$office_id    = (int)$_POST['office_id'];
$type         = mysqli_real_escape_string($conn, $_POST['type']);
$acquired     = date('Y-m-d');
$red_tagged   = 0;

// Insert asset into the database
$sql = "
  INSERT INTO assets 
    (asset_name, category, description, quantity, unit, value, status, office_id, type, red_tagged, acquisition_date, last_updated)
  VALUES 
    ('$asset_name', $category, '$description', $quantity, '$unit', $value, '$status', $office_id, '$type', $red_tagged, '$acquired', '$acquired')
";

if (mysqli_query($conn, $sql)) {
  $asset_id = mysqli_insert_id($conn);

  // Generate QR code and save
  $qr_filename = $asset_id . '.png';
  $qr_path = '../img/' . $qr_filename;
  QRcode::png((string)$asset_id, $qr_path, QR_ECLEVEL_L, 4);

  // Update the asset with the QR code filename
  $update = "UPDATE assets SET qr_code = '$qr_filename' WHERE id = $asset_id";
  mysqli_query($conn, $update);

  header("Location: inventory.php?add=success&qr=" . urlencode($qr_filename));
  exit();
} else {
  echo "Error inserting asset: " . mysqli_error($conn);
  exit();
}
?>
