<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
require_once '../includes/audit_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Sanitize and escape input values
$category    = (int)($_POST['category'] ?? 0);
$description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
$quantity    = (int)($_POST['quantity'] ?? 0);
$unit        = mysqli_real_escape_string($conn, $_POST['unit'] ?? '');
$value       = (float)($_POST['value'] ?? 0);
$status      = strtolower(mysqli_real_escape_string($conn, $_POST['status'] ?? 'available'));
$office_id   = (int)($_POST['office_id'] ?? 0);
$type        = strtolower(mysqli_real_escape_string($conn, $_POST['type'] ?? 'asset'));
$acq_input   = isset($_POST['acquisition_date']) ? trim((string)$_POST['acquisition_date']) : '';
// Safe date parsing with fallback to today
$ts = $acq_input !== '' ? strtotime($acq_input) : false;
$acquired    = $ts ? date('Y-m-d', $ts) : date('Y-m-d');
$red_tagged  = 0;

// Optional fields
$serial_no   = isset($_POST['serial_no']) && $_POST['serial_no'] !== '' ? mysqli_real_escape_string($conn, $_POST['serial_no']) : '';
$code        = isset($_POST['code']) && $_POST['code'] !== '' ? mysqli_real_escape_string($conn, $_POST['code']) : '';
$stock_no    = isset($_POST['stock_no']) && $_POST['stock_no'] !== '' ? mysqli_real_escape_string($conn, $_POST['stock_no']) : '';
$model       = isset($_POST['model']) && $_POST['model'] !== '' ? mysqli_real_escape_string($conn, $_POST['model']) : '';
$brand       = isset($_POST['brand']) && $_POST['brand'] !== '' ? mysqli_real_escape_string($conn, $_POST['brand']) : '';

// Optional extended fields to enable MR insertion parity with CSV import
$inventory_tag = isset($_POST['inventory_tag']) ? trim((string)$_POST['inventory_tag']) : '';
$employee_name = isset($_POST['employee_name']) ? trim((string)$_POST['employee_name']) : '';
$end_user      = isset($_POST['end_user']) ? trim((string)$_POST['end_user']) : '';

// Image handling
$image_filename = null;
if (isset($_FILES['asset_image']) && $_FILES['asset_image']['error'] === UPLOAD_ERR_OK) {
    $image_tmp = $_FILES['asset_image']['tmp_name'];
    $image_name = basename($_FILES['asset_image']['name']);
    $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed, true)) {
        $image_filename = 'asset_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $upload_dir = realpath(__DIR__ . '/../img/assets');
        if ($upload_dir === false) {
            // fallback: create directory
            $upload_dir_path = __DIR__ . '/../img/assets/';
            if (!is_dir($upload_dir_path)) { @mkdir($upload_dir_path, 0755, true); }
            move_uploaded_file($image_tmp, $upload_dir_path . $image_filename);
        } else {
            move_uploaded_file($image_tmp, $upload_dir . DIRECTORY_SEPARATOR . $image_filename);
        }
    }
}

// Lookup office name for logging and MR office_location
$office_name = 'Unknown Office';
if ($office_id > 0) {
    $office_stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
    $office_stmt->bind_param("i", $office_id);
    $office_stmt->execute();
    $office_res = $office_stmt->get_result();
    if ($office_res && ($of = $office_res->fetch_assoc())) { $office_name = $of['office_name']; }
    $office_stmt->close();
}

// Optional employee lookup by name => employee_id
$employee_id = null;
if ($employee_name !== '') {
    if ($stmtEmp = $conn->prepare("SELECT employee_id FROM employees WHERE name = ? LIMIT 1")) {
        $stmtEmp->bind_param('s', $employee_name);
        $stmtEmp->execute();
        $resEmp = $stmtEmp->get_result();
        if ($resEmp && ($er = $resEmp->fetch_assoc())) { $employee_id = (int)$er['employee_id']; }
        $stmtEmp->close();
    }
}

// Insert parent record into assets_new
if ($quantity <= 0 || $description === '' || $unit === '') {
    echo "Invalid data."; exit();
}

if (!($stmtAn = $conn->prepare("INSERT INTO assets_new (description, quantity, unit_cost, unit, office_id, ics_id, date_created) VALUES (?, ?, ?, ?, ?, NULL, NOW())"))) {
    echo "Failed to prepare assets_new insert."; exit();
}
$stmtAn->bind_param('sddsi', $description, $quantity, $value, $unit, $office_id);
if (!$stmtAn->execute()) { echo "Failed to insert into assets_new."; exit(); }
$asset_new_id = $conn->insert_id;
$stmtAn->close();

// Prepare item-level asset insert
$stmtInsAsset = $conn->prepare("INSERT INTO assets 
    (asset_name, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand, inventory_tag, asset_new_id)
    VALUES (?, ?, 1, ?, ?, ?, ?, ?, ?, NOW(), ?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Prepare MR details insert and existence check
$stmtMrExists = $conn->prepare("SELECT 1 FROM mr_details WHERE asset_id = ? LIMIT 1");
$stmtMrInsert = $conn->prepare("INSERT INTO mr_details 
    (item_id, asset_id, office_location, description, model_no, serial_no, serviceable, unserviceable, unit_quantity, unit, acquisition_date, acquisition_cost, person_accountable, end_user, acquired_date, counted_date, inventory_tag)
    VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Determine property_no value for item rows
// - For assets: use posted property_no (editable)
// - For consumables: use stock_no if provided
$property_no_post = isset($_POST['property_no']) ? trim((string)$_POST['property_no']) : '';
$property_no = ($type === 'asset') ? ($property_no_post !== '' ? $property_no_post : null)
                                   : ($stock_no !== '' ? $stock_no : null);

$created_count = 0;
for ($i = 0; $i < $quantity; $i++) {
    $asset_name = $description;
    $stmtInsAsset->bind_param(
        'sssssiiidssssssssi',
        $asset_name,         // s
        $description,        // s
        $unit,               // s
        $status,             // s
        $acquired,           // s
        $office_id,          // i
        $employee_id,        // i nullable
        $red_tagged,         // i
        $value,              // d
        $type,               // s
        $image_filename,     // s
        $serial_no,          // s
        $code,               // s
        $property_no,        // s|null
        $model,              // s
        $brand,              // s
        $inventory_tag,      // s
        $asset_new_id        // i
    );
    if ($stmtInsAsset->execute()) {
        $new_id = $conn->insert_id;
        $created_count++;
        // Generate QR file per item into ../img/{id}.png
        $qr_filename = $new_id . '.png';
        $qr_path = realpath(__DIR__ . '/../img');
        if ($qr_path === false) { $qr_path = __DIR__ . '/../img'; }
        QRcode::png((string)$new_id, $qr_path . DIRECTORY_SEPARATOR . $qr_filename, QR_ECLEVEL_L, 4);
        // Update asset with qr filename
        if ($stmtUpd = $conn->prepare("UPDATE assets SET qr_code = ? WHERE id = ?")) {
            $stmtUpd->bind_param('si', $qr_filename, $new_id);
            $stmtUpd->execute();
            $stmtUpd->close();
        }

        // Conditionally insert MR details if both inventory_tag and employee_name provided
        if ($inventory_tag !== '' && $employee_name !== '') {
            $stmtMrExists->bind_param('i', $new_id);
            $stmtMrExists->execute();
            $resE = $stmtMrExists->get_result();
            $exists = $resE && $resE->num_rows > 0;
            if (!$exists) {
                $serviceable = ($status === 'unserviceable') ? 0 : 1;
                $unserviceable = ($status === 'unserviceable') ? 1 : 0;
                $unit_quantity = 1;
                $acquisition_cost = (string)$value;
                $stmtMrInsert->bind_param(
                    'issssiiissssssss',
                    $new_id,           // asset_id
                    $office_name,      // office_location
                    $description,      // description
                    $model,            // model_no
                    $serial_no,        // serial_no
                    $serviceable,      // serviceable
                    $unserviceable,    // unserviceable
                    $unit_quantity,    // unit_quantity
                    $unit,             // unit
                    $acquired,         // acquisition_date
                    $acquisition_cost, // acquisition_cost
                    $employee_name,    // person_accountable
                    $end_user,         // end_user
                    $acquired,         // acquired_date
                    $acquired,         // counted_date
                    $inventory_tag     // inventory_tag
                );
                $stmtMrInsert->execute();
            }
        }
    }
}

// Audit log and redirect
$asset_details = "Created asset(s): {$description} (Items: {$created_count}/{$quantity}, Value: ₱" . number_format($value, 2) . ", Office: {$office_name})";
logAssetActivity('CREATE', $description, 0, "Qty: {$quantity}, Value: ₱" . number_format($value, 2) . ", Office: {$office_name}");

header("Location: inventory.php?add=success");
exit();
?>