<?php
require_once __DIR__ . '/../../includes/classes/Notification.php';
require_once __DIR__ . '/../../includes/classes/SessionManager.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'notifications' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not authenticated';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];
$notification = new Notification($conn);

// Get unread notifications (or all if specified)
$unreadOnly = isset($_GET['unread_only']) ? (bool)$_GET['unread_only'] : true;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

try {
    $notifications = $notification->getUserNotifications($userId, $unreadOnly, $limit);
    
    $response['success'] = true;
    $response['notifications'] = $notifications;
    $response['count'] = count($notifications);
    $response['unread_count'] = count(array_filter($notifications, function($n) {
        return !$n['is_read'];
    }));
    
} catch (Exception $e) {
    $response['message'] = 'Error fetching notifications: ' . $e->getMessage();
    error_log($response['message']);
}

echo json_encode($response);
