<?php
require_once '../connect.php';
session_start();

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$submission_id = (int)($_POST['submission_id'] ?? 0);

if (!$submission_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
    exit();
}

// Get current guest session ID
$guest_session_id = session_id();

// Verify that the submission belongs to the current guest
$verify_sql = "SELECT id FROM borrow_form_submissions WHERE id = ? AND guest_session_id = ? AND status = 'pending'";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param('is', $submission_id, $guest_session_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    $verify_stmt->close();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Request not found or cannot be cancelled']);
    exit();
}
$verify_stmt->close();

// Update the submission status to 'cancelled'
$update_sql = "UPDATE borrow_form_submissions SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param('i', $submission_id);
$success = $update_stmt->execute();
$update_stmt->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Borrow request cancelled successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to cancel borrow request']);
}

$conn->close();
?>
