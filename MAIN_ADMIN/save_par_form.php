<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Get main PAR form data ---
    $form_id = intval($_POST['form_id'] ?? 0);
    $header_image = $_POST['header_image'] ?? '';
    $entity_name = $_POST['entity_name'] ?? '';
    $fund_cluster = $_POST['fund_cluster'] ?? '';
    $par_no = $_POST['par_no'] ?? '';
    $position_left = $_POST['position_office_left'] ?? '';
    $position_right = $_POST['position_office_right'] ?? '';
    $date_received_left = $_POST['date_received_left'] ?? date('Y-m-d');
    $date_received_right = $_POST['date_received_right'] ?? date('Y-m-d');

    // --- Handle office selection ---
    $office_input = $_POST['office_id'] ?? 0;
    $is_outside_lgu = ($office_input === 'outside_lgu');
    $office_id = $is_outside_lgu ? null : intval($office_input);

    // --- Insert PAR form ---
    $stmt = $conn->prepare("INSERT INTO par_form 
        (header_image, entity_name, fund_cluster, par_no, position_office_left, position_office_right, date_received_left, date_received_right, office_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param(
        "sssssssss",
        $header_image,
        $entity_name,
        $fund_cluster,
        $par_no,
        $position_left,
        $position_right,
        $date_received_left,
        $date_received_right,
        $office_id
    );
    $stmt->execute();
    $par_id = $conn->insert_id;
    $stmt->close();

    // --- Prepare PAR items insert ---
    $stmt_items = $conn->prepare("INSERT INTO par_items 
        (form_id, asset_id, quantity, unit, description, property_no, date_acquired, unit_price, amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // --- Prepare stock update ---
    $stmt_update_assets = $conn->prepare("UPDATE assets SET quantity = quantity - ? WHERE id = ?");

    $items = $_POST['items'] ?? [];

    foreach ($items as $item) {
        $asset_id = intval($item['asset_id'] ?? 0);
        $quantity = floatval($item['quantity'] ?? 0);
        $unit = $item['unit'] ?? '';
        $description = trim($item['description'] ?? '');
        $property_no = $item['property_no'] ?? '';
        $date_acquired = $item['date_acquired'] ?? null;
        $unit_price = floatval($item['unit_price'] ?? 0);
        $amount = floatval($item['amount'] ?? 0);

        if (!$asset_id || $quantity <= 0 || empty($description)) continue;

        // --- Deduct stock from main asset ---
        $stmt_update_assets->bind_param("ii", $quantity, $asset_id);
        $stmt_update_assets->execute();

        // --- Determine asset for PAR item ---
        if ($is_outside_lgu) {
            $latest_asset_id = $asset_id; // just deduct, no transfer
        } elseif ($office_id > 0) {
            // transfer asset to office
            $latest_asset_id = transferAssetToOffice($conn, $asset_id, $quantity, $unit_price, $office_id);
        } else {
            $latest_asset_id = $asset_id;
        }

        // --- Insert into PAR items ---
        $stmt_items->bind_param(
            "iidssssdd",
            $par_id,
            $latest_asset_id,
            $quantity,
            $unit,
            $description,
            $property_no,
            $date_acquired,
            $unit_price,
            $amount
        );
        if (!$stmt_items->execute()) {
            die("PAR item insert error: " . $stmt_items->error);
        }
    }

    $stmt_items->close();
    $stmt_update_assets->close();

    header("Location: forms.php?id=" . $form_id . "&success=1");
    exit();
}

// --- Function: transfer asset to office ---
function transferAssetToOffice($conn, $asset_id, $quantity, $unit_cost, $office_id)
{
    $created_at = date('Y-m-d H:i:s');

    // Fetch main asset
    $stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
    $stmt->bind_param("i", $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset = $result->fetch_assoc();
    $stmt->close();
    if (!$asset) return null;

    // Check if asset already exists in the office
    $stmt = $conn->prepare("SELECT id, quantity FROM assets WHERE description = ? AND office_id = ?");
    $stmt->bind_param("si", $asset['description'], $office_id);
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
        // Insert new asset for office
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
