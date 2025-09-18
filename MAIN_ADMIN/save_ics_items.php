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

    // Prepare stock update (only deduct from main stock where office_id IS NULL)
    $stmt_update_assets = $conn->prepare("UPDATE assets SET quantity = quantity - ? WHERE description = ? AND office_id IS NULL");

    for ($i = 0; $i < count($descriptions); $i++) {
        $quantity = isset($quantities[$i]) ? floatval($quantities[$i]) : 0;
        $unit = $units[$i] ?? '';
        $unit_cost = isset($unit_costs[$i]) ? floatval($unit_costs[$i]) : 0;
        $total_cost = isset($total_costs[$i]) ? floatval($total_costs[$i]) : 0;
        $description = $descriptions[$i] ?? '';
        $item_no = $item_nos[$i] ?? '';
        $estimated_life = $estimated_lives[$i] ?? '';

        if (empty($description) || $quantity <= 0) continue;

        // Determine if main-stock asset exists
        $main_asset_id = getMainStockAssetId($conn, $description);

        if ($is_outside_lgu) {
            // Outside LGU: ensure main-stock exists, then deduct and record
            if (!$main_asset_id) {
                $main_asset_id = ensureAssetExists($conn, $description, $unit, $unit_cost, $quantity, $item_no);
            }
            // Deduct from main stock
            $stmt_update_assets->bind_param("ds", $quantity, $description);
            $stmt_update_assets->execute();
            $latest_asset_id = $main_asset_id;
        } elseif ($office_id > 0) {
            // Transfer to office: if main-stock exists, deduct and transfer; otherwise create directly in office without creating main-stock
            if ($main_asset_id) {
                $stmt_update_assets->bind_param("ds", $quantity, $description);
                $stmt_update_assets->execute();
            }
            $latest_asset_id = transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id, $unit, $item_no, $ics_id);
        } else {
            // No specific office: ensure main-stock exists, deduct and use main-stock
            if (!$main_asset_id) {
                $main_asset_id = ensureAssetExists($conn, $description, $unit, $unit_cost, $quantity, $item_no);
            }
            $stmt_update_assets->bind_param("ds", $quantity, $description);
            $stmt_update_assets->execute();
            $latest_asset_id = $main_asset_id;
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

// Ensure a main-stock asset exists for a description. If not, create it and return its id.
function ensureAssetExists($conn, $description, $unit, $unit_cost, $quantity, $item_no) {
    // Check if exists
    $stmt = $conn->prepare("SELECT id FROM assets WHERE description = ? LIMIT 1");
    $stmt->bind_param("s", $description);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row) {
        return (int)$row['id'];
    }

    // Create a minimal main-stock asset using provided info
    $created_at = date('Y-m-d H:i:s');
    $acq_date = date('Y-m-d');
    $qr_code = '';
    // Use description as asset_name to satisfy potential NOT NULL constraint
    $asset_name = $description;
    // Pick a valid category id from categories table
    $category = getDefaultCategoryId($conn);
    $status = 'Available';
    // Use NULL for main stock to satisfy FK (OFFICES.id) with ON DELETE SET NULL
    $office_id = null; // main stock (no office)
    // Prefer NULL for optional foreign keys
    $employee_id = null;
    $red_tagged = 0;
    $type = 'asset';
    $image = '';
    $serial_no = '';
    $code = '';
    $model = '';
    $brand = '';

    $stmt = $conn->prepare("INSERT INTO assets 
        (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssisssiiisdssssssss",
        $asset_name,
        $description,
        $quantity,
        $unit,
        $status,
        $acq_date,
        $office_id,
        $employee_id,
        $red_tagged,
        $created_at,
        $unit_cost,
        $qr_code,
        $type,
        $image,
        $serial_no,
        $code,
        $item_no,
        $model,
        $brand
    );
    $stmt->execute();
    $new_id = $conn->insert_id;
    $stmt->close();
    return (int)$new_id;
}

// Get main stock asset id by description
function getMainStockAssetId($conn, $description) {
    $stmt = $conn->prepare("SELECT id FROM assets WHERE description = ? AND office_id IS NULL LIMIT 1");
    $stmt->bind_param("s", $description);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['id'] : null;
}

// Find a valid category id to satisfy FK. Prefer a category named 'Uncategorized', otherwise use the smallest id.
function getDefaultCategoryId($conn) {
    // Try by name first
    $sql = "SELECT id FROM categories WHERE category_name = 'Uncategorized' LIMIT 1";
    if ($res = $conn->query($sql)) {
        if ($row = $res->fetch_assoc()) {
            return (int)$row['id'];
        }
    }
    // Fallback: any existing category (lowest id)
    $sql2 = "SELECT id FROM categories ORDER BY id ASC LIMIT 1";
    if ($res2 = $conn->query($sql2)) {
        if ($row2 = $res2->fetch_assoc()) {
            return (int)$row2['id'];
        }
    }
    // As a last resort, default to 1; caller should ensure categories table has at least one row
    return 1;
}

// Transfer asset to office with QR code generation, returns inserted asset_id
function transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id, $unit, $item_no, $ics_id)
{
    $created_at = date('Y-m-d H:i:s');

    // Fetch main stock asset (if any)
    $stmt = $conn->prepare("SELECT * FROM assets WHERE description = ? AND office_id IS NULL LIMIT 1");
    $stmt->bind_param("s", $description);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset = $result->fetch_assoc();
    $stmt->close();
    // If no main-stock asset, we'll still proceed to create/update an office asset using minimal provided details

    // Check if asset exists in office
    $stmt = $conn->prepare("SELECT id, quantity FROM assets WHERE description = ? AND office_id = ?");
    $stmt->bind_param("si", $description, $office_id);
    $stmt->execute();
    $result_check = $stmt->get_result();
    $stmt->close();

    if ($result_check && $row = $result_check->fetch_assoc()) {
        $new_quantity = $row['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE assets SET quantity = ?, value = ?, last_updated = ?, ics_id = COALESCE(ics_id, ?) WHERE id = ?");
        $stmt->bind_param("ddsii", $new_quantity, $unit_cost, $created_at, $ics_id, $row['id']);
        $stmt->execute();
        $stmt->close();
        // Create per-item records for the added quantity
        createAssetItems(
            $conn,
            (int)$row['id'],
            (int)$office_id,
            isset($asset['property_no']) ? $asset['property_no'] : $item_no,
            (int)$quantity,
            isset($asset['serial_no']) ? $asset['serial_no'] : '',
            isset($asset['acquisition_date']) ? $asset['acquisition_date'] : date('Y-m-d')
        );
        return $row['id'];
    } else {
        // Insert new office asset
        $qr_code = '';
        $stmt = $conn->prepare("INSERT INTO assets 
            (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand, ics_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Prepare variables (bind_param requires variables, not expressions)
        $p_asset_name = isset($asset['asset_name']) ? $asset['asset_name'] : $description;
        $p_description = isset($asset['description']) ? $asset['description'] : $description;
        $p_quantity = $quantity;
        $p_unit = isset($asset['unit']) ? $asset['unit'] : $unit;
        $p_status = isset($asset['status']) ? $asset['status'] : 'available';
        $p_acquisition_date = isset($asset['acquisition_date']) ? $asset['acquisition_date'] : date('Y-m-d');
        $p_office_id = (int)$office_id;
        $p_employee_id = isset($asset['employee_id']) ? (int)$asset['employee_id'] : null; // may be NULL
        $p_red_tagged = isset($asset['red_tagged']) ? (int)$asset['red_tagged'] : 0;
        $p_last_updated = $created_at;
        $p_value = $unit_cost;
        $p_qr_code = $qr_code;
        $p_type = isset($asset['type']) ? $asset['type'] : 'asset';
        $p_image = isset($asset['image']) ? $asset['image'] : '';
        $p_serial_no = isset($asset['serial_no']) ? $asset['serial_no'] : '';
        $p_code = isset($asset['code']) ? $asset['code'] : '';
        $p_property_no = isset($asset['property_no']) ? $asset['property_no'] : $item_no;
        $p_model = isset($asset['model']) ? $asset['model'] : '';
        $p_brand = isset($asset['brand']) ? $asset['brand'] : '';
        $p_ics_id = (int)$ics_id;

        $stmt->bind_param(
            "ssisssiiisdssssssssi",
            $p_asset_name,
            $p_description,
            $p_quantity,
            $p_unit,
            $p_status,
            $p_acquisition_date,
            $p_office_id,
            $p_employee_id,
            $p_red_tagged,
            $p_last_updated,
            $p_value,
            $p_qr_code,
            $p_type,
            $p_image,
            $p_serial_no,
            $p_code,
            $p_property_no,
            $p_model,
            $p_brand,
            $p_ics_id
        );
        $stmt->execute();
        $new_asset_id = $conn->insert_id;
        $stmt->close();

        // Create per-item records for this new asset quantity
        createAssetItems(
            $conn,
            (int)$new_asset_id,
            (int)$office_id,
            $p_property_no,
            (int)$p_quantity,
            $p_serial_no,
            $p_acquisition_date
        );

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

// Create item-level rows in asset_items for an asset. Generates unique qr_code filenames and inventory tags.
function createAssetItems($conn, $asset_id, $office_id, $base_property_no, $count, $serial_no, $date_acquired) {
    if ($count <= 0) return;
    // Ensure QR code directory
    $qrDir = realpath(__DIR__ . '/../img/qrcodes');
    if (!$qrDir) {
        // fallback to ../img/
        $qrDir = realpath(__DIR__ . '/../img');
    }

    $stmt = $conn->prepare("INSERT INTO asset_items (asset_id, office_id, qr_code, inventory_tag, serial_no, status, date_acquired, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'available', ?, NOW(), NOW())");

    for ($i = 1; $i <= $count; $i++) {
        $inventory_tag = $base_property_no ? ($base_property_no . '-' . $i) : ('ITM-' . $asset_id . '-' . $i);
        $qr_filename = 'asset_' . $asset_id . '_item_' . $i . '.png';
        $qr_path = $qrDir . DIRECTORY_SEPARATOR . $qr_filename;
        // Generate QR code content and image
        $qr_text = 'asset:' . $asset_id . '|item:' . $i;
        QRcode::png($qr_text, $qr_path, QR_ECLEVEL_L, 4);

        $stmt->bind_param(
            'iissss',
            $asset_id,
            $office_id,
            $qr_filename,
            $inventory_tag,
            $serial_no,
            $date_acquired
        );
        $stmt->execute();
    }
    $stmt->close();
}
?>
