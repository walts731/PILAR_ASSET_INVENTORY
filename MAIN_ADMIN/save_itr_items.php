<?php
require_once '../connect.php';
require_once '../phpqrcode/qrlib.php';
require_once '../includes/audit_logger.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ITR main info
    $itr_id = intval($_POST['itr_id'] ?? 0);
    $form_id = intval($_POST['form_id'] ?? 0);
    $entity_name = $_POST['entity_name'] ?? '';
    $fund_cluster = $_POST['fund_cluster'] ?? '';
    $from_accountable_officer = $_POST['from_accountable_officer'] ?? '';
    $to_accountable_officer = $_POST['to_accountable_officer'] ?? '';
    $itr_no = $_POST['itr_no'] ?? '';
    $date = $_POST['date'] ?? '';
    $reason_for_transfer = $_POST['reason_for_transfer'] ?? '';
    
    // Handle transfer type (now radio). Remains backward compatible with legacy checkbox array.
    $transfer_type = '';
    if (isset($_POST['transfer_type'])) {
        if (is_array($_POST['transfer_type'])) {
            // Legacy checkbox array
            $transfer_types = $_POST['transfer_type'];
            if (in_array('Others', $transfer_types) && !empty($_POST['transfer_type_other'])) {
                $key = array_search('Others', $transfer_types);
                $transfer_types[$key] = $_POST['transfer_type_other'];
            }
            $transfer_type = implode(', ', $transfer_types);
        } else {
            // New radio string
            $selected = trim((string)$_POST['transfer_type']);
            if (strcasecmp($selected, 'Others') === 0) {
                $transfer_type = trim((string)($_POST['transfer_type_other'] ?? ''));
            } else {
                $transfer_type = $selected;
            }
        }
    }
    
    // Footer fields
    $approved_by = $_POST['approved_by'] ?? '';
    $approved_designation = $_POST['approved_designation'] ?? '';
    $approved_date = $_POST['approved_date'] ?? '';
    $released_by = $_POST['released_by'] ?? '';
    $released_designation = $_POST['released_designation'] ?? '';
    $released_date = $_POST['released_date'] ?? '';
    $received_by = $_POST['received_by'] ?? '';
    $received_designation = $_POST['received_designation'] ?? '';
    $received_date = $_POST['received_date'] ?? '';
    $end_user = $_POST['end_user'] ?? '';

    // Handle header image (existing or new upload)
    $header_image = $_POST['header_image'] ?? ''; // Get existing header image from hidden input
    
    // Ensure database column for End User exists in mr_details table
    try {
        if ($chk = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mr_details' AND COLUMN_NAME = 'end_user'")) {
            $chk->execute(); $chk->bind_result($cnt); $chk->fetch(); $chk->close();
            if ((int)$cnt === 0) { $conn->query("ALTER TABLE mr_details ADD COLUMN end_user VARCHAR(255) NULL AFTER person_accountable"); }
        }
    } catch (Throwable $e) { /* non-fatal */ }
    
    // Handle optional header image file upload (overrides existing when provided)
    if (isset($_FILES['header_image_file']) && $_FILES['header_image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['header_image_file']['tmp_name'];
        $origName = $_FILES['header_image_file']['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed, true)) {
            $newName = 'itr_header_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destRel = '../img/' . $newName;
            if (@move_uploaded_file($tmp, $destRel)) {
                $header_image = $newName;
            }
        }
    }

    // Generate unique ITR number if empty
    if (empty($itr_no)) {
        $current_year = date('Y');
        $attempt = 0;
        do {
            $attempt++;
            // Get the latest ITR number to generate next one
            $latest_query = $conn->query("SELECT itr_no FROM itr_form WHERE itr_no IS NOT NULL AND itr_no != '' ORDER BY itr_id DESC LIMIT 1");
            if ($latest_query && $latest_query->num_rows > 0) {
                $latest_row = $latest_query->fetch_assoc();
                $latest_no = $latest_row['itr_no'];
                // Extract number from format like "ITR-2024-001"
                if (preg_match('/ITR-(\d{4})-(\d+)/', $latest_no, $matches)) {
                    $year = $matches[1];
                    $num = intval($matches[2]);
                    if ($year == $current_year) {
                        $next_num = $num + $attempt;
                    } else {
                        $next_num = $attempt; // Reset for new year
                    }
                    $itr_no = 'ITR-' . $current_year . '-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
                } else {
                    $itr_no = 'ITR-' . $current_year . '-' . str_pad($attempt, 3, '0', STR_PAD_LEFT);
                }
            } else {
                $itr_no = 'ITR-' . $current_year . '-' . str_pad($attempt, 3, '0', STR_PAD_LEFT);
            }
            
            // Check if this ITR number already exists
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM itr_form WHERE itr_no = ?");
            $check_stmt->bind_param("s", $itr_no);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();
            
        } while ($count > 0 && $attempt < 1000); // Prevent infinite loop
        
        if ($attempt >= 1000) {
            // Fallback with timestamp if we can't find a unique number
            $itr_no = 'ITR-' . $current_year . '-' . date('mdHis');
        }
    }

    // Get employee_id for the "to_accountable_officer"
    $to_employee_id = null;
    if (!empty($to_accountable_officer)) {
        $emp_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE name = ? LIMIT 1");
        $emp_stmt->bind_param("s", $to_accountable_officer);
        $emp_stmt->execute();
        $emp_result = $emp_stmt->get_result();
        if ($emp_row = $emp_result->fetch_assoc()) {
            $to_employee_id = (int)$emp_row['employee_id'];
        }
        $emp_stmt->close();
    }

    // Generate unique ITR number if empty
    if (empty($itr_no)) {
        $year = date('Y');
        $stmt_count = $conn->prepare("SELECT COUNT(*) FROM itr_form WHERE itr_no LIKE ?");
        $pattern = "ITR-{$year}-%";
        $stmt_count->bind_param("s", $pattern);
        $stmt_count->execute();
        $stmt_count->bind_result($count);
        $stmt_count->fetch();
        $stmt_count->close();
        
        $next_num = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        $itr_no = "ITR-{$year}-{$next_num}";
    }

    // Check if ITR form with this ID exists (for updates) or if we need to create new
    $new_itr_id = null;
    if ($itr_id > 0) {
        // Check if ITR exists
        $check_stmt = $conn->prepare("SELECT itr_id FROM itr_form WHERE itr_id = ?");
        $check_stmt->bind_param("i", $itr_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing ITR form
            if (!empty($header_image)) {
                $stmt = $conn->prepare("UPDATE itr_form SET 
                    header_image = ?, entity_name = ?, fund_cluster = ?, from_accountable_officer = ?, 
                    to_accountable_officer = ?, itr_no = ?, `date` = ?, transfer_type = ?, 
                    reason_for_transfer = ?, approved_by = ?, approved_designation = ?, approved_date = ?, 
                    released_by = ?, released_designation = ?, released_date = ?, received_by = ?, 
                    received_designation = ?, received_date = ? 
                    WHERE itr_id = ?");
                $stmt->bind_param(
                    "ssssssssssssssssssi",
                    $header_image, $entity_name, $fund_cluster, $from_accountable_officer,
                    $to_accountable_officer, $itr_no, $date, $transfer_type,
                    $reason_for_transfer, $approved_by, $approved_designation, $approved_date,
                    $released_by, $released_designation, $released_date, $received_by,
                    $received_designation, $received_date, $itr_id
                );
            } else {
                $stmt = $conn->prepare("UPDATE itr_form SET 
                    entity_name = ?, fund_cluster = ?, from_accountable_officer = ?, 
                    to_accountable_officer = ?, itr_no = ?, `date` = ?, transfer_type = ?, 
                    reason_for_transfer = ?, approved_by = ?, approved_designation = ?, approved_date = ?, 
                    released_by = ?, released_designation = ?, released_date = ?, received_by = ?, 
                    received_designation = ?, received_date = ? 
                    WHERE itr_id = ?");
                $stmt->bind_param(
                    "sssssssssssssssssi",
                    $entity_name, $fund_cluster, $from_accountable_officer,
                    $to_accountable_officer, $itr_no, $date, $transfer_type,
                    $reason_for_transfer, $approved_by, $approved_designation, $approved_date,
                    $released_by, $released_designation, $released_date, $received_by,
                    $received_designation, $received_date, $itr_id
                );
            }
            $stmt->execute();
            $stmt->close();
            $new_itr_id = $itr_id;
        } else {
            // ITR ID doesn't exist, create new
            $new_itr_id = null;
        }
        $check_stmt->close();
    }
    
    // Insert new ITR form if not updating existing
    if ($new_itr_id === null) {
        if (!empty($header_image)) {
            $stmt = $conn->prepare("INSERT INTO itr_form 
                (header_image, entity_name, fund_cluster, from_accountable_officer, 
                to_accountable_officer, itr_no, `date`, transfer_type, 
                reason_for_transfer, approved_by, approved_designation, approved_date, 
                released_by, released_designation, released_date, received_by, 
                received_designation, received_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "ssssssssssssssssss",
                $header_image, $entity_name, $fund_cluster, $from_accountable_officer,
                $to_accountable_officer, $itr_no, $date, $transfer_type,
                $reason_for_transfer, $approved_by, $approved_designation, $approved_date,
                $released_by, $released_designation, $released_date, $received_by,
                $received_designation, $received_date
            );
        } else {
            $stmt = $conn->prepare("INSERT INTO itr_form 
                (entity_name, fund_cluster, from_accountable_officer, 
                to_accountable_officer, itr_no, `date`, transfer_type, 
                reason_for_transfer, approved_by, approved_designation, approved_date, 
                released_by, released_designation, released_date, received_by, 
                received_designation, received_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssssssssssssssss",
                $entity_name, $fund_cluster, $from_accountable_officer,
                $to_accountable_officer, $itr_no, $date, $transfer_type,
                $reason_for_transfer, $approved_by, $approved_designation, $approved_date,
                $released_by, $released_designation, $released_date, $received_by,
                $received_designation, $received_date
            );
        }
        $stmt->execute();
        $new_itr_id = $conn->insert_id;
        $stmt->close();
    }

    // Log ITR form creation
    $logger = new AuditLogger($conn);
    $user_stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $username = $user_result->fetch_assoc()['fullname'] ?? 'Unknown User';
    $user_stmt->close();
    
    $logger->log($_SESSION['user_id'], $username, 'CREATE', 'ITR Form', "Created new ITR form: {$itr_no} - {$entity_name}", 'itr_form', $new_itr_id);

    // ITR items data
    $date_acquired = $_POST['date_acquired'] ?? [];
    $property_nos = $_POST['property_no'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $amounts = $_POST['amount'] ?? [];
    $conditions = $_POST['condition_of_PPE'] ?? [];

    // Prepare ITR items insert
    $stmt_items = $conn->prepare("INSERT INTO itr_items 
        (itr_id, asset_id, date_acquired, property_no, description, amount, condition_of_PPE)
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Track assets that need employee_id update
    $assets_to_update = [];

    for ($i = 0; $i < count($descriptions); $i++) {
        $acquired_date = $date_acquired[$i] ?? '';
        $property_no = $property_nos[$i] ?? '';
        $description = $descriptions[$i] ?? '';
        $amount = isset($amounts[$i]) ? floatval($amounts[$i]) : 0;
        $condition = $conditions[$i] ?? '';

        // Skip empty rows
        if (empty($description) && empty($property_no)) continue;

        // Find asset_id based on property_no or description
        $asset_id = null;
        if (!empty($property_no)) {
            $asset_stmt = $conn->prepare("SELECT id FROM assets WHERE property_no = ? LIMIT 1");
            $asset_stmt->bind_param("s", $property_no);
            $asset_stmt->execute();
            $asset_result = $asset_stmt->get_result();
            if ($asset_row = $asset_result->fetch_assoc()) {
                $asset_id = (int)$asset_row['id'];
                // Add to list for employee_id update
                if ($to_employee_id && !in_array($asset_id, $assets_to_update)) {
                    $assets_to_update[] = $asset_id;
                }
            }
            $asset_stmt->close();
        }

        // Insert ITR item
        $stmt_items->bind_param(
            "iisssds",
            $new_itr_id,
            $asset_id,
            $acquired_date,
            $property_no,
            $description,
            $amount,
            $condition
        );
        $stmt_items->execute();
        
        // Log individual ITR item creation
        $item_details = "Added item to ITR {$itr_no}: {$description} (Property No: {$property_no}, Amount: ₱" . number_format($amount, 2) . ")";
        $logger->log($_SESSION['user_id'], $username, 'CREATE', 'ITR Items', $item_details, 'itr_items', $conn->insert_id);
    }

    $stmt_items->close();

    // Update asset employee_id for transferred assets
    if (!empty($assets_to_update) && $to_employee_id) {
        $placeholders = str_repeat('?,', count($assets_to_update) - 1) . '?';
        $update_assets_stmt = $conn->prepare("UPDATE assets SET employee_id = ? WHERE id IN ($placeholders)");
        
        // Bind parameters: first the employee_id, then all asset_ids
        $types = 'i' . str_repeat('i', count($assets_to_update));
        $params = array_merge([$to_employee_id], $assets_to_update);
        $update_assets_stmt->bind_param($types, ...$params);
        $update_assets_stmt->execute();
        $update_assets_stmt->close();

        // Log asset transfers
        foreach ($assets_to_update as $asset_id) {
            $logger->log($_SESSION['user_id'], $username, 'UPDATE', 'Assets', 
                "Transferred asset ID {$asset_id} to employee: {$to_accountable_officer} via ITR {$itr_no}", 
                'assets', $asset_id);
        }
    }

    // Update mr_details for transferred assets (person_accountable and end_user)
    if (!empty($assets_to_update)) {
        $placeholders = str_repeat('?,', count($assets_to_update) - 1) . '?';
        
        // Prepare UPDATE query for both person_accountable and end_user
        if (!empty($end_user)) {
            // Update both person_accountable and end_user
            $update_mr_stmt = $conn->prepare("UPDATE mr_details SET person_accountable = ?, end_user = ? WHERE asset_id IN ($placeholders)");
            $types = 'ss' . str_repeat('i', count($assets_to_update));
            $params = array_merge([$to_accountable_officer, $end_user], $assets_to_update);
        } else {
            // Update only person_accountable
            $update_mr_stmt = $conn->prepare("UPDATE mr_details SET person_accountable = ? WHERE asset_id IN ($placeholders)");
            $types = 's' . str_repeat('i', count($assets_to_update));
            $params = array_merge([$to_accountable_officer], $assets_to_update);
        }
        
        $update_mr_stmt->bind_param($types, ...$params);
        $update_mr_stmt->execute();
        $affected_rows = $update_mr_stmt->affected_rows;
        $update_mr_stmt->close();

        // Log mr_details updates
        if ($affected_rows > 0) {
            foreach ($assets_to_update as $asset_id) {
                $log_details = "Updated person_accountable to '{$to_accountable_officer}'";
                if (!empty($end_user)) {
                    $log_details .= " and end_user to '{$end_user}'";
                }
                $log_details .= " for asset ID {$asset_id} via ITR {$itr_no}";
                
                $logger->log($_SESSION['user_id'], $username, 'UPDATE', 'MR Details', 
                    $log_details, 'mr_details', $asset_id);
            }
        }
    }

    // Set flash message for success and redirect
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'ITR has been saved successfully. ' . count($assets_to_update) . ' asset(s) transferred to ' . $to_accountable_officer . '.'
    ];

    // Redirect to forms.php with form_id if available
    if ($form_id > 0) {
        header("Location: forms.php?id=" . $form_id);
    } else {
        header("Location: itr_form.php");
    }
    exit();
}

// If not POST request, redirect back
header("Location: itr_form.php");
exit();
?>
