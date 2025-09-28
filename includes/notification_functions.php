<?php
/**
 * Notification Helper Functions
 * Contains helper functions for working with notifications
 */

/**
 * Get the notification icon HTML based on notification type
 * 
 * @param string $type Notification type
 * @return string HTML for the notification icon
 */
function getNotificationIcon($type) {
    $icons = [
        'low_stock' => 'exclamation-triangle-fill text-warning',
        'borrow_request' => 'envelope-paper-fill text-primary',
        'borrow_approved' => 'check-circle-fill text-success',
        'borrow_rejected' => 'x-circle-fill text-danger',
        'due_date_reminder' => 'clock-fill text-warning',
        'overdue_notice' => 'exclamation-octagon-fill text-danger',
        'maintenance_reminder' => 'tools text-info',
        'system_alert' => 'bell-fill text-secondary',
        'new_asset_assigned' => 'box-seam-fill text-success',
        'asset_returned' => 'arrow-return-left text-primary'
    ];
    
    $iconClass = $icons[$type] ?? 'bell-fill';
    return '<i class="bi ' . $iconClass . '"></i>';
}

/**
 * Get the notification type label
 * 
 * @param string $type Notification type
 * @return string Human-readable type label
 */
function getNotificationTypeLabel($type) {
    $labels = [
        'low_stock' => 'Low Stock',
        'borrow_request' => 'Borrow Request',
        'borrow_approved' => 'Request Approved',
        'borrow_rejected' => 'Request Rejected',
        'due_date_reminder' => 'Due Date Reminder',
        'overdue_notice' => 'Overdue Notice',
        'maintenance_reminder' => 'Maintenance Reminder',
        'system_alert' => 'System Alert',
        'new_asset_assigned' => 'New Asset Assigned',
        'asset_returned' => 'Asset Returned'
    ];
    
    return $labels[$type] ?? 'Notification';
}

/**
 * Format timestamp as a human-readable time ago string
 * 
 * @param string $datetime Datetime string
 * @return string Formatted time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $timeDiff = time() - $time;
    
    if ($timeDiff < 60) {
        return 'Just now';
    }
    
    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];
    
    foreach ($intervals as $seconds => $label) {
        $interval = floor($timeDiff / $seconds);
        
        if ($interval >= 1) {
            return $interval . ' ' . $label . ($interval === 1 ? '' : 's') . ' ago';
        }
    }
    
    return 'Just now';
}

/**
 * Get the URL for a notification's related entity
 * 
 * @param string $entityType Type of entity
 * @param int $entityId Entity ID
 * @return string|null URL or null if no matching route
 */
function getNotificationEntityUrl($entityType, $entityId) {
    $routes = [
        'asset' => 'view_asset.php',
        'borrow_request' => 'view_borrow_request.php',
        'borrow' => 'view_borrow.php',
        'user' => 'view_user.php',
        'inventory' => 'inventory.php'
    ];
    
    if (isset($routes[$entityType])) {
        return $routes[$entityType] . '?id=' . $entityId;
    }
    
    return null;
}

/**
 * Send a notification to users
 * 
 * @param mysqli $conn Database connection
 * @param string $type Notification type
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string|null $relatedEntityType Type of related entity
 * @param int|null $relatedEntityId ID of related entity
 * @param array|int|null $userIds Array of user IDs, single user ID, or null for all users
 * @param int $expiresInDays Number of days before the notification expires (0 = never)
 * @return int|bool Notification ID on success, false on failure
 */
function sendNotification($conn, $type, $title, $message, $relatedEntityType = null, $relatedEntityId = null, $userIds = null, $expiresInDays = 30) {
    require_once __DIR__ . '/classes/Notification.php';
    
    $notification = new Notification($conn);
    return $notification->create($type, $title, $message, $relatedEntityType, $relatedEntityId, $userIds, $expiresInDays);
}

/**
 * Get the unread notification count for a user
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return int Number of unread notifications
 */
function getUnreadNotificationCount($conn, $userId) {
    require_once __DIR__ . '/classes/Notification.php';
    
    $notification = new Notification($conn);
    return $notification->getUnreadCount($userId);
}

/**
 * Get notifications for a user
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param bool $unreadOnly Whether to return only unread notifications
 * @param int $limit Maximum number of notifications to return
 * @return array Array of notifications
 */
function getUserNotifications($conn, $userId, $unreadOnly = false, $limit = 10) {
    require_once __DIR__ . '/classes/Notification.php';
    
    $notification = new Notification($conn);
    return $notification->getUserNotifications($userId, $unreadOnly, $limit);
}

/**
 * Mark a notification as read
 * 
 * @param mysqli $conn Database connection
 * @param int $notificationId Notification ID
 * @param int $userId User ID
 * @return bool True on success, false on failure
 */
function markNotificationAsRead($conn, $notificationId, $userId) {
    require_once __DIR__ . '/classes/Notification.php';
    
    $notification = new Notification($conn);
    return $notification->markAsRead($notificationId, $userId);
}

/**
 * Mark all notifications as read for a user
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return bool True on success, false on failure
 */
function markAllNotificationsAsRead($conn, $userId) {
    require_once __DIR__ . '/classes/Notification.php';
    
    $notification = new Notification($conn);
    return $notification->markAllAsRead($userId);
}

/**
 * Delete a notification for a user
 * 
 * @param mysqli $conn Database connection
 * @param int $notificationId Notification ID
 * @param int $userId User ID
 * @return bool True on success, false on failure
 */
function deleteUserNotification($conn, $notificationId, $userId) {
    require_once __DIR__ . '/classes/Notification.php';
    
    $notification = new Notification($conn);
    return $notification->deleteNotification($notificationId, $userId);
}

/**
 * Get notification preferences for a user
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return array Notification preferences
 */
function getNotificationPreferences($conn, $userId) {
    $preferences = [];
    $query = "SELECT type_id, email_enabled, in_app_enabled 
              FROM user_notification_preferences 
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $preferences[$row['type_id']] = [
            'email' => (bool)$row['email_enabled'],
            'in_app' => (bool)$row['in_app_enabled']
        ];
    }
    
    $stmt->close();
    return $preferences;
}

/**
 * Update notification preferences for a user
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param array $preferences Array of preferences [type_id => ['email' => bool, 'in_app' => bool]]
 * @return bool True on success, false on failure
 */
function updateNotificationPreferences($conn, $userId, $preferences) {
    $conn->begin_transaction();
    
    try {
        // First, delete existing preferences
        $deleteStmt = $conn->prepare("DELETE FROM user_notification_preferences WHERE user_id = ?");
        $deleteStmt->bind_param('i', $userId);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Insert new preferences
        $insertStmt = $conn->prepare("
            INSERT INTO user_notification_preferences 
            (user_id, type_id, email_enabled, in_app_enabled) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($preferences as $typeId => $prefs) {
            $emailEnabled = (int)$prefs['email'];
            $inAppEnabled = (int)$prefs['in_app'];
            
            $insertStmt->bind_param('iiii', $userId, $typeId, $emailEnabled, $inAppEnabled);
            $insertStmt->execute();
        }
        
        $insertStmt->close();
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating notification preferences: " . $e->getMessage());
        return false;
    }
}
