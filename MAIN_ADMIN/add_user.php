<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
require_once '../includes/email_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $role = $_POST['role'];
  $status = $_POST['status'];
  $office_id = $_POST['office_id'];
  $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [];

  // Check for empty fields
  if (!$fullname || !$username || !$email || !$password || !$role || !$status || !$office_id) {
    header("Location: user.php?user_add=empty");
    exit();
  }

  // Check if username already exists
  $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $checkStmt->bind_param("s", $username);
  $checkStmt->execute();
  $checkStmt->store_result();

  if ($checkStmt->num_rows > 0) {
    header("Location: user.php?user_add=duplicate");
    exit();
  }
  $checkStmt->close();

  // Password validation: at least 12 chars, 1 number, 1 uppercase, 1 lowercase, 1 special (align with UI generation)
  if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{12,}$/', $password)) {
    header("Location: user.php?user_add=weak_password");
    exit();
  }

  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Ensure permissions table exists
  $conn->query("CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    PRIMARY KEY (user_id, permission),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

  // Insert user
  $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password, role, status, office_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssssi", $fullname, $username, $email, $hashed_password, $role, $status, $office_id);

  if ($stmt->execute()) {
    $new_user_id = $conn->insert_id;
    
    // Get office name for logging and email
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
    
    // Insert permissions for non-admin roles
    if (!in_array($role, ['admin','office_admin'], true) && !empty($permissions)) {
      $permStmt = $conn->prepare("INSERT IGNORE INTO user_permissions (user_id, permission) VALUES (?, ?)");
      foreach ($permissions as $perm) {
        $perm = trim($perm);
        if ($perm === '') continue;
        $permStmt->bind_param('is', $new_user_id, $perm);
        $permStmt->execute();
      }
      $permStmt->close();
    }

    // Send welcome email to the new user
    $email_result = sendWelcomeEmail($email, $fullname, $username, $password, $role, $office_name);
    
    // Log user creation with email status
    $perm_list = !empty($permissions) ? (implode(',', $permissions)) : 'none';
    $email_status = $email_result['success'] ? 'Email sent' : 'Email failed';
    $user_context = "Role: {$role}, Office: {$office_name}, Email: {$email}, Status: {$status}, Perms: {$perm_list}, {$email_status}";
    logUserManagementActivity('CREATE', $username, $new_user_id, $user_context);
    
    // Redirect with appropriate message
    if ($email_result['success']) {
        header("Location: user.php?user_add=success&email=sent");
    } else {
        header("Location: user.php?user_add=success&email=failed");
    }
    exit();
  } else {
    // Log user creation failure
    logErrorActivity('User Management', "Failed to create user: {$username}");
    
    header("Location: user.php?user_add=error");
    exit();
  }
}
?>
