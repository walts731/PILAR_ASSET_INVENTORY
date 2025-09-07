<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Sanitize and escape input values
$category    = (int)$_POST['category'];
$description = mysqli_real_escape_string($conn, $_POST['description']);
$quantity    = (int)$_POST['quantity'];
$unit        = mysqli_real_escape_string($conn, $_POST['unit']);
$value       = (float)$_POST['value'];
$status      = mysqli_real_escape_string($conn, $_POST['status']);
$office_id   = (int)$_POST['office_id'];
$type        = mysqli_real_escape_string($conn, $_POST['type']);
$acquired    = date('Y-m-d');
$red_tagged  = 0;

// New optional fields
$serial_no   = !empty($_POST['serial_no']) ? mysqli_real_escape_string($conn, $_POST['serial_no']) : null;
$code        = !empty($_POST['code']) ? mysqli_real_escape_string($conn, $_POST['code']) : null;
$stock_no    = !empty($_POST['stock_no']) ? mysqli_real_escape_string($conn, $_POST['stock_no']) : null; // will be used as property_no
$model       = !empty($_POST['model']) ? mysqli_real_escape_string($conn, $_POST['model']) : null;
$brand       = !empty($_POST['brand']) ? mysqli_real_escape_string($conn, $_POST['brand']) : null;

// Image handling
$image_filename = null;
if (isset($_FILES['asset_image']) && $_FILES['asset_image']['error'] === UPLOAD_ERR_OK) {
    $image_tmp = $_FILES['asset_image']['tmp_name'];
    $image_name = basename($_FILES['asset_image']['name']);
    $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed)) {
        $image_filename = 'asset_' . time() . '.' . $ext;
        $upload_dir = '../img/assets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        move_uploaded_file($image_tmp, $upload_dir . $image_filename);
    }
}

// Force property_no to take the same value as stock_no
$propertyNoValue = $stock_no ? "'$stock_no'" : "NULL";

// Insert asset into the database
$sql = "
  INSERT INTO assets 
    (category, description, quantity, unit, value, status, office_id, type, red_tagged, 
     acquisition_date, last_updated, image, serial_no, code, property_no, model, brand)
  VALUES 
    ($category, '$description', $quantity, '$unit', $value, '$status', $office_id, '$type', $red_tagged, 
     '$acquired', '$acquired', " . ($image_filename ? "'$image_filename'" : "NULL") . ",
     " . ($serial_no ? "'$serial_no'" : "NULL") . ",
     " . ($code ? "'$code'" : "NULL") . ",
     $propertyNoValue,
     " . ($model ? "'$model'" : "NULL") . ",
     " . ($brand ? "'$brand'" : "NULL") . "
    )
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

    header("Location: inventory.php?add=success&qr=" . urlencode($qr_filename) . "&office_id=" . $office_id);
    exit();
} else {
    echo "Error inserting asset: " . mysqli_error($conn);
    exit();
}
