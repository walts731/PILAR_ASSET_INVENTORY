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
    $received_by_name = trim($_POST['received_by_name'] ?? '');
    $issued_by_name = trim($_POST['issued_by_name'] ?? '');
    $date_received_left = $_POST['date_received_left'] ?? date('Y-m-d');
    $date_received_right = $_POST['date_received_right'] ?? date('Y-m-d');

    // --- Handle office selection ---
    $office_input = $_POST['office_id'] ?? 0;
    $is_outside_lgu = ($office_input === 'outside_lgu');
    $office_id = $is_outside_lgu ? null : intval($office_input);

    // --- Insert PAR form ---
    $stmt = $conn->prepare("INSERT INTO par_form 
        (header_image, entity_name, fund_cluster, par_no, position_office_left, position_office_right, date_received_left, date_received_right, office_id, received_by_name, issued_by_name, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param(
        "ssssssssiss",
        $header_image,
        $entity_name,
        $fund_cluster,
        $par_no,
        $position_left,
        $position_right,
        $date_received_left,
        $date_received_right,
        $office_id,
        $received_by_name,
        $issued_by_name
    );
    $stmt->execute();
    $par_id = $conn->insert_id;
    $stmt->close();

    // --- Prepare PAR items insert ---
    $stmt_items = $conn->prepare("INSERT INTO par_items 
        (form_id, asset_id, quantity, unit, description, property_no, date_acquired, unit_price, amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $items = $_POST['items'] ?? [];

    // --- Skipped items collector ---
    $skipped = [];

    // --- Prepare insert into assets_new (aggregate per PAR line) with explicit par linkage ---
    $stmt_assets_new = $conn->prepare("INSERT INTO assets_new (description, quantity, unit_cost, unit, office_id, par_id, date_created) VALUES (?, ?, ?, ?, ?, ?, NOW())");

    foreach ($items as $item) {
        $quantity = floatval($item['quantity'] ?? 0);
        $unit = $item['unit'] ?? '';
        $description = trim($item['description'] ?? '');
        $property_no = $item['property_no'] ?? '';
        $date_acquired = $item['date_acquired'] ?? null;
        $unit_price = floatval($item['unit_price'] ?? 0);
        $amount = floatval($item['amount'] ?? 0);

        if ($quantity <= 0 || empty($description)) continue;
        // Server-side enforcement for PAR rule: unit price must be > 50,000
        if ($unit_price <= 50000) {
            $skipped[] = "Item '" . $description . "' skipped (unit price must be > 50,000).";
            continue;
        }

        // --- Insert aggregate record into assets_new (like ICS flow) ---
        $target_office = $is_outside_lgu ? 0 : (int)$office_id; // align with ICS approach
        $stmt_assets_new->bind_param("sddsii", $description, $quantity, $unit_price, $unit, $target_office, $par_id);
        $stmt_assets_new->execute();
        $asset_new_id = $conn->insert_id;

        // --- Create item-level assets (quantity=1 each), linked to assets_new ---
        $first_item_id = createItemAssetsDirect(
            $conn,
            $description,
            $unit,
            (float)$unit_price,
            (int)$quantity,
            $is_outside_lgu ? null : ($office_id > 0 ? (int)$office_id : null),
            $property_no,
            $date_acquired ?: date('Y-m-d'),
            null, // keep assets.ics_id = NULL for PAR-created rows to satisfy FK to ics_form(id)
            (int)$asset_new_id,
            (int)$par_id // set assets.par_id for linkage to PAR
        );
        $latest_asset_id = $first_item_id;

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
    if (isset($stmt_assets_new)) { $stmt_assets_new->close(); }

    // Flash messages consistent with ICS flow
    $_SESSION['flash'] = [
        'type' => empty($skipped) ? 'success' : 'warning',
        'message' => empty($skipped)
            ? 'PAR has been saved successfully.'
            : ('PAR saved with some items skipped: ' . implode(' ', $skipped))
    ];

    header("Location: forms.php?id=" . $form_id);
    exit();
}

// Create item-level assets directly (quantity=1) and link to assets_new
function createItemAssetsDirect($conn, $description, $unit, $unit_cost, $count, $office_id, $item_no, $date_acquired, $ics_id, $asset_new_id, $par_id) {
    if ($count <= 0) return null;

    $qrDir = realpath(__DIR__ . '/../img');
    if (!$qrDir) { $qrDir = __DIR__; }

    $stmtIns = $conn->prepare("INSERT INTO assets 
        (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand, ics_id, asset_new_id, par_id)
        VALUES (?, ?, 1, ?, 'available', ?, ?, ?, ?, NOW(), ?, '', ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?)");

    $first_inserted_id = null;
    for ($i = 1; $i <= $count; $i++) {
        $p_asset_name = $description;
        $p_description = $description;
        $p_unit = $unit;
        $p_acq = $date_acquired ?: date('Y-m-d');
        $p_office = isset($office_id) ? (int)$office_id : null;
        $p_emp = null;
        $p_red = 0;
        $p_value = (float)$unit_cost;
        $p_type = 'asset';
        $p_image = '';
        $p_serial = '';
        $p_code = '';
        $p_model = '';
        $p_brand = '';
        $p_ics = isset($ics_id) ? (int)$ics_id : null;
        $p_asset_new = (int)$asset_new_id;
        $p_par = isset($par_id) ? (int)$par_id : null;

        $stmtIns->bind_param(
            'ssssiiidssssssiii',
            $p_asset_name,
            $p_description,
            $p_unit,
            $p_acq,
            $p_office,
            $p_emp,
            $p_red,
            $p_value,
            $p_type,
            $p_image,
            $p_serial,
            $p_code,
            $p_model,
            $p_brand,
            $p_ics,
            $p_asset_new,
            $p_par
        );

        if ($stmtIns->execute()) {
            $new_item_id = $conn->insert_id;
            if ($first_inserted_id === null) { $first_inserted_id = $new_item_id; }
            $qr_filename = $new_item_id . '.png';
            $qr_path = $qrDir . DIRECTORY_SEPARATOR . $qr_filename;
            QRcode::png((string)$new_item_id, $qr_path, QR_ECLEVEL_L, 4);

            $stmtUpd = $conn->prepare("UPDATE assets SET qr_code = ? WHERE id = ?");
            $stmtUpd->bind_param("si", $qr_filename, $new_item_id);
            $stmtUpd->execute();
            $stmtUpd->close();
        }
    }
    $stmtIns->close();
    return $first_inserted_id;
}
