<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the current hashed password from the database
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_hashed_password);
$stmt->fetch();
$stmt->close();

// Sanitize inputs
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: profile.php");
    exit();
}

// Check if current password matches
if (!password_verify($current_password, $current_hashed_password)) {
    $_SESSION['error'] = "Current password is incorrect.";
    header("Location: profile.php");
    exit();
}

// Check if new password and confirmation match
if ($new_password !== $confirm_password) {
    $_SESSION['error'] = "New passwords do not match.";
    header("Location: profile.php");
    exit();
}

// Optional: Enforce password strength (same pattern as HTML form)
$pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/';
if (!preg_match($pattern, $new_password)) {
    $_SESSION['error'] = "Password must be at least 6 characters and include uppercase, lowercase, number, and special character.";
    header("Location: profile.php");
    exit();
}

// Hash new password
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in the database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $new_hashed_password, $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Password updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update password. Please try again.";
}

$stmt->close();
$conn->close();

header("Location: profile.php");
exit();
?>
