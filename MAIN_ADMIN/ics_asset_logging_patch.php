<?php
/**
 * ICS Asset Creation Logging Patch
 * 
 * This code needs to be added to the createItemAssetsDirect function in save_ics_items.php
 * Insert this code after line 616 ($stmtUpd->close();) and before the closing brace of the if statement
 */

// Log asset creation (only log if we have session data available)
if (isset($_SESSION['user_id'])) {
    $logger = new AuditLogger($conn);
    $user_stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $username = $user_result->fetch_assoc()['fullname'] ?? 'System';
    $user_stmt->close();
    
    $office_name = 'Main Stock';
    if ($p_office) {
        $office_stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
        $office_stmt->bind_param("i", $p_office);
        $office_stmt->execute();
        $office_result = $office_stmt->get_result();
        if ($office_data = $office_result->fetch_assoc()) {
            $office_name = $office_data['office_name'];
        }
        $office_stmt->close();
    }
    
    $asset_details = "Created asset via ICS: {$description} (ID: {$new_item_id}, Value: ₱" . number_format($p_value, 2) . ", Office: {$office_name})";
    $logger->logAssetCreate($_SESSION['user_id'], $username, $new_item_id, $asset_details);
}

/**
 * INSTALLATION INSTRUCTIONS:
 * 
 * 1. Open save_ics_items.php
 * 2. Find the createItemAssetsDirect function (around line 552)
 * 3. Locate this block of code (around line 612-616):
 *    
 *    // Update the asset with its qr_code filename
 *    $stmtUpd = $conn->prepare("UPDATE assets SET qr_code = ? WHERE id = ?");
 *    $stmtUpd->bind_param("si", $qr_filename, $new_item_id);
 *    $stmtUpd->execute();
 *    $stmtUpd->close();
 * 
 * 4. Add the logging code from above right after $stmtUpd->close(); and before the closing brace }
 * 
 * The final code should look like:
 * 
 *    $stmtUpd->close();
 *    
 *    // Log asset creation (only log if we have session data available)
 *    if (isset($_SESSION['user_id'])) {
 *        // ... logging code here ...
 *    }
 * }
 */
?>
