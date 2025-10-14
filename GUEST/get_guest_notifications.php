<?php
// get_guest_notifications.php
// API endpoint for guests to fetch their notifications

session_start();
require_once '../connect.php';
require_once '../includes/classes/GuestNotification.php';

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$guest_id = $_SESSION['guest_id'] ?? null;
if (!$guest_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Guest ID not found']);
    exit();
}

$action = $_GET['action'] ?? 'fetch';

try {
    $notification = new GuestNotification($conn);

    if ($action === 'fetch') {
        // Fetch unread notifications
        $notifications = $notification->getGuestNotifications($guest_id, true, 10);

        // Format notifications for frontend
        $formatted_notifications = [];
        foreach ($notifications as $notif) {
            $formatted_notifications[] = [
                'id' => $notif['id'],
                'title' => $notif['title'],
                'message' => $notif['message'],
                'type' => $notif['notification_type'],
                'created_at' => $notif['created_at'],
                'related_entity_type' => $notif['related_entity_type'],
                'related_entity_id' => $notif['related_entity_id'],
                'is_read' => $notif['is_read']
            ];
        }

        echo json_encode([
            'success' => true,
            'notifications' => $formatted_notifications,
            'unread_count' => count($formatted_notifications)
        ]);

    } elseif ($action === 'mark_read') {
        // Mark specific notification as read
        $notification_id = (int)($_POST['notification_id'] ?? 0);

        if (!$notification_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Notification ID required']);
            exit();
        }

        $result = $notification->markAsRead($notification_id, $guest_id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
        ]);

    } elseif ($action === 'mark_all_read') {
        // Mark all notifications as read
        $result = $notification->markAllAsRead($guest_id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'All notifications marked as read' : 'Failed to mark notifications as read'
        ]);

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Guest notification API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

$conn->close();
?>
