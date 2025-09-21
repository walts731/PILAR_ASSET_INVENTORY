<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$ics_id = isset($_GET['ics_id']) ? intval($_GET['ics_id']) : null;
$ics_form_id = $_GET['form_id'] ?? '';


// Fetch the municipal logo from the system table
$logo_path = '';
$stmt_logo = $conn->prepare("SELECT logo FROM system WHERE id = 1");
$stmt_logo->execute();
$result_logo = $stmt_logo->get_result();

if ($result_logo->num_rows > 0) {
    $logo_data = $result_logo->fetch_assoc();
    $logo_path = '../img/' . $logo_data['logo']; // Path to the logo image
}

$stmt_logo->close();

$asset_id = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : null; // item-level asset id from assets table
$asset_data = [];
$office_name = '';
$asset_details = [];

// Fetch categories for dropdown
$categories = [];
$res_cats = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");
if ($res_cats && $res_cats->num_rows > 0) {
    while ($cr = $res_cats->fetch_assoc()) { $categories[] = $cr; }
}

// Dedicated query: fetch ALL categories independently for the Category dropdown
$all_categories = [];
$res_all_categories = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");
if ($res_all_categories && $res_all_categories->num_rows > 0) {
    while ($rowc = $res_all_categories->fetch_assoc()) { $all_categories[] = $rowc; }
}

// Determine document origin (ICS or PAR) and map to a source item id (ics_items.item_id or par_items.item_id)
$existing_mr_check = false;
$mr_item_id = null; // will hold item_id from ics_items or par_items when available
$origin = null;     // 'ICS' | 'PAR' | null

// Seed defaults directly from the item-level assets table
if ($asset_id) {
    $stmt = $conn->prepare("SELECT office_id, inventory_tag, serial_no, acquisition_date FROM assets WHERE id = ?");
    $stmt->bind_param("i", $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $auto_property_no = $row['inventory_tag'] ?? '';
        if (!isset($asset_details['serial_no']) || $asset_details['serial_no'] === '') {
            $asset_details['serial_no'] = $row['serial_no'] ?? '';
        }
        if (!isset($asset_details['acquisition_date']) || $asset_details['acquisition_date'] === '') {
            $asset_details['acquisition_date'] = $row['acquisition_date'] ?? '';
        }
    }
    $stmt->close();
}

// Derive a valid ics_items.item_id for this asset to satisfy mr_details FK
if ($asset_id) {
    // Detect origin by checking assets.ics_id vs assets.par_id
    $stmt_origin = $conn->prepare("SELECT ics_id, par_id FROM assets WHERE id = ?");
    $stmt_origin->bind_param("i", $asset_id);
    $stmt_origin->execute();
    $res_origin = $stmt_origin->get_result();
    $row_origin = $res_origin ? $res_origin->fetch_assoc() : null;
    $stmt_origin->close();

    if ($row_origin && !empty($row_origin['ics_id'])) {
        $origin = 'ICS';
        $stmt_mrmap = $conn->prepare("SELECT item_id FROM ics_items WHERE asset_id = ? ORDER BY item_id ASC LIMIT 1");
        $stmt_mrmap->bind_param("i", $asset_id);
        $stmt_mrmap->execute();
        $res_mrmap = $stmt_mrmap->get_result();
        if ($res_mrmap && $rm = $res_mrmap->fetch_assoc()) {
            $mr_item_id = (int)$rm['item_id'];
        }
        $stmt_mrmap->close();
    } elseif ($row_origin && !empty($row_origin['par_id'])) {
        $origin = 'PAR';
        // Do not set item_id from par_items to avoid violating FK to ics_items
        // We'll proceed with item_id = NULL and identify MR rows by asset_id
    }
}

// Check if MR exists for this mapped ics_items.item_id
if ($mr_item_id) {
    $stmt_check = $conn->prepare("SELECT 1 FROM mr_details WHERE item_id = ? LIMIT 1");
    $stmt_check->bind_param("i", $mr_item_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check && $result_check->num_rows > 0) {
        $existing_mr_check = true;
    }
    $stmt_check->close();
} elseif (!empty($asset_id)) {
    // Fallback: check by asset_id to prevent duplicates when item mapping is unavailable (e.g., PAR items beyond first)
    $stmt_check2 = $conn->prepare("SELECT 1 FROM mr_details WHERE asset_id = ? LIMIT 1");
    $stmt_check2->bind_param("i", $asset_id);
    $stmt_check2->execute();
    $res_check2 = $stmt_check2->get_result();
    if ($res_check2 && $res_check2->num_rows > 0) {
        $existing_mr_check = true;
    }
    $stmt_check2->close();
}
// Generate Inventory Tag
$inventory_tag = '';
if ($asset_id) {
    // Example logic for generating an inventory tag
    $prefix = "PS"; // Asset type prefix
    $size = "5S"; // Size or category, you can modify this based on your asset's size
    $department_code = "03"; // Department or office number
    $factory_code = "F02"; // Factory or location code
    $unique_id = str_pad((string)$asset_id, 2, "0", STR_PAD_LEFT); // Use asset_id for the last part of the tag

    // Concatenate to form the full inventory tag
    $inventory_tag = "No. " . $prefix . "-" . $size . "-" . $department_code . "-" . $factory_code . "-" . $unique_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $asset_id_form = isset($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;
    $office_location = $_POST['office_location'];
    $description = $_POST['description'];
    $model_no = $_POST['model_no'];
    $serial_no = $_POST['serial_no'];
    $code = $_POST['code'] ?? '';
    $property_no = $_POST['property_no'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;

    $serviceable = isset($_POST['serviceable']) ? 1 : 0;
    $unserviceable = isset($_POST['unserviceable']) ? 1 : 0;
    $unit_quantity = $_POST['unit_quantity'];
    $unit = $_POST['unit'];
    $acquisition_date = $_POST['acquisition_date'];
    $acquisition_cost = $_POST['acquisition_cost'];

    // Generate inventory_tag for this POST scope as well (based on asset_id)
    $inventory_tag_gen = '';
    if (!empty($asset_id_form)) {
        $prefix = "PS"; // Asset type prefix
        $size = "5S";   // Category/size code
        $department_code = "03";
        $factory_code = "F02";
        $unique_id = str_pad((string)$asset_id_form, 2, "0", STR_PAD_LEFT);
        $inventory_tag_gen = "No. " . $prefix . "-" . $size . "-" . $department_code . "-" . $factory_code . "-" . $unique_id;
    }

    $person_accountable_name = $_POST['person_accountable_name']; 
    $employee_id = $_POST['employee_id']; 
    $acquired_date = $_POST['acquired_date'];
    $counted_date = $_POST['counted_date'];

    // Server-side validation for required fields
    if ($category_id === null || trim((string)$person_accountable_name) === '') {
        $_SESSION['error_message'] = 'Please select a Category and specify the Person Accountable.';
        header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
        exit();
    }

    // Handle optional asset image upload
    if (!empty($asset_id_form) && isset($_FILES['asset_image']) && $_FILES['asset_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['asset_image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                $_SESSION['error_message'] = 'Image too large. Maximum size is 5MB.';
                header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
                exit();
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            if (!isset($allowed[$mime])) {
                $_SESSION['error_message'] = 'Invalid image type. Allowed: JPG, PNG, GIF.';
                header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
                exit();
            }
            $ext = $allowed[$mime];
            $safeBase = 'asset_' . $asset_id_form . '_' . time();
            $filename = $safeBase . '.' . $ext;
            $targetDir = realpath(__DIR__ . '/../img/assets');
            if ($targetDir === false) {
                $_SESSION['error_message'] = 'Upload directory not found.';
                header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
                exit();
            }
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $_SESSION['error_message'] = 'Failed to move uploaded image.';
                header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
                exit();
            }
            // Save filename (not full path) to assets.image
            $stmt_img = $conn->prepare("UPDATE assets SET image = ? WHERE id = ?");
            $stmt_img->bind_param("si", $filename, $asset_id_form);
            if (!$stmt_img->execute()) {
                $_SESSION['error_message'] = 'Failed to save image to database: ' . $stmt_img->error;
                $stmt_img->close();
                header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
                exit();
            }
            $stmt_img->close();
        } else {
            $_SESSION['error_message'] = 'Image upload error (code ' . (int)$file['error'] . ').';
            header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
            exit();
        }
    }

    // --- Update employee_id in assets table ---
    if ($employee_id) {
        $stmt_update_employee = $conn->prepare("UPDATE assets SET employee_id = ? WHERE id = ?");
        $stmt_update_employee->bind_param("ii", $employee_id, $asset_id);
        if (!$stmt_update_employee->execute()) {
            $_SESSION['error_message'] = "Error updating employee_id in assets: " . $stmt_update_employee->error;
            $stmt_update_employee->close();
            header("Location: create_mr.php?asset_id=" . $asset_id_form);
            exit();
        }
        $stmt_update_employee->close();
    }

    // Property tags are stored on the item-level assets table now

    // If property_no wasn't posted for some reason, compute a fallback
    if (trim((string)$property_no) === '') {
        $basePropPost = isset($asset_details['property_no']) ? trim((string)$asset_details['property_no']) : '';
        if ($basePropPost !== '') {
            $property_no = $basePropPost;
        } elseif (!empty($auto_property_no)) {
            $property_no = $auto_property_no;
        } else {
            $yr = date('Y');
            $property_no = 'MR-' . $yr . '-' . str_pad((string)($asset_id_form ?? 0), 5, '0', STR_PAD_LEFT);
        }
    }

    // --- NEW: Update other asset details to complete the asset record ---
    if ($asset_id) {
        if ($category_id === null) {
            $stmt_update_asset = $conn->prepare("UPDATE assets 
                SET description = ?, model = ?, serial_no = ?, code = ?, brand = ?, unit = ?, value = ?, acquisition_date = ? 
                WHERE id = ?");
            $stmt_update_asset->bind_param(
                "ssssssdsi",
                $description,
                $model_no,
                $serial_no,
                $code,
                $brand,
                $unit,
                $acquisition_cost,
                $acquisition_date,
                $asset_id
            );
        } else {
            $stmt_update_asset = $conn->prepare("UPDATE assets 
                SET category = ?, description = ?, model = ?, serial_no = ?, code = ?, brand = ?, unit = ?, value = ?, acquisition_date = ? 
                WHERE id = ?");
            $stmt_update_asset->bind_param(
                "issssssdsi",
                $category_id,
                $description,
                $model_no,
                $serial_no,
                $code,
                $brand,
                $unit,
                $acquisition_cost,
                $acquisition_date,
                $asset_id
            );
        }
        if (!$stmt_update_asset->execute()) {
            $_SESSION['error_message'] = "Error updating asset details: " . $stmt_update_asset->error;
            $stmt_update_asset->close();
            header("Location: create_mr.php?asset_id=" . $asset_id_form);
            exit();
        }
        $stmt_update_asset->close();
    }

    // Ensure we have a source item mapping for this asset when available
    // Re-check mapping for the posted asset_id, in case GET and POST differ
    $mr_item_id = null;
    $doc_origin = null;
    if (!empty($asset_id_form)) {
        // Determine origin again for POST context
        $stmt_origin2 = $conn->prepare("SELECT ics_id, par_id FROM assets WHERE id = ?");
        $stmt_origin2->bind_param("i", $asset_id_form);
        $stmt_origin2->execute();
        $res_origin2 = $stmt_origin2->get_result();
        $row_origin2 = $res_origin2 ? $res_origin2->fetch_assoc() : null;
        $stmt_origin2->close();

        if ($row_origin2 && !empty($row_origin2['ics_id'])) {
            $doc_origin = 'ICS';
            $stmt_mrmap2 = $conn->prepare("SELECT item_id FROM ics_items WHERE asset_id = ? ORDER BY item_id ASC LIMIT 1");
            $stmt_mrmap2->bind_param("i", $asset_id_form);
            $stmt_mrmap2->execute();
            $res_mrmap2 = $stmt_mrmap2->get_result();
            if ($res_mrmap2 && ($rm2 = $res_mrmap2->fetch_assoc())) {
                $mr_item_id = (int)$rm2['item_id'];
            } else {
                // Optionally auto-create minimal ics_items mapping
                $stmt_asset = $conn->prepare("SELECT a.description, a.unit, a.value, a.property_no, a.ics_id, f.ics_no FROM assets a LEFT JOIN ics_form f ON f.id = a.ics_id WHERE a.id = ?");
                $stmt_asset->bind_param("i", $asset_id_form);
                $stmt_asset->execute();
                $res_asset = $stmt_asset->get_result();
                $asset_row = $res_asset ? $res_asset->fetch_assoc() : null;
                $stmt_asset->close();

                if ($asset_row && !empty($asset_row['ics_id'])) {
                    $ics_no_ins = $asset_row['ics_no'] ?? '';
                    $qty_ins = 1; // item-level
                    $unit_ins = $asset_row['unit'] ?? '';
                    $unit_cost_ins = (float)($asset_row['value'] ?? 0);
                    $total_cost_ins = $unit_cost_ins * $qty_ins;
                    $desc_ins = $asset_row['description'] ?? '';
                    $item_no_ins = $asset_row['property_no'] ?? '';
                    $est_life_ins = '';

                    $stmt_items_ins = $conn->prepare("INSERT INTO ics_items (ics_id, asset_id, ics_no, quantity, unit, unit_cost, total_cost, description, item_no, estimated_useful_life, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt_items_ins->bind_param(
                        "iisisddsss",
                        $asset_row['ics_id'],   // i
                        $asset_id_form,         // i
                        $ics_no_ins,            // s
                        $qty_ins,               // i
                        $unit_ins,              // s
                        $unit_cost_ins,         // d
                        $total_cost_ins,        // d
                        $desc_ins,              // s
                        $item_no_ins,           // s
                        $est_life_ins           // s
                    );
                    if ($stmt_items_ins->execute()) {
                        $mr_item_id = $conn->insert_id;
                    }
                    $stmt_items_ins->close();
                }
            }
            if (isset($stmt_mrmap2)) { $stmt_mrmap2->close(); }
        } elseif ($row_origin2 && !empty($row_origin2['par_id'])) {
            $doc_origin = 'PAR';
            // Do not derive item_id from par_items due to FK to ics_items; keep NULL
            $mr_item_id = null;
        }
    }

    // Insert or Update mr_details
    // For ICS-origin, mr_item_id is expected. For PAR-origin, allow item_id to be NULL if mapping is unavailable.
    if (!$mr_item_id && $doc_origin === 'ICS') {
        $_SESSION['error_message'] = "No ICS item mapping found for this asset. Cannot create MR due to foreign key constraint.";
        header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
        exit();
    }

    if ($existing_mr_check) {
        // UPDATE
        $stmt_upd = $conn->prepare("UPDATE mr_details SET 
            office_location = ?, description = ?, model_no = ?, serial_no = ?, serviceable = ?, unserviceable = ?, unit_quantity = ?, unit = ?, acquisition_date = ?, acquisition_cost = ?, person_accountable = ?, acquired_date = ?, counted_date = ?, inventory_tag = ?
            WHERE (item_id = ? OR (? IS NULL AND item_id IS NULL)) AND asset_id = ?");
        $stmt_upd->bind_param(
            "ssssiiisssssssiii",
            $office_location,
            $description,
            $model_no,
            $serial_no,
            $serviceable,
            $unserviceable,
            $unit_quantity,
            $unit,
            $acquisition_date,
            $acquisition_cost,
            $person_accountable_name,
            $acquired_date,
            $counted_date,
            $inventory_tag,
            $mr_item_id,
            $mr_item_id,
            $asset_id
        );
        if ($stmt_upd->execute()) {
            // Persist Property No. and Inventory Tag to the item-level asset record
            $stmt_ai = $conn->prepare("UPDATE assets SET property_no = ?, inventory_tag = ? WHERE id = ?");
            $stmt_ai->bind_param("ssi", $property_no, $inventory_tag_gen, $asset_id_form);
            if (!$stmt_ai->execute()) {
                $_SESSION['error_message'] = "Failed to update asset Property No./Inventory Tag: " . $stmt_ai->error;
            }
            $stmt_ai->close();
            $_SESSION['success_message'] = "MR Details successfully updated!";
            header("Location: create_mr.php?asset_id=" . $asset_id_form);
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating MR: " . $stmt_upd->error;
        }
        $stmt_upd->close();
    } else {
        // INSERT
        $stmt_insert = $conn->prepare("INSERT INTO mr_details 
            (item_id, asset_id, office_location, description, model_no, serial_no, serviceable, unserviceable, unit_quantity, unit, acquisition_date, acquisition_cost, person_accountable, acquired_date, counted_date, inventory_tag) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt_insert->bind_param(
            "iissssiiisssssss",
            $mr_item_id,
            $asset_id,
            $office_location,
            $description,
            $model_no,
            $serial_no,
            $serviceable,
            $unserviceable,
            $unit_quantity,
            $unit,
            $acquisition_date,
            $acquisition_cost,
            $person_accountable_name,
            $acquired_date,
            $counted_date,
            $inventory_tag
        );

        if ($stmt_insert->execute()) {
            // Persist Property No. and Inventory Tag to the item-level asset record
            $stmt_ai = $conn->prepare("UPDATE assets SET property_no = ?, inventory_tag = ? WHERE id = ?");
            $stmt_ai->bind_param("ssi", $property_no, $inventory_tag_gen, $asset_id_form);
            if (!$stmt_ai->execute()) {
                $_SESSION['error_message'] = "Failed to update asset Property No./Inventory Tag: " . $stmt_ai->error;
            }
            $stmt_ai->close();
            $_SESSION['success_message'] = "MR has been successfully created!";
            header("Location: create_mr.php?asset_id=" . $asset_id_form);
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
}


// --- End of PHP code for form submission and insertion ---

// Prefill using item-level assets relationship
if ($asset_id) {
    // Fetch office name from assets.office_id
    $stmt_offices = $conn->prepare("SELECT o.office_name FROM assets a LEFT JOIN offices o ON a.office_id = o.id WHERE a.id = ?");
    $stmt_offices->bind_param("i", $asset_id);
    $stmt_offices->execute();
    $result_offices = $stmt_offices->get_result();
    if ($result_offices && $od = $result_offices->fetch_assoc()) {
        $office_name = $od['office_name'] ?? '';
    }
    $stmt_offices->close();

    // Fetch detailed asset record
    $stmt_assets = $conn->prepare("SELECT id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, serial_no, code, property_no, model, brand FROM assets WHERE id = ?");
    $stmt_assets->bind_param("i", $asset_id);
    $stmt_assets->execute();
    $result_assets = $stmt_assets->get_result();
    if ($result_assets && $result_assets->num_rows > 0) {
        $asset_details = $result_assets->fetch_assoc();
    }
    $stmt_assets->close();

    // Ensure auto_property_no has a value from assets if not already set
    if (!isset($auto_property_no)) {
        $auto_property_no = '';
        $stmt_ai = $conn->prepare("SELECT inventory_tag FROM assets WHERE id = ?");
        $stmt_ai->bind_param("i", $asset_id);
        $stmt_ai->execute();
        $res_ai = $stmt_ai->get_result();
        if ($res_ai && $row_ai = $res_ai->fetch_assoc()) {
            $auto_property_no = $row_ai['inventory_tag'] ?? '';
        }
        $stmt_ai->close();
    }
}

// Fetch the employee's name based on the employee_id
$person_accountable_name = '';
if (isset($asset_details['employee_id'])) {
    $employee_id = $asset_details['employee_id'];
    $stmt_employee = $conn->prepare("SELECT name FROM employees WHERE employee_id = ?");
    $stmt_employee->bind_param("i", $employee_id);
    $stmt_employee->execute();
    $result_employee = $stmt_employee->get_result();

    if ($result_employee->num_rows > 0) {
        $employee_data = $result_employee->fetch_assoc();
        $person_accountable_name = $employee_data['name'];  // Get the name of the person accountable
    }

    $stmt_employee->close();
}


// Fetch employees for datalist
$employees = [];
$sql_employees = "SELECT employee_id, employee_no, name FROM employees";
$result_employees = $conn->query($sql_employees);

if ($result_employees && $result_employees->num_rows > 0) {
    while ($row = $result_employees->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Ensure employee assignment persisted (if provided)
if (!empty($asset_id) && !empty($employee_id)) {
    $stmt_assets = $conn->prepare("UPDATE assets SET employee_id = ? WHERE id = ?");
    $stmt_assets->bind_param("ii", $employee_id, $asset_id);
    $stmt_assets->execute();
    $stmt_assets->close();
}

// Compute system-generated Property No for the form
$baseProp = isset($asset_details['property_no']) ? trim((string)$asset_details['property_no']) : '';
if ($baseProp !== '') {
    $generated_property_no = $baseProp;
} elseif (!empty($auto_property_no)) {
    $generated_property_no = $auto_property_no;
} else {
    $yr = date('Y');
    $generated_property_no = 'MR-' . $yr . '-' . str_pad((string)($asset_id ?? 0), 5, '0', STR_PAD_LEFT);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Property Tag</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <!-- Form for MR Asset -->
        <div class="container mt-4">
            <?php
            // Display success or error messages
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
                    . htmlspecialchars($_SESSION['success_message']) .
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
                    '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
                    . htmlspecialchars($_SESSION['error_message']) .
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
                    '</div>';
                unset($_SESSION['error_message']);
            }

            if ($existing_mr_check) {
                echo '<div class="alert alert-info">An MR record already exists for this item. You can review and edit the details below.</div>';
            }
            ?>

            <!-- Card wrapper -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Create Property Tag</h5>
                    <!-- Top-right button (optional) -->
                    <a href="saved_mr.php" class="btn btn-info btn-sm">
                        <i class="bi bi-folder-check"></i> View Saved Property Tags
                    </a>
                </div>
                <!-- Header: Logo, QR, and GOV LABEL -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <!-- Municipal Logo -->
                    <img id="municipalLogoImg" src="<?= $logo_path ?>" alt="Municipal Logo" style="height: 70px;">

                    <!-- Government Label -->
                    <div class="text-center flex-grow-1">
                        <h6 class="m-0 text-uppercase fw-bold">Government Property</h6>
                    </div>

                    <!-- Inventory Tag Display -->
                    <div class="text-center">
                        <p class="fw-bold">Inventory Tag: <?= $inventory_tag ?></p> <!-- Display the inventory tag here -->
                    </div>

                    <!-- QR Code -->
                    <img id="viewQrCode" src="../img/<?= isset($asset_details['qr_code']) ? $asset_details['qr_code'] : '' ?>" alt="QR Code" style="height: 70px;">
                </div>
                <div class="card-body">
                    <form method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="asset_id" value="<?= htmlspecialchars($asset_id) ?>">

                        <!-- Office Location -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-3">
                                <label for="office_location" class="form-label">Office Location</label>
                                <input type="text" class="form-control" name="office_location"
                                    value="<?= isset($office_name) ? htmlspecialchars($office_name) : '' ?>" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" name="description"
                                    value="<?= isset($asset_details['description']) ? htmlspecialchars($asset_details['description']) : '' ?>" required>
                            </div>
                        </div>

                        <!-- Model No and Serial No -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="model_no" class="form-label">Model No</label>
                                <input type="text" class="form-control" name="model_no"
                                    value="<?= isset($asset_details['model']) ? htmlspecialchars($asset_details['model']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="serial_no" class="form-label">Serial No</label>
                                <input type="text" class="form-control" name="serial_no"
                                    value="<?= isset($asset_details['serial_no']) ? htmlspecialchars($asset_details['serial_no']) : '' ?>">
                            </div>
                        </div>

                        <!-- Code and Property No -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" class="form-control" name="code"
                                       value="<?= isset($asset_details['code']) ? htmlspecialchars($asset_details['code']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="property_no" class="form-label">Property No</label>
                                <input type="text" class="form-control" name="property_no" readonly
                                       value="<?= htmlspecialchars($generated_property_no) ?>">
                            </div>
                        </div>

                        <!-- Brand and Category -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" name="brand"
                                       value="<?= isset($asset_details['brand']) ? htmlspecialchars($asset_details['brand']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?= (int)$cat['id'] ?>" <?= (isset($asset_details['category']) && (int)$asset_details['category'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Upload Asset Photo -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="asset_image" class="form-label">Upload Asset Photo</label>
                                <input type="file" class="form-control" name="asset_image" id="asset_image" accept="image/*">
                                <div class="form-text">Accepted: JPG, JPEG, PNG, GIF. Max 5MB.</div>
                            </div>
                            <div class="col-md-6 text-center">
                                <label class="form-label d-block">Preview</label>
                                <img id="asset_image_preview" src="<?= !empty($asset_details['image']) ? '../img/assets/' . htmlspecialchars($asset_details['image']) : '' ?>" alt="Asset Image Preview" class="img-fluid border rounded" style="max-height: 180px; object-fit: contain;">
                            </div>
                        </div>

                        <!-- Serviceable and Unserviceable -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="serviceable" value="1" checked>
                                    <label class="form-check-label">Serviceable</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="unserviceable" value="1"
                                        <?= (isset($asset_data['quantity']) && $asset_data['quantity'] == 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Unserviceable</label>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity, Unit, Acquisition Date & Cost -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="unit_quantity" class="form-label">Unit Quantity</label>
                                <div class="d-flex">
                                    <input type="number" class="form-control" name="unit_quantity"
                                        value="1" min="1" required>
                                    <select name="unit" class="form-select" required>
                                        <?php
                                        // Populate units from unit table if available
                                        $unit_rows = [];
                                        $res_units = $conn->query("SELECT unit_name FROM unit");
                                        if ($res_units && $res_units->num_rows > 0) {
                                            while ($ur = $res_units->fetch_assoc()) { $unit_rows[] = $ur['unit_name']; }
                                        } else {
                                            $unit_rows = ['kg', 'pcs', 'liter'];
                                        }
                                        foreach ($unit_rows as $u) {
                                            $sel = (isset($asset_details['unit']) && $asset_details['unit'] == $u) ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($u) . '" ' . $sel . '>' . htmlspecialchars($u) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="acquisition_date" class="form-label">Acquisition Date</label>
                                <input type="date" class="form-control" name="acquisition_date"
                                    value="<?= isset($asset_details['acquisition_date']) ? htmlspecialchars($asset_details['acquisition_date']) : '' ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label for="acquisition_cost" class="form-label">Acquisition Cost</label>
                                <input type="number" class="form-control" name="acquisition_cost" step="0.01"
                                    value="<?= isset($asset_details['value']) ? htmlspecialchars($asset_details['value']) : '' ?>" required>
                            </div>
                        </div>

                        <!-- Person Accountable -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="person_accountable" class="form-label">Person Accountable <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="person_accountable_name" id="person_accountable" required
                                    list="employeeList" placeholder="Type to search employee" autocomplete="off"
                                    value="<?= htmlspecialchars($person_accountable_name) ?>">
                                <input type="hidden" name="employee_id" id="employee_id"
                                    value="<?= isset($asset_details['employee_id']) ? htmlspecialchars($asset_details['employee_id']) : '' ?>">
                                <datalist id="employeeList">
                                    <?php foreach ($employees as $emp): ?>
                                        <option data-id="<?= $emp['employee_id'] ?>" value="<?= htmlspecialchars($emp['name']) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>

                        <!-- Acquired Date & Counted Date -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="acquired_date" class="form-label">Acquired Date</label>
                                <input type="date" class="form-control" name="acquired_date"
                                    value="<?= isset($asset_details['last_updated']) ? htmlspecialchars($asset_details['last_updated']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="counted_date" class="form-label">Counted Date</label>
                                <input type="date" class="form-control" name="counted_date">
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">
                            <?= $existing_mr_check ? 'Edit' : 'Submit' ?>
                        </button>

                        <?php if ($existing_mr_check): ?>
                            <a href="print_mr.php?asset_id=<?= htmlspecialchars($asset_id) ?>" class="btn btn-info ms-2">Print</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        document.getElementById('person_accountable').addEventListener('input', function() {
            const inputVal = this.value;
            const options = document.querySelectorAll('#employeeList option');
            let selectedId = '';

            options.forEach(option => {
                if (option.value === inputVal) {
                    selectedId = option.getAttribute('data-id');
                }
            });

            document.getElementById('employee_id').value = selectedId;
        });

        // Preview uploaded asset image
        const imageInput = document.getElementById('asset_image');
        const imagePreview = document.getElementById('asset_image_preview');
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }
    </script>

</body>

</html>