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

// Get parameters
$unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Get notifications
$notifications = $notification->getUserNotifications($userId, $unreadOnly, $limit);
$unreadCount = $notification->getUnreadCount($userId);

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $unreadCount
]);
?>
