<?php
/**
 * Notification Class
 * Handles all notification-related functionality
 */
class Notification {
    private $conn;
    private $notificationTypes = [
        'low_stock' => 'Low Stock Alert',
        'borrow_request' => 'Borrow Request',
        'borrow_approved' => 'Borrow Request Approved',
        'borrow_rejected' => 'Borrow Request Rejected',
        'due_date_reminder' => 'Due Date Reminder',
        'overdue_notice' => 'Overdue Notice',
        'maintenance_reminder' => 'Maintenance Reminder',
        'system_alert' => 'System Alert',
        'new_asset_assigned' => 'New Asset Assigned',
        'asset_returned' => 'Asset Returned'
    ];

    /**
     * Default notification preferences
     */
    private $defaultPreferences = [
        'email_notifications' => 1,
        'desktop_notifications' => 1,
        'sound_alert' => 1,
        'low_stock_notification' => 1,
        'borrow_request_notification' => 1,
        'borrow_approved_notification' => 1,
        'borrow_rejected_notification' => 1,
        'due_date_reminder_notification' => 1,
        'overdue_notice_notification' => 1,
        'maintenance_reminder_notification' => 1,
        'system_alert_notification' => 1,
        'new_asset_assigned_notification' => 1,
        'asset_returned_notification' => 1
    ];

    /**
     * Constructor
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Create a new notification
     * @param string $type Notification type (must match a key in $notificationTypes)
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $relatedEntityType Type of related entity (e.g., 'asset', 'borrow_request')
     * @param int|null $relatedEntityId ID of the related entity
     * @param array|string $userIds Array of user IDs or comma-separated string of user IDs
     * @param int $expiresInDays Number of days before the notification expires (0 = never)
     * @return int|bool Notification ID on success, false on failure
     */
    public function create($type, $title, $message, $relatedEntityType = null, $relatedEntityId = null, $userIds = null, $expiresInDays = 30) {
        if (!array_key_exists($type, $this->notificationTypes)) {
            error_log("Invalid notification type: $type");
            return false;
        }

        // If user IDs is an array, convert to comma-separated string
        if (is_array($userIds)) {
            $userIds = implode(',', array_map('intval', $userIds));
        }

        // Prepare the call to the stored procedure
        $stmt = $this->conn->prepare("CALL create_notification(?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param(
            'ssssiii',
            $type,
            $title,
            $message,
            $relatedEntityType,
            $relatedEntityId,
            $userIds,
            $expiresInDays
        );

        if (!$stmt->execute()) {
            error_log("Failed to create notification: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();
        $stmt->close();

        return $notification ? $notification['notification_id'] : false;
    }

    /**
     * Get notifications for a specific user
     * @param int $userId User ID
     * @param bool $unreadOnly Whether to return only unread notifications
     * @param int $limit Maximum number of notifications to return
     * @return array Array of notifications
     */
    public function getUserNotifications($userId, $unreadOnly = false, $limit = 10) {
        $userId = (int)$userId;
        $limit = (int)$limit;
        
        $query = "SELECT 
                    n.id, n.title, n.message, n.related_entity_type, n.related_entity_id,
                    n.created_at, un.is_read, un.read_at, n.expires_at,
                    nt.name AS type, nt.description AS type_description
                  FROM user_notifications un
                  JOIN notifications n ON un.notification_id = n.id
                  JOIN notification_types nt ON n.type_id = nt.id
                  WHERE un.user_id = ? AND un.deleted_at IS NULL";
        
        if ($unreadOnly) {
            $query .= " AND un.is_read = 0";
        }
        
        $query .= " AND (n.expires_at IS NULL OR n.expires_at > NOW())";
        $query .= " ORDER BY n.created_at DESC";
        $query .= " LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        $stmt->close();
        return $notifications;
    }

    /**
     * Mark a notification as read
     * @param int $notificationId Notification ID
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function markAsRead($notificationId, $userId) {
        $stmt = $this->conn->prepare("
            UPDATE user_notifications 
            SET is_read = 1, read_at = NOW() 
            WHERE notification_id = ? AND user_id = ? AND is_read = 0
        ");
        $stmt->bind_param('ii', $notificationId, $userId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Mark all notifications as read for a user
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function markAllAsRead($userId) {
        $stmt = $this->conn->prepare("
            UPDATE user_notifications 
            SET is_read = 1, read_at = NOW() 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->bind_param('i', $userId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Delete a notification for a specific user
     * @param int $notificationId Notification ID
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function deleteNotification($notificationId, $userId) {
        $stmt = $this->conn->prepare("
            UPDATE user_notifications 
            SET deleted_at = NOW() 
            WHERE notification_id = ? AND user_id = ?
        ");
        $stmt->bind_param('ii', $notificationId, $userId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get unread notification count for a user
     * @param int $userId User ID
     * @return int Number of unread notifications
     */
    public function getUnreadCount($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM user_notifications un
            JOIN notifications n ON un.notification_id = n.id
            WHERE un.user_id = ? 
            AND un.is_read = 0 
            AND un.deleted_at IS NULL
            AND (n.expires_at IS NULL OR n.expires_at > NOW())
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }

    // Specific notification type methods

    /**
     * Send low stock notification
     * @param int $assetId Asset ID
     * @param string $assetName Asset name
     * @param int $currentStock Current stock level
     * @param int $threshold Threshold for low stock
     * @param array|int $userIds User IDs to notify (default: notify all admins)
     * @return int|bool Notification ID on success, false on failure
     */
    public function sendLowStockNotification($assetId, $assetName, $currentStock, $threshold, $userIds = null) {
        if ($userIds === null) {
            // Get all admin users if no specific users provided
            $userIds = $this->getAdminUserIds();
        }

        $title = "Low Stock Alert: $assetName";
        $message = "$assetName is running low on stock. Current stock: $currentStock (Threshold: $threshold)";
        
        return $this->create(
            'low_stock',
            $title,
            $message,
            'asset',
            $assetId,
            $userIds,
            7 // Expires in 7 days
        );
    }

    /**
     * Send borrow request notification
     * @param int $requestId Borrow request ID
     * @param int $requesterId User ID of the requester
     * @param string $requesterName Name of the requester
     * @param array|int $approverIds User IDs of approvers to notify
     * @return int|bool Notification ID on success, false on failure
     */
    public function sendBorrowRequestNotification($requestId, $requesterId, $requesterName, $approverIds) {
        $title = "New Borrow Request";
        $message = "$requesterName has submitted a new borrow request";
        
        return $this->create(
            'borrow_request',
            $title,
            $message,
            'borrow_request',
            $requestId,
            $approverIds,
            14 // Expires in 14 days
        );
    }

    /**
     * Send borrow request status update
     * @param int $requestId Borrow request ID
     * @param int $userId User ID to notify
     * @param string $status New status (approved/rejected)
     * @param string $adminName Name of the admin who processed the request
     * @return int|bool Notification ID on success, false on failure
     */
    public function sendBorrowRequestStatusUpdate($requestId, $userId, $status, $adminName) {
        $statusText = ucfirst($status);
        $title = "Borrow Request $statusText";
        $message = "Your borrow request has been $status by $adminName";
        $type = $status === 'approved' ? 'borrow_approved' : 'borrow_rejected';
        
        return $this->create(
            $type,
            $title,
            $message,
            'borrow_request',
            $requestId,
            $userId,
            14 // Expires in 14 days
        );
    }

    /**
     * Send due date reminder
     * @param int $borrowId Borrow record ID
     * @param int $userId User ID to notify
     * @param string $assetName Name of the borrowed asset
     * @param string $dueDate Due date (formatted)
     * @return int|bool Notification ID on success, false on failure
     */
    public function sendDueDateReminder($borrowId, $userId, $assetName, $dueDate) {
        $title = "Due Date Reminder: $assetName";
        $message = "The due date for '$assetName' is approaching on $dueDate. Please return it on time.";
        
        return $this->create(
            'due_date_reminder',
            $title,
            $message,
            'borrow',
            $borrowId,
            $userId,
            7 // Expires in 7 days
        );
    }

    // Helper methods

    /**
     * Get admin user IDs
     * @return array Array of admin user IDs
     */
    private function getAdminUserIds() {
        $query = "SELECT id FROM users WHERE role IN ('MAIN_ADMIN', 'OFFICE_ADMIN', 'SYSTEM_ADMIN')";
        $result = $this->conn->query($query);
        
        $userIds = [];
        while ($row = $result->fetch_assoc()) {
            $userIds[] = $row['id'];
        }
        
        return $userIds;
    }

    /**
     * Get user notification preferences
     * 
     * @param int $userId User ID
     * @return array User preferences
     */
    public function getUserPreferences($userId) {
        $userId = (int)$userId;
        $query = "SELECT * FROM user_notification_preferences WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $prefs = $result->fetch_assoc();
            
            // Convert database values to boolean for the application
            foreach ($prefs as $key => $value) {
                if (is_numeric($value)) {
                    $prefs[$key] = (bool)$value;
                }
            }
            
            return array_merge($this->defaultPreferences, $prefs);
        }
        
        // If no preferences found, return defaults
        return $this->defaultPreferences;
    }
    
    /**
     * Save user notification preferences
     * 
     * @param int $userId User ID
     * @param array $preferences User preferences to save
     * @return bool True on success, false on failure
     */
    public function saveUserPreferences($userId, $preferences) {
        $userId = (int)$userId;
        
        // Ensure we have all required fields with proper values
        $preferences = array_merge($this->defaultPreferences, $preferences);
        
        // Convert boolean values to integers for database
        foreach ($preferences as $key => $value) {
            if (is_bool($value)) {
                $preferences[$key] = $value ? 1 : 0;
            }
        }
        
        // Build the query
        $query = "INSERT INTO user_notification_preferences (
                    user_id, email_notifications, desktop_notifications, sound_alert,
                    low_stock_notification, borrow_request_notification, borrow_approved_notification,
                    borrow_rejected_notification, due_date_reminder_notification, overdue_notice_notification,
                    maintenance_reminder_notification, system_alert_notification, 
                    new_asset_assigned_notification, asset_returned_notification, updated_at
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE 
                    email_notifications = VALUES(email_notifications),
                    desktop_notifications = VALUES(desktop_notifications),
                    sound_alert = VALUES(sound_alert),
                    low_stock_notification = VALUES(low_stock_notification),
                    borrow_request_notification = VALUES(borrow_request_notification),
                    borrow_approved_notification = VALUES(borrow_approved_notification),
                    borrow_rejected_notification = VALUES(borrow_rejected_notification),
                    due_date_reminder_notification = VALUES(due_date_reminder_notification),
                    overdue_notice_notification = VALUES(overdue_notice_notification),
                    maintenance_reminder_notification = VALUES(maintenance_reminder_notification),
                    system_alert_notification = VALUES(system_alert_notification),
                    new_asset_assigned_notification = VALUES(new_asset_assigned_notification),
                    asset_returned_notification = VALUES(asset_returned_notification),
                    updated_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "iiiiiiiiiiiiii",
            $userId,
            $preferences['email_notifications'],
            $preferences['desktop_notifications'],
            $preferences['sound_alert'],
            $preferences['low_stock_notification'],
            $preferences['borrow_request_notification'],
            $preferences['borrow_approved_notification'],
            $preferences['borrow_rejected_notification'],
            $preferences['due_date_reminder_notification'],
            $preferences['overdue_notice_notification'],
            $preferences['maintenance_reminder_notification'],
            $preferences['system_alert_notification'],
            $preferences['new_asset_assigned_notification'],
            $preferences['asset_returned_notification']
        );
        
        return $stmt->execute();
    }
    
    /**
     * Check if a user should receive a specific type of notification
     * 
     * @param int $userId User ID
     * @param string $notificationType Type of notification
     * @return bool True if user should receive the notification, false otherwise
     */
    public function shouldReceiveNotification($userId, $notificationType) {
        $prefs = $this->getUserPreferences($userId);
        
        // Map notification types to their corresponding preference keys
        $typeMap = [
            'low_stock' => 'low_stock_notification',
            'borrow_request' => 'borrow_request_notification',
            'borrow_approved' => 'borrow_approved_notification',
            'borrow_rejected' => 'borrow_rejected_notification',
            'due_date_reminder' => 'due_date_reminder_notification',
            'overdue_notice' => 'overdue_notice_notification',
            'maintenance_reminder' => 'maintenance_reminder_notification',
            'system_alert' => 'system_alert_notification',
            'new_asset_assigned' => 'new_asset_assigned_notification',
            'asset_returned' => 'asset_returned_notification'
        ];
        
        // Check if notifications are enabled and this type is enabled
        return $prefs['desktop_notifications'] && 
               isset($typeMap[$notificationType]) && 
               $prefs[$typeMap[$notificationType]];
    }

    /**
     * Get notification type display name
     * 
     * @param string $type Notification type key
     * @return string Display name or the type if not found
     */
    public function getNotificationTypeName($type) {
        return $this->notificationTypes[$type] ?? $type;
    }
    
    /**
     * Get all notification types with their display names
     * 
     * @return array Array of notification types with their display names
     */
    public function getAllNotificationTypes() {
        return $this->notificationTypes;
    }
}

// Initialize Notification class if included directly (for backward compatibility)
if (isset($conn) && !isset($notification)) {
    $notification = new Notification($conn);
}
