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

// If accepting the request, update asset statuses to 'borrowed'
if ($success && $action === 'accept') {
    // Fetch the items from the submission
    $fetch_sql = "SELECT items FROM borrow_form_submissions WHERE id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param('i', $submission_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $items = json_decode($row['items'], true);

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
