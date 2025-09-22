<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $role = $_POST['role'];
  $status = $_POST['status'];
  $office_id = $_POST['office_id'];

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

  // Password validation: at least 8 characters, 1 number, 1 uppercase, 1 lowercase
  if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    header("Location: user.php?user_add=weak_password");
    exit();
  }

  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Insert user
  $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password, role, status, office_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssssi", $fullname, $username, $email, $hashed_password, $role, $status, $office_id);

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
    $user_context = "Role: {$role}, Office: {$office_name}, Email: {$email}, Status: {$status}";
    logUserManagementActivity('CREATE', $username, $new_user_id, $user_context);
    
    header("Location: user.php?user_add=success");
    exit();
  } else {
    // Log user creation failure
    logErrorActivity('User Management', "Failed to create user: {$username}");
    
    header("Location: user.php?user_add=error");
    exit();
  }
}
?>
