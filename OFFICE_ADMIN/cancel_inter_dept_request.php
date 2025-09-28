<?php
require_once '../connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$request_id = intval($_POST['request_id'] ?? 0);

if ($request_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get request details
    $request_sql = "
        SELECT br.*, a.asset_name, 
               so.office_name as source_office_name, 
               do.office_name as dest_office_name
        FROM borrow_requests br
        JOIN assets a ON br.asset_id = a.id
        JOIN office so ON br.source_office_id = so.id
        JOIN office do ON br.office_id = do.id
        WHERE br.id = ? AND br.requested_by_user_id = ? AND br.status = 'pending_approval'
        AND br.is_inter_department = 1
    ";
    
    $stmt = $conn->prepare($request_sql);
    $stmt->bind_param('ii', $request_id, $user_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$request) {
        throw new Exception('Request not found or cannot be cancelled');
    }
    
    // Update request status to cancelled
    $update_sql = "
        UPDATE borrow_requests 
        SET status = 'cancelled', 
            updated_at = NOW()
        WHERE id = ? AND requested_by_user_id = ?
    ";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ii', $request_id, $user_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to cancel request: ' . $update_stmt->error);
    }
    
    // Update approval records to cancelled
    $update_approvals_sql = "
        UPDATE inter_department_approvals 
        SET status = 'cancelled', 
            comments = 'Request cancelled by requester',
            updated_at = NOW()
        WHERE request_id = ?
    ";
    
    $update_approvals = $conn->prepare($update_approvals_sql);
    $update_approvals->bind_param('i', $request_id);
    
    if (!$update_approvals->execute()) {
        throw new Exception('Failed to update approval records: ' . $update_approvals->error);
    }
    
    // Create notification for office heads
    $notification_message = "Inter-department borrow request #{$request_id} for {$request['asset_name']} has been cancelled by the requester.";
    $notification_sql = "
        INSERT INTO notifications (
            user_id, title, message, type, related_id, related_type, is_read, created_at
        ) 
        SELECT 
            head_user_id, 
            'Request Cancelled', 
            ?, 
            'borrow_request', 
            ?, 
            'inter_dept_borrow', 
            0, 
            NOW()
        FROM office 
        WHERE id IN (?, ?)
    ";
    
    $notification_stmt = $conn->prepare($notification_sql);
    $notification_stmt->bind_param(
        'siii', 
        $notification_message,
        $request_id,
        $request['office_id'],
        $request['source_office_id']
    );
    
    if (!$notification_stmt->execute()) {
        error_log("Failed to create notifications: " . $notification_stmt->error);
        // Don't fail the whole process if notification fails
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Request has been cancelled successfully.'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error cancelling inter-department request: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
