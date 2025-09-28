<?php
require_once '../connect.php';
require_once '../includes/classes/Notification.php';

header('Content-Type: application/json');

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$notification = new Notification($conn);

// Get action from POST data
$action = $_POST['action'] ?? '';
$notificationId = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : null;

$response = ['success' => false];

switch ($action) {
    case 'mark_as_read':
        if ($notificationId) {
            $response['success'] = $notification->markAsRead($notificationId, $userId);
        } else {
            $response['error'] = 'Notification ID is required';
        }
        break;
        
    case 'mark_all_read':
        $response['success'] = $notification->markAllAsRead($userId);
        break;
        
    case 'delete':
        if ($notificationId) {
            $response['success'] = $notification->deleteNotification($notificationId, $userId);
        } else {
            $response['error'] = 'Notification ID is required';
        }
        break;
        
    default:
        $response['error'] = 'Invalid action';
        break;
}

// Get updated unread count
$response['unread_count'] = $notification->getUnreadCount($userId);

echo json_encode($response);
?>
