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

    // Handle OFFICE selection (can be numeric ID or "outside_lgu")
    $office_input = $_POST['office_id'] ?? 0;
    $is_outside_lgu = ($office_input === 'outside_lgu');
    $office_id = $is_outside_lgu ? 0 : intval($office_input);

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
        $office_id // 0 if "Outside LGU"
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

        // Always deduct from main stock
        $stmt_update_assets->bind_param("ds", $quantity, $description);
        $stmt_update_assets->execute();

        // Decide asset_id
        if ($is_outside_lgu) {
            // ✅ Just reduce main stock, no transfer
            $latest_asset_id = getMainStockAssetId($conn, $description);
        } elseif ($office_id > 0) {
            // ✅ Transfer asset to office
            $latest_asset_id = transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id);
        } else {
            // ✅ Default to main stock asset
            $latest_asset_id = getMainStockAssetId($conn, $description);
        }

        // Insert ICS item with the latest asset_id
        $stmt_items->bind_param(
            "iisdssssss",
            $ics_id,
            $latest_asset_id,
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
    }

    $stmt_items->close();
    $stmt_update_assets->close();

    header("Location: forms.php?id=" . $form_id);
    exit();
}

// Get main stock asset id by description
function getMainStockAssetId($conn, $description) {
    $stmt = $conn->prepare("SELECT id FROM assets WHERE description = ? LIMIT 1");
    $stmt->bind_param("s", $description);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['id'] : null;
}

// Transfer asset to office with QR code generation, returns inserted asset_id
function transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id)
{
    $created_at = date('Y-m-d H:i:s');

    // Fetch main stock asset
    $stmt = $conn->prepare("SELECT * FROM assets WHERE description = ? LIMIT 1");
    $stmt->bind_param("s", $description);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset = $result->fetch_assoc();
    $stmt->close();
    if (!$asset) return null;

    // Check if asset exists in office
    $stmt = $conn->prepare("SELECT id, quantity FROM assets WHERE description = ? AND office_id = ?");
    $stmt->bind_param("si", $description, $office_id);
    $stmt->execute();
    $result_check = $stmt->get_result();
    $stmt->close();

    if ($result_check && $row = $result_check->fetch_assoc()) {
        $new_quantity = $row['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE assets SET quantity = ?, value = ?, last_updated = ? WHERE id = ?");
        $stmt->bind_param("ddsi", $new_quantity, $unit_cost, $created_at, $row['id']);
        $stmt->execute();
        $stmt->close();
        return $row['id'];
    } else {
        // Insert new office asset
        $qr_code = '';
        $stmt = $conn->prepare("INSERT INTO assets 
            (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
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
        $stmt->execute();
        $new_asset_id = $conn->insert_id;
        $stmt->close();

        // Generate QR code
        $qr_filename = $new_asset_id . '.png';
        $qr_path = '../img/' . $qr_filename;
        QRcode::png((string)$new_asset_id, $qr_path, QR_ECLEVEL_L, 4);

        // Update asset with QR code
        $stmt = $conn->prepare("UPDATE assets SET qr_code = ? WHERE id = ?");
        $stmt->bind_param("si", $qr_filename, $new_asset_id);
        $stmt->execute();
        $stmt->close();

        return $new_asset_id;
    }
}
?>
