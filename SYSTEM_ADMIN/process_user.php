<?php
session_start();
require_once '../connect.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Verify user has permission to manage users
$has_permission = false;
$user_id = $_SESSION['user_id'];
$permission_check = $conn->prepare("
    SELECT 1 FROM users u
    LEFT JOIN user_permissions up ON u.id = up.user_id
    WHERE u.id = ? AND (u.role = 'super_admin' OR up.permission = 'manage_users')
    LIMIT 1
");

if ($permission_check) {
    $permission_check->bind_param('i', $user_id);
    $permission_check->execute();
    $permission_check->store_result();
    $has_permission = $permission_check->num_rows > 0;
    $permission_check->close();
}

if (!$has_permission) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to perform this action.']);
    exit();
}

// Get action from request
$action = $_POST['action'] ?? '';

// Process based on action
switch ($action) {
    case 'add_user':
        addUser($conn);
        break;
    case 'update_user':
        updateUser($conn);
        break;
    case 'update_user_status':
        updateUserStatus($conn);
        break;
    case 'reset_password':
        resetPassword($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

/**
 * Add a new user
 */
function addUser($conn) {
    // Validate required fields
    $required = ['fullname', 'username', 'email', 'role'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit();
        }
    }

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $sendWelcomeEmail = isset($_POST['send_welcome_email']) && $_POST['send_welcome_email'] === 'on';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists.']);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Generate a random password
    $password = generateRandomPassword();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param('sssss', $fullname, $username, $email, $hashedPassword, $role);
        $stmt->execute();
        $newUserId = $conn->insert_id;
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Send welcome email if requested
        if ($sendWelcomeEmail) {
            // In a real application, you would send an email here
            // For now, we'll just log it
            error_log("Welcome email would be sent to: $email with password: $password");
        }

        echo json_encode([
            'success' => true, 
            'message' => 'User added successfully.' . ($sendWelcomeEmail ? ' Welcome email sent.' : '')
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error adding user: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error adding user. Please try again.']);
    }
}

/**
 * Update an existing user
 */
function updateUser($conn) {
    // Validate required fields
    if (empty($_POST['user_id']) || empty($_POST['fullname']) || empty($_POST['username']) || empty($_POST['email']) || empty($_POST['role'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    $userId = (int)$_POST['user_id'];
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $status = !empty($_POST['status']) ? trim($_POST['status']) : 'active';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit();
    }

    // Check if username already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param('si', $username, $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists.']);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Check if email already exists (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        $stmt->close();
        exit();
    }
    $stmt->close();

    try {
        // Update user
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('sssssi', $fullname, $username, $email, $role, $status, $userId);
        $stmt->execute();
        $rowsAffected = $stmt->affected_rows;
        $stmt->close();

        if ($rowsAffected > 0) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made.']);
        }

    } catch (Exception $e) {
        error_log("Error updating user: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating user. Please try again.']);
    }
}

/**
 * Update user status (active/inactive)
 */
function updateUserStatus($conn) {
    if (empty($_POST['user_id']) || !isset($_POST['status'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit();
    }

    $userId = (int)$_POST['user_id'];
    $status = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'inactive';

    // Prevent deactivating own account
    if ($userId === $_SESSION['user_id'] && $status === 'inactive') {
        echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account.']);
        exit();
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $userId);
        $stmt->execute();
        $rowsAffected = $stmt->affected_rows;
        $stmt->close();

        if ($rowsAffected > 0) {
            $action = $status === 'active' ? 'activated' : 'deactivated';
            echo json_encode(['success' => true, 'message' => "User $action successfully."]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found or no changes made.']);
        }

    } catch (Exception $e) {
        error_log("Error updating user status: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating user status. Please try again.']);
    }
}

/**
 * Reset user password
 */
function resetPassword($conn) {
    if (empty($_POST['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit();
    }

    $userId = (int)$_POST['user_id'];
    $newPassword = !empty($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $forcePasswordChange = isset($_POST['force_password_change']) && $_POST['force_password_change'] === 'on';

    // Generate a random password if none provided
    if (empty($newPassword)) {
        $newPassword = generateRandomPassword();
    }

    // Validate password strength
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    try {
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ?, force_password_change = ? WHERE id = ?");
        $forcePasswordChangeInt = $forcePasswordChange ? 1 : 0;
        $stmt->bind_param('sii', $hashedPassword, $forcePasswordChangeInt, $userId);
        $stmt->execute();
        $rowsAffected = $stmt->affected_rows;
        $stmt->close();

        if ($rowsAffected > 0) {
            // In a real application, you would send an email to the user with the new password
            // For now, we'll just log it
            error_log("Password reset for user ID $userId. New password: $newPassword");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Password reset successfully. ' . 
                           ($forcePasswordChange ? 'User will be required to change password on next login.' : '')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }

    } catch (Exception $e) {
        error_log("Error resetting password: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error resetting password. Please try again.']);
    }
}

/**
 * Generate a random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
    $password = '';
    $charsLength = strlen($chars);
    
    // Ensure at least one of each character type
    $password .= 'abcdefghijklmnopqrstuvwxyz'[rand(0, 25)]; // lowercase
    $password .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[rand(0, 25)]; // uppercase
    $password .= '0123456789'[rand(0, 9)]; // number
    $password .= '!@#$%^&*()_+-=[]{}|;:,.<>?'[rand(0, 23)]; // special char
    
    // Fill the rest with random characters
    for ($i = 4; $i < $length; $i++) {
        $password .= $chars[rand(0, $charsLength - 1)];
    }
    
    // Shuffle the password to make it more random
    return str_shuffle($password);
}
?>
