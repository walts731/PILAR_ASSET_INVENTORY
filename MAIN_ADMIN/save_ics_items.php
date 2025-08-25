<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ICS main info
    $form_id = intval($_POST['form_id'] ?? 0); 
    $header_image = $_POST['header_image'] ?? '';
    $entity_name = $_POST['entity_name'] ?? '';
    $fund_cluster = $_POST['fund_cluster'] ?? '';
    $ics_no = $_POST['ics_no'] ?? '';
    $received_from_name = $_POST['received_from_name'] ?? '';
    $received_from_position = $_POST['received_from_position'] ?? '';
    $received_by_name = $_POST['received_by_name'] ?? '';
    $received_by_position = $_POST['received_by_position'] ?? '';
    $office_id = intval($_POST['office_id'] ?? 0);

    // Insert new ICS form
    $stmt = $conn->prepare("INSERT INTO ics_form 
        (header_image, entity_name, fund_cluster, ics_no, received_from_name, received_from_position, received_by_name, received_by_position, office_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param(
        "ssssssssi",
        $header_image,
        $entity_name,
        $fund_cluster,
        $ics_no,
        $received_from_name,
        $received_from_position,
        $received_by_name,
        $received_by_position,
        $office_id
    );
    $stmt->execute();
    $ics_id = $conn->insert_id;
    $stmt->close();

    // ICS items data
    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $unit_costs = $_POST['unit_cost'] ?? [];
    $total_costs = $_POST['total_cost'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $item_nos = $_POST['item_no'] ?? [];
    $estimated_lives = $_POST['estimated_useful_life'] ?? [];

    // Prepare ICS items insert
    $stmt_items = $conn->prepare("INSERT INTO ics_items 
        (ics_id, asset_id, ics_no, quantity, unit, unit_cost, total_cost, description, item_no, estimated_useful_life, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    // Prepare stock update
    $stmt_update_assets = $conn->prepare("UPDATE assets SET quantity = quantity - ? WHERE description = ?");

    for ($i = 0; $i < count($descriptions); $i++) {
        $quantity = isset($quantities[$i]) ? floatval($quantities[$i]) : 0;
        $unit = $units[$i] ?? '';
        $unit_cost = isset($unit_costs[$i]) ? floatval($unit_costs[$i]) : 0;
        $total_cost = isset($total_costs[$i]) ? floatval($total_costs[$i]) : 0;
        $description = $descriptions[$i] ?? '';
        $item_no = $item_nos[$i] ?? '';
        $estimated_life = $estimated_lives[$i] ?? '';

        if (empty($description) || $quantity <= 0) continue;

        // Fetch asset_id
        $asset_id = null;
        $stmt_asset = $conn->prepare("SELECT id FROM assets WHERE description = ? LIMIT 1");
        $stmt_asset->bind_param("s", $description);
        $stmt_asset->execute();
        $result_asset = $stmt_asset->get_result();
        if ($result_asset && $row_asset = $result_asset->fetch_assoc()) {
            $asset_id = $row_asset['id'];
        }
        $stmt_asset->close();

        // Insert ICS item
        $stmt_items->bind_param(
            "iisdssssss",
            $ics_id,
            $asset_id,
            $ics_no,
            $quantity,
            $unit,
            $unit_cost,
            $total_cost,
            $description,
            $item_no,
            $estimated_life
        );
        $stmt_items->execute();

        // Update main stock
        $stmt_update_assets->bind_param("ds", $quantity, $description);
        $stmt_update_assets->execute();

        // Transfer to office
        if ($office_id > 0) {
            transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id);
        }
    }

    $stmt_items->close();
    $stmt_update_assets->close();

    header("Location: forms.php?id=" . $form_id);
    exit();
}

// Transfer asset to office with QR code generation
function transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id)
{
    $created_at = date('Y-m-d H:i:s');

    // Fetch main stock asset
    $fetch = $conn->prepare("SELECT * FROM assets WHERE description = ? LIMIT 1");
    $fetch->bind_param("s", $description);
    $fetch->execute();
    $result = $fetch->get_result();
    $asset = $result->fetch_assoc();
    $fetch->close();

    if (!$asset) return;

    // Check if asset exists in office
    $check = $conn->prepare("SELECT id, quantity FROM assets WHERE description = ? AND office_id = ?");
    $check->bind_param("si", $description, $office_id);
    $check->execute();
    $result_check = $check->get_result();

    if ($result_check && $row = $result_check->fetch_assoc()) {
        $new_quantity = $row['quantity'] + $quantity;
        $update = $conn->prepare("UPDATE assets SET quantity = ?, value = ?, last_updated = ? WHERE id = ?");
        $update->bind_param("ddsi", $new_quantity, $unit_cost, $created_at, $row['id']);
        $update->execute();
        $update->close();
    } else {
        // Insert new office asset
        $qr_code = '';
        $insert = $conn->prepare("INSERT INTO assets 
            (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $insert->bind_param(
            "sisisssiiisdssssssss",
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
            $qr_code,
            $asset['type'],
            $asset['image'],
            $asset['serial_no'],
            $asset['code'],
            $asset['property_no'],
            $asset['model'],
            $asset['brand']
        );
        $insert->execute();

        // Generate QR code for the new asset
        $new_asset_id = $conn->insert_id;
        $qr_filename = $new_asset_id . '.png';
        $qr_path = '../img/' . $qr_filename;
        QRcode::png((string)$new_asset_id, $qr_path, QR_ECLEVEL_L, 4);

        // Update asset with QR code
        $update_qr = $conn->prepare("UPDATE assets SET qr_code = ? WHERE id = ?");
        $update_qr->bind_param("si", $qr_filename, $new_asset_id);
        $update_qr->execute();
        $update_qr->close();
    }

    $check->close();
}
?>
