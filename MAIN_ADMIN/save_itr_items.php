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
    $entity_name = $_POST['entity_name'] ?? '';
    $fund_cluster = $_POST['fund_cluster'] ?? '';
    $from_accountable_officer = $_POST['from_accountable_officer'] ?? '';
    $to_accountable_officer = $_POST['to_accountable_officer'] ?? '';
    $itr_no = $_POST['itr_no'] ?? '';
    $date = $_POST['date'] ?? '';
    $reason_for_transfer = $_POST['reason_for_transfer'] ?? '';
    
    // Handle transfer type checkboxes
    $transfer_type = '';
    if (isset($_POST['transfer_type']) && is_array($_POST['transfer_type'])) {
        $transfer_types = $_POST['transfer_type'];
        // Handle "Other" custom input
        if (in_array('Others', $transfer_types) && !empty($_POST['transfer_type_other'])) {
            // Replace "Others" with the custom value
            $key = array_search('Others', $transfer_types);
            $transfer_types[$key] = $_POST['transfer_type_other'];
        }
        $transfer_type = implode(', ', $transfer_types);
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

    // Handle optional header image upload
    $header_image = '';
    if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['header_image']['tmp_name'];
        $origName = $_FILES['header_image']['name'];
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

    // Update ITR form header fields
    if (!empty($header_image)) {
        $stmt = $conn->prepare("UPDATE itr_form SET 
            header_image = ?, entity_name = ?, fund_cluster = ?, from_accountable_officer = ?, 
            to_accountable_officer = ?, itr_no = ?, `date` = ?, transfer_type = ?, 
            reason_for_transfer = ?, approved_by = ?, approved_designation = ?, approved_date = ?, 
            released_by = ?, released_designation = ?, released_date = ?, received_by = ?, 
            received_designation = ?, received_date = ? WHERE itr_id = ?");
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
            received_designation = ?, received_date = ? WHERE itr_id = ?");
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

    // Log ITR form update
    $logger = new AuditLogger($conn);
    $user_stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $username = $user_result->fetch_assoc()['fullname'] ?? 'Unknown User';
    $user_stmt->close();
    
    $logger->log($_SESSION['user_id'], $username, 'UPDATE', 'ITR Form', "Updated ITR form: {$itr_no} - {$entity_name}", 'itr_form', $itr_id);

    // Clear existing ITR items for this ITR
    $delete_stmt = $conn->prepare("DELETE FROM itr_items WHERE itr_id = ?");
    $delete_stmt->bind_param("i", $itr_id);
    $delete_stmt->execute();
    $delete_stmt->close();

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
            $itr_id,
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

    // Set flash message for success and redirect
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'ITR has been saved successfully. ' . count($assets_to_update) . ' asset(s) transferred to ' . $to_accountable_officer . '.'
    ];

    header("Location: itr_form.php");
    exit();
}

// If not POST request, redirect back
header("Location: itr_form.php");
exit();
?>
