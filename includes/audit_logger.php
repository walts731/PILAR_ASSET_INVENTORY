<?php
/**
 * Audit Logger Utility
 * Provides functions for logging system activities
 */

require_once __DIR__ . '/../connect.php';

class AuditLogger {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Log system activity
     * 
     * @param int $user_id User ID performing the action
     * @param string $username Username performing the action
     * @param string $action Action performed (CREATE, UPDATE, DELETE, LOGIN, LOGOUT, etc.)
     * @param string $module Module/section where action occurred
     * @param string $details Detailed description of the action
     * @param string $affected_table Database table affected (optional)
     * @param int $affected_id ID of affected record (optional)
     * @param string $ip_address IP address (optional, will auto-detect)
     * @param string $user_agent User agent (optional, will auto-detect)
     */
    public function log($user_id, $username, $action, $module, $details, $affected_table = null, $affected_id = null, $ip_address = null, $user_agent = null) {
        try {
            // Auto-detect IP address if not provided
            if ($ip_address === null) {
                $ip_address = $this->getClientIP();
            }
            
            // Auto-detect user agent if not provided
            if ($user_agent === null) {
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            }
            
            $stmt = $this->conn->prepare("
                INSERT INTO audit_logs 
                (user_id, username, action, module, details, affected_table, affected_id, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("isssssiss", 
                $user_id, 
                $username, 
                $action, 
                $module, 
                $details, 
                $affected_table, 
                $affected_id, 
                $ip_address, 
                $user_agent
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Audit Log Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    /**
     * Quick logging methods for common actions
     */
    public function logLogin($user_id, $username) {
        return $this->log($user_id, $username, 'LOGIN', 'Authentication', 'User successfully logged into the system');
    }
    
    public function logLogout($user_id, $username) {
        return $this->log($user_id, $username, 'LOGOUT', 'Authentication', 'User logged out of the system');
    }
    
    public function logAssetCreate($user_id, $username, $asset_id, $asset_description) {
        return $this->log($user_id, $username, 'CREATE', 'Assets', "Created new asset: {$asset_description}", 'assets', $asset_id);
    }
    
    public function logAssetUpdate($user_id, $username, $asset_id, $details) {
        return $this->log($user_id, $username, 'UPDATE', 'Assets', $details, 'assets', $asset_id);
    }
    
    public function logAssetDelete($user_id, $username, $asset_id, $asset_description) {
        return $this->log($user_id, $username, 'DELETE', 'Assets', "Deleted asset: {$asset_description}", 'assets', $asset_id);
    }
    
    public function logRedTagCreate($user_id, $username, $red_tag_id, $details) {
        return $this->log($user_id, $username, 'CREATE', 'Red Tags', $details, 'red_tags', $red_tag_id);
    }
    
    public function logICSCreate($user_id, $username, $ics_id, $details) {
        return $this->log($user_id, $username, 'CREATE', 'ICS Form', $details, 'ics_form', $ics_id);
    }
    
    public function logReportGenerate($user_id, $username, $report_type) {
        return $this->log($user_id, $username, 'GENERATE', 'Reports', "Generated {$report_type} report");
    }
    
    public function logBulkPrint($user_id, $username, $module, $count, $type) {
        return $this->log($user_id, $username, 'PRINT', $module, "Bulk printed {$count} {$type}");
    }
    
    public function logUserCreate($user_id, $username, $new_user_id, $new_username) {
        return $this->log($user_id, $username, 'CREATE', 'Users', "Created new user: {$new_username}", 'users', $new_user_id);
    }
    
    public function logSettingsUpdate($user_id, $username, $setting_details) {
        return $this->log($user_id, $username, 'UPDATE', 'Settings', $setting_details, 'system');
    }
}

// Global function for easy access
function logActivity($user_id, $username, $action, $module, $details, $affected_table = null, $affected_id = null) {
    global $conn;
    $logger = new AuditLogger($conn);
    return $logger->log($user_id, $username, $action, $module, $details, $affected_table, $affected_id);
}
?>
