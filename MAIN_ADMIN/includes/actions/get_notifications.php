<?php
require_once __DIR__ . '/../../../includes/classes/Notification.php';
require_once __DIR__ . '/../../../connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'notifications' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $response['message'] = 'User not authenticated';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['user_id'];

// Check database connection
if (!isset($conn) || !$conn->ping()) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

$notification = new Notification($conn);

try {
    // Get unread notifications (or all if specified)
    $unreadOnly = isset($_GET['unread_only']) ? (bool)$_GET['unread_only'] : true;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    $notifications = $notification->getUserNotifications($userId, $unreadOnly, $limit);
    
    $response['success'] = true;
    $response['notifications'] = is_array($notifications) ? $notifications : [];
    $response['count'] = count($response['notifications']);
    $response['unread_count'] = count(array_filter($response['notifications'], function($n) {
        return !$n['is_read'];
    }));
    
} catch (Exception $e) {
    $response['message'] = 'Error fetching notifications: ' . $e->getMessage();
    error_log('Notification Error: ' . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT);