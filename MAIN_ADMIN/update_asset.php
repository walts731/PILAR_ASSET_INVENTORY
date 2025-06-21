<?php
require_once '../connect.php';
session_start();

// Check if all required GET parameters are set
if (
    isset($_GET['id'], $_GET['asset_name'], $_GET['category'], $_GET['description'],
          $_GET['quantity'], $_GET['unit'], $_GET['status'])
) {
    // Sanitize and assign variables
    $id = intval($_GET['id']);
    $name = mysqli_real_escape_string($conn, $_GET['asset_name']);
    $category = intval($_GET['category']);
    $description = mysqli_real_escape_string($conn, $_GET['description']);
    $quantity = intval($_GET['quantity']);
    $unit = mysqli_real_escape_string($conn, $_GET['unit']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    // Build SQL query
    $sql = "
        UPDATE assets 
        SET 
            asset_name = '$name',
            category = $category,
            description = '$description',
            quantity = $quantity,
            unit = '$unit',
            status = '$status',
            last_updated = NOW()
        WHERE id = $id AND type = 'asset'
    ";

    // Execute and redirect or show error
    if (mysqli_query($conn, $sql)) {
        header("Location: inventory.php?update=success");
        exit();
    } else {
        echo "Error updating asset: " . mysqli_error($conn);
    }
} else {
    echo "Missing required fields.";
}
?>
