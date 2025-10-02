<?php
require_once '../connect.php';
require_once '../includes/audit_logger.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    
    try {
        // Get ITR main info
        $form_id = intval($_POST['form_id'] ?? 0);
        $itr_id = intval($_POST['itr_id'] ?? 0);
        $header_image = $_POST['header_image'] ?? '';
        $entity_name = $_POST['entity_name'] ?? '';
        $fund_cluster = $_POST['fund_cluster'] ?? '';
        $from_accountable_officer = trim($_POST['from_accountable_officer'] ?? '');
        $to_accountable_officer = trim($_POST['to_accountable_officer'] ?? '');
        $itr_no = trim($_POST['itr_no'] ?? '');
        $date = $_POST['date'] ?? '';
        $transfer_type = $_POST['transfer_type'] ?? '';
        $transfer_type_other = $_POST['transfer_type_other'] ?? '';
        $reason_for_transfer = trim($_POST['reason_for_transfer'] ?? '');
        $approved_by = $_POST['approved_by'] ?? '';
        $approved_designation = $_POST['approved_designation'] ?? '';
        $approved_date = $_POST['approved_date'] ?? '';
        $released_by = $_POST['released_by'] ?? '';
        $released_designation = $_POST['released_designation'] ?? '';
        $released_date = $_POST['released_date'] ?? '';
        $received_by = trim($_POST['received_by'] ?? '');
        $received_designation = $_POST['received_designation'] ?? '';
        $received_date = $_POST['received_date'] ?? '';
        $end_user = trim($_POST['end_user'] ?? '');

        // Handle transfer type - if "Others" is selected, use the custom value
        if ($transfer_type === 'Others' && !empty($transfer_type_other)) {
            $final_transfer_type = 'others'; // Use enum value for others
        } else {
            // Convert to lowercase to match enum values
            $final_transfer_type = strtolower($transfer_type);
            // Map specific values
            if ($final_transfer_type === 'relocation') {
                $final_transfer_type = 'relocate';
            }
        }

        // Validate required fields
        if (empty($entity_name) || empty($fund_cluster) || empty($from_accountable_officer) || 
            empty($to_accountable_officer) || empty($itr_no) || empty($reason_for_transfer) ||
            empty($end_user)) {
            throw new Exception('Please fill in all required fields.');
        }

        // Check if ITR number already exists (for new ITR forms)
        if ($itr_id == 0) {
            $check_stmt = $conn->prepare("SELECT itr_id FROM itr_form WHERE itr_no = ? LIMIT 1");
            $check_stmt->bind_param("s", $itr_no);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                throw new Exception('ITR Number already exists. Please use a unique ITR number.');
            }
            $check_stmt->close();
        }

        // Get item arrays
        $date_acquired_array = $_POST['date_acquired'] ?? [];
        $property_no_array = $_POST['property_no'] ?? [];
        $description_array = $_POST['description'] ?? [];
        $amount_array = $_POST['amount'] ?? [];
        $condition_array = $_POST['condition_of_PPE'] ?? [];

        // Validate that we have items
        if (empty($date_acquired_array) || count($date_acquired_array) == 0) {
            throw new Exception('Please add at least one item to the ITR.');
        }

        // Extract asset IDs from descriptions and validate
        $assets_to_update = [];
        $asset_inventory_tags = [];
        $valid_items = [];
        
        for ($i = 0; $i < count($date_acquired_array); $i++) {
            if (!empty($description_array[$i]) && !empty($property_no_array[$i])) {
                // Extract asset ID from description or property number
                $asset_id = null;
                
                // Try to find asset by property number first
                $asset_stmt = $conn->prepare("SELECT id, employee_id, inventory_tag FROM assets WHERE property_no = ? AND type = 'asset' LIMIT 1");
                $asset_stmt->bind_param("s", $property_no_array[$i]);
                $asset_stmt->execute();
                $asset_result = $asset_stmt->get_result();
                
                if ($asset_result->num_rows > 0) {
                    $asset_row = $asset_result->fetch_assoc();
                    $asset_id = $asset_row['id'];
                    $current_employee_id = $asset_row['employee_id'];
                    $asset_inventory_tags[(int)$asset_id] = $asset_row['inventory_tag'] ?? '';
                } else {
                    // Try to extract from description format "Description (Property No)"
                    if (preg_match('/\(([^)]+)\)$/', $description_array[$i], $matches)) {
                        $extracted_property_no = $matches[1];
                        $asset_stmt2 = $conn->prepare("SELECT id, employee_id, inventory_tag FROM assets WHERE property_no = ? AND type = 'asset' LIMIT 1");
                        $asset_stmt2->bind_param("s", $extracted_property_no);
                        $asset_stmt2->execute();
                        $asset_result2 = $asset_stmt2->get_result();
                        
                        if ($asset_result2->num_rows > 0) {
                            $asset_row2 = $asset_result2->fetch_assoc();
                            $asset_id = $asset_row2['id'];
                            $current_employee_id = $asset_row2['employee_id'];
                            $asset_inventory_tags[(int)$asset_id] = $asset_row2['inventory_tag'] ?? '';
                        }
                        $asset_stmt2->close();
                    }
                }
                $asset_stmt->close();
                
                if ($asset_id) {
                    $assets_to_update[] = (int)$asset_id;
                    $valid_items[] = [
                        'date_acquired' => $date_acquired_array[$i],
                        'property_no' => $property_no_array[$i],
                        'asset_id' => $asset_id,
                        'description' => $description_array[$i],
                        'amount' => $amount_array[$i],
                        'condition_of_PPE' => $condition_array[$i]
                    ];
                }
            }
        }

        if (empty($valid_items)) {
            throw new Exception('No valid assets found. Please ensure all items have valid property numbers.');
        }

        // De-duplicate asset IDs to avoid double updates
        if (!empty($assets_to_update)) {
            $assets_to_update = array_values(array_unique($assets_to_update, SORT_NUMERIC));
        }

        // Get employee ID for the "To Accountable Officer"
        $to_employee_id = null;
        $emp_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE name = ? AND status = 'permanent' LIMIT 1");
        $emp_stmt->bind_param("s", $to_accountable_officer);
        $emp_stmt->execute();
        $emp_result = $emp_stmt->get_result();
        
        if ($emp_result->num_rows > 0) {
            $emp_row = $emp_result->fetch_assoc();
            $to_employee_id = $emp_row['employee_id'];
        }
        $emp_stmt->close();

        // ALWAYS INSERT new ITR form (no updates)
        $itr_stmt = $conn->prepare("INSERT INTO itr_form 
            (header_image, entity_name, fund_cluster, from_accountable_officer, to_accountable_officer, 
            itr_no, date, transfer_type, reason_for_transfer, approved_by, approved_designation, 
            approved_date, released_by, released_designation, released_date, received_by, 
            received_designation, received_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $itr_stmt->bind_param("ssssssssssssssssss", 
            $header_image, $entity_name, $fund_cluster, $from_accountable_officer,
            $to_accountable_officer, $itr_no, $date, $final_transfer_type,
            $reason_for_transfer, $approved_by, $approved_designation, $approved_date,
            $released_by, $released_designation, $released_date, $received_by,
            $received_designation, $received_date);

        if (!$itr_stmt->execute()) {
            throw new Exception('Failed to save ITR form: ' . $itr_stmt->error);
        }

        // Get the new ITR ID
        $itr_id = $conn->insert_id;
        $itr_stmt->close();

        // No clearing of itr_items; we always insert for this new ITR

        // Insert ITR items
        $item_stmt = $conn->prepare("INSERT INTO itr_items 
            (itr_id, date_acquired, property_no, asset_id, description, amount, condition_of_PPE) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($valid_items as $item) {
            $item_stmt->bind_param("ississs", 
                $itr_id, 
                $item['date_acquired'], 
                $item['property_no'], 
                $item['asset_id'], 
                $item['description'], 
                $item['amount'], 
                $item['condition_of_PPE']
            );
            
            if (!$item_stmt->execute()) {
                throw new Exception('Failed to save ITR item: ' . $item_stmt->error);
            }
        }
        $item_stmt->close();

        // Update assets table: set end_user and optionally employee_id for transferred assets (per-asset)
        if (!empty($assets_to_update)) {
            if ($to_employee_id) {
                $update_assets_stmt = $conn->prepare("UPDATE assets SET end_user = ?, employee_id = ? WHERE id = ?");
                foreach ($assets_to_update as $aid_int) {
                    $update_assets_stmt->bind_param('sii', $end_user, $to_employee_id, $aid_int);
                    if (!$update_assets_stmt->execute()) {
                        throw new Exception('Failed to update asset assignments: ' . $update_assets_stmt->error);
                    }
                    // Optional diagnostics
                    if ($update_assets_stmt->affected_rows === 0) {
                        error_log("ITR: assets update affected 0 rows for asset_id={$aid_int}");
                    }
                }
                $update_assets_stmt->close();
            } else {
                // Update only end_user when employee_id couldn't be resolved
                $update_assets_stmt = $conn->prepare("UPDATE assets SET end_user = ? WHERE id = ?");
                foreach ($assets_to_update as $aid_int) {
                    $update_assets_stmt->bind_param('si', $end_user, $aid_int);
                    if (!$update_assets_stmt->execute()) {
                        throw new Exception('Failed to update asset end_user: ' . $update_assets_stmt->error);
                    }
                    if ($update_assets_stmt->affected_rows === 0) {
                        error_log("ITR: assets end_user-only update affected 0 rows for asset_id={$aid_int}");
                    }
                }
                $update_assets_stmt->close();
            }
        }

        // Update mr_details table: set person_accountable and end_user for transferred assets (per-asset)
        if (!empty($assets_to_update)) {
            $update_mr_stmt = $conn->prepare("UPDATE mr_details SET person_accountable = ?, end_user = ? WHERE asset_id = ?");
            $update_mr_by_tag_stmt = $conn->prepare("UPDATE mr_details SET person_accountable = ?, end_user = ? WHERE inventory_tag = ?");
            foreach ($assets_to_update as $aid_int) {
                $update_mr_stmt->bind_param('ssi', $to_accountable_officer, $end_user, $aid_int);
                if (!$update_mr_stmt->execute()) {
                    throw new Exception('Failed to update MR details: ' . $update_mr_stmt->error);
                }
                if ($update_mr_stmt->affected_rows === 0) {
                    // Try fallback by inventory tag if available
                    $tag = $asset_inventory_tags[$aid_int] ?? '';
                    if (!empty($tag)) {
                        $update_mr_by_tag_stmt->bind_param('sss', $to_accountable_officer, $end_user, $tag);
                        if (!$update_mr_by_tag_stmt->execute()) {
                            throw new Exception('Failed to update MR details by inventory_tag: ' . $update_mr_by_tag_stmt->error);
                        }
                        if ($update_mr_by_tag_stmt->affected_rows === 0) {
                            error_log("ITR: mr_details update affected 0 rows for asset_id={$aid_int} and inventory_tag={$tag}");
                        }
                    } else {
                        error_log("ITR: mr_details update affected 0 rows for asset_id={$aid_int} (no inventory_tag fallback)");
                    }
                }
            }
            $update_mr_stmt->close();
            $update_mr_by_tag_stmt->close();
        }

        // Log the ITR creation
        if (function_exists('logAssetActivity')) {
            $action = 'ITR_CREATE';
            $description = "ITR No: {$itr_no}, From: {$from_accountable_officer}, To: {$to_accountable_officer}, Assets: " . count($valid_items);
            logAssetActivity($action, $description, $itr_id, "Transfer Type: {$final_transfer_type}, End User: {$end_user}");
        }

        $conn->commit();

        // Set success message
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'ITR form saved successfully! Assets have been transferred to ' . htmlspecialchars($to_accountable_officer) . '.'
        ];

        // Redirect back to forms or dashboard
        if ($form_id > 0) {
            header("Location: forms.php?id=" . $form_id);
        } else {
            header("Location: dashboard.php");
        }
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        
        // Set error message
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Error saving ITR form: ' . $e->getMessage()
        ];

        // Redirect back to form
        if ($form_id > 0) {
            header("Location: forms.php?id=" . $form_id);
        } else {
            header("Location: itr_form.php");
        }
        exit();
    }
} else {
    // Invalid request method
    header("Location: dashboard.php");
    exit();
}
?>
