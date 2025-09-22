<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !isset($_GET['office'])) {
  header("Location: user.php");
  exit();
}

$user_id = intval($_GET['id']);
$office_id = intval($_GET['office']); // capture office ID

// Get user details before deletion
$user_details_stmt = $conn->prepare("
    SELECT u.username, u.fullname, u.role, u.email, o.office_name 
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
$target_fullname = $user_data['fullname'] ?? 'Unknown Name';
$target_role = $user_data['role'] ?? 'Unknown Role';
$target_email = $user_data['email'] ?? 'No Email';
$target_office = $user_data['office_name'] ?? 'No Office';

// Attempt deletion
try {
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    // Log user deletion
    $deletion_context = "Full Name: {$target_fullname}, Role: {$target_role}, Office: {$target_office}, Email: {$target_email}";
    logUserManagementActivity('DELETE', $target_username, $user_id, $deletion_context);
    
    header("Location: user.php?office={$office_id}&delete=success");
  } else {
    // Log deletion failure - user not found or locked
    logErrorActivity('User Management', "Failed to delete user: {$target_username} - User not found or locked");
    
    header("Location: user.php?office={$office_id}&delete=locked");
  }
} catch (mysqli_sql_exception $e) {
  if ($e->getCode() == 1451) {
    // Foreign key constraint violation
    logErrorActivity('User Management', "Failed to delete user: {$target_username} - Foreign key constraint violation");
    header("Location: user.php?office={$office_id}&delete=locked");
  } else {
    // Other database error
    logErrorActivity('User Management', "Failed to delete user: {$target_username} - Database error: " . $e->getMessage());
    header("Location: user.php?office={$office_id}&delete=error");
  }
}
exit();
?>
