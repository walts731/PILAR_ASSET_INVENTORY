<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
require_once '../includes/audit_helper.php';
require '../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Helpers
function parse_bool($val) {
    $v = strtolower(trim((string)$val));
    return in_array($v, ['1','true','yes','y','t','on'], true) ? 1 : 0;
}

function safe_date($val) {
    if (empty($val)) return date('Y-m-d');
    $ts = strtotime($val);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $fileName = $_FILES['csv_file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $dataRows = [];

    // Read header + rows (support CSV or XLSX)
    $headers = [];
    if ($fileExt === 'csv') {
        $handle = fopen($fileTmpPath, 'r');
        if (!$handle) { die("Error opening CSV file."); }
        $headers = fgetcsv($handle); // header
        while (($row = fgetcsv($handle, 10000, ',')) !== false) {
            $dataRows[] = $row;
        }
        fclose($handle);
    } elseif ($fileExt === 'xlsx') {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $headers = array_shift($rows);
        $dataRows = $rows;
    } else {
        die("Unsupported file format. Please upload a CSV or XLSX file.");
    }

    // Normalize headers to lowercase keys for mapping
    $map = [];
    foreach ($headers as $idx => $h) {
        $map[strtolower(trim((string)$h))] = $idx;
    }

    // Required minimal columns
    $required = ['description','category_name','quantity','unit','value','office_name','type'];
    foreach ($required as $col) {
        if (!array_key_exists($col, $map)) {
            die("Missing required column: {$col}");
        }
    }

    // Optional extended columns
    $opt = [
        'status','acquisition_date','employee_name','end_user','red_tagged','serial_no','code','property_no','model','brand','inventory_tag'
    ];

    $success = 0; $failed = 0; $messages = [];

    // Prepare reusable statements
    $stmtFindCategory = $conn->prepare("SELECT id FROM categories WHERE category_name = ? LIMIT 1");
    $stmtFindOffice = $conn->prepare("SELECT id FROM offices WHERE office_name = ? LIMIT 1");
    $stmtFindEmployee = $conn->prepare("SELECT employee_id FROM employees WHERE name = ? LIMIT 1");

    // Prepare assets_new insert (ics_id NULL for CSV imports)
    $stmtInsAssetNew = $conn->prepare("INSERT INTO assets_new (description, quantity, unit_cost, unit, office_id, ics_id, date_created) VALUES (?, ?, ?, ?, ?, NULL, NOW())");

    // Prepare item-level asset insert
    $stmtInsAsset = $conn->prepare("INSERT INTO assets 
        (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand, inventory_tag, asset_new_id)
        VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?, NOW(), ?, '', ?, '', ?, ?, ?, ?, ?, ?, ?)");

    // Detect if mr_details table has end_user column (once) and prepare MR statements
    $hasMrEndUserCol = false;
    if ($resCol2 = $conn->query("SHOW COLUMNS FROM mr_details LIKE 'end_user'")) {
        $hasMrEndUserCol = ($resCol2->num_rows > 0);
        $resCol2->close();
    }

    // Existence check to avoid duplicate MR for same asset
    $stmtMrExists = $conn->prepare("SELECT 1 FROM mr_details WHERE asset_id = ? LIMIT 1");
    // Insert MR (item_id nullable)
    $stmtMrInsert = $conn->prepare("INSERT INTO mr_details 
        (item_id, asset_id, office_location, description, model_no, serial_no, serviceable, unserviceable, unit_quantity, unit, acquisition_date, acquisition_cost, person_accountable, end_user, acquired_date, counted_date, inventory_tag)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Detect if assets table has end_user column (once)
    $hasEndUserCol = false;
    if ($resCol = $conn->query("SHOW COLUMNS FROM assets LIKE 'end_user'")) {
        $hasEndUserCol = ($resCol->num_rows > 0);
        $resCol->close();
    }

    foreach ($dataRows as $row) {
        // Extract required
        $description   = trim((string)$row[$map['description']] ?? '');
        $category_name = trim((string)$row[$map['category_name']] ?? '');
        $quantity      = (int)($row[$map['quantity']] ?? 0);
        $unit          = trim((string)$row[$map['unit']] ?? '');
        $value         = (float)($row[$map['value']] ?? 0);
        $office_name   = trim((string)$row[$map['office_name']] ?? '');
        $type          = strtolower(trim((string)$row[$map['type']] ?? 'asset'));

        if ($description === '' || $quantity <= 0 || $unit === '' || $office_name === '') {
            $failed++; $messages[] = "Skipped row (missing required fields): " . htmlspecialchars($description);
            continue;
        }

        // Optional values
        $status        = isset($map['status']) ? strtolower(trim((string)($row[$map['status']] ?? 'available'))) : 'available';
        $acq_date      = isset($map['acquisition_date']) ? safe_date($row[$map['acquisition_date']] ?? '') : date('Y-m-d');
        $employee_name = isset($map['employee_name']) ? trim((string)($row[$map['employee_name']] ?? '')) : '';
        $end_user      = isset($map['end_user']) ? trim((string)($row[$map['end_user']] ?? '')) : '';
        $red_tagged    = isset($map['red_tagged']) ? parse_bool($row[$map['red_tagged']] ?? 0) : 0;
        $serial_no     = isset($map['serial_no']) ? trim((string)($row[$map['serial_no']] ?? '')) : '';
        $code          = isset($map['code']) ? trim((string)($row[$map['code']] ?? '')) : '';
        $property_no   = isset($map['property_no']) ? trim((string)($row[$map['property_no']] ?? '')) : null;
        $model         = isset($map['model']) ? trim((string)($row[$map['model']] ?? '')) : '';
        $brand         = isset($map['brand']) ? trim((string)($row[$map['brand']] ?? '')) : '';
        $inventory_tag = isset($map['inventory_tag']) ? trim((string)($row[$map['inventory_tag']] ?? '')) : '';

        // Lookups
        // Category is optional for item rows (not set in item-level insert), but we validate presence
        $category_id = null;
        if ($category_name !== '') {
            $stmtFindCategory->bind_param('s', $category_name);
            $stmtFindCategory->execute();
            $res = $stmtFindCategory->get_result();
            $cat = $res ? $res->fetch_assoc() : null;
            $category_id = $cat['id'] ?? null; // not used directly below, retained for future extension
        }

        // Office
        $office_id = null;
        $stmtFindOffice->bind_param('s', $office_name);
        $stmtFindOffice->execute();
        $resOfc = $stmtFindOffice->get_result();
        if ($resOfc && ($ofc = $resOfc->fetch_assoc())) {
            $office_id = (int)$ofc['id'];
        } else {
            $failed++; $messages[] = "Office not found: {$office_name}"; continue;
        }

        // Employee (by name)
        $employee_id = null;
        if ($employee_name !== '') {
            $stmtFindEmployee->bind_param('s', $employee_name);
            $stmtFindEmployee->execute();
            $resEmp = $stmtFindEmployee->get_result();
            if ($resEmp && ($emp = $resEmp->fetch_assoc())) {
                $employee_id = (int)$emp['employee_id'];
            }
        }

        // Insert into assets_new (parent aggregate for the batch)
        $stmtInsAssetNew->bind_param('sddsi', $description, $quantity, $value, $unit, $office_id);
        if (!$stmtInsAssetNew->execute()) {
            $failed++; $messages[] = "Failed to insert into assets_new for: {$description}"; continue;
        }
        $asset_new_id = $conn->insert_id;

        // Create item-level assets equal to quantity
        $inserted_count = 0;
        for ($i = 0; $i < $quantity; $i++) {
            // Bind and insert item
            $asset_name = $description; // use description as name
            $types = 'sssssiiidsssssssi';
            $stmtInsAsset->bind_param(
                $types,
                $asset_name,      // s
                $description,     // s
                $unit,            // s
                $status,          // s
                $acq_date,        // s
                $office_id,       // i
                $employee_id,     // i
                $red_tagged,      // d (we'll treat as int but bind as d? better use i)
                $value,           // d
                $type,            // s
                $serial_no,       // s
                $code,            // s
                $property_no,     // s
                $model,           // s
                $brand,           // s
                $inventory_tag,   // s
                $asset_new_id     // i
            );

            if ($stmtInsAsset->execute()) {
                $new_item_id = $conn->insert_id;
                $inserted_count++;
                // Generate QR code per item
                $qr_filename = $new_item_id . '.png';
                $qr_path = '../img/' . $qr_filename;
                QRcode::png((string)$new_item_id, $qr_path, QR_ECLEVEL_L, 4);
                // Update asset with qr filename and optional end_user if column exists
                $updSql = "UPDATE assets SET qr_code = ?" . (($hasEndUserCol && !empty($end_user)) ? ", end_user = ?" : "") . " WHERE id = ?";
                if ($hasEndUserCol && !empty($end_user)) {
                    $stmtUpd = $conn->prepare($updSql);
                    $stmtUpd->bind_param('ssi', $qr_filename, $end_user, $new_item_id);
                } else {
                    $stmtUpd = $conn->prepare($updSql);
                    $stmtUpd->bind_param('si', $qr_filename, $new_item_id);
                }
                $stmtUpd->execute();
                $stmtUpd->close();

                // Conditionally create MR record when both inventory_tag and employee_name are provided
                if (!empty($inventory_tag) && !empty($employee_name)) {
                    // Avoid duplicates
                    $stmtMrExists->bind_param('i', $new_item_id);
                    $stmtMrExists->execute();
                    $resExists = $stmtMrExists->get_result();
                    $exists = $resExists && $resExists->num_rows > 0;
                    if (!$exists) {
                        // Derive MR fields
                        $item_id_null = null; // CSV import has no ICS mapping
                        $office_location = $office_name; // use provided office_name
                        $model_no = $model;
                        $serviceable = (strtolower($status) === 'unserviceable') ? 0 : 1;
                        $unserviceable = (strtolower($status) === 'unserviceable') ? 1 : 0;
                        $unit_quantity = 1;
                        $acquisition_cost = (string)$value; // keep as string to match existing bind pattern
                        $person_accountable = $employee_name;
                        $acquired_date = $acq_date;
                        $counted_date = $acq_date;

                        $stmtMrInsert->bind_param(
                            'iissssiiissssssss',
                            $item_id_null,           // i (NULL)
                            $new_item_id,            // i
                            $office_location,        // s
                            $description,            // s
                            $model_no,               // s
                            $serial_no,              // s
                            $serviceable,            // i
                            $unserviceable,          // i
                            $unit_quantity,          // i
                            $unit,                   // s
                            $acq_date,               // s
                            $acquisition_cost,       // s
                            $person_accountable,     // s
                            $end_user,               // s (allowed empty)
                            $acquired_date,          // s
                            $counted_date,           // s
                            $inventory_tag           // s
                        );
                        $stmtMrInsert->execute();
                    }
                }
            }
        }

        if ($inserted_count > 0) { $success++; } else { $failed++; }
    }

    // Log CSV import operation
    logBulkActivity('IMPORT', $success, "CSV/Excel Assets from file: {$fileName}");

    // Redirect with summary
    header("Location: inventory.php?import=success&ok={$success}&fail={$failed}");
    exit();
} else {
    echo "Invalid request.";
}
?>
