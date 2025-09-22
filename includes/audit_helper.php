<?php
/**
 * Centralized Audit Logging Helper
 * 
 * This file provides a simple, consistent interface for logging user activities
 * across all modules in the PILAR Asset Inventory system.
 * 
 * Usage:
 * require_once 'includes/audit_helper.php';
 * logUserActivity('CREATE', 'Assets', 'Created new laptop asset', 'assets', 123);
 */

require_once __DIR__ . '/audit_logger.php';

/**
 * Log user activity with automatic user detection and context
 * 
 * @param string $action The action performed (CREATE, UPDATE, DELETE, LOGIN, etc.)
 * @param string $module The module/section where action occurred
 * @param string $details Descriptive details of the action
 * @param string|null $affected_table Database table affected (optional)
 * @param int|null $affected_id Record ID affected (optional)
 * @return bool Success status of logging operation
 */
function logUserActivity($action, $module, $details, $affected_table = null, $affected_id = null) {
    global $conn;
    
    // Ensure we have a database connection
    if (!isset($conn) || !$conn) {
        error_log("Audit Helper: No database connection available");
        return false;
    }
    
    // Get user information from session
    $user_id = $_SESSION['user_id'] ?? null;
    $username = 'System'; // Default for system operations
    
    if ($user_id) {
        try {
            $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $username = $row['fullname'];
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Audit Helper: Error fetching username - " . $e->getMessage());
            $username = 'Unknown User';
        }
    }
    
    try {
        $logger = new AuditLogger($conn);
        return $logger->log($user_id, $username, $action, $module, $details, $affected_table, $affected_id);
    } catch (Exception $e) {
        error_log("Audit Helper: Error logging activity - " . $e->getMessage());
        return false;
    }
}

/**
 * Log authentication events (login, logout, password changes)
 * 
 * @param string $action LOGIN, LOGOUT, LOGIN_FAILED, PASSWORD_RESET, etc.
 * @param string $details Descriptive details
 * @param int|null $user_id User ID (for failed logins, may be null)
 * @param string|null $username Username (for failed logins)
 * @return bool Success status
 */
function logAuthActivity($action, $details, $user_id = null, $username = null) {
    global $conn;
    
    if (!isset($conn) || !$conn) {
        error_log("Audit Helper: No database connection available");
        return false;
    }
    
    // For failed logins or when user_id is not in session
    if (!$user_id) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    if (!$username && $user_id) {
        try {
            $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $username = $row['fullname'];
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Audit Helper: Error fetching username - " . $e->getMessage());
            $username = 'Unknown User';
        }
    }
    
    try {
        $logger = new AuditLogger($conn);
        return $logger->log($user_id, $username ?? 'Unknown', $action, 'Authentication', $details);
    } catch (Exception $e) {
        error_log("Audit Helper: Error logging auth activity - " . $e->getMessage());
        return false;
    }
}

/**
 * Log asset-related activities with enhanced context
 * 
 * @param string $action CREATE, UPDATE, DELETE, BORROW, RETURN, etc.
 * @param string $asset_name Name/description of the asset
 * @param int|null $asset_id Asset ID
 * @param string $additional_context Additional context (office, employee, etc.)
 * @return bool Success status
 */
function logAssetActivity($action, $asset_name, $asset_id = null, $additional_context = '') {
    $details = "{$action} asset: {$asset_name}";
    if ($additional_context) {
        $details .= " ({$additional_context})";
    }
    
    return logUserActivity($action, 'Assets', $details, 'assets', $asset_id);
}

/**
 * Log user management activities
 * 
 * @param string $action CREATE, UPDATE, DELETE, ACTIVATE, DEACTIVATE
 * @param string $target_username Username of the user being managed
 * @param int|null $target_user_id User ID being managed
 * @param string $additional_context Additional context (role, office, etc.)
 * @return bool Success status
 */
function logUserManagementActivity($action, $target_username, $target_user_id = null, $additional_context = '') {
    $details = "{$action} user: {$target_username}";
    if ($additional_context) {
        $details .= " ({$additional_context})";
    }
    
    return logUserActivity($action, 'User Management', $details, 'users', $target_user_id);
}

/**
 * Log report generation activities
 * 
 * @param string $report_type Type of report generated
 * @param string $filters Any filters applied
 * @param int $record_count Number of records in report
 * @return bool Success status
 */
function logReportActivity($report_type, $filters = '', $record_count = 0) {
    $details = "Generated {$report_type} report";
    if ($record_count > 0) {
        $details .= " ({$record_count} records)";
    }
    if ($filters) {
        $details .= " with filters: {$filters}";
    }
    
    return logUserActivity('GENERATE', 'Reports', $details);
}

/**
 * Log system configuration changes
 * 
 * @param string $setting_type Type of setting changed (Category, Office, System, etc.)
 * @param string $setting_name Name of the setting
 * @param string $action CREATE, UPDATE, DELETE
 * @param int|null $setting_id ID of the setting record
 * @return bool Success status
 */
function logConfigActivity($setting_type, $setting_name, $action, $setting_id = null) {
    $details = "{$action} {$setting_type}: {$setting_name}";
    
    $table_map = [
        'Category' => 'categories',
        'Office' => 'offices',
        'Employee' => 'employees',
        'System' => 'system'
    ];
    
    $table = $table_map[$setting_type] ?? strtolower($setting_type);
    
    return logUserActivity($action, 'Configuration', $details, $table, $setting_id);
}

/**
 * Log bulk operations (imports, exports, bulk prints)
 * 
 * @param string $operation_type Type of bulk operation
 * @param int $item_count Number of items processed
 * @param string $additional_context Additional context
 * @return bool Success status
 */
function logBulkActivity($operation_type, $item_count, $additional_context = '') {
    $details = "Bulk {$operation_type}: {$item_count} items";
    if ($additional_context) {
        $details .= " ({$additional_context})";
    }
    
    return logUserActivity('BULK_' . strtoupper($operation_type), 'Bulk Operations', $details);
}

/**
 * Log error events for troubleshooting
 * 
 * @param string $module Module where error occurred
 * @param string $error_details Error description
 * @param string $error_context Additional context (file, line, etc.)
 * @return bool Success status
 */
function logErrorActivity($module, $error_details, $error_context = '') {
    $details = "Error in {$module}: {$error_details}";
    if ($error_context) {
        $details .= " ({$error_context})";
    }
    
    return logUserActivity('ERROR', $module, $details);
}

/**
 * Check if audit logging is enabled and available
 * 
 * @return bool True if logging is available
 */
function isAuditLoggingAvailable() {
    global $conn;
    
    if (!isset($conn) || !$conn) {
        return false;
    }
    
    try {
        // Check if audit_logs table exists
        $result = $conn->query("SHOW TABLES LIKE 'audit_logs'");
        return $result && $result->num_rows > 0;
    } catch (Exception $e) {
        error_log("Audit Helper: Error checking audit logs table - " . $e->getMessage());
        return false;
    }
}

/**
 * Get current user information for logging
 * 
 * @return array User information (id, username, ip, user_agent)
 */
function getCurrentUserContext() {
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Auto-initialize logging if session is active and database is available
if (session_status() === PHP_SESSION_ACTIVE && isset($conn) && isAuditLoggingAvailable()) {
    // Optionally log page access for high-security environments
    // Uncomment the following line if you want to log every page access
    // logUserActivity('ACCESS', 'Page Access', 'Accessed: ' . ($_SERVER['REQUEST_URI'] ?? 'Unknown Page'));
}
?>
