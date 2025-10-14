<?php
/**
 * GuestNotification Class
 * Handles notification functionality specifically for guest users
 */
class GuestNotification {
    private $conn;

    /**
     * Constructor
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Create a new guest notification
     * @param int $guestId Guest ID
     * @param string $type Notification type (borrow_approved, borrow_rejected, borrow_return_reminder)
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $relatedEntityType Type of related entity
     * @param int|null $relatedEntityId ID of the related entity
     * @param int $expiresInDays Number of days before the notification expires (0 = never)
     * @return int|bool Notification ID on success, false on failure
     */
    public function create($guestId, $type, $title, $message, $relatedEntityType = null, $relatedEntityId = null, $expiresInDays = 30) {
        // Calculate expiration date if needed
        $expiresAt = null;
        if ($expiresInDays > 0) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));
        }

        $stmt = $this->conn->prepare("
            INSERT INTO guest_notifications
            (guest_id, notification_type, title, message, related_entity_type, related_entity_id, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param('issssis', $guestId, $type, $title, $message, $relatedEntityType, $relatedEntityId, $expiresAt);

        if (!$stmt->execute()) {
            error_log("Failed to create guest notification: " . $stmt->error);
            return false;
        }

        $notificationId = $this->conn->insert_id;
        $stmt->close();

        return $notificationId;
    }

    /**
     * Get notifications for a specific guest
     * @param int $guestId Guest ID
     * @param bool $unreadOnly Whether to return only unread notifications
     * @param int $limit Maximum number of notifications to return
     * @return array Array of notifications
     */
    public function getGuestNotifications($guestId, $unreadOnly = false, $limit = 10) {
        $guestId = (int)$guestId;
        $limit = (int)$limit;

        $query = "
            SELECT id, notification_type, title, message, related_entity_type, related_entity_id,
                   is_read, read_at, created_at, expires_at
            FROM guest_notifications
            WHERE guest_id = ?
        ";

        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }

        $query .= " AND (expires_at IS NULL OR expires_at > NOW())";
        $query .= " ORDER BY created_at DESC";
        $query .= " LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $guestId, $limit);

        if (!$stmt->execute()) {
            error_log("Failed to get guest notifications: " . $stmt->error);
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
     * @param int $guestId Guest ID
     * @return bool True on success, false on failure
     */
    public function markAsRead($notificationId, $guestId) {
        $stmt = $this->conn->prepare("
            UPDATE guest_notifications
            SET is_read = 1, read_at = NOW()
            WHERE id = ? AND guest_id = ? AND is_read = 0
        ");

        $stmt->bind_param('ii', $notificationId, $guestId);
        $result = $stmt->execute();
        $affected = $stmt->affected_rows > 0;
        $stmt->close();

        return $affected;
    }

    /**
     * Mark all notifications as read for a guest
     * @param int $guestId Guest ID
     * @return bool True on success, false on failure
     */
    public function markAllAsRead($guestId) {
        $stmt = $this->conn->prepare("
            UPDATE guest_notifications
            SET is_read = 1, read_at = NOW()
            WHERE guest_id = ? AND is_read = 0
        ");

        $stmt->bind_param('i', $guestId);
        $result = $stmt->execute();
        $affected = $stmt->affected_rows > 0;
        $stmt->close();

        return $affected;
    }

    /**
     * Get unread notification count for a guest
     * @param int $guestId Guest ID
     * @return int Number of unread notifications
     */
    public function getUnreadCount($guestId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count
            FROM guest_notifications
            WHERE guest_id = ?
              AND is_read = 0
              AND (expires_at IS NULL OR expires_at > NOW())
        ");

        $stmt->bind_param('i', $guestId);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();

        return (int)$count;
    }

    /**
     * Send borrow request status update notification to guest
     * @param int $submissionId Borrow form submission ID
     * @param int $guestId Guest ID to notify
     * @param string $status New status (approved/rejected)
     * @param string $adminName Name of the admin who processed the request
     * @return int|bool Notification ID on success, false on failure
     */
    public function sendBorrowRequestStatusUpdate($submissionId, $guestId, $status, $adminName) {
        $statusText = ucfirst($status);
        $title = "Borrow Request $statusText";
        $message = "Your borrow request has been $status by $adminName";
        $type = $status === 'approved' ? 'borrow_approved' : 'borrow_rejected';

        return $this->create(
            $guestId,
            $type,
            $title,
            $message,
            'borrow_form_submission',
            $submissionId,
            14 // Expires in 14 days
        );
    }

    /**
     * Send return reminder notification to guest
     * @param int $submissionId Borrow form submission ID
     * @param int $guestId Guest ID to notify
     * @param string $assetNames Comma-separated list of asset names
     * @param string $dueDate Due date
     * @return int|bool Notification ID on success, false on failure
     */
    public function sendReturnReminder($submissionId, $guestId, $assetNames, $dueDate) {
        $title = "Return Reminder";
        $message = "Please remember to return your borrowed items ($assetNames) by $dueDate.";

        return $this->create(
            $guestId,
            'borrow_return_reminder',
            $title,
            $message,
            'borrow_form_submission',
            $submissionId,
            7 // Expires in 7 days
        );
    }

    /**
     * Clean up expired notifications
     * @return bool True on success, false on failure
     */
    public function cleanupExpiredNotifications() {
        $stmt = $this->conn->prepare("
            DELETE FROM guest_notifications
            WHERE expires_at IS NOT NULL
              AND expires_at <= NOW()
        ");

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Get notification by ID for a specific guest
     * @param int $notificationId Notification ID
     * @param int $guestId Guest ID
     * @return array|bool Notification data or false if not found
     */
    public function getNotification($notificationId, $guestId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM guest_notifications
            WHERE id = ? AND guest_id = ?
        ");

        $stmt->bind_param('ii', $notificationId, $guestId);

        if (!$stmt->execute()) {
            error_log("Failed to get guest notification: " . $stmt->error);
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
}

// Initialize GuestNotification class if included directly (for backward compatibility)
if (isset($conn) && !isset($guestNotification)) {
    $guestNotification = new GuestNotification($conn);
}
