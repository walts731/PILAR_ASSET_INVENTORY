<?php
/**
 * Borrowing System Audit Logging Patches
 * 
 * This file contains audit logging code for asset borrowing and return operations
 */

// =============================================================================
// PATCH 1: borrow.php
// =============================================================================

// Add to top of file:
/*
require_once '../includes/audit_helper.php';
*/

// Add after successful borrowing operation:
/*
if ($stmt->execute()) {
    $borrow_id = $conn->insert_id;
    
    // Get asset and employee details for logging
    $asset_stmt = $conn->prepare("SELECT description FROM assets WHERE id = ?");
    $asset_stmt->bind_param("i", $asset_id);
    $asset_stmt->execute();
    $asset_result = $asset_stmt->get_result();
    $asset_data = $asset_result->fetch_assoc();
    $asset_stmt->close();
    
    $employee_stmt = $conn->prepare("SELECT name FROM employees WHERE id = ?");
    $employee_stmt->bind_param("i", $employee_id);
    $employee_stmt->execute();
    $employee_result = $employee_stmt->get_result();
    $employee_data = $employee_result->fetch_assoc();
    $employee_stmt->close();
    
    $asset_name = $asset_data['description'] ?? 'Unknown Asset';
    $employee_name = $employee_data['name'] ?? 'Unknown Employee';
    
    // Log borrowing activity
    $borrow_context = "Asset: {$asset_name}, Borrower: {$employee_name}, Due: {$due_date}";
    logUserActivity('BORROW', 'Asset Borrowing', $borrow_context, 'borrowed_assets', $borrow_id);
    
    // Existing success response
} else {
    // Log borrowing failure
    logErrorActivity('Asset Borrowing', "Failed to process borrow request for asset ID: {$asset_id}");
    
    // Existing error response
}
*/

// =============================================================================
// PATCH 2: borrowed_assets.php (Return functionality)
// =============================================================================

// Add to top of file:
/*
require_once '../includes/audit_helper.php';
*/

// Add for asset return operation (if handled in this file):
/*
if ($return_stmt->execute()) {
    // Get borrow details for logging
    $borrow_stmt = $conn->prepare("
        SELECT ba.id, a.description, e.name as employee_name, ba.borrow_date 
        FROM borrowed_assets ba 
        JOIN assets a ON ba.asset_id = a.id 
        JOIN employees e ON ba.employee_id = e.id 
        WHERE ba.id = ?
    ");
    $borrow_stmt->bind_param("i", $borrow_id);
    $borrow_stmt->execute();
    $borrow_result = $borrow_stmt->get_result();
    $borrow_data = $borrow_result->fetch_assoc();
    $borrow_stmt->close();
    
    $asset_name = $borrow_data['description'] ?? 'Unknown Asset';
    $employee_name = $borrow_data['employee_name'] ?? 'Unknown Employee';
    $borrow_date = $borrow_data['borrow_date'] ?? 'Unknown Date';
    
    // Log return activity
    $return_context = "Asset: {$asset_name}, Returned by: {$employee_name}, Borrowed: {$borrow_date}";
    logUserActivity('RETURN', 'Asset Borrowing', $return_context, 'borrowed_assets', $borrow_id);
    
    // Existing success response
} else {
    // Log return failure
    logErrorActivity('Asset Borrowing', "Failed to process return for borrow ID: {$borrow_id}");
    
    // Existing error response
}
*/

// =============================================================================
// PATCH 3: borrow_bulk.php (if exists)
// =============================================================================

// Add to top of file:
/*
require_once '../includes/audit_helper.php';
*/

// Add after successful bulk borrowing:
/*
// Log bulk borrowing operation
$borrowed_count = count($successfully_borrowed);
$employee_name = getEmployeeName($conn, $employee_id);
logBulkActivity('BORROW', $borrowed_count, "Bulk borrow to: {$employee_name}");
*/

// =============================================================================
// PATCH 4: borrow_requests.php (if it handles approvals)
// =============================================================================

// Add to top of file:
/*
require_once '../includes/audit_helper.php';
*/

// Add for borrow request approval:
/*
if ($approval_stmt->execute()) {
    // Get request details for logging
    $request_stmt = $conn->prepare("
        SELECT br.id, a.description, e.name as employee_name, br.status 
        FROM borrow_requests br 
        JOIN assets a ON br.asset_id = a.id 
        JOIN employees e ON br.employee_id = e.id 
        WHERE br.id = ?
    ");
    $request_stmt->bind_param("i", $request_id);
    $request_stmt->execute();
    $request_result = $request_stmt->get_result();
    $request_data = $request_result->fetch_assoc();
    $request_stmt->close();
    
    $asset_name = $request_data['description'] ?? 'Unknown Asset';
    $employee_name = $request_data['employee_name'] ?? 'Unknown Employee';
    $new_status = $request_data['status'] ?? 'Unknown Status';
    
    // Log request status change
    $request_context = "Asset: {$asset_name}, Requester: {$employee_name}, Status: {$new_status}";
    logUserActivity('UPDATE', 'Borrow Requests', $request_context, 'borrow_requests', $request_id);
    
    // Existing success response
} else {
    // Log approval failure
    logErrorActivity('Borrow Requests', "Failed to update borrow request ID: {$request_id}");
    
    // Existing error response
}
*/

// =============================================================================
// HELPER FUNCTIONS (Add to audit_helper.php if not already present)
// =============================================================================

/*
function getEmployeeName($conn, $employee_id) {
    if (!$employee_id) return 'Unknown Employee';
    $stmt = $conn->prepare("SELECT name FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data['name'] ?? 'Unknown Employee';
}

function getAssetDescription($conn, $asset_id) {
    if (!$asset_id) return 'Unknown Asset';
    $stmt = $conn->prepare("SELECT description FROM assets WHERE id = ?");
    $stmt->bind_param("i", $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data['description'] ?? 'Unknown Asset';
}
*/

/**
 * INSTALLATION INSTRUCTIONS:
 * 
 * For each borrowing system file:
 * 1. Add the audit helper require statement at the top
 * 2. Add the appropriate logging code after database operations
 * 3. Include relevant context (asset names, employee names, dates)
 * 4. Test each operation to ensure functionality is preserved
 * 5. Verify logs appear correctly in the audit trail
 * 
 * Files to modify:
 * - MAIN_ADMIN/borrow.php
 * - MAIN_ADMIN/borrowed_assets.php
 * - MAIN_ADMIN/borrow_bulk.php (if exists)
 * - MAIN_ADMIN/borrow_requests.php (if exists)
 * 
 * Expected Log Entries:
 * - "BORROW Asset Borrowing: Asset: Laptop Dell XPS 15, Borrower: John Doe, Due: 2025-10-01"
 * - "RETURN Asset Borrowing: Asset: Laptop Dell XPS 15, Returned by: John Doe, Borrowed: 2025-09-15"
 * - "Bulk BORROW: 5 items (Bulk borrow to: Jane Smith)"
 */
?>
