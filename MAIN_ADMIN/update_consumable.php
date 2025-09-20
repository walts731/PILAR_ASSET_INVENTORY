<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';
    $unit = isset($_POST['unit']) ? $conn->real_escape_string(trim($_POST['unit'])) : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $status = isset($_POST['status']) ? $conn->real_escape_string(trim($_POST['status'])) : '';

    // Category removed from the form; do not require it for update
    if ($id > 0 && $description !== '' && $unit !== '' && $status !== '') {

        // Fetch current asset info to identify related RIS item
        $property_no = '';
        $old_desc = '';
        if ($stmtA = $conn->prepare("SELECT property_no, description FROM assets WHERE id = ? AND type = 'consumable'")) {
            $stmtA->bind_param("i", $id);
            $stmtA->execute();
            $stmtA->bind_result($property_no, $old_desc);
            $stmtA->fetch();
            $stmtA->close();
        }

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
                description = '$description',
                unit = '$unit',
                quantity = $quantity,
                added_stock = $quantity,
                status = '$status',
                last_updated = NOW()
                $image_query
            WHERE id = $id AND type = 'consumable'
        ";

        if ($conn->query($sql)) {
            // Try to update the most recent matching RIS item quantity
            // Prefer match by stock_no (property_no) and description; fall back to description only
            $ris_item_id = null;
            if (!empty($property_no)) {
                if ($stmt = $conn->prepare("SELECT ri.id
                                             FROM ris_items ri
                                             INNER JOIN ris_form rf ON rf.id = ri.ris_form_id
                                             WHERE ri.stock_no = ? AND ri.description = ?
                                             ORDER BY rf.date DESC, ri.ris_form_id DESC, ri.id DESC
                                             LIMIT 1")) {
                    $stmt->bind_param("ss", $property_no, $description);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) { $ris_item_id = (int)$row['id']; }
                    $stmt->close();
                }
            }

            if (!$ris_item_id) {
                if ($stmt = $conn->prepare("SELECT ri.id
                                             FROM ris_items ri
                                             INNER JOIN ris_form rf ON rf.id = ri.ris_form_id
                                             WHERE ri.description = ?
                                             ORDER BY rf.date DESC, ri.ris_form_id DESC, ri.id DESC
                                             LIMIT 1")) {
                    $stmt->bind_param("s", $description);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) { $ris_item_id = (int)$row['id']; }
                    $stmt->close();
                }
            }

            if ($ris_item_id) {
                if ($u = $conn->prepare("UPDATE ris_items SET quantity = ?, unit = ? WHERE id = ?")) {
                    $u->bind_param("isi", $quantity, $unit, $ris_item_id);
                    $u->execute();
                    $u->close();
                }
            }

            header('Location: http://localhost/pilar_asset_inventory/MAIN_ADMIN/inventory.php?office=3#consumables');
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
