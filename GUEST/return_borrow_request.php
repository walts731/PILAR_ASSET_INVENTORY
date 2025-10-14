<?php
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';
require_once '../includes/classes/Notification.php';
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

// Get current guest's persistent ID
$guest_id = $_SESSION['guest_id'] ?? null;

if (!$guest_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Guest session expired']);
    exit();
}

// Verify that the submission belongs to the current guest and is in a returnable status
$verify_sql = "SELECT id, status, items FROM borrow_form_submissions WHERE id = ? AND guest_id = ? AND status IN ('approved', 'borrowed')";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param('is', $submission_id, $guest_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    $verify_stmt->close();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Request not found or cannot be returned']);
    exit();
}

$row = $verify_result->fetch_assoc();
$items = json_decode($row['items'], true);
$verify_stmt->close();

// Extract asset_ids from the submission items
$asset_ids = [];
if ($items && is_array($items)) {
    foreach ($items as $item) {
        if (isset($item['asset_id']) && !empty($item['asset_id'])) {
            $asset_ids[] = (int)$item['asset_id'];
        }
    }
}

// Update borrow_form_submissions status to 'returned'
$update_submission_sql = "UPDATE borrow_form_submissions SET status = 'returned', updated_at = NOW() WHERE id = ?";
$update_stmt = $conn->prepare($update_submission_sql);
$update_stmt->bind_param('i', $submission_id);
$success = $update_stmt->execute();
$update_stmt->close();

if ($success && !empty($asset_ids)) {
    // Update assets status to 'serviceable'
    $placeholders = str_repeat('?,', count($asset_ids) - 1) . '?';
    $update_assets_sql = "UPDATE assets SET status = 'serviceable', last_updated = NOW() WHERE id IN ($placeholders)";
    $update_assets_stmt = $conn->prepare($update_assets_sql);

    if ($update_assets_stmt) {
        $types = str_repeat('i', count($asset_ids));
        $update_assets_stmt->bind_param($types, ...$asset_ids);
        $update_assets_stmt->execute();
        $update_assets_stmt->close();
    }

    // Log lifecycle events for returned assets
    $borrower_name = $_SESSION['guest_name'] ?? 'Unknown Guest';
    foreach ($asset_ids as $asset_id) {
        logLifecycleEvent(
            $asset_id,
            'RETURNED',
            'borrow_form_submissions',
            $submission_id,
            null, // from_employee_id (guest returning)
            null, // to_employee_id (returning to inventory)
            null, // from_office_id
            null, // to_office_id
            "Asset returned by {$borrower_name} (Submission #{$submission_id})"
        );
    }

    // Send notification to MAIN_ADMIN users
    $notification = new Notification($conn);
    $title = "Asset Return Notification";
    $message = "Guest {$borrower_name} has returned assets. Submission #{$submission_id}";
    $notification->create(
        'asset_returned',
        $title,
        $message,
        'borrow_form_submissions',
        $submission_id,
        null, // Send to all admins
        7 // Expires in 7 days
    );
}

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Items returned successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process return']);
}

$conn->close();
?>
