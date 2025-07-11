<?php
require_once '../connect.php';
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
    header("Location: user.php?user_add=success");
    exit();
  } else {
    header("Location: user.php?user_add=error");
    exit();
  }
}
?>
