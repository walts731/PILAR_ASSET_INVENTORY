<?php
/**
 * User Management Audit Logging Patches
 * 
 * This file contains audit logging code for all user management operations
 */

// =============================================================================
// PATCH 1: add_user.php
// =============================================================================

// Add to top of file (after existing requires):
/*
require_once '../includes/audit_helper.php';
*/

// Add after successful user creation:
/*
if ($stmt->execute()) {
    $new_user_id = $conn->insert_id;
    
    // Get office name for logging
    $office_name = 'No Office';
    if ($office_id > 0) {
        $office_stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
        $office_stmt->bind_param("i", $office_id);
        $office_stmt->execute();
        $office_result = $office_stmt->get_result();
        if ($office_data = $office_result->fetch_assoc()) {
            $office_name = $office_data['office_name'];
        }
        $office_stmt->close();
    }
    
    // Log user creation
    $user_context = "Role: {$role}, Office: {$office_name}, Email: {$email}";
    logUserManagementActivity('CREATE', $username, $new_user_id, $user_context);
    
    // Existing success response
} else {
    // Log user creation failure
    logErrorActivity('User Management', "Failed to create user: {$username}");
    
    // Existing error response
}
*/

// =============================================================================
// PATCH 2: delete_user.php
// =============================================================================

// Add to top of file:
/*
require_once '../includes/audit_helper.php';
*/

// Add before user deletion:
/*
// Get user details before deletion
$user_details_stmt = $conn->prepare("
    SELECT u.username, u.role, u.email, o.office_name 
    FROM users u 
    LEFT JOIN offices o ON u.office_id = o.id 
    WHERE u.id = ?
");
$user_details_stmt->bind_param("i", $user_id);
$user_details_stmt->execute();
$user_details_result = $user_details_stmt->get_result();
$user_data = $user_details_result->fetch_assoc();
$user_details_stmt->close();

$target_username = $user_data['username'] ?? 'Unknown User';
$target_role = $user_data['role'] ?? 'Unknown Role';
$target_email = $user_data['email'] ?? 'No Email';
$target_office = $user_data['office_name'] ?? 'No Office';
*/

// Add after successful deletion:
/*
if ($delete_stmt->execute()) {
    // Log user deletion
    $deletion_context = "Role: {$target_role}, Office: {$target_office}, Email: {$target_email}";
    logUserManagementActivity('DELETE', $target_username, $user_id, $deletion_context);
    
    // Existing success response
} else {
    // Log deletion failure
    logErrorActivity('User Management', "Failed to delete user: {$target_username}");
    
    // Existing error response
}
*/

// =============================================================================
// PATCH 3: activate_user.php
// =============================================================================

// Add to top of file:
/*
require_once '../includes/audit_helper.php';
*/

// Add after successful activation:
/*
if ($stmt->execute()) {
    // Get username for logging
    $username_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $username_stmt->bind_param("i", $user_id);
    $username_stmt->execute();
    $username_result = $username_stmt->get_result();
    $username_data = $username_result->fetch_assoc();
    $username_stmt->close();
    
    $target_username = $username_data['username'] ?? 'Unknown User';
    
    // Log user activation
    logUserManagementActivity('ACTIVATE', $target_username, $user_id, 'Status changed to: active');
    
    // Existing success response
} else {
    logErrorActivity('User Management', "Failed to activate user ID: {$user_id}");
    // Existing error response
}
*/

// =============================================================================
// PATCH 4: deactivate_user.php
// =============================================================================

// Add to top of file:
/*
require_once '../includes/audit_helper.php';
*/

// Add after successful deactivation:
/*
if ($stmt->execute()) {
    // Get username for logging
    $username_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $username_stmt->bind_param("i", $user_id);
    $username_stmt->execute();
    $username_result = $username_stmt->get_result();
    $username_data = $username_result->fetch_assoc();
    $username_stmt->close();
    
    $target_username = $username_data['username'] ?? 'Unknown User';
    
    // Log user deactivation
    logUserManagementActivity('DEACTIVATE', $target_username, $user_id, 'Status changed to: inactive');
    
    // Existing success response
} else {
    logErrorActivity('User Management', "Failed to deactivate user ID: {$user_id}");
    // Existing error response
}
*/

/**
 * INSTALLATION INSTRUCTIONS:
 * 
 * For each user management file:
 * 1. Add the audit helper require statement at the top
 * 2. Add the appropriate logging code after database operations
 * 3. Test each operation to ensure functionality is preserved
 * 4. Verify logs appear correctly in the audit trail
 * 
 * Files to modify:
 * - MAIN_ADMIN/add_user.php
 * - MAIN_ADMIN/delete_user.php  
 * - MAIN_ADMIN/activate_user.php
 * - MAIN_ADMIN/deactivate_user.php
 */
?>
