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

// Helper function to increment alphanumeric identifiers
function increment_identifier($identifier, $index) {
    if (empty($identifier)) return '';
    
    // If identifier contains numbers, increment the numeric part
    if (preg_match('/^(.*)([0-9]+)(.*)$/', $identifier, $matches)) {
        $prefix = $matches[1];
        $number = intval($matches[2]);
        $suffix = $matches[3];
        $new_number = $number + $index;
        // Preserve leading zeros
        $number_length = strlen($matches[2]);
        $formatted_number = str_pad($new_number, $number_length, '0', STR_PAD_LEFT);
        return $prefix . $formatted_number . $suffix;
    }
    
    // If no numbers found, append the index
    return $identifier . '-' . ($index + 1);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $fileName = $_FILES['csv_file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Enhanced error handling for file upload
    if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = "File upload failed: ";
        switch ($_FILES['csv_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= "File is too large.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= "File was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message .= "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message .= "Missing temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message .= "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message .= "File upload stopped by extension.";
                break;
            default:
                $error_message .= "Unknown upload error.";
                break;
        }
        header("Location: inventory.php?import=error&message=" . urlencode($error_message));
        exit();
    }

    // Check if file exists and is readable
    if (!file_exists($fileTmpPath) || !is_readable($fileTmpPath)) {
        header("Location: inventory.php?import=error&message=" . urlencode("Uploaded file is not accessible."));
        exit();
    }

    $dataRows = [];

    // Read header + rows (support CSV or XLSX) with enhanced error handling
    $headers = [];
    try {
        if ($fileExt === 'csv') {
            $handle = fopen($fileTmpPath, 'r');
            if (!$handle) {
                header("Location: inventory.php?import=error&message=" . urlencode("Error opening CSV file. Please check file format."));
                exit();
            }
            $headers = fgetcsv($handle); // header
            if ($headers === false) {
                fclose($handle);
                header("Location: inventory.php?import=error&message=" . urlencode("Could not read CSV headers. Please check file format."));
                exit();
            }
            while (($row = fgetcsv($handle, 10000, ',')) !== false) {
                $dataRows[] = $row;
            }
            fclose($handle);
        } elseif ($fileExt === 'xlsx') {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            if (empty($rows)) {
                header("Location: inventory.php?import=error&message=" . urlencode("Excel file appears to be empty."));
                exit();
            }
            $headers = array_shift($rows);
            $dataRows = $rows;
        } else {
            header("Location: inventory.php?import=error&message=" . urlencode("Unsupported file format. Please upload a CSV or XLSX file."));
            exit();
        }
    } catch (Exception $e) {
        header("Location: inventory.php?import=error&message=" . urlencode("Error reading file: " . $e->getMessage()));
        exit();
    }

    // Normalize headers to lowercase keys for mapping
    $map = [];
    foreach ($headers as $idx => $h) {
        $map[strtolower(trim((string)$h))] = $idx;
    }

    // Check if we have any data rows
    if (empty($dataRows)) {
        header("Location: inventory.php?import=error&message=" . urlencode("No data rows found in the file. Please check your file content."));
        exit();
    }

    // Required minimal columns
    $required = ['description','category_name','quantity','unit','value','office_name','type'];
    $missing_columns = [];
    foreach ($required as $col) {
        if (!array_key_exists($col, $map)) {
            $missing_columns[] = $col;
        }
    }
    
    if (!empty($missing_columns)) {
        $missing_list = implode(', ', $missing_columns);
        header("Location: inventory.php?import=error&message=" . urlencode("Missing required columns: {$missing_list}. Please check your CSV headers."));
        exit();
    }

    // Optional extended columns
    $opt = [
        'status','acquisition_date','employee_name','end_user','red_tagged','serial_no','code','property_no','model','brand','inventory_tag'
    ];

    $success = 0; $failed = 0; $messages = [];
    $detailed_errors = [];

    // Prepare reusable statements
    $stmtFindCategory = $conn->prepare("SELECT id FROM categories WHERE category_name = ? LIMIT 1");
    $stmtFindOffice = $conn->prepare("SELECT id FROM offices WHERE office_name = ? LIMIT 1");
    $stmtFindEmployee = $conn->prepare("SELECT employee_id FROM employees WHERE name = ? LIMIT 1");
    
    // Prepare uniqueness check statements
    $stmtCheckSerial = $conn->prepare("SELECT id FROM assets WHERE serial_no = ? AND serial_no != '' LIMIT 1");
    $stmtCheckCode = $conn->prepare("SELECT id FROM assets WHERE code = ? AND code != '' LIMIT 1");
    $stmtCheckProperty = $conn->prepare("SELECT id FROM assets WHERE property_no = ? AND property_no != '' LIMIT 1");
    $stmtCheckInventory = $conn->prepare("SELECT id FROM assets WHERE inventory_tag = ? AND inventory_tag != '' LIMIT 1");

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
            $failed++;
            $row_number = $success + $failed;
            $error_details = [];
            if ($description === '') $error_details[] = 'description is empty';
            if ($quantity <= 0) $error_details[] = 'quantity is invalid (' . $quantity . ')';
            if ($unit === '') $error_details[] = 'unit is empty';
            if ($office_name === '') $error_details[] = 'office_name is empty';
            $detailed_errors[] = "Row {$row_number}: " . implode(', ', $error_details);
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
            $failed++;
            $row_number = $success + $failed;
            $detailed_errors[] = "Row {$row_number}: Office '{$office_name}' not found in system";
            continue;
        }

        // Employee (by name) - Enhanced validation like office_id
        $employee_id = null;
        if ($employee_name !== '') {
            $stmtFindEmployee->bind_param('s', $employee_name);
            $stmtFindEmployee->execute();
            $resEmp = $stmtFindEmployee->get_result();
            if ($resEmp && ($emp = $resEmp->fetch_assoc())) {
                $employee_id = (int)$emp['employee_id'];
            } else {
                // Employee name provided but not found - this is now an error
                $failed++;
                $row_number = $success + $failed;
                $detailed_errors[] = "Row {$row_number}: Employee '{$employee_name}' not found in system";
                continue;
            }
        }

        // Validate uniqueness of key fields if they are provided
        $uniqueness_errors = [];
        
        // Check serial number uniqueness
        if (!empty($serial_no)) {
            $stmtCheckSerial->bind_param('s', $serial_no);
            $stmtCheckSerial->execute();
            $resSerial = $stmtCheckSerial->get_result();
            if ($resSerial && $resSerial->num_rows > 0) {
                $uniqueness_errors[] = "serial number '{$serial_no}' already exists";
            }
        }
        
        // Check code uniqueness
        if (!empty($code)) {
            $stmtCheckCode->bind_param('s', $code);
            $stmtCheckCode->execute();
            $resCode = $stmtCheckCode->get_result();
            if ($resCode && $resCode->num_rows > 0) {
                $uniqueness_errors[] = "code '{$code}' already exists";
            }
        }
        
        // Check property number uniqueness
        if (!empty($property_no)) {
            $stmtCheckProperty->bind_param('s', $property_no);
            $stmtCheckProperty->execute();
            $resProperty = $stmtCheckProperty->get_result();
            if ($resProperty && $resProperty->num_rows > 0) {
                $uniqueness_errors[] = "property number '{$property_no}' already exists";
            }
        }
        
        // Check inventory tag uniqueness
        if (!empty($inventory_tag)) {
            $stmtCheckInventory->bind_param('s', $inventory_tag);
            $stmtCheckInventory->execute();
            $resInventory = $stmtCheckInventory->get_result();
            if ($resInventory && $resInventory->num_rows > 0) {
                $uniqueness_errors[] = "inventory tag '{$inventory_tag}' already exists";
            }
        }
        
        // If there are uniqueness errors, skip this row
        if (!empty($uniqueness_errors)) {
            $failed++;
            $row_number = $success + $failed;
            $detailed_errors[] = "Row {$row_number}: Duplicate values found - " . implode(', ', $uniqueness_errors);
            continue;
        }
        
        // Validate required fields that cannot be empty (if provided)
        $required_field_errors = [];
        
        // These fields, if provided in CSV, cannot be empty strings
        if (isset($map['serial_no']) && array_key_exists($map['serial_no'], $row) && trim((string)$row[$map['serial_no']]) === '') {
            // Serial number column exists but is empty - this is allowed, so no error
        }
        if (isset($map['code']) && array_key_exists($map['code'], $row) && trim((string)$row[$map['code']]) === '') {
            // Code column exists but is empty - this is allowed, so no error
        }
        if (isset($map['property_no']) && array_key_exists($map['property_no'], $row) && trim((string)$row[$map['property_no']]) === '') {
            // Property number column exists but is empty - this is allowed, so no error
        }
        if (isset($map['inventory_tag']) && array_key_exists($map['inventory_tag'], $row) && trim((string)$row[$map['inventory_tag']]) === '') {
            // Inventory tag column exists but is empty - this is allowed, so no error
        }
        
        // If there are required field errors, skip this row
        if (!empty($required_field_errors)) {
            $failed++;
            $row_number = $success + $failed;
            $detailed_errors[] = "Row {$row_number}: Required fields cannot be empty - " . implode(', ', $required_field_errors);
            continue;
        }

        // Insert into assets_new (parent aggregate for the batch)
        $stmtInsAssetNew->bind_param('sddsi', $description, $quantity, $value, $unit, $office_id);
        if (!$stmtInsAssetNew->execute()) {
            $failed++;
            $row_number = $success + $failed;
            $db_error = $conn->error ? ': ' . $conn->error : '';
            $detailed_errors[] = "Row {$row_number}: Failed to create asset record for '{$description}'{$db_error}";
            continue;
        }
        $asset_new_id = $conn->insert_id;

        // Create item-level assets equal to quantity
        $inserted_count = 0;
        for ($i = 0; $i < $quantity; $i++) {
            // Generate incremented identifiers for each item when quantity > 1
            $item_serial_no = ($quantity > 1 && !empty($serial_no)) ? increment_identifier($serial_no, $i) : $serial_no;
            $item_code = ($quantity > 1 && !empty($code)) ? increment_identifier($code, $i) : $code;
            $item_property_no = ($quantity > 1 && !empty($property_no)) ? increment_identifier($property_no, $i) : $property_no;
            $item_inventory_tag = ($quantity > 1 && !empty($inventory_tag)) ? increment_identifier($inventory_tag, $i) : $inventory_tag;
            
            // Validate uniqueness of incremented identifiers
            $item_uniqueness_errors = [];
            
            // Check incremented serial number uniqueness
            if (!empty($item_serial_no)) {
                $stmtCheckSerial->bind_param('s', $item_serial_no);
                $stmtCheckSerial->execute();
                $resSerial = $stmtCheckSerial->get_result();
                if ($resSerial && $resSerial->num_rows > 0) {
                    $item_uniqueness_errors[] = "serial number '{$item_serial_no}' already exists";
                }
            }
            
            // Check incremented code uniqueness
            if (!empty($item_code)) {
                $stmtCheckCode->bind_param('s', $item_code);
                $stmtCheckCode->execute();
                $resCode = $stmtCheckCode->get_result();
                if ($resCode && $resCode->num_rows > 0) {
                    $item_uniqueness_errors[] = "code '{$item_code}' already exists";
                }
            }
            
            // Check incremented property number uniqueness
            if (!empty($item_property_no)) {
                $stmtCheckProperty->bind_param('s', $item_property_no);
                $stmtCheckProperty->execute();
                $resProperty = $stmtCheckProperty->get_result();
                if ($resProperty && $resProperty->num_rows > 0) {
                    $item_uniqueness_errors[] = "property number '{$item_property_no}' already exists";
                }
            }
            
            // Check incremented inventory tag uniqueness
            if (!empty($item_inventory_tag)) {
                $stmtCheckInventory->bind_param('s', $item_inventory_tag);
                $stmtCheckInventory->execute();
                $resInventory = $stmtCheckInventory->get_result();
                if ($resInventory && $resInventory->num_rows > 0) {
                    $item_uniqueness_errors[] = "inventory tag '{$item_inventory_tag}' already exists";
                }
            }
            
            // If there are uniqueness errors for this item, skip it and continue with next
            if (!empty($item_uniqueness_errors)) {
                $failed++;
                $row_number = $success + $failed;
                $item_number = $i + 1;
                $detailed_errors[] = "Row {$row_number} (Item {$item_number}): Duplicate values found - " . implode(', ', $item_uniqueness_errors);
                continue;
            }
            
            // Bind and insert item with incremented identifiers
            $asset_name = $description; // use description as name
            $types = 'sssssiiidsssssssi';
            $stmtInsAsset->bind_param(
                $types,
                $asset_name,         // s
                $description,        // s
                $unit,              // s
                $status,            // s
                $acq_date,          // s
                $office_id,         // i
                $employee_id,       // i
                $red_tagged,        // i
                $value,             // d
                $type,              // s
                $item_serial_no,    // s - incremented
                $item_code,         // s - incremented
                $item_property_no,  // s - incremented
                $model,             // s
                $brand,             // s
                $item_inventory_tag, // s - incremented
                $asset_new_id       // i
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
                            $item_serial_no,         // s - use incremented serial
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
                            $item_inventory_tag      // s - use incremented inventory tag
                        );
                        $stmtMrInsert->execute();
                    }
                }
            }
        }

        if ($inserted_count > 0) { 
            $success++; 
            // Log successful import with quantity details
            if ($quantity > 1) {
                $messages[] = "Successfully imported {$inserted_count} items for '{$description}' with auto-incremented identifiers";
            }
        } else { 
            $failed++; 
        }
    }

    // Close prepared statements
    $stmtFindCategory->close();
    $stmtFindOffice->close();
    $stmtFindEmployee->close();
    $stmtCheckSerial->close();
    $stmtCheckCode->close();
    $stmtCheckProperty->close();
    $stmtCheckInventory->close();
    $stmtInsAssetNew->close();
    $stmtInsAsset->close();
    $stmtMrExists->close();
    $stmtMrInsert->close();
    
    // Log CSV import operation
    logBulkActivity('IMPORT', $success, "CSV/Excel Assets from file: {$fileName}");

    // Prepare detailed error message if there were failures
    $error_details = '';
    if (!empty($detailed_errors)) {
        // Limit to first 10 errors to avoid URL length issues
        $limited_errors = array_slice($detailed_errors, 0, 10);
        $error_details = implode('; ', $limited_errors);
        if (count($detailed_errors) > 10) {
            $error_details .= '; and ' . (count($detailed_errors) - 10) . ' more errors...';
        }
    }

    // Redirect with comprehensive summary
    if ($success > 0 && $failed == 0) {
        // Complete success
        header("Location: inventory.php?import=success&ok={$success}&fail={$failed}");
    } elseif ($success > 0 && $failed > 0) {
        // Partial success
        header("Location: inventory.php?import=partial&ok={$success}&fail={$failed}&errors=" . urlencode($error_details));
    } else {
        // Complete failure
        header("Location: inventory.php?import=failed&ok={$success}&fail={$failed}&errors=" . urlencode($error_details));
    }
    exit();
} else {
    header("Location: inventory.php?import=error&message=" . urlencode("Invalid request. Please use the import form."));
    exit();
}
?>
