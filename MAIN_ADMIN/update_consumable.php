<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? $conn->real_escape_string(trim($_POST['status'])) : '';
    $office = isset($_POST['office']) ? $conn->real_escape_string(trim($_POST['office'])) : '';
    $removeImage = isset($_POST['remove_image']) ? (int)$_POST['remove_image'] : 0;

    if ($id > 0 && $status !== '') {
        // Image handling: upload or remove
        $image_query = '';
        if ($removeImage === 1) {
            $image_query = ", image = NULL";
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $original_name = basename($_FILES['image']['name']);
            $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($imageFileType, $allowed_types)) {
                $new_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9_\.-]/", '', $original_name);
                $target_path = '../img/assets/' . $new_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_query = ", image = '$new_filename'"; // overrides removal if both set
                } else {
                    echo 'Error uploading image.';
                    exit();
                }
            } else {
                echo 'Invalid image type.';
                exit();
            }
        }

        // Only update status (and image if provided)
        $sql = "
            UPDATE assets
            SET
                status = '$status',
                last_updated = NOW()
                $image_query
            WHERE id = $id AND type = 'consumable'
        ";

        if ($conn->query($sql)) {
            // Redirect back preserving office filter
            $officeParam = $office !== '' ? urlencode($office) : 'all';
            header('Location: http://localhost/pilar_asset_inventory/MAIN_ADMIN/inventory.php?office=' . $officeParam . '&update=success#consumables');
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
