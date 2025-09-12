<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // HEADER + FOOTER fields
    $division = $_POST['division'] ?? '';
    $responsibility_center = $_POST['responsibility_center'] ?? '';
    $ris_no = $_POST['ris_no'] ?? '';
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
    $stmt = $conn->prepare("
        INSERT INTO ris_form (
            form_id, header_image,
            division, responsibility_center, ris_no, date, office_id, responsibility_code, sai_no, reason_for_transfer,
            requested_by_name, requested_by_designation, requested_by_date,
            approved_by_name, approved_by_designation, approved_by_date,
            issued_by_name, issued_by_designation, issued_by_date,
            received_by_name, received_by_designation, received_by_date,
            footer_date
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issssisssssssssssssssss",
        $form_id,
        $header_image,   // ✅ handle header image here
        $division,
        $responsibility_center,
        $ris_no,
        $date,
        $office_id,
        $responsibility_code,
        $sai_no,
        $purpose,
        $requested_by_name,
        $requested_by_designation,
        $requested_by_date,
        $approved_by_name,
        $approved_by_designation,
        $approved_by_date,
        $issued_by_name,
        $issued_by_designation,
        $issued_by_date,
        $received_by_name,
        $received_by_designation,
        $received_by_date,
        $footer_date
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

            $item_stmt = $conn->prepare("
                INSERT INTO ris_items (ris_form_id, stock_no, unit, description, quantity, price, total)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

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

                // ✅ Deduct and duplicate into assets if valid
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
                            // ✅ Restock existing office asset
                            $updated_qty = $existing_asset['quantity'] + $qty;

                            $restock_stmt = $conn->prepare("UPDATE assets SET quantity = ?, added_stock = ?, last_updated = NOW() WHERE id = ?");
                            $restock_stmt->bind_param("iii", $updated_qty, $qty, $existing_asset['id']);
                            $restock_stmt->execute();
                            $restock_stmt->close();
                        } else {
                            // ❌ Not found → Insert as new asset for this office
                            $new_quantity = $qty; // moved quantity

                            $sql = "
    INSERT INTO assets (
        asset_name, category, description, quantity, added_stock, unit, status,
        acquisition_date, office_id, employee_id, red_tagged,
        value, qr_code, type, image, serial_no,
        code, property_no, model, brand, inventory_tag, last_updated
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, NOW()
    )";

                            $insert_stmt = $conn->prepare($sql);
                            $insert_stmt->bind_param(
                                "ssiiisssiiissssssss",
                                $asset['asset_name'],
                                $asset['category'],
                                $asset['description'],
                                $new_quantity,
                                $qty, // ✅ set added_stock to this qty
                                $asset['unit'],
                                $asset['status'],
                                $asset['acquisition_date'],
                                $office_id,
                                $asset['employee_id'],
                                $asset['red_tagged'],
                                $asset['value'],
                                $asset['qr_code'],
                                $asset['type'],
                                $asset['image'],
                                $asset['serial_no'],
                                $asset['code'],
                                $asset['property_no'],
                                $asset['model'],
                                $asset['brand'],
                                $asset['inventory_tag']
                            );

                            if (!$insert_stmt->execute()) {
                                echo "Error inserting asset: " . $insert_stmt->error;
                            }
                            $insert_stmt->close();
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
