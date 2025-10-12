<?php
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';
require_once '../includes/email_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$ics_id = isset($_GET['ics_id']) ? intval($_GET['ics_id']) : null;
$ics_form_id = $_GET['form_id'] ?? '';


// Helper: ensure email_notifications table exists
function ensureEmailNotificationsTable(mysqli $conn) {
    try {
        $conn->query("CREATE TABLE IF NOT EXISTS email_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(50) NOT NULL,
            recipient_email VARCHAR(255) NULL,
            recipient_name VARCHAR(255) NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            status VARCHAR(50) NOT NULL,
            error_message TEXT NULL,
            related_asset_id INT NULL,
            related_mr_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) { /* ignore */ }
}

// Helper: save email log
function saveEmailNotification(mysqli $conn, array $data) {
    $stmt = $conn->prepare("INSERT INTO email_notifications
        (type, recipient_email, recipient_name, subject, body, status, error_message, related_asset_id, related_mr_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        'sssssssii',
        $data['type'],
        $data['recipient_email'],
        $data['recipient_name'],
        $data['subject'],
        $data['body'],
        $data['status'],
        $data['error_message'],
        $data['related_asset_id'],
        $data['related_mr_id']
    );
    $stmt->execute();
    $stmt->close();
}

// Helper: send MR email and log regardless of delivery success
function sendMrEmailAndLog(mysqli $conn, $employeeId, $personName, $assetId, $mrId, $officeLocation, $inventoryTag, $description, $propertyNo, $serialNo) {
    ensureEmailNotificationsTable($conn);

    // Fetch employee email
    $recipientEmail = null;
    if (!empty($employeeId)) {
        if ($st = $conn->prepare("SELECT email FROM employees WHERE employee_id = ? LIMIT 1")) {
            $st->bind_param('i', $employeeId);
            $st->execute();
            $res = $st->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $recipientEmail = $row['email'] ?? null;
            }
            $st->close();
        }
    }

    // Build subject/body
    $subject = 'New Material Receipt (MR) Assignment Notification';
    $body = "Hello " . htmlspecialchars((string)$personName) . ",<br><br>" .
            "You have been set as the Person Accountable for an item in the PILAR Asset Inventory system.<br>" .
            "<ul>" .
              "<li><strong>Office:</strong> " . htmlspecialchars((string)$officeLocation) . "</li>" .
              "<li><strong>Inventory Tag:</strong> " . htmlspecialchars((string)$inventoryTag) . "</li>" .
              "<li><strong>Description:</strong> " . htmlspecialchars((string)$description) . "</li>" .
              "<li><strong>Property No.:</strong> " . htmlspecialchars((string)$propertyNo) . "</li>" .
              "<li><strong>Serial No.:</strong> " . htmlspecialchars((string)$serialNo) . "</li>" .
            "</ul>" .
            "If this was not expected, please contact your system administrator.";

    // Default log data
    $log = [
        'type' => 'MR_CREATED',
        'recipient_email' => $recipientEmail,
        'recipient_name' => $personName,
        'subject' => $subject,
        'body' => $body,
        'status' => 'queued',
        'error_message' => null,
        'related_asset_id' => !empty($assetId) ? (int)$assetId : null,
        'related_mr_id' => !empty($mrId) ? (int)$mrId : null,
    ];

    // Attempt to send if email present
    if (!empty($recipientEmail)) {
        try {
            $mail = configurePHPMailer();
            $mail->addAddress($recipientEmail, (string)$personName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
            $mail->send();
            $log['status'] = 'sent';
        } catch (Throwable $e) {
            $log['status'] = 'failed';
            $log['error_message'] = $e->getMessage();
        }
    } else {
        // No email on file
        $log['status'] = 'no_email';
    }

    saveEmailNotification($conn, $log);
}

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

// Fetch categories for dropdown (include category_code) - only active
$categories = [];
$res_cats = $conn->query("SELECT id, category_name, category_code FROM categories WHERE status = 1 ORDER BY category_name");
if ($res_cats && $res_cats->num_rows > 0) {
    while ($cr = $res_cats->fetch_assoc()) {
        $categories[] = $cr;
    }
}

// Dedicated query: fetch ALL categories independently for the Category dropdown (include category_code) - only active
$all_categories = [];
$res_all_categories = $conn->query("SELECT id, category_name, category_code FROM categories WHERE status = 1 ORDER BY category_name");
if ($res_all_categories && $res_all_categories->num_rows > 0) {
    while ($rowc = $res_all_categories->fetch_assoc()) {
        $all_categories[] = $rowc;
    }
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
        // Populate office name for display/replacements
        if (!empty($row['office_id'])) {
            if ($stOff = $conn->prepare("SELECT office_name FROM offices WHERE id = ? LIMIT 1")) {
                $oid = (int)$row['office_id'];
                $stOff->bind_param('i', $oid);
                if ($stOff->execute()) {
                    $rsOff = $stOff->get_result();
                    if ($rsOff && ($of = $rsOff->fetch_assoc())) {
                        $office_name = $of['office_name'] ?? '';
                    }
                }
                $stOff->close();
            }
        }
        // Fallback: if office_name is still empty, derive from ICS/PAR form linkage
        if (trim((string)$office_name) === '') {
            if ($stIds = $conn->prepare("SELECT ics_id, par_id FROM assets WHERE id = ?")) {
                $stIds->bind_param('i', $asset_id);
                if ($stIds->execute()) {
                    $rsIds = $stIds->get_result();
                    if ($rsIds && ($ri = $rsIds->fetch_assoc())) {
                        $ics_id_fk = (int)($ri['ics_id'] ?? 0);
                        $par_id_fk = (int)($ri['par_id'] ?? 0);
                        if ($ics_id_fk > 0) {
                            if ($stI = $conn->prepare("SELECT o.office_name FROM ics_form f LEFT JOIN offices o ON f.office_id = o.id WHERE f.id = ? LIMIT 1")) {
                                $stI->bind_param('i', $ics_id_fk);
                                if ($stI->execute()) {
                                    $rsI = $stI->get_result();
                                    if ($rsI && ($rI = $rsI->fetch_assoc())) {
                                        $office_name = $rI['office_name'] ?? $office_name;
                                    }
                                }
                                $stI->close();
                            }
                        }
                        if (trim((string)$office_name) === '' && $par_id_fk > 0) {
                            if ($stP = $conn->prepare("SELECT o.office_name FROM par_form p LEFT JOIN offices o ON p.office_id = o.id WHERE p.id = ? LIMIT 1")) {
                                $stP->bind_param('i', $par_id_fk);
                                if ($stP->execute()) {
                                    $rsP = $stP->get_result();
                                    if ($rsP && ($rP = $rsP->fetch_assoc())) {
                                        $office_name = $rP['office_name'] ?? $office_name;
                                    }
                                }
                                $stP->close();
                            }
                        }
                    }
                }
                $stIds->close();
            }
        }

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

// Generate automatic inventory tag using the new tag format system
require_once '../includes/tag_format_helper.php';

// Check if asset already has an inventory_tag
$existing_inventory_tag = '';
if ($asset_id) {
    $stmt_check_tag = $conn->prepare("SELECT inventory_tag FROM assets WHERE id = ?");
    $stmt_check_tag->bind_param("i", $asset_id);
    $stmt_check_tag->execute();
    $result_check_tag = $stmt_check_tag->get_result();
    if ($result_check_tag && $row_check_tag = $result_check_tag->fetch_assoc()) {
        $existing_inventory_tag = $row_check_tag['inventory_tag'] ?? '';
    }
    $stmt_check_tag->close();
}

// Use existing inventory_tag if available, otherwise generate new one
if (!empty($existing_inventory_tag)) {
    $inventory_tag = $existing_inventory_tag;
} else {
    $inventory_tag = generateTag('inventory_tag');
}

// Check if asset already has saved asset code and serial number (like inventory_tag behavior)
$existing_asset_code = '';
$existing_serial_no = '';
if ($asset_id) {
    $stmt_check_codes = $conn->prepare("SELECT code, serial_no FROM assets WHERE id = ?");
    $stmt_check_codes->bind_param("i", $asset_id);
    $stmt_check_codes->execute();
    $result_check_codes = $stmt_check_codes->get_result();
    if ($result_check_codes && $row_check_codes = $result_check_codes->fetch_assoc()) {
        $existing_asset_code = $row_check_codes['code'] ?? '';
        $existing_serial_no = $row_check_codes['serial_no'] ?? '';
    }
    $stmt_check_codes->close();
}

// Generate asset code for display (like inventory_tag - only if not already saved)
$display_asset_code = '';
if (!empty($existing_asset_code)) {
    $display_asset_code = $existing_asset_code;
} else {
    // Generate new asset code only if none exists
    if (!empty($categories) && count($categories) > 0) {
        // Use first category as default for display
        $default_category = $categories[0];
        $category_code = $default_category['category_code'] ?? '';
        if (!empty($category_code)) {
            $tagHelper = new TagFormatHelper($conn);
            $replacements = ['CODE' => $category_code];
            $display_asset_code = $tagHelper->generateNextTag('asset_code', $replacements);
        }
    }
}

// Generate serial number for display (like inventory_tag - only if not already saved)
if (!empty($existing_serial_no)) {
    $display_serial_no = $existing_serial_no;
} else {
    // Generate new serial number only if none exists
    $tagHelper = new TagFormatHelper($conn);
    $display_serial_no = $tagHelper->generateNextTag('serial_no');
}

// Format patterns are now handled by the tag format system
$property_no_format = '';

// Fetch asset code format from tag_formats table
$code_format = '';
$code_stmt = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'asset_code' AND is_active = 1 LIMIT 1");
$code_stmt->execute();
$code_result = $code_stmt->get_result();
if ($code_result->num_rows > 0) {
    $code_row = $code_result->fetch_assoc();
    $code_format = $code_row['format_template'];
}
$code_stmt->close();

// Fetch serial number format from tag_formats table
$serial_format = '';
$serial_stmt = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'serial_no' AND is_active = 1 LIMIT 1");
$serial_stmt->execute();
$serial_result = $serial_stmt->get_result();
if ($serial_result->num_rows > 0) {
    $serial_row = $serial_result->fetch_assoc();
    $serial_format = $serial_row['format_template'];
}
$serial_stmt->close();

// Ensure database columns for End User exist (assets.end_user, mr_details.end_user)
try {
    if ($chk = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'assets' AND COLUMN_NAME = 'end_user'")) {
        $chk->execute();
        $chk->bind_result($cnt);
        $chk->fetch();
        $chk->close();
        if ((int)$cnt === 0) {
            $conn->query("ALTER TABLE assets ADD COLUMN end_user VARCHAR(255) NULL AFTER employee_id");
        }
    }
    if ($chk2 = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mr_details' AND COLUMN_NAME = 'end_user'")) {
        $chk2->execute();
        $chk2->bind_result($cnt2);
        $chk2->fetch();
        $chk2->close();
        if ((int)$cnt2 === 0) {
            $conn->query("ALTER TABLE mr_details ADD COLUMN end_user VARCHAR(255) NULL AFTER person_accountable");
        }
    }
} catch (Throwable $e) { /* non-fatal */
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

    $asset_status = 'serviceable'; // Always serviceable since unserviceable option is removed
    $serviceable = 1; // Always set to serviceable
    $unserviceable = 0; // Always set to 0 since unserviceable option is removed
    $unit_quantity = $_POST['unit_quantity'];
    $unit = $_POST['unit'];
    $acquisition_date = $_POST['acquisition_date'];
    $acquisition_cost = $_POST['acquisition_cost'];
    $supplier = trim($_POST['supplier'] ?? '');

    // Use existing tag if present; otherwise generate with PROPERTY_NO replacement support
    if (!empty($existing_inventory_tag)) {
        $inventory_tag_gen = $existing_inventory_tag;
    } else {
        // Prefer a format that can embed the Property No if the format template uses {PROPERTY_NO}
        $inventory_tag_gen = generateTag('inventory_tag', ['PROPERTY_NO' => (string)$property_no]);
        // Fallback to precomputed value if generation fails
        if (empty($inventory_tag_gen)) {
            $inventory_tag_gen = $inventory_tag;
        }
    }

    $person_accountable_name = $_POST['person_accountable_name'];
    $employee_id = $_POST['employee_id'];
    $end_user = trim($_POST['end_user'] ?? '');
    $acquired_date = $_POST['acquired_date'];
    $counted_date = $_POST['counted_date'];

    // Generate asset code using tag format system if not provided or empty
    if (empty($code) && $category_id) {
        // Get category code for asset code generation
        $cat_stmt = $conn->prepare("SELECT category_code FROM categories WHERE id = ?");
        $cat_stmt->bind_param("i", $category_id);
        $cat_stmt->execute();
        $cat_result = $cat_stmt->get_result();
        if ($cat_result->num_rows > 0) {
            $cat_row = $cat_result->fetch_assoc();
            $category_code = $cat_row['category_code'];
            
            if (!empty($category_code)) {
                // Generate asset code using tag format system with category code replacement
                $tagHelper = new TagFormatHelper($conn);
                $replacements = ['CODE' => $category_code];
                $generated_code = $tagHelper->generateNextTag('asset_code', $replacements);
                
                if ($generated_code) {
                    $code = $generated_code;
                } else {
                    // Fallback to simple format if tag generation fails
                    $year = date('Y');
                    $code = $year . '-' . $category_code . '-0001';
                }
            }
        }
        $cat_stmt->close();
    }

    // Generate serial number using tag format system if not provided or empty
    if (empty($serial_no)) {
        $tagHelper = new TagFormatHelper($conn);
        $generated_serial = $tagHelper->generateNextTag('serial_no');
        if ($generated_serial) {
            $serial_no = $generated_serial;
        } else {
            // Fallback to simple format if tag generation fails
            $year = date('Y');
            $serial_no = 'SN-' . $year . '-000001';
        }
    }

    // Server-side validation for required fields
    if ($category_id === null || trim((string)$person_accountable_name) === '') {
        $_SESSION['error_message'] = 'Please select a Category and specify the Person Accountable.';
        header("Location: create_mr.php?asset_id=" . urlencode((string)$asset_id_form));
        exit();
    }

    // Required field validation (server-side)
    if (trim((string)$serial_no) === '' || trim((string)$code) === '' || trim((string)$property_no) === '') {
        $_SESSION['error_message'] = 'Serial number, Asset code, and Property number are required.';
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


    // Property tags are stored on the item-level assets table now

    // No auto-generation of property_no; keep whatever user posted

    // No backend auto-generation of code; UI will propose a pattern which user can edit

    // Capture previous assignment before we mutate the asset
    $prev_employee_id = null;
    $prev_office_id = null;
    if (!empty($asset_id_form)) {
        if ($__stPrev = $conn->prepare("SELECT employee_id, office_id FROM assets WHERE id = ?")) {
            $__stPrev->bind_param("i", $asset_id_form);
            $__stPrev->execute();
            $__rsPrev = $__stPrev->get_result();
            if ($__rsPrev && ($__rowPrev = $__rsPrev->fetch_assoc())) {
                $prev_employee_id = $__rowPrev['employee_id'] ?? null;
                $prev_office_id = $__rowPrev['office_id'] ?? null;
            }
            $__stPrev->close();
        }
    }

    // --- NEW: Update other asset details to complete the asset record ---
    if ($asset_id_form) {
        if ($category_id === null) {
            $stmt_update_asset = $conn->prepare("UPDATE assets 
                SET description = ?, model = ?, serial_no = ?, code = ?, brand = ?, unit = ?, value = ?, acquisition_date = ?, end_user = ?, employee_id = ?, status = 'serviceable' 
                WHERE id = ?");
            $stmt_update_asset->bind_param(
                "ssssssdssii",
                $description,
                $model_no,
                $serial_no,
                $code,
                $brand,
                $unit,
                $acquisition_cost,
                $acquisition_date,
                $end_user,
                $employee_id,
                $asset_id_form
            );
        } else {
            $stmt_update_asset = $conn->prepare("UPDATE assets 
                SET category = ?, description = ?, model = ?, serial_no = ?, code = ?, brand = ?, unit = ?, value = ?, acquisition_date = ?, end_user = ?, employee_id = ?, status = 'serviceable' 
                WHERE id = ?");
            $stmt_update_asset->bind_param(
                "issssssdssii",
                $category_id,
                $description,
                $model_no,
                $serial_no,
                $code,
                $brand,
                $unit,
                $acquisition_cost,
                $acquisition_date,
                $end_user,
                $employee_id,
                $asset_id_form
            );
        }
        if (!$stmt_update_asset->execute()) {
            $_SESSION['error_message'] = "Error updating asset details: " . $stmt_update_asset->error;
            $stmt_update_asset->close();
            header("Location: create_mr.php?asset_id=" . $asset_id_form);
            exit();
        }
        $stmt_update_asset->close();
        // If supplier field exists, update it separately
        if ($supplier !== '') {
            try {
                $chk = $conn->query("SHOW COLUMNS FROM assets LIKE 'supplier'");
                if ($chk && $chk->num_rows > 0) {
                    if ($stSup = $conn->prepare("UPDATE assets SET supplier = ? WHERE id = ?")) {
                        $stSup->bind_param('si', $supplier, $asset_id_form);
                        $stSup->execute();
                        $stSup->close();
                    }
                }
                if ($chk) { $chk->close(); }
            } catch (Throwable $e) { /* ignore */ }
        }
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
            if (isset($stmt_mrmap2)) {
                $stmt_mrmap2->close();
            }
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
            office_location = ?, description = ?, model_no = ?, serial_no = ?, serviceable = ?, unserviceable = ?, unit_quantity = ?, unit = ?, acquisition_date = ?, acquisition_cost = ?, person_accountable = ?, end_user = ?, acquired_date = ?, counted_date = ?, inventory_tag = ?
            WHERE (item_id = ? OR (? IS NULL AND item_id IS NULL)) AND asset_id = ?");
        $stmt_upd->bind_param(
            "ssssiiissssssssiii",
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
            $end_user,
            $acquired_date,
            $counted_date,
            $inventory_tag,
            $mr_item_id,
            $mr_item_id,
            $asset_id_form
        );
        if ($stmt_upd->execute()) {
            // Persist Property No., Inventory Tag, and Employee ID to the item-level asset record
            $stmt_ai = $conn->prepare("UPDATE assets SET property_no = ?, inventory_tag = ?, employee_id = ? WHERE id = ?");
            $stmt_ai->bind_param("ssii", $property_no, $inventory_tag_gen, $employee_id, $asset_id_form);
            if (!$stmt_ai->execute()) {
                $_SESSION['error_message'] = "Failed to update asset details: " . $stmt_ai->error;
            }
            $stmt_ai->close();
            // Send notification email and log (UPDATE path)
            $mrIdForLog = null;
            if ($stFind = $conn->prepare("SELECT mr_id FROM mr_details WHERE (item_id = ? OR (? IS NULL AND item_id IS NULL)) AND asset_id = ? ORDER BY mr_id DESC LIMIT 1")) {
                $stFind->bind_param('iii', $mr_item_id, $mr_item_id, $asset_id_form);
                $stFind->execute();
                $rsFind = $stFind->get_result();
                if ($rsFind && ($rowF = $rsFind->fetch_assoc())) { $mrIdForLog = (int)$rowF['mr_id']; }
                $stFind->close();
            }
            sendMrEmailAndLog($conn, $employee_id, $person_accountable_name, $asset_id_form, $mrIdForLog, $office_location, $inventory_tag_gen, $description, $property_no, $serial_no);
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
            (item_id, asset_id, office_location, description, model_no, serial_no, serviceable, unserviceable, unit_quantity, unit, acquisition_date, acquisition_cost, person_accountable, end_user, acquired_date, counted_date, inventory_tag) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt_insert->bind_param(
            "iissssiiissssssss",
            $mr_item_id,
            $asset_id_form,
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
            $end_user,
            $acquired_date,
            $counted_date,
            $inventory_tag
        );

        if ($stmt_insert->execute()) {
            // Persist Property No., Inventory Tag, and Employee ID to the item-level asset record
            $stmt_ai = $conn->prepare("UPDATE assets SET property_no = ?, inventory_tag = ?, employee_id = ? WHERE id = ?");
            $stmt_ai->bind_param("ssii", $property_no, $inventory_tag_gen, $employee_id, $asset_id_form);
            if (!$stmt_ai->execute()) {
                $_SESSION['error_message'] = "Failed to update asset details: " . $stmt_ai->error;
            }
            $stmt_ai->close();
            // Lifecycle: ASSIGNED (create)
            if (function_exists('logLifecycleEvent') && !empty($asset_id_form)) {
                $note = sprintf('MR create; PA: %s; InvTag: %s', (string)$person_accountable_name, (string)$inventory_tag_gen);
                logLifecycleEvent((int)$asset_id_form, 'ASSIGNED', 'mr_details', null, $prev_employee_id ? (int)$prev_employee_id : null, $employee_id ? (int)$employee_id : null, $prev_office_id ? (int)$prev_office_id : null, null, $note);
            }
            // Send notification email and log (INSERT path)
            $insertedMrId = $conn->insert_id;
            sendMrEmailAndLog($conn, $employee_id, $person_accountable_name, $asset_id_form, $insertedMrId, $office_location, $inventory_tag_gen, $description, $property_no, $serial_no);
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
    $stmt_assets = $conn->prepare("SELECT id, asset_name, category, description, quantity, unit, status, acquisition_date, office_id, employee_id, end_user, red_tagged, last_updated, value, qr_code, type, image, additional_images, serial_no, code, property_no, model, brand, supplier FROM assets WHERE id = ?");
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
$sql_employees = "SELECT employee_id, employee_no, name FROM employees WHERE status = 'permanent' ORDER BY name ASC";
$result_employees = $conn->query($sql_employees);

if ($result_employees && $result_employees->num_rows > 0) {
    while ($row = $result_employees->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Fetch offices for dropdown
$offices = [];
$sql_offices = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
$result_offices_dropdown = $conn->query($sql_offices);

if ($result_offices_dropdown && $result_offices_dropdown->num_rows > 0) {
    while ($row = $result_offices_dropdown->fetch_assoc()) {
        $offices[] = $row;
    }
}

// Ensure employee assignment persisted (if provided)
if (!empty($asset_id) && !empty($employee_id)) {
    $stmt_assets = $conn->prepare("UPDATE assets SET employee_id = ? WHERE id = ?");
    $stmt_assets->bind_param("ii", $employee_id, $asset_id);
    $stmt_assets->execute();
    $stmt_assets->close();
}

// Since unserviceable option is removed, always default to serviceable
$mr_serviceable = 1; // Always serviceable
$mr_unserviceable = 0; // Always 0 since unserviceable option is removed

// Determine default property number for display: use existing, else the configured Property No format
$baseProp = isset($asset_details['property_no']) ? trim((string)$asset_details['property_no']) : '';
if ($baseProp !== '') {
    $generated_property_no = $baseProp;
} else {
    $generated_property_no = $property_no_format; // fetched from tag_formats
}

// Helper to replace {OFFICE}/OFFICE and office acronym with full office name for display
function mr_replace_office_tokens($text, $officeFullName) {
    $out = (string)$text;
    $office = trim((string)$officeFullName);
    if ($office !== '') {
        $out = preg_replace('/\{OFFICE\}|OFFICE/u', $office, $out);
        // Derive acronym from full office name
        $upper = mb_strtoupper($office, 'UTF-8');
        $parts = preg_split('/\s+/', $upper);
        $acronym = '';
        foreach ($parts as $p) {
            $first = preg_replace('/[^A-Z0-9]/u', '', mb_substr($p, 0, 1, 'UTF-8'));
            $acronym .= $first;
        }
        if ($acronym !== '') {
            $re = '/\b' . preg_quote($acronym, '/') . '\b/u';
            $out = preg_replace($re, $office, $out);
        }
    }
    return $out;
}

// Compute display/placeholder with full office name applied
$__officeDisplay = trim((string)$office_name);
$display_property_no = mr_replace_office_tokens($generated_property_no, $__officeDisplay);
$placeholder_property_no = mr_replace_office_tokens(($property_no_format ?: 'YYYY-CODE-0001'), $__officeDisplay);

// Auto-generate serial number using tag format (similar to property number)
$baseSerial = isset($asset_details['serial_no']) ? trim((string)$asset_details['serial_no']) : '';
if ($baseSerial !== '') {
    $generated_serial_no = $baseSerial;
} else {
    // Generate serial number from format template
    $generated_serial_no = $serial_format ?: 'SN-' . date('Y') . '-000001';
    
    // Replace placeholders in serial format
    if (!empty($serial_format)) {
        $now = new DateTime();
        $year = $now->format('Y');
        $month = $now->format('m');
        $day = $now->format('d');
        
        $generated_serial_no = str_replace(
            ['{YYYY}', '{YY}', '{MM}', '{DD}', '{YYYYMM}', '{YYYYMMDD}'],
            [$year, substr($year, -2), $month, $day, $year.$month, $year.$month.$day],
            $serial_format
        );
        
        // Replace digit placeholders with sequential numbers
        $generated_serial_no = preg_replace_callback('/\{(#+)\}/', function($matches) {
            $digitCount = strlen($matches[1]);
            return str_pad('1', $digitCount, '0', STR_PAD_LEFT);
        }, $generated_serial_no);
    }
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
        <div class="container mt-2">
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

            <!-- Government Property Header -->
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-body bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img id="municipalLogoImg" src="<?= $logo_path ?>" alt="Municipal Logo" 
                                 class="img-fluid rounded shadow-sm" style="max-height: 60px;">
                        </div>
                        <div class="col-md-6 text-center">
                            <h6 class="mb-1 text-uppercase fw-bold text-primary">Government Property</h6>
                            <p class="mb-0 text-muted">Official Asset Documentation</p>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="bg-primary bg-opacity-10 rounded p-2">
                                <small class="text-primary fw-bold d-block">Inventory Tag</small>
                                <span class="badge bg-primary small"><?= $inventory_tag ?></span>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <?php if (isset($asset_details['qr_code']) && !empty($asset_details['qr_code'])): ?>
                                <img id="viewQrCode" src="../img/<?= $asset_details['qr_code'] ?>" alt="QR Code" 
                                     class="img-fluid rounded shadow-sm" style="max-height: 60px;">
                            <?php else: ?>
                                <div class="bg-secondary bg-opacity-10 rounded p-2">
                                    <i class="bi bi-qr-code fs-4 text-muted"></i>
                                    <p class="mb-0 small text-muted">QR Code</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <div class="row">
                        <div class="col-12">
                            <!-- Progress Steps -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="step-indicator active me-1">
                                        <span class="step-number">1</span>
                                    </div>
                                    <span class="text-primary fw-semibold">Asset Information</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="step-indicator me-1">
                                        <span class="step-number">2</span>
                                    </div>
                                    <span class="text-muted">Property Details</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="step-indicator me-1">
                                        <span class="step-number">3</span>
                                    </div>
                                    <span class="text-muted">Accountability</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="step-indicator">
                                        <span class="step-number">4</span>
                                    </div>
                                    <span class="text-muted">Review & Submit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <form method="post" action="" enctype="multipart/form-data" id="propertyTagForm">
                        <input type="hidden" name="asset_id" value="<?= htmlspecialchars($asset_id) ?>">

                        <!-- Step 1: Basic Asset Information -->
                        <div class="form-step active" id="step1">
                            <div class="step-header bg-primary bg-opacity-10 p-2 border-bottom">
                                <h5 class="mb-1 text-primary">
                                    <i class="bi bi-info-circle me-1"></i>Basic Asset Information
                                </h5>
                                <p class="mb-0 text-muted">Enter the fundamental details about this asset</p>
                            </div>
                            <div class="p-2">
                                <!-- Office Location -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="office_location" class="form-label fw-semibold">
                                            <i class="bi bi-building me-1 text-primary"></i>Office Location
                                            <span class="text-danger">*</span>
                                        </label>
                                        <?php $__officeDisplay = trim((string)$office_name); ?>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($__officeDisplay !== '' ? $__officeDisplay : 'Unassigned / Outside LGU') ?>" readonly>
                                        <input type="hidden" name="office_location" value="<?= htmlspecialchars($__officeDisplay) ?>">
                                        <div class="form-text">This MR uses the asset's current office. Office selection is managed on the source form (ICS/PAR/RIS).</div>
                                    </div>

                                    <div class="col-md-8">
                                        <label for="description" class="form-label fw-semibold">
                                            <i class="bi bi-card-text me-1 text-primary"></i>Asset Description
                                            <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" name="description" rows="2"
                                            required placeholder="Provide a detailed description of the asset"><?= isset($asset_details['description']) ? htmlspecialchars($asset_details['description']) : '' ?></textarea>
                                        <div class="form-text">Include make, model, specifications, and any distinguishing features</div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="row mb-3">
                                    
                                </div>

                                <!-- Category Selection -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label fw-semibold">
                                            <i class="bi bi-tags me-1 text-primary"></i>Asset Category
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="category_id" id="category_id" class="form-select" required>
                                            <option value="">Choose asset category...</option>
                                            <?php foreach ($all_categories as $cat): ?>
                                                <option value="<?= (int)$cat['id'] ?>" data-code="<?= htmlspecialchars($cat['category_code'] ?? '') ?>" 
                                                    <?= (isset($asset_details['category']) && (int)$asset_details['category'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['category_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Select the appropriate category for classification</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="brand" class="form-label fw-semibold">
                                            <i class="bi bi-award me-1 text-primary"></i>Brand/Manufacturer
                                        </label>
                                        <input type="text" class="form-control" name="brand"
                                            value="<?= isset($asset_details['brand']) ? htmlspecialchars($asset_details['brand']) : '' ?>"
                                            placeholder="Enter brand or manufacturer name">
                                        <div class="form-text">Brand name or manufacturer of the asset</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Property Details -->
                        <div class="form-step" id="step2">
                            <div class="step-header bg-success bg-opacity-10 p-2 border-bottom">
                                <h5 class="mb-1 text-success">
                                    <i class="bi bi-gear me-1"></i>Technical Specifications
                                </h5>
                                <p class="mb-0 text-muted">Define technical details and property identifiers</p>
                            </div>
                            <div class="p-2">
                                <!-- Model and Serial Numbers -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="model_no" class="form-label fw-semibold">
                                            <i class="bi bi-cpu me-1 text-success"></i>Model
                                        </label>
                                        <input type="text" class="form-control" name="model_no"
                                            value="<?= isset($asset_details['model']) ? htmlspecialchars($asset_details['model']) : '' ?>"
                                            placeholder="Enter model number or identifier">
                                        <div class="form-text">Manufacturer's model or product identifier</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="serial_no" class="form-label fw-semibold">
                                            <i class="bi bi-hash me-1 text-success"></i>Serial Number
                                        </label>
                                        <input type="text" class="form-control" name="serial_no"
                                            value="<?= htmlspecialchars($display_serial_no) ?>"
                                            placeholder="Auto-generated from tag format" <?= !empty($existing_serial_no) ? 'readonly' : '' ?> id="serial_no">
                                        <div class="form-text">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Auto-generated unique serial number - Format managed in 
                                            <strong>System Admin → Manage Tag Format</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Property Codes -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="code" class="form-label fw-semibold">
                                            <i class="bi bi-upc me-1 text-success"></i>Asset Code
                                        </label>
                                        <input type="text" class="form-control" name="code" id="code"
                                            value="<?= htmlspecialchars($display_asset_code) ?>"
                                            placeholder="Auto-generated based on category" <?= !empty($existing_asset_code) ? 'readonly' : '' ?>>
                                        <div class="form-text">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Internal asset classification code - Format managed in 
                                            <strong>System Admin → Manage Tag Format</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="property_no" class="form-label fw-semibold">
                                            <i class="bi bi-card-heading me-1 text-success"></i>Property Number
                                        </label>
                                        <input type="text" class="form-control" name="property_no" id="property_no"
                                            placeholder="<?= htmlspecialchars($placeholder_property_no) ?>"
                                            value="<?= htmlspecialchars($display_property_no) ?>">
                                        <div class="form-text">Official government property number</div>
                                    </div>
                                </div>

                                <!-- Asset Status -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-check-circle me-1 text-success"></i>Asset Condition Status
                                        </label>
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <div class="form-check form-check-lg">
                                                    <input class="form-check-input" type="radio" name="asset_status" value="serviceable" id="serviceable" checked>
                                                    <label class="form-check-label fw-semibold text-success" for="serviceable">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Serviceable
                                                    </label>
                                                    <div class="form-text">Asset is in good working condition</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Financial & Accountability Information -->
                        <div class="form-step" id="step3">
                            <div class="step-header bg-warning bg-opacity-10 p-2 border-bottom">
                                <h5 class="mb-1 text-warning">
                                    <i class="bi bi-people me-1"></i>Financial & Accountability
                                </h5>
                                <p class="mb-0 text-muted">Set acquisition details and assign responsibility</p>
                            </div>
                            <div class="p-2">
                                <!-- Quantity, Unit, and Financial Information -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="unit_quantity" class="form-label fw-semibold">
                                            <i class="bi bi-123 me-1 text-warning"></i>Quantity
                                        </label>
                                        <input type="number" class="form-control" name="unit_quantity"
                                            value="1" min="1" required readonly style="background-color: #f8f9fa;"
                                            title="Quantity is fixed at 1 for individual asset records">
                                        <div class="form-text">Fixed at 1 for individual assets</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="unit" class="form-label fw-semibold">
                                            <i class="bi bi-box me-1 text-warning"></i>Unit
                                        </label>
                                        <select name="unit" class="form-select" required>
                                            <?php
                                            $unit_rows = [];
                                            $res_units = $conn->query("SELECT unit_name FROM unit");
                                            if ($res_units && $res_units->num_rows > 0) {
                                                while ($ur = $res_units->fetch_assoc()) {
                                                    $unit_rows[] = $ur['unit_name'];
                                                }
                                            } else {
                                                $unit_rows = ['kg', 'pcs', 'liter'];
                                            }
                                            foreach ($unit_rows as $u) {
                                                $sel = (isset($asset_details['unit']) && $asset_details['unit'] == $u) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($u) . '" ' . $sel . '>' . htmlspecialchars($u) . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <div class="form-text">Unit of measurement</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="acquisition_date" class="form-label fw-semibold">
                                            <i class="bi bi-calendar me-1 text-warning"></i>Acquisition Date
                                        </label>
                                        <input type="date" class="form-control" name="acquisition_date"
                                            value="<?= isset($asset_details['acquisition_date']) ? htmlspecialchars($asset_details['acquisition_date']) : '' ?>" required>
                                        <div class="form-text">Date when asset was acquired</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="acquisition_cost" class="form-label fw-semibold">
                                            <i class="bi bi-currency-dollar me-1 text-warning"></i>Acquisition Cost
                                        </label>
                                        <input type="number" class="form-control" name="acquisition_cost" step="0.01"
                                            value="<?= isset($asset_details['value']) ? htmlspecialchars($asset_details['value']) : '' ?>" required>
                                        <div class="form-text">Total cost of acquisition</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="supplier" class="form-label fw-semibold">
                                            <i class="bi bi-truck me-1 text-warning"></i>Supplier
                                        </label>
                                        <input type="text" class="form-control" name="supplier" id="supplier"
                                            placeholder="Enter supplier name"
                                            value="<?= isset($asset_details['supplier']) ? htmlspecialchars($asset_details['supplier']) : '' ?>">
                                        <div class="form-text">Supplier/vendor of the asset</div>
                                    </div>
                                </div>

                                <!-- Person Accountable & End User -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="person_accountable" class="form-label fw-semibold">
                                            <i class="bi bi-person-check me-1 text-warning"></i>Person Accountable
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="person_accountable_name" id="person_accountable" required
                                            list="employeeList" placeholder="Type to search employee" autocomplete="off"
                                            value="<?= htmlspecialchars($person_accountable_name) ?>">
                                        <input type="hidden" name="employee_id" id="employee_id" value="<?= isset($employee_id) ? htmlspecialchars($employee_id) : '' ?>">
                                        <datalist id="employeeList">
                                            <?php foreach ($employees as $emp): ?>
                                                <option data-id="<?= $emp['employee_id'] ?>" value="<?= htmlspecialchars($emp['name']) ?>"></option>
                                            <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text">Employee responsible for this asset</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_user" class="form-label fw-semibold">
                                            <i class="bi bi-person me-1 text-warning"></i>End User
                                        </label>
                                        <input type="text" class="form-control" name="end_user" id="end_user"
                                            placeholder="Enter end user name"
                                            value="<?= isset($asset_details['end_user']) ? htmlspecialchars($asset_details['end_user']) : '' ?>">
                                        <div class="form-text">Person who will actually use this asset</div>
                                    </div>
                                </div>

                                <!-- Acquired Date & Counted Date -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="acquired_date" class="form-label fw-semibold">
                                            <i class="bi bi-calendar-check me-1 text-warning"></i>Date Acquired
                                        </label>
                                        <input type="date" class="form-control" name="acquired_date"
                                            value="<?= isset($asset_details['last_updated']) ? htmlspecialchars($asset_details['last_updated']) : '' ?>">
                                        <div class="form-text">Date when asset was officially acquired</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="counted_date" class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event me-1 text-warning"></i>Date Counted
                                        </label>
                                        <input type="date" class="form-control" name="counted_date">
                                        <div class="form-text">Date when asset was physically counted</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Asset Documentation -->
                        <div class="form-step" id="step4">
                            <div class="step-header bg-info bg-opacity-10 p-2 border-bottom">
                                <h5 class="mb-1 text-info">
                                    <i class="bi bi-camera me-1"></i>Asset Documentation
                                </h5>
                                <p class="mb-0 text-muted">Upload photos and review information before submission</p>
                            </div>
                            <div class="p-2">
                                <!-- Upload Asset Photo -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="asset_image" class="form-label fw-semibold">
                                            <i class="bi bi-camera me-1 text-info"></i>Upload Asset Photo
                                        </label>
                                        <input type="file" class="form-control" name="asset_image" id="asset_image" accept="image/*">
                                        <div class="form-text">Accepted: JPG, JPEG, PNG, GIF. Maximum size: 5MB</div>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <label class="form-label fw-semibold d-block">
                                            <i class="bi bi-eye me-1 text-info"></i>Photo Preview
                                        </label>
                                        <div class="border rounded p-2 bg-light" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                            <img id="asset_image_preview" 
                                                src="<?= !empty($asset_details['image']) ? '../img/assets/' . htmlspecialchars($asset_details['image']) : '' ?>" 
                                                alt="Asset Image Preview" 
                                                class="img-fluid rounded shadow-sm" 
                                                style="max-height: 130px; object-fit: contain; <?= empty($asset_details['image']) ? 'display: none;' : '' ?>">
                                            <div id="preview_placeholder" class="text-center text-muted <?= !empty($asset_details['image']) ? 'd-none' : '' ?>">
                                                <i class="bi bi-image fs-1 mb-2"></i>
                                                <p class="mb-0">Photo preview will appear here</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Existing Asset Images Gallery -->
                                <?php
                                $additional_images = [];
                                if (!empty($asset_details['additional_images'])) {
                                    $additional_images = json_decode($asset_details['additional_images'], true);
                                    if (!is_array($additional_images)) {
                                        $additional_images = [];
                                    }
                                }
                                ?>
                                <?php if (!empty($asset_details['image']) || !empty($additional_images)): ?>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-header bg-transparent border-0 pb-0">
                                                    <h6 class="mb-0 text-info fw-semibold">
                                                        <i class="bi bi-images me-1"></i>Existing Asset Images
                                                        <small class="text-muted ms-2">
                                                            (<?= (!empty($asset_details['image']) ? 1 : 0) + count($additional_images) ?> image<?= ((!empty($asset_details['image']) ? 1 : 0) + count($additional_images)) > 1 ? 's' : '' ?>)
                                                        </small>
                                                    </h6>
                                                </div>
                                                <div class="card-body pt-3">
                                                    <div class="row g-3">
                                                        <?php if (!empty($asset_details['image'])): ?>
                                                            <!-- Main Image -->
                                                            <div class="col-6 col-md-4 col-lg-3">
                                                                <div class="card shadow-sm border-primary" style="transition: transform 0.2s;">
                                                                    <div class="position-relative overflow-hidden rounded-top">
                                                                        <img src="../img/assets/<?= htmlspecialchars($asset_details['image']) ?>"
                                                                            class="card-img-top"
                                                                            style="height: 120px; object-fit: cover; cursor: pointer; transition: transform 0.3s;"
                                                                            onclick="showImageModal('../img/assets/<?= htmlspecialchars($asset_details['image']) ?>', 'Main Asset Image')"
                                                                            onmouseover="this.style.transform='scale(1.05)'"
                                                                            onmouseout="this.style.transform='scale(1)'"
                                                                            alt="Main Asset Image">
                                                                        <div class="position-absolute top-0 start-0 m-2">
                                                                            <span class="badge bg-primary shadow-sm">
                                                                                <i class="bi bi-star-fill me-1"></i>Main
                                                                            </span>
                                                                        </div>
                                                                        <div class="position-absolute bottom-0 end-0 m-2">
                                                                            <span class="badge bg-dark bg-opacity-75">
                                                                                <i class="bi bi-zoom-in"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card-body p-2 text-center bg-primary bg-opacity-10">
                                                                        <small class="text-primary fw-medium">Primary Image</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (!empty($additional_images)): ?>
                                                            <!-- Additional Images -->
                                                            <?php foreach ($additional_images as $index => $imageName): ?>
                                                                <div class="col-6 col-md-4 col-lg-3">
                                                                    <div class="card shadow-sm border-info" style="transition: transform 0.2s;">
                                                                        <div class="position-relative overflow-hidden rounded-top">
                                                                            <img src="../img/assets/<?= htmlspecialchars($imageName) ?>"
                                                                                class="card-img-top"
                                                                                style="height: 120px; object-fit: cover; cursor: pointer; transition: transform 0.3s;"
                                                                                onclick="showImageModal('../img/assets/<?= htmlspecialchars($imageName) ?>', 'Additional Image <?= $index + 1 ?>')"
                                                                                onmouseover="this.style.transform='scale(1.05)'"
                                                                                onmouseout="this.style.transform='scale(1)'"
                                                                                alt="Additional Asset Image <?= $index + 1 ?>">
                                                                            <div class="position-absolute top-0 start-0 m-2">
                                                                                <span class="badge bg-info shadow-sm">
                                                                                    <i class="bi bi-image me-1"></i><?= $index + 1 ?>
                                                                                </span>
                                                                            </div>
                                                                            <div class="position-absolute bottom-0 end-0 m-2">
                                                                                <span class="badge bg-dark bg-opacity-75">
                                                                                    <i class="bi bi-zoom-in"></i>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="card-body p-2 text-center bg-info bg-opacity-10">
                                                                            <small class="text-info fw-medium">Additional Image <?= $index + 1 ?></small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Gallery Instructions -->
                                                    <div class="mt-3 text-center">
                                                        <small class="text-muted">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Click on any image to view in full size
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- No Images Available -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-image display-6 d-block mb-2 opacity-50"></i>
                                                <h6 class="text-muted">No Images Available</h6>
                                                <p class="mb-0 small">No images have been uploaded for this asset yet.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                            </div>
                        </div>

                        <!-- Form Navigation and Submission -->
                        <div class="card-footer bg-white border-top p-2">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                                            <i class="bi bi-arrow-left me-1"></i>Previous
                                        </button>
                                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                                            Next<i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-success shadow-sm" id="submitBtn" style="display: none;">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <?= $existing_mr_check ? 'Update Property Tag' : 'Create Property Tag' ?>
                                    </button>
                                    <?php if ($existing_mr_check): ?>
                                        <?php
                                            // Determine MR ID for printing (prefer mapping by item_id+asset_id)
                                            $mr_id_for_print = null;
                                            if (isset($asset_id) && !empty($asset_id)) {
                                                if ($stFindBtn = $conn->prepare("SELECT mr_id FROM mr_details WHERE (item_id = ? OR (? IS NULL AND item_id IS NULL)) AND asset_id = ? ORDER BY mr_id DESC LIMIT 1")) {
                                                    // $mr_item_id may be null (PAR-origin) which is handled by the OR clause
                                                    $temp_item_id = isset($mr_item_id) ? $mr_item_id : null;
                                                    $stFindBtn->bind_param('iii', $temp_item_id, $temp_item_id, $asset_id);
                                                    $stFindBtn->execute();
                                                    $rsBtn = $stFindBtn->get_result();
                                                    if ($rsBtn && ($rowBtn = $rsBtn->fetch_assoc())) {
                                                        $mr_id_for_print = (int)$rowBtn['mr_id'];
                                                    }
                                                    $stFindBtn->close();
                                                }
                                            }
                                        ?>
                                        <a href="bulk_print_mr.php?ids=<?= htmlspecialchars((string)$mr_id_for_print) ?>" class="btn btn-info ms-2 shadow-sm" <?= empty($mr_id_for_print) ? 'disabled' : '' ?>>
                                            <i class="bi bi-printer me-1"></i>Print Tag
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Custom CSS for Step Indicators and Form Steps -->
        <style>
            .step-indicator {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background-color: #e9ecef;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid #dee2e6;
                transition: all 0.3s ease;
            }
            
            .step-indicator.active {
                background-color: #0d6efd;
                border-color: #0d6efd;
                color: white;
                transform: scale(1.1);
            }
            
            .step-indicator.completed {
                background-color: #198754;
                border-color: #198754;
                color: white;
            }
            
            .step-number {
                font-weight: bold;
                font-size: 12px;
            }
            
            .form-step {
                display: none;
            }
            
            .form-step.active {
                display: block;
                animation: fadeIn 0.3s ease-in;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .form-control-lg, .form-select-lg {
                border-radius: 8px;
                border: 2px solid #e9ecef;
                transition: all 0.3s ease;
            }
            
            .form-control-lg:focus, .form-select-lg:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
                transform: translateY(-1px);
            }
            
            .step-header {
                border-left: 4px solid;
            }
            
            .step-header h5 {
                font-size: 1.25rem;
            }
            
            .form-text {
                font-size: 0.875rem;
                margin-top: 0.5rem;
            }
            
            .card {
                transition: all 0.3s ease;
            }
            
            .card:hover {
                transform: translateY(-2px);
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="js/dashboard.js"></script>
        
        <script>
            // Multi-step form functionality
            let currentStep = 1;
            const totalSteps = 4;

            function showStep(step) {
                // Hide all steps
                document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
                
                // Show current step
                document.getElementById('step' + step).classList.add('active');
                
                // Update step indicators
                document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                    indicator.classList.remove('active', 'completed');
                    if (index + 1 < step) {
                        indicator.classList.add('completed');
                        indicator.innerHTML = '<i class="bi bi-check-lg"></i>';
                    } else if (index + 1 === step) {
                        indicator.classList.add('active');
                        indicator.innerHTML = '<span class="step-number">' + (index + 1) + '</span>';
                    } else {
                        indicator.innerHTML = '<span class="step-number">' + (index + 1) + '</span>';
                    }
                });
                
                // Update step labels
                document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                    const label = indicator.nextElementSibling;
                    if (index + 1 <= step) {
                        label.classList.remove('text-muted');
                        label.classList.add(index + 1 === step ? 'text-primary' : 'text-success');
                        label.classList.add('fw-semibold');
                    } else {
                        label.classList.add('text-muted');
                        label.classList.remove('text-primary', 'text-success', 'fw-semibold');
                    }
                });
                
                // Update navigation buttons
                document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
                document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'inline-block';
                document.getElementById('submitBtn').style.display = step === totalSteps ? 'inline-block' : 'none';
            }

            function changeStep(direction) {
                const newStep = currentStep + direction;
                if (newStep >= 1 && newStep <= totalSteps) {
                    // Validate current step before proceeding
                    if (direction > 0 && !validateStep(currentStep)) {
                        return;
                    }
                    currentStep = newStep;
                    showStep(currentStep);
                }
            }

            function validateStep(step) {
                const stepElement = document.getElementById('step' + step);
                const requiredFields = stepElement.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    // Show error message
                    const alertHtml = `
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Please fill in all required fields before proceeding.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    stepElement.insertAdjacentHTML('afterbegin', alertHtml);
                    
                    // Auto-remove alert after 5 seconds
                    setTimeout(() => {
                        const alert = stepElement.querySelector('.alert');
                        if (alert) alert.remove();
                    }, 5000);
                }
                
                return isValid;
            }

            // Initialize form
            document.addEventListener('DOMContentLoaded', function() {
                showStep(1);
                
                // Remove validation classes on input
                document.querySelectorAll('[required]').forEach(field => {
                    field.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    });
                });
            });

            // Employee selection functionality
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
            const previewPlaceholder = document.getElementById('preview_placeholder');
            
            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function() {
                    const file = this.files && this.files[0];
                    if (!file) return;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                        previewPlaceholder.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Serial number and asset code generation - Connected to manage tag format system
            (function() {
                const categorySelect = document.getElementById('category_id');
                const codeInput = document.getElementById('code');
                const serialInput = document.getElementById('serial_no');
                
                // PHP-provided formats from manage tag format
                const codeFormatTemplate = <?= json_encode($code_format ?? '') ?>;
                const serialFormatTemplate = <?= json_encode($serial_format ?? '') ?>;

                // Enhanced asset code generation using tag format system
                function buildCodeFromCategory(catCode) {
                    if (!catCode) return '';
                    
                    let template = (codeFormatTemplate || '').trim();
                    if (!template) {
                        // Fallback to default format if no template is configured
                        template = '{YYYY}-{CODE}-{####}';
                    }
                    
                    // Replace date placeholders
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    
                    let output = template;
                    
                    // Replace date placeholders (both with and without curly braces)
                    output = output.replace(/\{YYYY\}|YYYY/g, year.toString());
                    output = output.replace(/\{YY\}|YY/g, year.toString().slice(-2));
                    output = output.replace(/\{MM\}|MM/g, month);
                    output = output.replace(/\{DD\}|DD/g, day);
                    output = output.replace(/\{YYYYMM\}|YYYYMM/g, year.toString() + month);
                    output = output.replace(/\{YYYYMMDD\}|YYYYMMDD/g, year.toString() + month + day);
                    
                    // Replace category code placeholder
                    output = output.replace(/\{CODE\}|CODE/g, catCode);
                    
                    // Enhanced flexible digit replacement - supports any number of # symbols
                    output = output.replace(/\{(#+)\}/g, function(match, hashes) {
                        const digitCount = hashes.length;
                        return '0'.repeat(Math.max(1, digitCount - 1)) + '1';
                    });
                    
                    // Legacy support for specific patterns
                    output = output.replace(/\{XXXX\}|XXXX/g, '0001');
                    
                    return output;
                }
                
                // Serial number generation using tag format system
                function buildSerialNumber() {
                    let template = (serialFormatTemplate || '').trim();
                    if (!template) {
                        // Fallback to default format if no template is configured
                        template = 'SN-{YYYY}-{######}';
                    }

                    let output = template;
                    
                    // Replace date placeholders
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    
                    output = output.replace(/\{YYYY\}|YYYY/g, year.toString());
                    output = output.replace(/\{YY\}|YY/g, year.toString().slice(-2));
                    output = output.replace(/\{MM\}|MM/g, month);
                    output = output.replace(/\{DD\}|DD/g, day);
                    output = output.replace(/\{YYYYMM\}|YYYYMM/g, year.toString() + month);
                    output = output.replace(/\{YYYYMMDD\}|YYYYMMDD/g, year.toString() + month + day);
                    
                    // Enhanced flexible digit replacement - supports any number of # symbols
                    output = output.replace(/\{(#+)\}/g, function(match, hashes) {
                        const digitCount = hashes.length;
                        return '0'.repeat(Math.max(1, digitCount - 1)) + '1';
                    });
                    
                    return output;
                }
                
                function maybePrefillCode() {
                    if (!categorySelect || !codeInput) return;
                    const selected = categorySelect.options[categorySelect.selectedIndex];
                    if (!selected) return;
                    const catCode = selected.getAttribute('data-code') || '';
                    if ((codeInput.value || '').trim() === '' && catCode) {
                        codeInput.value = buildCodeFromCategory(catCode);
                    }
                }

                if (categorySelect && codeInput) {
                    // Note: Asset code is now generated server-side on page load like inventory_tag
                    // This provides proper incrementing behavior on each page refresh
                    categorySelect.addEventListener('change', function() {
                        // Asset code will be generated server-side on form submission
                        // No client-side generation needed - maintains server incrementing
                    });
                    
                    // Asset code and serial number are now generated server-side on page load
                    // This ensures proper incrementing behavior like inventory_tag
                }
            })();


            // Function to show image in modal with enhanced features
            function showImageModal(imageSrc, imageTitle) {
                // Create modal if it doesn't exist
                let imageModal = document.getElementById('imageViewModal');
                if (!imageModal) {
                    const modalHTML = `
                        <div class="modal fade" id="imageViewModal" tabindex="-1" aria-labelledby="imageViewModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="imageViewModalLabel">
                                            <i class="bi bi-image me-1"></i>Asset Image Viewer
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center p-2" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                        <div class="mb-3">
                                            <h6 id="imageTitle" class="text-primary mb-2"></h6>
                                        </div>
                                        <div class="position-relative d-inline-block">
                                            <img id="modalImage" src="" alt="Asset Image" 
                                                 class="img-fluid rounded shadow-sm" 
                                                 style="max-height: 75vh; max-width: 100%; object-fit: contain; transition: transform 0.3s;">
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <button class="btn btn-sm btn-dark bg-opacity-75 border-0" 
                                                        onclick="toggleImageZoom()" 
                                                        title="Toggle Zoom">
                                                    <i class="bi bi-zoom-in" id="zoomIcon"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <small class="text-muted me-auto">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Click the zoom button or double-click the image to zoom
                                        </small>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x-lg me-1"></i>Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', modalHTML);
                    imageModal = document.getElementById('imageViewModal');
                    
                    // Add double-click zoom functionality
                    document.getElementById('modalImage').addEventListener('dblclick', toggleImageZoom);
                }
                
                // Update modal content
                document.getElementById('imageTitle').textContent = imageTitle;
                document.getElementById('modalImage').src = imageSrc;
                
                // Reset zoom state
                const img = document.getElementById('modalImage');
                img.style.transform = 'scale(1)';
                img.style.cursor = 'zoom-in';
                document.getElementById('zoomIcon').className = 'bi bi-zoom-in';
                
                // Show modal
                const modal = new bootstrap.Modal(imageModal);
                modal.show();
            }

            // Function to toggle image zoom
            function toggleImageZoom() {
                const img = document.getElementById('modalImage');
                const zoomIcon = document.getElementById('zoomIcon');
                
                if (img.style.transform === 'scale(2)') {
                    // Zoom out
                    img.style.transform = 'scale(1)';
                    img.style.cursor = 'zoom-in';
                    zoomIcon.className = 'bi bi-zoom-in';
                } else {
                    // Zoom in
                    img.style.transform = 'scale(2)';
                    img.style.cursor = 'zoom-out';
                    zoomIcon.className = 'bi bi-zoom-out';
                }
            }
        </script>
    </body>
</html>
