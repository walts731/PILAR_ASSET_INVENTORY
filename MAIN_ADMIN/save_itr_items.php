<?php
require_once '../connect.php';
require_once '../includes/audit_logger.php';
require_once '../includes/lifecycle_helper.php';
require_once '../includes/tag_format_helper.php';
require_once '../includes/email_helper.php';
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
        // Generate automatic ITR number
        $itr_no = generateTag('itr_no');
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
        $to_office_id = isset($_POST['office_id']) && $_POST['office_id'] !== '' ? (int)$_POST['office_id'] : null;

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
        // Optional: asset IDs posted by itr_form hidden inputs per row
        $asset_id_array = $_POST['asset_id'] ?? [];

        // Validate that we have items
        if (empty($date_acquired_array) || count($date_acquired_array) == 0) {
            throw new Exception('Please add at least one item to the ITR.');
        }

        // Extract asset IDs from descriptions and validate
        $assets_to_update = [];
        $asset_inventory_tags = [];
        $asset_propnos = []; // map asset_id => property_no for email context
        $valid_items = [];
        $from_employee_map = [];
        
        for ($i = 0; $i < count($date_acquired_array); $i++) {
            if (!empty($description_array[$i])) {
                // Resolve asset_id based on description-first logic
                $asset_id = null;
                $desc = trim((string)$description_array[$i]);
                $propNo = trim((string)($property_no_array[$i] ?? ''));

                // 1) If hidden asset_id[] was posted, validate and use it
                if (isset($asset_id_array[$i]) && !empty($asset_id_array[$i])) {
                    $candidate = (int)$asset_id_array[$i];
                    $chk = $conn->prepare("SELECT id, employee_id, inventory_tag FROM assets WHERE id = ? AND type = 'asset' LIMIT 1");
                    $chk->bind_param('i', $candidate);
                    $chk->execute();
                    $cr = $chk->get_result();
                    if ($cr && $cr->num_rows === 1) {
                        $r = $cr->fetch_assoc();
                        $asset_id = (int)$r['id'];
                        $current_employee_id = $r['employee_id'];
                        $asset_inventory_tags[$asset_id] = $r['inventory_tag'] ?? '';
                        $from_employee_map[$asset_id] = $current_employee_id;
                    }
                    $chk->close();
                }

                // 2) Parse description in format "Description (PROPERTY_NO)"
                if (!$asset_id && preg_match('/\(([^)]+)\)$/', $desc, $m)) {
                    $extracted = trim($m[1]);
                    $s1 = $conn->prepare("SELECT id, employee_id, inventory_tag FROM assets WHERE property_no = ? AND type = 'asset' LIMIT 1");
                    $s1->bind_param('s', $extracted);
                    $s1->execute();
                    $res1 = $s1->get_result();
                    if ($res1 && $res1->num_rows === 1) {
                        $ar = $res1->fetch_assoc();
                        $asset_id = (int)$ar['id'];
                        $current_employee_id = $ar['employee_id'];
                        $asset_inventory_tags[$asset_id] = $ar['inventory_tag'] ?? '';
                        $from_employee_map[$asset_id] = $current_employee_id;
                    }
                    $s1->close();
                }

                // 3) Fallback to posted property_no[]
                if (!$asset_id && !empty($propNo)) {
                    $s2 = $conn->prepare("SELECT id, employee_id, inventory_tag FROM assets WHERE property_no = ? AND type = 'asset' LIMIT 1");
                    $s2->bind_param('s', $propNo);
                    $s2->execute();
                    $res2 = $s2->get_result();
                    if ($res2 && $res2->num_rows === 1) {
                        $ar2 = $res2->fetch_assoc();
                        $asset_id = (int)$ar2['id'];
                        $current_employee_id = $ar2['employee_id'];
                        $asset_inventory_tags[$asset_id] = $ar2['inventory_tag'] ?? '';
                        $from_employee_map[$asset_id] = $current_employee_id;
                    }
                    $s2->close();
                }

                // 4) Last resort: exact description match if unique
                if (!$asset_id) {
                    $s3 = $conn->prepare("SELECT id, employee_id, inventory_tag FROM assets WHERE description = ? AND type = 'asset' LIMIT 2");
                    $s3->bind_param('s', $desc);
                    $s3->execute();
                    $res3 = $s3->get_result();
                    if ($res3 && $res3->num_rows === 1) {
                        $ar3 = $res3->fetch_assoc();
                        $asset_id = (int)$ar3['id'];
                        $current_employee_id = $ar3['employee_id'];
                        $asset_inventory_tags[$asset_id] = $ar3['inventory_tag'] ?? '';
                        $from_employee_map[$asset_id] = $current_employee_id;
                    }
                    $s3->close();
                }

                if ($asset_id) {
                    $assets_to_update[] = $asset_id;
                    $valid_items[] = [
                        'date_acquired' => $date_acquired_array[$i] ?? '',
                        'property_no' => $propNo,
                        'asset_id' => $asset_id,
                        'description' => $desc,
                        'amount' => $amount_array[$i] ?? 0,
                        'condition_of_PPE' => $condition_array[$i] ?? ''
                    ];
                    $asset_propnos[$asset_id] = $propNo;
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
                if ($to_office_id) {
                    $update_assets_stmt = $conn->prepare("UPDATE assets SET end_user = ?, employee_id = ?, office_id = ? WHERE id = ?");
                } else {
                    $update_assets_stmt = $conn->prepare("UPDATE assets SET end_user = ?, employee_id = ? WHERE id = ?");
                }
                foreach ($assets_to_update as $aid_int) {
                    if ($to_office_id) {
                        $update_assets_stmt->bind_param('siii', $end_user, $to_employee_id, $to_office_id, $aid_int);
                    } else {
                        $update_assets_stmt->bind_param('sii', $end_user, $to_employee_id, $aid_int);
                    }
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
                if ($to_office_id) {
                    $update_assets_stmt = $conn->prepare("UPDATE assets SET end_user = ?, office_id = ? WHERE id = ?");
                } else {
                    $update_assets_stmt = $conn->prepare("UPDATE assets SET end_user = ? WHERE id = ?");
                }
                foreach ($assets_to_update as $aid_int) {
                    if ($to_office_id) {
                        $update_assets_stmt->bind_param('sii', $end_user, $to_office_id, $aid_int);
                    } else {
                        $update_assets_stmt->bind_param('si', $end_user, $aid_int);
                    }
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

        // Lifecycle logging for office change per asset (TRANSFERRED with office context)
        if (function_exists('logLifecycleEvent') && !empty($assets_to_update)) {
            foreach ($assets_to_update as $aid_int) {
                $from_emp = $from_employee_map[$aid_int] ?? null;
                // Resolve previous office_id for from_office_id
                $prev_office_id = null;
                if ($stPrev = $conn->prepare("SELECT office_id FROM assets WHERE id = ? LIMIT 1")) {
                    $stPrev->bind_param('i', $aid_int);
                    $stPrev->execute();
                    $rsPrev = $stPrev->get_result();
                    if ($rsPrev && ($rPrev = $rsPrev->fetch_assoc())) { $prev_office_id = $rPrev['office_id'] ?? null; }
                    $stPrev->close();
                }
                // Only log office IDs when destination office is provided
                $note = sprintf('ITR %s; Reason: %s; To: %s', (string)$itr_no, (string)$reason_for_transfer, (string)$to_accountable_officer);
                logLifecycleEvent((int)$aid_int, 'TRANSFERRED', 'itr_form', (int)$itr_id, $from_emp ? (int)$from_emp : null, $to_employee_id ? (int)$to_employee_id : null, $prev_office_id ? (int)$prev_office_id : null, $to_office_id ? (int)$to_office_id : null, $note);
            }
        }

        // Log the ITR creation
        if (function_exists('logAssetActivity')) {
            $action = 'ITR_CREATE';
            $description = "ITR No: {$itr_no}, From: {$from_accountable_officer}, To: {$to_accountable_officer}, Assets: " . count($valid_items);
            logAssetActivity($action, $description, $itr_id, "Transfer Type: {$final_transfer_type}, End User: {$end_user}");
        }

        // (Removed duplicate lifecycle logging - now handled above with office context)

        $conn->commit();

        // --- Email notifications (post-commit) ---
        // Helpers for email logging table reuse
        if (!function_exists('ensureEmailNotificationsTable_bulk_itr')) {
            function ensureEmailNotificationsTable_bulk_itr(mysqli $conn) {
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
        }

        if (!function_exists('logEmail_itr')) {
            function logEmail_itr(mysqli $conn, array $data) {
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
        }

        if (!function_exists('sendItrEmail')) {
            function sendItrEmail(mysqli $conn, $employeeId, $personName, $direction, $itrId, $itrNo, $assetId, $inventoryTag, $propertyNo, $description, $reason, $dateStr, $otherOfficerName) {
                ensureEmailNotificationsTable_bulk_itr($conn);
                // resolve email
                $recipientEmail = null;
                if (!empty($employeeId)) {
                    if ($st = $conn->prepare("SELECT email FROM employees WHERE employee_id = ? LIMIT 1")) {
                        $st->bind_param('i', $employeeId);
                        $st->execute();
                        $rs = $st->get_result();
                        if ($rs && ($row = $rs->fetch_assoc())) { $recipientEmail = $row['email'] ?? null; }
                        $st->close();
                    }
                }

                // Resolve asset office and serial_no for richer context
                $officeName = '';
                $serialNoVal = '';
                if (!empty($assetId)) {
                    if ($stA = $conn->prepare("SELECT a.serial_no, o.office_name FROM assets a LEFT JOIN offices o ON o.id = a.office_id WHERE a.id = ? LIMIT 1")) {
                        $stA->bind_param('i', $assetId);
                        $stA->execute();
                        $rsA = $stA->get_result();
                        if ($rsA && ($rowA = $rsA->fetch_assoc())) {
                            $serialNoVal = $rowA['serial_no'] ?? '';
                            $officeName = $rowA['office_name'] ?? '';
                        }
                        $stA->close();
                    }
                }

                $subject = 'ITR Asset Transfer Notification (' . strtoupper((string)$direction) . ')';
                $counterpartyLabel = ($direction === 'to') ? 'From Accountable Officer' : 'To Accountable Officer';
                $body = "Hello " . htmlspecialchars((string)$personName) . ",<br><br>"
                      . "An asset has been transferred " . htmlspecialchars((string)$direction) . " you via ITR.<br>"
                      . "<ul>"
                      . "<li><strong>ITR No.:</strong> " . htmlspecialchars((string)$itrNo) . "</li>"
                      . "<li><strong>Date:</strong> " . htmlspecialchars((string)$dateStr) . "</li>"
                      . "<li><strong>Reason:</strong> " . htmlspecialchars((string)$reason) . "</li>"
                      . "<li><strong>" . htmlspecialchars($counterpartyLabel) . ":</strong> " . htmlspecialchars((string)$otherOfficerName) . "</li>"
                      . "<li><strong>Office:</strong> " . htmlspecialchars((string)$officeName) . "</li>"
                      . "<li><strong>Inventory Tag:</strong> " . htmlspecialchars((string)$inventoryTag) . "</li>"
                      . "<li><strong>Description:</strong> " . htmlspecialchars((string)$description) . "</li>"
                      . "<li><strong>Property No.:</strong> " . htmlspecialchars((string)$propertyNo) . "</li>"
                      . "<li><strong>Serial No.:</strong> " . htmlspecialchars((string)$serialNoVal) . "</li>"
                      . "</ul>"
                      . "If this was not expected, please contact your system administrator.";

                $log = [
                    'type' => 'ITR_TRANSFER',
                    'recipient_email' => $recipientEmail,
                    'recipient_name' => $personName,
                    'subject' => $subject,
                    'body' => $body,
                    'status' => 'queued',
                    'error_message' => null,
                    'related_asset_id' => !empty($assetId) ? (int)$assetId : null,
                    'related_mr_id' => !empty($itrId) ? (int)$itrId : null,
                ];

                if (!empty($recipientEmail)) {
                    try {
                        $mail = configurePHPMailer();
                        $mail->addAddress($recipientEmail, (string)$personName);
                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body = $body;
                        $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','<br />'], "\n", $body));
                        $mail->send();
                        $log['status'] = 'sent';
                    } catch (Throwable $e) {
                        $log['status'] = 'failed';
                        $log['error_message'] = $e->getMessage();
                    }
                } else {
                    $log['status'] = 'no_email';
                }

                logEmail_itr($conn, $log);
            }
        }

        // Send emails per asset to FROM and TO officers
        foreach ($assets_to_update as $aid_int) {
            // Initialize per-asset previous owner name for safe use below
            $fromName = '';
            $propNo = $asset_propnos[$aid_int] ?? '';
            $invTag = $asset_inventory_tags[$aid_int] ?? '';
            // Match description from valid_items
            $desc = '';
            foreach ($valid_items as $vi) {
                if ((int)$vi['asset_id'] === (int)$aid_int) { $desc = $vi['description']; break; }
            }
            // FROM officer (current owner before transfer)
            $fromEmpId = $from_employee_map[$aid_int] ?? null;
            if (!empty($fromEmpId)) {
                // Resolve name
                $fromName = '';
                if ($stn = $conn->prepare("SELECT name FROM employees WHERE employee_id = ? LIMIT 1")) {
                    $stn->bind_param('i', $fromEmpId);
                    $stn->execute();
                    $rsn = $stn->get_result();
                    if ($rsn && ($rowN = $rsn->fetch_assoc())) { $fromName = $rowN['name'] ?? ''; }
                    $stn->close();
                }
                // Email to FROM officer should include TO officer's name
                sendItrEmail($conn, $fromEmpId, $fromName, 'from', $itr_id, $itr_no, $aid_int, $invTag, $propNo, $desc, $reason_for_transfer, $date, $to_accountable_officer);
            }
            // TO officer (new owner)
            if (!empty($to_employee_id)) {
                // Email to TO officer should include FROM officer's name
                // Prefer resolved per-asset previous owner name if available, otherwise use form's from_accountable_officer
                $fromOfficerForEmail = !empty($fromName) ? $fromName : $from_accountable_officer;
                sendItrEmail($conn, $to_employee_id, $to_accountable_officer, 'to', $itr_id, $itr_no, $aid_int, $invTag, $propNo, $desc, $reason_for_transfer, $date, $fromOfficerForEmail);
            }
        }

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
