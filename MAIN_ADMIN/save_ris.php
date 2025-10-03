<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // HEADER + FOOTER fields
    $division = $_POST['division'] ?? '';
    $responsibility_center = $_POST['responsibility_center'] ?? '';
    // Generate automatic RIS number
    $ris_no = generateTag('ris_no');
    $date = $_POST['date'] ?? date('Y-m-d');
    $office_id = $_POST['office_id'] ?? '';
    $responsibility_code = $_POST['responsibility_code'] ?? '';
    $sai_no = $_POST['sai_no'] ?? '';
    $purpose = $_POST['purpose'] ?? '';

    // ✅ Handle header image upload with fallback to existing
    $header_image = $_POST['existing_header_image'] ?? null; // existing image
    if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['header_image']['tmp_name'];
        $fileName = $_FILES['header_image']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Sanitize + unique filename
        $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
        $uploadDir = '../img/';
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $header_image = $newFileName; // save just filename
        }
    }

    // Footer fields
    $requested_by_name = $_POST['requested_by_name'] ?? '';
    $requested_by_designation = $_POST['requested_by_designation'] ?? '';
    $requested_by_date = $_POST['requested_by_date'] ?? null;

    $approved_by_name = $_POST['approved_by_name'] ?? '';
    $approved_by_designation = $_POST['approved_by_designation'] ?? '';
    $approved_by_date = $_POST['approved_by_date'] ?? null;

    $issued_by_name = $_POST['issued_by_name'] ?? '';
    $issued_by_designation = $_POST['issued_by_designation'] ?? '';
    $issued_by_date = $_POST['issued_by_date'] ?? null;

    $received_by_name = $_POST['received_by_name'] ?? '';
    $received_by_designation = $_POST['received_by_designation'] ?? '';
    $received_by_date = $_POST['received_by_date'] ?? null;

    $footer_date = $_POST['footer_date'] ?? null;
    $form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;

    // Insert RIS header
   // Insert RIS header
$stmt = $conn->prepare("
    INSERT INTO ris_form (
        form_id, header_image,
        division, responsibility_center, ris_no, date, office_id, responsibility_code, sai_no, reason_for_transfer,
        requested_by_name, requested_by_designation, requested_by_date,
        approved_by_name, approved_by_designation, approved_by_date,
        issued_by_name, issued_by_designation, issued_by_date,
        received_by_name, received_by_designation, received_by_date,
        footer_date
    ) VALUES (?,?,?,?,?, NOW(), ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");


    $stmt->bind_param(
    "issssissssssssssssssss",
    $form_id,              // i
    $header_image,         // s
    $division,             // s
    $responsibility_center,// s
    $ris_no,               // s 
    $office_id,            // i
    $responsibility_code,  // s
    $sai_no,               // s
    $purpose,              // s
    $requested_by_name,    // s
    $requested_by_designation, // s
    $requested_by_date,    // s
    $approved_by_name,     // s
    $approved_by_designation, // s
    $approved_by_date,     // s
    $issued_by_name,       // s
    $issued_by_designation,// s
    $issued_by_date,       // s
    $received_by_name,     // s
    $received_by_designation, // s
    $received_by_date,     // s
    $footer_date           // s
);


    if ($stmt->execute()) {
        $ris_form_id = $stmt->insert_id;
        $stmt->close();

        // Insert RIS items
        if (!empty($_POST['description'])) {
            $stock_nos   = $_POST['stock_no'] ?? [];
            $units       = $_POST['unit'] ?? [];
            $descriptions = $_POST['description'] ?? [];
            $quantities  = $_POST['req_quantity'] ?? []; // corrected from 'quantity'
            $prices      = $_POST['price'] ?? [];
            $totals      = $_POST['total'] ?? [];
            $asset_ids = $_POST['asset_id'] ?? [];

            $item_stmt = $conn->prepare("\n                INSERT INTO ris_items (ris_form_id, stock_no, unit, description, quantity, price, total)\n                VALUES (?, ?, ?, ?, ?, ?, ?)\n            ");

            foreach ($descriptions as $i => $desc) {
                if (trim($desc) === '') continue; // skip empty rows

                $asset_id = (int)($asset_ids[$i] ?? 0);
                $stock = $stock_nos[$i] ?? '';
                $unit = $units[$i] ?? '';
                $qty = (int)($quantities[$i] ?? 0);
                $price = (float)($prices[$i] ?? 0);
                $total = (float)($totals[$i] ?? 0);

                // Insert into ris_items
                $item_stmt->bind_param("isssidd", $ris_form_id, $stock, $unit, $desc, $qty, $price, $total);
                $item_stmt->execute();

                // Case 1: Source asset provided -> deduct and move to office as consumable
                if ($asset_id > 0 && $qty > 0) {
                    // 1. Fetch original asset details
                    $fetch_stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
                    $fetch_stmt->bind_param("i", $asset_id);
                    $fetch_stmt->execute();
                    $asset_result = $fetch_stmt->get_result();
                    $asset = $asset_result->fetch_assoc();
                    $fetch_stmt->close();

                    if ($asset) {
                        // 2. Update original asset quantity
                        $update_stmt = $conn->prepare("UPDATE assets SET quantity = GREATEST(quantity - ?, 0) WHERE id = ?");
                        $update_stmt->bind_param("ii", $qty, $asset_id);
                        $update_stmt->execute();
                        $update_stmt->close();

                        // 3. Check if asset already exists in target office (by property_no + office_id)
                        $check_stmt = $conn->prepare("SELECT id, quantity FROM assets WHERE property_no = ? AND office_id = ?");
                        $check_stmt->bind_param("si", $asset['property_no'], $office_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        $existing_asset = $check_result->fetch_assoc();
                        $check_stmt->close();

                        if ($existing_asset) {
                            // Restock existing office asset and ensure type is 'consumable'
                            $updated_qty = ((int)$existing_asset['quantity']) + $qty;
                            $restock_stmt = $conn->prepare("UPDATE assets SET quantity = ?, added_stock = ?, type = 'consumable', last_updated = NOW() WHERE id = ?");
                            $restock_stmt->bind_param("iii", $updated_qty, $qty, $existing_asset['id']);
                            $restock_stmt->execute();
                            $restock_stmt->close();
                        } else {
                            // Insert as new asset for this office (force type consumable)
                            $new_quantity = $qty;
                            
                            // Resolve category ID from category name/value
                            $category_id = null;
                            if (!empty($asset['category'])) {
                                // Check if category is already an ID (numeric)
                                if (is_numeric($asset['category'])) {
                                    $category_id = (int)$asset['category'];
                                } else {
                                    // Look up category ID by name
                                    $cat_stmt = $conn->prepare("SELECT id FROM categories WHERE category_name = ? LIMIT 1");
                                    $cat_stmt->bind_param("s", $asset['category']);
                                    $cat_stmt->execute();
                                    $cat_result = $cat_stmt->get_result();
                                    if ($cat_row = $cat_result->fetch_assoc()) {
                                        $category_id = (int)$cat_row['id'];
                                    }
                                    $cat_stmt->close();
                                }
                            }
                            
                            $sql = "
    INSERT INTO assets (
        asset_name, category, description, quantity, added_stock, unit, status,
        acquisition_date, office_id, employee_id, red_tagged,
        value, qr_code, type, image, serial_no,
        code, property_no, model, brand, inventory_tag, last_updated
    ) VALUES (
        '" . $conn->real_escape_string($asset['asset_name']) . "',
        " . ($category_id ? (int)$category_id : 'NULL') . ",
        '" . $conn->real_escape_string($asset['description']) . "',
        '" . (int)$new_quantity . "',
        '" . (int)$qty . "',
        '" . $conn->real_escape_string($asset['unit']) . "',
        '" . $conn->real_escape_string($asset['status']) . "',
        '" . $conn->real_escape_string($asset['acquisition_date']) . "',
        '" . (int)$office_id . "',
        NULL,
        '" . $conn->real_escape_string($asset['red_tagged']) . "',
        '" . $conn->real_escape_string($asset['value']) . "',
        '" . $conn->real_escape_string($asset['qr_code']) . "',
        'consumable',
        '" . $conn->real_escape_string($asset['image']) . "',
        '" . $conn->real_escape_string($asset['serial_no']) . "',
        '" . $conn->real_escape_string($asset['code']) . "',
        '" . $conn->real_escape_string($asset['property_no']) . "',
        '" . $conn->real_escape_string($asset['model']) . "',
        '" . $conn->real_escape_string($asset['brand']) . "',
        '" . $conn->real_escape_string($asset['inventory_tag']) . "',
        NOW()
    )";
                            if (!$conn->query($sql)) {
                                error_log('RIS asset insert error: ' . $conn->error);
                            }
                        }
                    }
                } else {
                    // Case 2: No source asset provided -> merge with existing or insert minimal consumable asset in target office
                    if ($qty > 0) {
                        // Resolve unit name if unit contains an ID
                        $unit_value = $unit;
                        if (ctype_digit((string)$unit_value)) {
                            $unit_stmt = $conn->prepare("SELECT unit_name FROM unit WHERE id = ? LIMIT 1");
                            $unit_id_int = (int)$unit_value;
                            $unit_stmt->bind_param("i", $unit_id_int);
                            $unit_stmt->execute();
                            $unit_res = $unit_stmt->get_result();
                            if ($u = $unit_res->fetch_assoc()) { $unit_value = $u['unit_name']; }
                            $unit_stmt->close();
                        }

                        // Try to find an existing consumable asset in this office that matches by property_no OR by description+unit
                        $existing_id = null;
                        $existing_qty = 0;

                        // Prefer exact property_no match when provided
                        if (!empty($stock)) {
                            $chk1 = $conn->prepare("SELECT id, quantity FROM assets WHERE office_id = ? AND type = 'consumable' AND property_no = ? LIMIT 1");
                            $chk1->bind_param("is", $office_id, $stock);
                            $chk1->execute();
                            $r1 = $chk1->get_result();
                            if ($row1 = $r1->fetch_assoc()) { $existing_id = (int)$row1['id']; $existing_qty = (int)$row1['quantity']; }
                            $chk1->close();
                        }

                        // If no property_no match, try description + unit match
                        if ($existing_id === null) {
                            $chk2 = $conn->prepare("SELECT id, quantity FROM assets WHERE office_id = ? AND type = 'consumable' AND description = ? AND unit = ? LIMIT 1");
                            $chk2->bind_param("iss", $office_id, $desc, $unit_value);
                            $chk2->execute();
                            $r2 = $chk2->get_result();
                            if ($row2 = $r2->fetch_assoc()) { $existing_id = (int)$row2['id']; $existing_qty = (int)$row2['quantity']; }
                            $chk2->close();
                        }

                        if ($existing_id !== null) {
                            // Merge quantities into existing asset
                            $new_qty = $existing_qty + $qty;
                            $upd = $conn->prepare("UPDATE assets SET quantity = ?, added_stock = COALESCE(added_stock,0) + ?, last_updated = NOW() WHERE id = ?");
                            $upd->bind_param("iii", $new_qty, $qty, $existing_id);
                            $upd->execute();
                            $upd->close();
                        } else {
                            // Insert as a new consumable asset record
                            $ins_stmt = $conn->prepare("INSERT INTO assets (asset_name, category, description, quantity, added_stock, unit, status, acquisition_date, office_id, employee_id, red_tagged, value, qr_code, type, image, serial_no, code, property_no, model, brand, inventory_tag, last_updated) VALUES (?, NULL, ?, ?, ?, ?, 'available', NOW(), ?, NULL, 0, ?, '', 'consumable', '', '', '', ?, '', '', '', NOW())");
                            $asset_name_val = $desc;
                            $added_stock_val = $qty;
                            $property_no_val = $stock;
                            $ins_stmt->bind_param(
                                "ssiisids",
                                $asset_name_val,   // s asset_name
                                $desc,            // s description
                                $qty,             // i quantity
                                $added_stock_val, // i added_stock
                                $unit_value,      // s unit
                                $office_id,       // i office_id
                                $price,           // d value
                                $property_no_val  // s property_no
                            );
                            $ins_stmt->execute();
                            $ins_stmt->close();
                        }
                    }
                }
            }

            $item_stmt->close();
        }

        header("Location: forms.php?id={$form_id}&add=success");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
