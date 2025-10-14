<?php
require_once __DIR__ . '/../../includes/classes/Notification.php';
require_once __DIR__ . '/../../includes/classes/SessionManager.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not authenticated';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['notification_id'])) {
    $response['message'] = 'Notification ID is required';
    echo json_encode($response);
    exit;
}

$notificationId = (int)$_POST['notification_id'];
$userId = $_SESSION['user_id'];
$notification = new Notification($conn);

try {
    $result = $notification->markAsRead($notificationId, $userId);
    
    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Notification marked as read';
    } else {
        $response['message'] = 'Failed to mark notification as read';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log($response['message']);
}

echo json_encode($response);
