<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit();
}

$action = $_POST['action'] ?? '';
$submission_id = (int)($_POST['submission_id'] ?? 0);

if (!$submission_id || !in_array($action, ['accept', 'decline'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
  exit();
}

// Determine new status
$new_status = ($action === 'accept') ? 'approved' : 'rejected';

// Update the borrow form submission
$sql = "UPDATE borrow_form_submissions SET status = ?, updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Database error']);
  exit();
}

$stmt->bind_param('si', $new_status, $submission_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
  echo json_encode(['success' => true, 'message' => 'Borrow request ' . ($action === 'accept' ? 'approved' : 'declined') . ' successfully']);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to update borrow request']);
}

$conn->close();
?>
