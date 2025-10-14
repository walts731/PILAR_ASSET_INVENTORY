<?php
/**
 * Notification Class
 * Handles all notification-related functionality
 */
class Notification {
    private $conn;
    /**
     * @var array Default notification types that will be inserted if not exists
     */
    private $notificationTypes = [
        ['name' => 'low_stock', 'description' => 'Low Stock Alert'],
        ['name' => 'borrow_request', 'description' => 'Borrow Request'],
        ['name' => 'borrow_approved', 'description' => 'Borrow Request Approved'],
        ['name' => 'borrow_rejected', 'description' => 'Borrow Request Rejected'],
        ['name' => 'due_date_reminder', 'description' => 'Due Date Reminder'],
        ['name' => 'overdue_notice', 'description' => 'Overdue Notice'],
        ['name' => 'maintenance_reminder', 'description' => 'Maintenance Reminder'],
        ['name' => 'system_alert', 'description' => 'System Alert'],
        ['name' => 'new_asset_assigned', 'description' => 'New Asset Assigned'],
        ['name' => 'asset_returned', 'description' => 'Asset Returned']
    ];

    /**
     * @var array Default notification preferences
     */
    private $defaultPreferences = [
        'email_notifications' => 1,
        'desktop_notifications' => 1,
        'sound_alert' => 1,
        'notification_types' => [
            'low_stock' => 1,
            'borrow_request' => 1,
            'borrow_approved' => 1,
            'borrow_rejected' => 1,
            'due_date_reminder' => 1,
            'overdue_notice' => 1,
            'maintenance_reminder' => 1,
            'system_alert' => 1,
            'new_asset_assigned' => 1,
            'asset_returned' => 1
        ]
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
     * @param int|array|string $userIds Single user ID, array of user IDs, or comma-separated string of user IDs
     * @param int $expiresInDays Number of days before the notification expires (0 = never)
     * @return int|bool Notification ID on success, false on failure
     */
    public function create($type, $title, $message, $relatedEntityType = null, $relatedEntityId = null, $userIds = null, $expiresInDays = 30) {
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Get or create notification type
            $typeId = $this->getOrCreateNotificationType($type);
            if (!$typeId) {
                throw new Exception("Failed to get or create notification type: $type");
            }
            
            // Calculate expiration date if needed
            $expiresAt = null;
            if ($expiresInDays > 0) {
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));
            }
            
            // Create the notification
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (type_id, title, message, related_entity_type, related_entity_id, expires_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param('isssss', $typeId, $title, $message, $relatedEntityType, $relatedEntityId, $expiresAt);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create notification: " . $stmt->error);
            }
            
            $notificationId = $this->conn->insert_id;
            
            // If user IDs are provided, create user notifications
            if ($userIds !== null) {
                // Convert to array if it's not already
                if (!is_array($userIds)) {
                    if (is_string($userIds) && strpos($userIds, ',') !== false) {
                        $userIds = array_map('intval', explode(',', $userIds));
                    } else {
                        $userIds = [(int)$userIds];
                    }
                }
                
                // Remove duplicates
                $userIds = array_unique($userIds);
                
                // Insert user notifications
                $stmt = $this->conn->prepare("
                    INSERT INTO user_notifications (user_id, notification_id, is_read, created_at)
                    VALUES (?, ?, 0, NOW())
                ");
                
                foreach ($userIds as $userId) {
                    $userId = (int)$userId;
                    if ($userId > 0) {
                        $stmt->bind_param('ii', $userId, $notificationId);
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to create user notification: " . $stmt->error);
                        }
                    }
                }
            }
            
            // Commit transaction
            $this->conn->commit();
            return $notificationId;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get or create a notification type
     * @param string $typeName Notification type name
     * @return int|bool Type ID or false on failure
     */
    private function getOrCreateNotificationType($typeName) {
        // Check if type exists
        $stmt = $this->conn->prepare("SELECT id FROM notification_types WHERE name = ?");
        $stmt->bind_param('s', $typeName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['id'];
        }
        
        // Find the type in our default types
        $description = '';
        foreach ($this->notificationTypes as $type) {
            if ($type['name'] === $typeName) {
                $description = $type['description'];
                break;
            }
        }
        
        // Type doesn't exist, create it
        $stmt = $this->conn->prepare("
            INSERT INTO notification_types (name, description, created_at)
            VALUES (?, ?, NOW())
        ");
        
        $stmt->bind_param('ss', $typeName, $description);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
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
        
        $query = "
            SELECT 
                n.id, n.title, n.message, n.related_entity_type, n.related_entity_id,
                n.created_at, un.is_read, un.read_at, n.expires_at,
                nt.name AS type, nt.description AS type_description
            FROM user_notifications un
            JOIN notifications n ON un.notification_id = n.id
            JOIN notification_types nt ON n.type_id = nt.id
            WHERE un.user_id = ? AND un.deleted_at IS NULL
        ";
        
        if ($unreadOnly) {
            $query .= " AND un.is_read = 0";
        }
        
        $query .= " AND (n.expires_at IS NULL OR n.expires_at > NOW())";
        $query .= " ORDER BY n.created_at DESC";
        $query .= " LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $userId, $limit);
        
        if (!$stmt->execute()) {
            error_log("Failed to get user notifications: " . $stmt->error);
            return [];
        }
        
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
        $affected = $stmt->affected_rows > 0;
        $stmt->close();
        
        return $affected;
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
        $affected = $stmt->affected_rows > 0;
        $stmt->close();
        
        return $affected;
    }

    /**
     * Get user notification preferences
     * @param int $userId User ID
     * @return array User preferences
     */
    public function getUserPreferences($userId) {
        $stmt = $this->conn->prepare("SELECT preferences FROM user_notification_preferences WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $prefs = json_decode($row['preferences'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return array_merge($this->defaultPreferences, $prefs);
            }
        }
        
        // Return default preferences if no preferences found
        return $this->defaultPreferences;
    }

    /**
     * Save user notification preferences
     * @param int $userId User ID
     * @param array $preferences User preferences
     * @return bool True on success, false on failure
     */
    public function saveUserPreferences($userId, $preferences) {
        // Make sure we have all required fields
        $preferences = array_merge($this->defaultPreferences, $preferences);
        
        // Convert to JSON for storage
        $prefsJson = json_encode($preferences);
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both new and existing users
        $stmt = $this->conn->prepare("
            INSERT INTO user_notification_preferences (user_id, preferences, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                preferences = VALUES(preferences),
                updated_at = NOW()
        ");
        
        $stmt->bind_param('is', $userId, $prefsJson);
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
        $count = $result->fetch_assoc()['count'];
        $stmt->close();
        
        return (int)$count;
    }

    /**
     * Delete a notification for a user
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
        $affected = $stmt->affected_rows > 0;
        $stmt->close();
        
        return $affected;
    }

    /**
     * Clean up expired notifications
     * @return bool True on success, false on failure
     */
    public function cleanupExpiredNotifications() {
        // Soft delete user notifications for expired notifications
        $stmt = $this->conn->prepare("
            UPDATE user_notifications un
            JOIN notifications n ON un.notification_id = n.id
            SET un.deleted_at = NOW()
            WHERE n.expires_at IS NOT NULL 
              AND n.expires_at <= NOW()
              AND un.deleted_at IS NULL
        ");
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get notification by ID
     * @param int $notificationId Notification ID
     * @param int $userId Optional user ID to verify ownership
     * @return array|bool Notification data or false if not found
     */
    public function getNotification($notificationId, $userId = null) {
        $query = "
            SELECT n.*, nt.name as type, nt.description as type_description,
                   un.user_id, un.is_read, un.read_at
            FROM notifications n
            JOIN notification_types nt ON n.type_id = nt.id
            LEFT JOIN user_notifications un ON n.id = un.notification_id
            WHERE n.id = ?
        ";
        
        $params = [$notificationId];
        $types = 'i';
        
        if ($userId !== null) {
            $query .= " AND un.user_id = ?";
            $params[] = $userId;
            $types .= 'i';
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            error_log("Failed to get notification: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $notification = $result->fetch_assoc();
        $stmt->close();
        
        return $notification;
    }

    /**
     * Get notifications for admin dashboard
     * @param int $limit Maximum number of notifications to return
     * @return array Array of notifications
     */
    public function getRecentNotifications($limit = 10) {
        $limit = (int)$limit;
        $query = "
            SELECT n.*, nt.name as type, nt.description as type_description,
                   COUNT(DISTINCT un.user_id) as recipient_count,
                   COUNT(DISTINCT CASE WHEN un.is_read = 1 THEN un.id END) as read_count
            FROM notifications n
            JOIN notification_types nt ON n.type_id = nt.id
            LEFT JOIN user_notifications un ON n.id = un.notification_id
            WHERE un.deleted_at IS NULL
            GROUP BY n.id
            ORDER BY n.created_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $limit);
        
        if (!$stmt->execute()) {
            error_log("Failed to get recent notifications: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $notifications = [];
        
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        $stmt->close();
        return $notifications;
    }

    /**
     * Get notification statistics
     * @return array Array of statistics
     */
    public function getNotificationStats() {
        $stats = [
            'total' => 0,
            'unread' => 0,
            'by_type' => [],
            'recent_activity' => []
        ];
        
        try {
            // Get total and unread counts
            $query = "
                SELECT 
                    COUNT(DISTINCT n.id) as total,
                    COUNT(DISTINCT CASE WHEN un.is_read = 0 THEN n.id END) as unread
                FROM notifications n
                LEFT JOIN user_notifications un ON n.id = un.notification_id
                WHERE un.deleted_at IS NULL
            ";
            
            $result = $this->conn->query($query);
            if ($result && $row = $result->fetch_assoc()) {
                $stats['total'] = (int)$row['total'];
                $stats['unread'] = (int)$row['unread'];
            }
            
            // Get counts by type
            $query = "
                SELECT 
                    nt.name as type,
                    COUNT(DISTINCT n.id) as count
                FROM notifications n
                JOIN notification_types nt ON n.type_id = nt.id
                LEFT JOIN user_notifications un ON n.id = un.notification_id
                WHERE un.deleted_at IS NULL
                GROUP BY nt.name
                ORDER BY count DESC
            ";
            
            $result = $this->conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $stats['by_type'][$row['type']] = (int)$row['count'];
                }
            }
            
            // Get recent activity
            $query = "
                SELECT 
                    n.id,
                    n.title,
                    n.created_at,
                    nt.name as type,
                    COUNT(DISTINCT un.user_id) as recipient_count
                FROM notifications n
                JOIN notification_types nt ON n.type_id = nt.id
                LEFT JOIN user_notifications un ON n.id = un.notification_id
                WHERE un.deleted_at IS NULL
                GROUP BY n.id
                ORDER BY n.created_at DESC
                LIMIT 5
            ";
            
            $result = $this->conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $stats['recent_activity'][] = [
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'type' => $row['type'],
                        'created_at' => $row['created_at'],
                        'recipient_count' => (int)$row['recipient_count']
                    ];
                }
            }
            
        } catch (Exception $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Initialize notification types in the database
     * @return bool True on success, false on failure
     */
    public function initializeNotificationTypes() {
        $success = true;
        
        foreach ($this->notificationTypes as $type) {
            $stmt = $this->conn->prepare("
                INSERT IGNORE INTO notification_types (name, description, created_at)
                VALUES (?, ?, NOW())
            ");
            
            $stmt->bind_param('ss', $type['name'], $type['description']);
            if (!$stmt->execute()) {
                error_log("Failed to initialize notification type {$type['name']}: " . $stmt->error);
                $success = false;
            }
            $stmt->close();
        }
        
        return $success;
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
