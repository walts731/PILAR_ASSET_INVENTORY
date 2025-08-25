<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ICS main info
    $form_id = intval($_POST['form_id'] ?? 0); // from hidden input (URL-based ref)
    $header_image = $_POST['header_image'] ?? ''; 
    $entity_name = $_POST['entity_name'] ?? '';
    $fund_cluster = $_POST['fund_cluster'] ?? '';
    $ics_no = $_POST['ics_no'] ?? '';
    $received_from_name = $_POST['received_from_name'] ?? '';
    $received_from_position = $_POST['received_from_position'] ?? '';
    $received_by_name = $_POST['received_by_name'] ?? '';
    $received_by_position = $_POST['received_by_position'] ?? '';
    $created_at = date('Y-m-d H:i:s');
    $office_id = intval($_POST['office_id'] ?? 0);

    // Always INSERT new ICS form with header_image and office_id
    $stmt = $conn->prepare("INSERT INTO ics_form 
        (header_image, entity_name, fund_cluster, ics_no, received_from_name, received_from_position, received_by_name, received_by_position, created_at, office_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssi", 
        $header_image,
        $entity_name, 
        $fund_cluster, 
        $ics_no, 
        $received_from_name, 
        $received_from_position, 
        $received_by_name, 
        $received_by_position, 
        $created_at,
        $office_id
    );
    $stmt->execute();
    $id = $conn->insert_id; // get new ICS id for items
    $stmt->close();

    // Get ICS items data
    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $unit_costs = $_POST['unit_cost'] ?? [];
    $total_costs = $_POST['total_cost'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $item_nos = $_POST['item_no'] ?? [];
    $estimated_lives = $_POST['estimated_useful_life'] ?? [];

    // Prepare insert for items
    $stmt_items = $conn->prepare("INSERT INTO ics_items 
        (ics_id, ics_no, quantity, unit, unit_cost, total_cost, description, item_no, estimated_useful_life, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Prepare update for assets
    $stmt_update_assets = $conn->prepare("UPDATE assets SET quantity = quantity - ? WHERE description = ?");

    for ($i = 0; $i < count($descriptions); $i++) {
        $quantity = isset($quantities[$i]) ? floatval($quantities[$i]) : 0;
        $unit = $units[$i] ?? '';
        $unit_cost = isset($unit_costs[$i]) ? floatval($unit_costs[$i]) : 0;
        $total_cost = isset($total_costs[$i]) ? floatval($total_costs[$i]) : 0;
        $description = $descriptions[$i] ?? '';
        $item_no = $item_nos[$i] ?? '';
        $estimated_life = $estimated_lives[$i] ?? '';

        if (empty($description) || $quantity <= 0) continue; // skip empty rows

        // Insert item
        $stmt_items->bind_param("isddssssss", 
            $id,           // ics_id
            $ics_no,       // ics_no
            $quantity, 
            $unit_cost, 
            $total_cost, 
            $unit, 
            $description, 
            $item_no, 
            $estimated_life, 
            $created_at
        );
        $stmt_items->execute();

        // Subtract from main stock
        $stmt_update_assets->bind_param("ds", $quantity, $description);
        $stmt_update_assets->execute();

        // Transfer to office
        if ($office_id > 0) {
            transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id);
        }
    }

    $stmt_items->close();
    $stmt_update_assets->close();

    // Redirect back
    header("Location: forms.php?id=" . $form_id);
    exit();
}

function transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id) {
    $created_at = date('Y-m-d H:i:s');

    // Fetch asset from main stock
    $fetch = $conn->prepare("SELECT * FROM assets WHERE description = ? LIMIT 1");
    $fetch->bind_param("s", $description);
    $fetch->execute();
    $result = $fetch->get_result();
    $asset = $result->fetch_assoc();
    $fetch->close();

    if (!$asset) return;

    // Check if asset already exists in this office
    $check = $conn->prepare("SELECT id, quantity FROM assets WHERE description = ? AND office_id = ?");
    $check->bind_param("si", $description, $office_id);
    $check->execute();
    $result = $check->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $new_quantity = $row['quantity'] + $quantity;
        $update = $conn->prepare("UPDATE assets SET quantity = ?, value = ?, last_updated = ? WHERE id = ?");
        $update->bind_param("ddsi", $new_quantity, $unit_cost, $created_at, $row['id']);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO assets 
            (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $insert->bind_param("sisisssiiisdssssssss",
            $asset['asset_name'],
            $asset['category'],
            $asset['description'],
            $quantity,
            $asset['unit'],
            $asset['status'],
            $asset['acquisition_date'],
            $office_id,
            $asset['employee_id'],
            $asset['red_tagged'],
            $created_at,
            $unit_cost,
            $asset['qr_code'],
            $asset['type'],
            $asset['image'],
            $asset['serial_no'],
            $asset['code'],
            $asset['property_no'],
            $asset['model'],
            $asset['brand']
        );
        $insert->execute();
        $insert->close();
    }

    $check->close();
}
?>
