<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
require '../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

function safe_date($val) {
    if (empty($val)) return date('Y-m-d');
    $ts = strtotime($val);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}

function parse_bool($val) {
    $v = strtolower(trim((string)$val));
    return in_array($v, ['1','true','yes','y','t','on'], true) ? 1 : 0;
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
        header("Location: infrastructure_inventory.php?import=error&message=" . urlencode($error_message));
        exit();
    }

    // Check if file exists and is readable
    if (!file_exists($fileTmpPath) || !is_readable($fileTmpPath)) {
        header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Uploaded file is not accessible."));
        exit();
    }

    $dataRows = [];

    // Read header + rows (support CSV or XLSX) with enhanced error handling
    $headers = [];
    try {
        if ($fileExt === 'csv') {
            $handle = fopen($fileTmpPath, 'r');
            if (!$handle) {
                header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Error opening CSV file. Please check file format."));
                exit();
            }
            $headers = fgetcsv($handle); // header
            if ($headers === false) {
                fclose($handle);
                header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Could not read CSV headers. Please check file format."));
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
                header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Excel file appears to be empty."));
                exit();
            }
            $headers = array_shift($rows);
            $dataRows = $rows;
        } else {
            header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Unsupported file format. Please upload a CSV or XLSX file."));
            exit();
        }
    } catch (Exception $e) {
        header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Error reading file: " . $e->getMessage()));
        exit();
    }

    // Normalize headers to lowercase keys for mapping
    $map = [];
    foreach ($headers as $idx => $h) {
        $map[strtolower(trim((string)$h))] = $idx;
    }

    // Check if we have any data rows
    if (empty($dataRows)) {
        header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("No data rows found in the file. Please check your file content."));
        exit();
    }

    // Required columns for infrastructure
    $required = ['classification_type', 'item_description', 'location'];
    $missing_columns = [];
    foreach ($required as $col) {
        if (!array_key_exists($col, $map)) {
            $missing_columns[] = $col;
        }
    }

    if (!empty($missing_columns)) {
        $missing_list = implode(', ', $missing_columns);
        header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Missing required columns: {$missing_list}. Please check your CSV headers."));
        exit();
    }

    // Optional extended columns
    $opt = [
        'nature_occupancy', 'property_no_or_reference', 'acquisition_cost',
        'market_appraisal_insurable_interest', 'date_constructed_acquired_manufactured',
        'date_of_appraisal', 'remarks'
    ];

    $success = 0; $failed = 0; $messages = [];
    $detailed_errors = [];

    // Prepare statements
    $stmtInsInfrastructure = $conn->prepare("INSERT INTO infrastructure_inventory
        (classification_type, item_description, nature_occupancy, location,
         date_constructed_acquired_manufactured, property_no_or_reference,
         acquisition_cost, market_appraisal_insurable_interest, date_of_appraisal, remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($dataRows as $row) {
        // Extract required fields
        $classification_type = trim((string)$row[$map['classification_type']] ?? '');
        $item_description = trim((string)$row[$map['item_description']] ?? '');
        $location = trim((string)$row[$map['location']] ?? '');

        // Extract optional fields
        $nature_occupancy = isset($map['nature_occupancy']) ? trim((string)($row[$map['nature_occupancy']] ?? '')) : '';
        $property_no_or_reference = isset($map['property_no_or_reference']) ? trim((string)($row[$map['property_no_or_reference']] ?? '')) : '';
        $acquisition_cost = isset($map['acquisition_cost']) ? (float)($row[$map['acquisition_cost']] ?? 0) : null;
        $market_appraisal_insurable_interest = isset($map['market_appraisal_insurable_interest']) ? (float)($row[$map['market_appraisal_insurable_interest']] ?? 0) : null;
        $date_constructed_acquired_manufactured = isset($map['date_constructed_acquired_manufactured']) ? safe_date($row[$map['date_constructed_acquired_manufactured']] ?? '') : null;
        $date_of_appraisal = isset($map['date_of_appraisal']) ? safe_date($row[$map['date_of_appraisal']] ?? '') : null;
        $remarks = isset($map['remarks']) ? trim((string)($row[$map['remarks']] ?? '')) : '';

        if (empty($classification_type) || empty($item_description) || empty($location)) {
            $failed++;
            $row_number = $success + $failed;
            $error_details = [];
            if (empty($classification_type)) $error_details[] = 'classification_type is empty';
            if (empty($item_description)) $error_details[] = 'item_description is empty';
            if (empty($location)) $error_details[] = 'location is empty';
            $detailed_errors[] = "Row {$row_number}: " . implode(', ', $error_details);
            continue;
        }

        // Insert infrastructure record
        $stmtInsInfrastructure->bind_param(
            'ssssssssss',
            $classification_type,
            $item_description,
            $nature_occupancy,
            $location,
            $date_constructed_acquired_manufactured,
            $property_no_or_reference,
            $acquisition_cost,
            $market_appraisal_insurable_interest,
            $date_of_appraisal,
            $remarks
        );

        if ($stmtInsInfrastructure->execute()) {
            $success++;
            $messages[] = "Successfully imported '{$item_description}'";
        } else {
            $failed++;
            $row_number = $success + $failed;
            $db_error = $conn->error ? ': ' . $conn->error : '';
            $detailed_errors[] = "Row {$row_number}: Failed to import '{$item_description}'{$db_error}";
        }
    }

    // Close prepared statements
    $stmtInsInfrastructure->close();

    // Log CSV import operation
    logBulkActivity('IMPORT', $success, "CSV/Excel Infrastructure from file: {$fileName}");

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
        header("Location: infrastructure_inventory.php?import=success&ok={$success}&fail={$failed}");
    } elseif ($success > 0 && $failed > 0) {
        // Partial success
        header("Location: infrastructure_inventory.php?import=partial&ok={$success}&fail={$failed}&errors=" . urlencode($error_details));
    } else {
        // Complete failure
        header("Location: infrastructure_inventory.php?import=failed&ok={$success}&fail={$failed}&errors=" . urlencode($error_details));
    }
    exit();
} else {
    header("Location: infrastructure_inventory.php?import=error&message=" . urlencode("Invalid request. Please use the import form."));
    exit();
}
?>
