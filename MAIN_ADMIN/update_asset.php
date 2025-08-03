<?php
require_once '../connect.php';
session_start();

if (
    isset($_POST['id'], $_POST['category'], $_POST['description'],
          $_POST['quantity'], $_POST['unit'], $_POST['status'], $_POST['office'])
) {
    $id = intval($_POST['id']);
    $category = intval($_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = intval($_POST['quantity']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $office = intval($_POST['office']);

    // Image handling
    $image_query = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $original_name = basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            $new_filename = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/", "", $original_name);
            $target_path = "../img/assets/" . $new_filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
                $image_query = ", image = '$new_filename'";
            } else {
                echo "Error uploading image.";
                exit();
            }
        } else {
            echo "Invalid image type. Allowed types: JPG, JPEG, PNG, GIF.";
            exit();
        }
    }

    // Update query
    $sql = "
        UPDATE assets 
        SET 
            category = $category,
            description = '$description',
            quantity = $quantity,
            unit = '$unit',
            status = '$status',
            last_updated = NOW()
            $image_query
        WHERE id = $id AND type = 'asset'
    ";

    if (mysqli_query($conn, $sql)) {
        header("Location: inventory.php?update=success&office=$office");
        exit();
    } else {
        echo "Error updating asset: " . mysqli_error($conn);
    }
} else {
    echo "Missing required fields.";
}
?>
