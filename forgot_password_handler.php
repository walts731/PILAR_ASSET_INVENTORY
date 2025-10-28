<?php
/**
 * Forgot Password Handler
 * Handles AJAX requests for password reset functionality
 */

header('Content-Type: application/json');

require_once 'connect.php';
require_once 'includes/email_helper.php';
require_once 'includes/audit_helper.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    if (empty($_SERVER['HTTP_HOST']) || !preg_match('/^[a-z0-9.-]+$/i', $_SERVER['HTTP_HOST'])) {
        throw new Exception('Invalid request origin');
    }

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Please enter your email address.']);
        exit;
    }
    
    // Check if user exists and is active
    $stmt = $conn->prepare("SELECT id, username, email, fullname FROM users WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Don't reveal if username exists or not for security
        echo json_encode([
            'success' => true, 
            'message' => 'If your email exists, a password reset link has been sent to your registered email address.'
        ]);
        
        // Log failed attempt
        logAuthActivity('PASSWORD_RESET_FAILED', "Password reset attempted for non-existent or inactive email: {$email}");
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Generate secure reset token
    $token = bin2hex(random_bytes(32)); // 64 character token
    
    // Update user with reset token (use MySQL's DATE_ADD to avoid timezone issues)
    $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
    $updateStmt->bind_param("si", $token, $user['id']);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to generate reset token');
    }
    $updateStmt->close();
    
    // Send password reset email
    $emailResult = sendPasswordResetEmail($user['email'], $user['username'], $token);
    
    if ($emailResult['success']) {
        // Log successful password reset request
        logAuthActivity('PASSWORD_RESET_REQUESTED', "Password reset link sent to user: {$user['username']}", $user['id'], $user['username']);
        
        echo json_encode([
            'success' => true,
            'message' => 'A password reset link has been sent to your registered email address. Please check your inbox and follow the instructions.'
        ]);
    } else {
        // Log email failure but don't reveal details to user
        logAuthActivity('PASSWORD_RESET_EMAIL_FAILED', "Password reset email failed for user: {$user['username']} - {$emailResult['message']}", $user['id'], $user['username']);
        
        echo json_encode([
            'success' => false,
            'message' => 'Unable to send reset email at this time. Please try again later or contact your administrator.'
        ]);
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Password reset error: " . $e->getMessage());
    logErrorActivity('Password Reset', "Password reset system error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again later.'
    ]);
}

$conn->close();
?>
