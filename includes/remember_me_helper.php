<?php
/**
 * Remember Me Helper Functions
 * Handles secure remember me token generation, validation, and cleanup
 */

/**
 * Generate a secure random token
 * @return string
 */
function generateRememberToken() {
    return bin2hex(random_bytes(32)); // 64 character hex string
}

/**
 * Create a remember me token for a user
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $days_valid Number of days the token should be valid (default: 30)
 * @return string|false Token string on success, false on failure
 */
function createRememberToken($conn, $user_id, $days_valid = 30) {
    try {
        $token = generateRememberToken();
        $expires_at = date('Y-m-d H:i:s', strtotime("+{$days_valid} days"));
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Clean up old tokens for this user (keep only the 3 most recent)
        $cleanup_stmt = $conn->prepare("
            DELETE FROM remember_tokens 
            WHERE user_id = ? AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM remember_tokens 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 3
                ) AS recent_tokens
            )
        ");
        $cleanup_stmt->bind_param("ii", $user_id, $user_id);
        $cleanup_stmt->execute();
        $cleanup_stmt->close();
        
        // Insert new token
        $stmt = $conn->prepare("
            INSERT INTO remember_tokens (user_id, token, expires_at, user_agent, ip_address) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $user_id, $token, $expires_at, $user_agent, $ip_address);
        
        if ($stmt->execute()) {
            $stmt->close();
            return $token;
        }
        
        $stmt->close();
        return false;
    } catch (Exception $e) {
        error_log("Remember token creation failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate and use a remember me token
 * @param mysqli $conn Database connection
 * @param string $token Token to validate
 * @return array|false User data on success, false on failure
 */
function validateRememberToken($conn, $token) {
    try {
        // Get token with user data
        $stmt = $conn->prepare("
            SELECT rt.user_id, rt.id as token_id, u.username, u.role, u.office_id, u.status
            FROM remember_tokens rt
            JOIN users u ON rt.user_id = u.id
            WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status = 'active'
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            $stmt->close();
            
            // Update last_used timestamp
            $update_stmt = $conn->prepare("UPDATE remember_tokens SET last_used = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $data['token_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            return $data;
        }
        
        $stmt->close();
        return false;
    } catch (Exception $e) {
        error_log("Remember token validation failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a specific remember me token
 * @param mysqli $conn Database connection
 * @param string $token Token to delete
 * @return bool Success status
 */
function deleteRememberToken($conn, $token) {
    try {
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    } catch (Exception $e) {
        error_log("Remember token deletion failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete all remember me tokens for a user (logout from all devices)
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return bool Success status
 */
function deleteAllUserTokens($conn, $user_id) {
    try {
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    } catch (Exception $e) {
        error_log("User tokens deletion failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Clean up expired tokens (should be run periodically)
 * @param mysqli $conn Database connection
 * @return int Number of tokens cleaned up
 */
function cleanupExpiredTokens($conn) {
    try {
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
        $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $affected_rows;
    } catch (Exception $e) {
        error_log("Token cleanup failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Set remember me cookie
 * @param string $token Token value
 * @param int $days_valid Number of days the cookie should be valid
 * @return bool Success status
 */
function setRememberCookie($token, $days_valid = 30) {
    $expire_time = time() + ($days_valid * 24 * 60 * 60);
    return setcookie('remember_token', $token, [
        'expires' => $expire_time,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']), // Only send over HTTPS if available
        'httponly' => true, // Prevent JavaScript access
        'samesite' => 'Strict' // CSRF protection
    ]);
}

/**
 * Clear remember me cookie
 * @return bool Success status
 */
function clearRememberCookie() {
    return setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}
?>
