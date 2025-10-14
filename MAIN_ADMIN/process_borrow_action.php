<?php
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';
require_once '../includes/email_helper.php';
require_once '../includes/classes/GuestNotification.php';
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

// If accepting the request, update asset statuses to 'borrowed'
if ($success && $action === 'accept') {
    // Fetch the items from the submission
    $fetch_sql = "SELECT guest_name, items FROM borrow_form_submissions WHERE id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param('i', $submission_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();

    if ($result->num_rows > 0) {
        $submission_data = $result->fetch_assoc();
        $borrower_name = $submission_data['guest_name'] ?? 'Unknown Guest';
        $items = json_decode($submission_data['items'], true);

        if ($items && is_array($items)) {
            // Extract asset_ids from items
            $asset_ids = [];
            foreach ($items as $item) {
                if (isset($item['asset_id']) && !empty($item['asset_id'])) {
                    $asset_ids[] = (int)$item['asset_id'];
                }
            }

            // Update asset statuses to 'borrowed'
            if (!empty($asset_ids)) {
                $placeholders = str_repeat('?,', count($asset_ids) - 1) . '?';
                $update_assets_sql = "UPDATE assets SET status = 'borrowed', last_updated = NOW() WHERE id IN ($placeholders)";
                $update_stmt = $conn->prepare($update_assets_sql);

                if ($update_stmt) {
                    $types = str_repeat('i', count($asset_ids));
                    $update_stmt->bind_param($types, ...$asset_ids);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // Log lifecycle events for borrowed assets
                foreach ($asset_ids as $asset_id) {
                    logLifecycleEvent(
                        $asset_id,
                        'BORROWED',
                        'borrow_form_submissions',
                        $submission_id,
                        null, // from_employee_id (admin processing)
                        null, // to_employee_id (guest borrowing, no specific employee)
                        null, // from_office_id
                        null, // to_office_id
                        "Asset borrowed by {$borrower_name} (Submission #{$submission_id})"
                    );
                }

                // Send approval email to guest
                $guest_email = $submission_data['guest_email'] ?? null;
                if (!empty($guest_email)) {
                    $email_result = sendBorrowApprovalEmail(
                        $guest_email,
                        $borrower_name,
                        $submission_data['submission_number'] ?? 'N/A',
                        $items
                    );

                    if (!$email_result['success']) {
                        // Log email failure but don't fail the approval process
                        error_log("Failed to send approval email to {$guest_email}: " . $email_result['message']);
                    }
                }

                // Send in-app notification to guest
                $guest_id = $submission_data['guest_id'] ?? null;
                if ($guest_id) {
                    $notification = new GuestNotification($conn);
                    $admin_name = $_SESSION['username'] ?? 'System Admin';

                    $notification_result = $notification->sendBorrowRequestStatusUpdate(
                        $submission_id,
                        $guest_id,
                        'approved',
                        $admin_name
                    );

                    if (!$notification_result) {
                        error_log("Failed to send approval notification to guest ID {$guest_id}");
                    }
                }

                // Automatically reject other pending borrow requests for the same assets
                if (!empty($asset_ids)) {
                    // Find all pending submissions that contain any of these asset_ids
                    $conflict_placeholders = str_repeat('?,', count($asset_ids) - 1) . '?';
                    $conflict_sql = "SELECT id, items FROM borrow_form_submissions
                                   WHERE status = 'pending' AND id != ?";
                    $conflict_stmt = $conn->prepare($conflict_sql);
                    $conflict_stmt->bind_param('i', $submission_id);
                    $conflict_stmt->execute();
                    $conflict_result = $conflict_stmt->get_result();

                    $conflicting_submission_ids = [];
                    while ($conflict_row = $conflict_result->fetch_assoc()) {
                        $conflict_items = json_decode($conflict_row['items'], true);
                        if ($conflict_items && is_array($conflict_items)) {
                            foreach ($conflict_items as $item) {
                                if (isset($item['asset_id']) && in_array((int)$item['asset_id'], $asset_ids)) {
                                    $conflicting_submission_ids[] = $conflict_row['id'];
                                    break; // Found a conflict, no need to check other items in this submission
                                }
                            }
                        }
                    }
                    $conflict_stmt->close();

                    // Reject all conflicting submissions
                    if (!empty($conflicting_submission_ids)) {
                        $reject_placeholders = str_repeat('?,', count($conflicting_submission_ids) - 1) . '?';
                        $reject_sql = "UPDATE borrow_form_submissions SET status = 'rejected', updated_at = NOW() WHERE id IN ($reject_placeholders)";
                        $reject_stmt = $conn->prepare($reject_sql);

                        if ($reject_stmt) {
                            $reject_types = str_repeat('i', count($conflicting_submission_ids));
                            $reject_stmt->bind_param($reject_types, ...$conflicting_submission_ids);
                            $reject_stmt->execute();
                            $reject_stmt->close();
                        }
                    }
                }
            }
        }
    }
    $fetch_stmt->close();
} elseif ($action === 'decline') {
    // Handle rejection
    $fetch_sql = "SELECT b.*, g.email as guest_email, g.name as guest_name FROM borrow_form_submissions b LEFT JOIN guests g ON b.guest_id = g.guest_id WHERE b.id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param('i', $submission_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();

    if ($result->num_rows > 0) {
        $submission_data = $result->fetch_assoc();

        // Decode items for email
        $items = json_decode($submission_data['items'], true);

        // Send email notification to guest about rejection
        $guest_email = $submission_data['guest_email'] ?? null;
        $borrower_name = $submission_data['guest_name'] ?? 'Guest';

        if ($guest_email) {
            $email_result = sendBorrowRejectionEmail(
                $guest_email,
                $borrower_name,
                $submission_data['submission_number'] ?? 'N/A',
                $items
            );

            if (!$email_result['success']) {
                // Log email failure but don't fail the rejection process
                error_log("Failed to send rejection email to {$guest_email}: " . $email_result['message']);
            }
        }

        // Send in-app notification to guest about rejection
        $guest_id = $submission_data['guest_id'] ?? null;
        if ($guest_id) {
            $notification = new GuestNotification($conn);
            $admin_name = $_SESSION['username'] ?? 'System Admin';

            $notification_result = $notification->sendBorrowRequestStatusUpdate(
                $submission_id,
                $guest_id,
                'rejected',
                $admin_name
            );

            if (!$notification_result) {
                error_log("Failed to send rejection notification to guest ID {$guest_id}");
            }
        }
    }
    $fetch_stmt->close();
}

if ($success) {
  echo json_encode(['success' => true, 'message' => 'Borrow request ' . ($action === 'accept' ? 'approved' : 'declined') . ' successfully']);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to update borrow request']);
}

$conn->close();
?>
