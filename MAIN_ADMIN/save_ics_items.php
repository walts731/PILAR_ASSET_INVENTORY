<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
require_once '../includes/audit_logger.php';
require_once '../includes/lifecycle_helper.php';
require_once '../includes/tag_format_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ICS main info
    $form_id = intval($_POST['form_id'] ?? 0); 
    $existing_ics_id = intval($_POST['existing_ics_id'] ?? 0);
    $header_image = $_POST['header_image'] ?? '';
    $entity_name = $_POST['entity_name'] ?? '';
    $fund_cluster = $_POST['fund_cluster'] ?? '';
    // Generate automatic ICS number
    $ics_no = generateTag('ics_no');
    $received_from_name = $_POST['received_from_name'] ?? '';
    $received_from_position = $_POST['received_from_position'] ?? '';
    $received_by_name = $_POST['received_by_name'] ?? '';
    $received_by_position = $_POST['received_by_position'] ?? '';

    // Handle OFFICE selection (can be numeric ID or "outside_lgu")
    $office_input = $_POST['office_id'] ?? 0;
    $is_outside_lgu = ($office_input === 'outside_lgu');
    $office_id = $is_outside_lgu ? 0 : intval($office_input);

    // Optional header image upload (overrides posted header_image when provided)
    if (isset($_FILES['header_image_file']) && isset($_FILES['header_image_file']['tmp_name']) && $_FILES['header_image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['header_image_file']['tmp_name'];
        $origName = $_FILES['header_image_file']['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed, true)) {
            $newName = 'ics_header_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destRel = '../img/' . $newName; // relative to this PHP file
            if (@move_uploaded_file($tmp, $destRel)) {
                $header_image = $newName;
            }
        }
    }

    // Validate: ICS NO must be unique
    if (!empty($ics_no)) {
        if ($existing_ics_id > 0) {
            $dupStmt = $conn->prepare("SELECT id FROM ics_form WHERE ics_no = ? AND id <> ? LIMIT 1");
            $dupStmt->bind_param("si", $ics_no, $existing_ics_id);
        } else {
            $dupStmt = $conn->prepare("SELECT id FROM ics_form WHERE ics_no = ? LIMIT 1");
            $dupStmt->bind_param("s", $ics_no);
        }
        $dupStmt->execute();
        $dupRes = $dupStmt->get_result();
        $duplicateFound = $dupRes && $dupRes->num_rows > 0;
        $dupStmt->close();

        if ($duplicateFound) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'ICS No. already exists. Please use a unique ICS No.'
            ];
            header("Location: forms.php?id=" . $form_id);
            exit();
        }
    }

    // UPDATE flow when editing existing ICS
    if ($existing_ics_id > 0) {
        $ics_id = $existing_ics_id;
        // Update ICS form header fields (optionally update header_image if provided) and office
        if (!empty($header_image)) {
            $stmt = $conn->prepare("UPDATE ics_form SET header_image = ?, entity_name = ?, fund_cluster = ?, ics_no = ?, received_from_name = ?, received_from_position = ?, received_by_name = ?, received_by_position = ?, office_id = ? WHERE id = ?");
            $stmt->bind_param(
                "ssssssssii",
                $header_image,
                $entity_name,
                $fund_cluster,
                $ics_no,
                $received_from_name,
                $received_from_position,
                $received_by_name,
                $received_by_position,
                $office_id,
                $ics_id
            );
        } else {
            $stmt = $conn->prepare("UPDATE ics_form SET entity_name = ?, fund_cluster = ?, ics_no = ?, received_from_name = ?, received_from_position = ?, received_by_name = ?, received_by_position = ?, office_id = ? WHERE id = ?");
            $stmt->bind_param(
                "ssssssiii",
                $entity_name,
                $fund_cluster,
                $ics_no,
                $received_from_name,
                $received_from_position,
                $received_by_name,
                $received_by_position,
                $office_id,
                $ics_id
            );
        }
        $stmt->execute();
        $stmt->close();

        // Propagate office change to linked assets and assets_new
        if ($office_input !== null) {
            // Update item-level assets created for this ICS
            $stmtUpdAssets = $conn->prepare("UPDATE assets SET office_id = ? WHERE ics_id = ?");
            if ($stmtUpdAssets) { $stmtUpdAssets->bind_param('ii', $office_id, $ics_id); $stmtUpdAssets->execute(); $stmtUpdAssets->close(); }

            // Update assets_new rows created for this ICS
            $stmtUpdAssetsNew = $conn->prepare("UPDATE assets_new SET office_id = ? WHERE ics_id = ?");
            if ($stmtUpdAssetsNew) { $stmtUpdAssetsNew->bind_param('ii', $office_id, $ics_id); $stmtUpdAssetsNew->execute(); $stmtUpdAssetsNew->close(); }
        }

        // Log ICS form update
        $logger = new AuditLogger($conn);
        $user_stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $_SESSION['user_id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $username = $user_result->fetch_assoc()['fullname'] ?? 'Unknown User';
        $user_stmt->close();
        
        $logger->logICSCreate($_SESSION['user_id'], $username, $ics_id, "Updated ICS form: {$ics_no} - {$entity_name}");

        // Update ICS items submitted as items[item_id][field]
        if (!empty($_POST['items']) && is_array($_POST['items'])) {
            $stmt_item_upd = $conn->prepare("UPDATE ics_items SET quantity = ?, unit = ?, unit_cost = ?, total_cost = ?, description = ?, item_no = ?, estimated_useful_life = ? WHERE item_id = ? AND ics_id = ?");
            foreach ($_POST['items'] as $item_id => $fields) {
                $qty = isset($fields['quantity']) ? floatval($fields['quantity']) : 0;
                $unit = $fields['unit'] ?? '';
                $unit_cost = isset($fields['unit_cost']) ? floatval($fields['unit_cost']) : 0;
                $total_cost = isset($fields['total_cost']) ? floatval($fields['total_cost']) : ($qty * $unit_cost);
                $description = $fields['description'] ?? '';
                $item_no = $fields['item_no'] ?? '';
                $eul = $fields['estimated_useful_life'] ?? '';
                $iid = intval($item_id);
                $stmt_item_upd->bind_param("isdssssii", $qty, $unit, $unit_cost, $total_cost, $description, $item_no, $eul, $iid, $ics_id);
                $stmt_item_upd->execute();
            }
            $stmt_item_upd->close();
        }

        // Flash success and redirect back to the ICS view page
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'ICS has been updated successfully.'
        ];
        header("Location: view_ics.php?id=" . $ics_id . "&form_id=" . $form_id . "&success=1");
        exit();
    }

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

    // Log new ICS form creation
    $logger = new AuditLogger($conn);
    $user_stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $username = $user_result->fetch_assoc()['fullname'] ?? 'Unknown User';
    $user_stmt->close();
    
    $office_name = 'Outside LGU';
    if (!$is_outside_lgu && $office_id > 0) {
        $office_stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
        $office_stmt->bind_param("i", $office_id);
        $office_stmt->execute();
        $office_result = $office_stmt->get_result();
        if ($office_data = $office_result->fetch_assoc()) {
            $office_name = $office_data['office_name'];
        }
        $office_stmt->close();
    }
    
    $logger->logICSCreate($_SESSION['user_id'], $username, $ics_id, "Created new ICS form: {$ics_no} - {$entity_name} (Destination: {$office_name})");

    // ICS items data
    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $unit_costs = $_POST['unit_cost'] ?? [];
    $total_costs = $_POST['total_cost'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $item_nos = $_POST['item_no'] ?? [];
    $estimated_lives = $_POST['estimated_useful_life'] ?? [];

    // Load thresholds (ICS max)
    $ics_max = 50000.00; // default
    $thrRes = $conn->query("SELECT ics_max FROM form_thresholds ORDER BY id ASC LIMIT 1");
    if ($thrRes && $thrRes->num_rows > 0) {
        $thrRow = $thrRes->fetch_assoc();
        if (isset($thrRow['ics_max'])) { $ics_max = (float)$thrRow['ics_max']; }
    }

    // Prepare ICS items insert
    $stmt_items = $conn->prepare("INSERT INTO ics_items 
        (ics_id, asset_id, ics_no, quantity, unit, unit_cost, total_cost, description, item_no, estimated_useful_life, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    // Prepare insert into the new minimal assets table (assets_new) per ICS line (now including ics_id)
    $stmt_assets_new = $conn->prepare("INSERT INTO assets_new (description, quantity, unit_cost, unit, office_id, ics_id, date_created) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    // We no longer create/update aggregate assets or deduct stock here; only per-item assets are created.

    $skipped = [];
    for ($i = 0; $i < count($descriptions); $i++) {
        $quantity = isset($quantities[$i]) ? floatval($quantities[$i]) : 0;
        $unit = $units[$i] ?? '';
        $unit_cost = isset($unit_costs[$i]) ? floatval($unit_costs[$i]) : 0;
        $total_cost = isset($total_costs[$i]) ? floatval($total_costs[$i]) : 0;
        $description = $descriptions[$i] ?? '';
        $item_no = $item_nos[$i] ?? '';
        $estimated_life = $estimated_lives[$i] ?? '';

        if (empty($description) || $quantity <= 0) continue;

        // Enforce ICS maximum unit cost
        if ($unit_cost > $ics_max) {
            $skipped[] = "Item '" . $description . "' skipped (unit cost must be ≤ " . number_format($ics_max, 2) . ").";
            continue;
        }

        // Record this line into assets_new, include destination office and link to ICS
        $stmt_assets_new->bind_param("sddsii", $description, $quantity, $unit_cost, $unit, $office_id, $ics_id);
        $stmt_assets_new->execute();
        $asset_new_id = $conn->insert_id;

        // Create only per-item assets directly (quantity = 1 each), linked to assets_new and ICS.
        $target_office_id = $is_outside_lgu ? null : ($office_id > 0 ? (int)$office_id : null);
        $first_item_id = createItemAssetsDirect(
            $conn,
            $description,
            $unit,
            (float)$unit_cost,
            (int)$quantity,
            $target_office_id,
            $item_no,
            date('Y-m-d'),
            (int)$ics_id,
            (int)$asset_new_id
        );
        $latest_asset_id = $first_item_id; // Link ICS item to the first created item-level asset

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
        
        // Log individual ICS item creation
        $item_details = "Added item to ICS {$ics_no}: {$description} (Qty: {$quantity}, Unit Cost: ₱" . number_format($unit_cost, 2) . ", Total: ₱" . number_format($total_cost, 2) . ")";
        $logger->log($_SESSION['user_id'], $username, 'CREATE', 'ICS Items', $item_details, 'ics_items', $conn->insert_id);

        // Lifecycle: mark asset(s) as acquired via ICS with dynamic office placeholder
        if (function_exists('logLifecycleEvent') && !empty($latest_asset_id)) {
            $toOffice = $is_outside_lgu ? null : ($office_id > 0 ? (int)$office_id : null);
            // Extract trailing numeric sequence from ICS number for compact display (e.g., 037)
            $ics_suffix = $ics_no;
            if (preg_match('/(\d+)$/', (string)$ics_no, $m)) {
                $ics_suffix = $m[1];
            }
            // Use {OFFICE} placeholder; lifecycle_helper will replace it with destination office name
            $note = sprintf('ICS {OFFICE}-%s; Qty %s; UnitCost ₱%0.2f; Total ₱%0.2f', (string)$ics_suffix, (string)$quantity, (float)$unit_cost, (float)$total_cost);
            logLifecycleEvent((int)$latest_asset_id, 'ACQUIRED', 'ics_form', (int)$ics_id, null, null, null, $toOffice, $note);
        }
    }

    $stmt_items->close();
    if (isset($stmt_assets_new) && $stmt_assets_new) { $stmt_assets_new->close(); }

    // Set flash message for success (or partial) and redirect back to ICS form
    $_SESSION['flash'] = empty($skipped)
        ? [ 'type' => 'success', 'message' => 'ICS has been saved successfully.' ]
        : [ 'type' => 'warning', 'message' => 'ICS saved with some items skipped: ' . implode(' ', $skipped) ];

    header("Location: forms.php?id=" . $form_id);
    exit();
}

// Ensure a main-stock asset exists for a description. If not, create it and return its id.
// Template assets are created with quantity 1 to serve as templates for individual items
function ensureAssetExists($conn, $description, $unit, $unit_cost, $item_no) {
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

    // Create a minimal main-stock asset template using provided info
    // Template assets have quantity 1 and serve as templates for individual items
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
    $template_quantity = 1; // Always create template with quantity 1

    $stmt = $conn->prepare("INSERT INTO assets 
        (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssisssiiisdssssssss",
        $asset_name,
        $description,
        $template_quantity,
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
function transferAssetToOffice($conn, $description, $unit_cost, $quantity, $office_id, $unit, $item_no, $ics_id, $asset_new_id)
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
            isset($asset['acquisition_date']) ? $asset['acquisition_date'] : date('Y-m-d'),
            (int)$ics_id,
            (int)$asset_new_id
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
        $p_status = 'pending'; // Set to pending when ICS form is submitted
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
            $p_acquisition_date,
            (int)$ics_id,
            (int)$asset_new_id
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

// Create per-item rows directly in assets (quantity=1 each), linked to parent asset/template and ICS.
function createAssetItems($conn, $asset_id, $office_id, $base_property_no, $count, $serial_no, $date_acquired, $ics_id, $asset_new_id) {
    if ($count <= 0) return;

    // Fetch parent/template asset to copy details
    $stmtFetch = $conn->prepare("SELECT * FROM assets WHERE id = ? LIMIT 1");
    $stmtFetch->bind_param("i", $asset_id);
    $stmtFetch->execute();
    $res = $stmtFetch->get_result();
    $tmpl = $res ? $res->fetch_assoc() : null;
    $stmtFetch->close();
    if (!$tmpl) return;

    // Directory for QR code images
    $qrDir = realpath(__DIR__ . '/../img');
    if (!$qrDir) { $qrDir = __DIR__; }

    // Prepare insert for new item-level asset
    $stmtIns = $conn->prepare("INSERT INTO assets 
        (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand, ics_id, asset_new_id)
        VALUES (?, ?, 1, ?, 'pending', ?, ?, ?, ?, NOW(), ?, '', ?, ?, ?, ?, NULL, ?, ?, ?, ?)");

    $first_inserted_id = null;
    for ($i = 1; $i <= $count; $i++) {
        // Copy values from template where available
        $p_asset_name = $tmpl['asset_name'] ?? $tmpl['description'] ?? 'Asset Item';
        $p_description = $tmpl['description'] ?? '';
        $p_unit = $tmpl['unit'] ?? '';
        $p_acq = $tmpl['acquisition_date'] ?? $date_acquired ?? date('Y-m-d');
        $p_office = isset($office_id) ? (int)$office_id : null; // allow NULL for main stock/outside LGU
        $p_emp = isset($tmpl['employee_id']) ? (int)$tmpl['employee_id'] : null;
        $p_red = isset($tmpl['red_tagged']) ? (int)$tmpl['red_tagged'] : 0;
        $p_value = isset($tmpl['value']) ? (float)$tmpl['value'] : 0.0;
        $p_type = $tmpl['type'] ?? 'asset';
        $p_image = $tmpl['image'] ?? '';
        $p_serial = $serial_no !== '' ? $serial_no : ($tmpl['serial_no'] ?? '');
        $p_code = $tmpl['code'] ?? '';
        $p_model = $tmpl['model'] ?? '';
        $p_brand = $tmpl['brand'] ?? '';
        $p_ics = (int)$ics_id;
        $p_asset_new_id = (int)$asset_new_id;

        $stmtIns->bind_param(
            'ssssiiidssssssii',
            $p_asset_name,      // s
            $p_description,     // s
            $p_unit,            // s
            $p_acq,             // s (date string)
            $p_office,          // i
            $p_emp,             // i (nullable)
            $p_red,             // i
            $p_value,           // d
            $p_type,            // s
            $p_image,           // s
            $p_serial,          // s
            $p_code,            // s
            $p_model,           // s
            $p_brand,           // s
            $p_ics,             // i (nullable)
            $p_asset_new_id     // i
        );

        // Execute insert
        if ($stmtIns->execute()) {
            // Generate QR code for the newly created asset row
            $new_item_id = $conn->insert_id;
            if ($first_inserted_id === null) { $first_inserted_id = $new_item_id; }
            $qr_filename = $new_item_id . '.png';
            $qr_path = $qrDir . DIRECTORY_SEPARATOR . $qr_filename;
            QRcode::png((string)$new_item_id, $qr_path, QR_ECLEVEL_L, 4);

            // Update the asset with its qr_code filename
            $stmtUpd = $conn->prepare("UPDATE assets SET qr_code = ? WHERE id = ?");
            $stmtUpd->bind_param("si", $qr_filename, $new_item_id);
            $stmtUpd->execute();
            $stmtUpd->close();
        }
    }
    $stmtIns->close();
    return $first_inserted_id;
}

// Create item-level assets directly without creating/updating any aggregate asset row.
function createItemAssetsDirect($conn, $description, $unit, $unit_cost, $count, $office_id, $item_no, $date_acquired, $ics_id, $asset_new_id) {
    if ($count <= 0) return null;

    // Directory for QR code images
    $qrDir = realpath(__DIR__ . '/../img');
    if (!$qrDir) { $qrDir = __DIR__; }

    // Prepare insert for new item-level asset (quantity = 1)
    $stmtIns = $conn->prepare("INSERT INTO assets 
        (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand, ics_id, asset_new_id)
        VALUES (?, ?, 1, ?, 'pending', ?, ?, ?, ?, NOW(), ?, '', ?, ?, ?, ?, NULL, ?, ?, ?, ?)");

    $first_inserted_id = null;
    for ($i = 1; $i <= $count; $i++) {
        $p_asset_name = $description; // use description as name for item-level assets
        $p_description = $description;
        $p_unit = $unit;
        $p_acq = $date_acquired ?: date('Y-m-d');
        $p_office = isset($office_id) ? (int)$office_id : null; // allow NULL for main stock/outside LGU
        $p_emp = null; // no employee assignment on creation
        $p_red = 0;
        $p_value = (float)$unit_cost;
        $p_type = 'asset';
        $p_image = '';
        $p_serial = '';
        $p_code = '';
        // property_no intentionally left NULL to match existing per-item schema behavior
        $p_model = '';
        $p_brand = '';
        $p_ics = (int)$ics_id;
        $p_asset_new_id = (int)$asset_new_id;

        $stmtIns->bind_param(
            'ssssiiidssssssii',
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
            $p_asset_new_id
        );

        if ($stmtIns->execute()) {
            // Generate QR code for the newly created asset row
            $new_item_id = $conn->insert_id;
            if ($first_inserted_id === null) { $first_inserted_id = $new_item_id; }
            $qr_filename = $new_item_id . '.png';
            $qr_path = $qrDir . DIRECTORY_SEPARATOR . $qr_filename;
            QRcode::png((string)$new_item_id, $qr_path, QR_ECLEVEL_L, 4);

            // Update the asset with its qr_code filename
            $stmtUpd = $conn->prepare("UPDATE assets SET qr_code = ? WHERE id = ?");
            $stmtUpd->bind_param("si", $qr_filename, $new_item_id);
            $stmtUpd->execute();
            $stmtUpd->close();
        }
    }
    $stmtIns->close();
    return $first_inserted_id;
}
?>
