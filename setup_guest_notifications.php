<?php
// setup_guest_notifications.php
// Script to create the guest_notifications table

require_once 'connect.php';

$sql = "
CREATE TABLE IF NOT EXISTS `guest_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_id` int(11) NOT NULL COMMENT 'Guest ID from guests table',
  `notification_type` enum('borrow_approved','borrow_rejected','borrow_return_reminder') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_entity_type` enum('borrow_form_submission','borrow_request') DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `guest_id` (`guest_id`),
  KEY `notification_type` (`notification_type`),
  KEY `is_read` (`is_read`),
  KEY `expires_at` (`expires_at`),
  KEY `related_entity` (`related_entity_type`, `related_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notifications for guest users';
";

if ($conn->query($sql) === TRUE) {
    echo "✓ guest_notifications table created successfully!\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
