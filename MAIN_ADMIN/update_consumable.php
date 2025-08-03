<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $category = isset($_POST['category']) ? (int)$_POST['category'] : 0;
    $description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
    $unit = isset($_POST['unit']) ? $conn->real_escape_string(trim($_POST['unit'])) : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $status = isset($_POST['status']) ? $conn->real_escape_string(trim($_POST['status'])) : '';

    if ($id > 0 && $category > 0 && $description !== '' && $unit !== '' && $status !== '') {

        // Image upload handling
        $image_query = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $original_name = basename($_FILES['image']['name']);
            $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                $new_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9_\.-]/", '', $original_name);
                $target_path = '../img/assets/' . $new_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_query = ", image = '$new_filename'";
                } else {
                    echo 'Error uploading image.';
                    exit();
                }
            } else {
                echo 'Invalid image type.';
                exit();
            }
        }

        // Build the update query
        $sql = "
            UPDATE assets
            SET
                category = $category,
                description = '$description',
                unit = '$unit',
                quantity = $quantity,
                status = '$status',
                last_updated = NOW()
                $image_query
            WHERE id = $id AND type = 'consumable'
        ";

        if ($conn->query($sql)) {
            header('Location: inventory.php?update=success');
            exit();
        } else {
            echo 'Failed to update: ' . $conn->error;
        }

    } else {
        echo 'Missing required fields.';
    }
} else {
    echo 'Invalid request method.';
}
?>
