-- PILAR Asset Inventory - Notification System Database Changes
-- Created: 2025-09-29

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Create notification_types table
CREATE TABLE IF NOT EXISTS `notification_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `related_entity_type` varchar(50) DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `related_entity` (`related_entity_type`,`related_entity_id`),
  KEY `is_read` (`is_read`),
  KEY `is_archived` (`is_archived`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `notification_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create user_notifications table
CREATE TABLE IF NOT EXISTS `user_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_notification` (`user_id`,`notification_id`),
  KEY `notification_id` (`notification_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_notifications_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create user_notification_preferences table
CREATE TABLE IF NOT EXISTS `user_notification_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `in_app_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_type` (`user_id`,`type_id`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `user_notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_notification_preferences_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `notification_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Drop existing procedures if they exist
DROP PROCEDURE IF EXISTS `create_notification`;
DROP TRIGGER IF EXISTS `after_user_insert`;
DROP VIEW IF EXISTS `vw_user_notifications`;

-- Create the create_notification stored procedure
DELIMITER //
CREATE PROCEDURE `create_notification`(
    IN p_type_name VARCHAR(50),
    IN p_title VARCHAR(100),
    IN p_message TEXT,
    IN p_related_entity_type VARCHAR(50),
    IN p_related_entity_id INT,
    IN p_user_ids TEXT,
    IN p_expires_in_days INT
)
BEGIN
    DECLARE v_type_id INT;
    DECLARE v_notification_id INT;
    DECLARE v_expires_at TIMESTAMP;
    
    -- Get the notification type ID
    SELECT id INTO v_type_id FROM notification_types WHERE name = p_type_name LIMIT 1;
    
    -- Set expiration if specified
    IF p_expires_in_days > 0 THEN
        SET v_expires_at = TIMESTAMPADD(DAY, p_expires_in_days, NOW());
    END IF;
    
    -- Create the notification
    INSERT INTO notifications (
        type_id,
        title,
        message,
        related_entity_type,
        related_entity_id,
        expires_at
    ) VALUES (
        v_type_id,
        p_title,
        p_message,
        p_related_entity_type,
        p_related_entity_id,
        v_expires_at
    );
    
    SET v_notification_id = LAST_INSERT_ID();
    
    -- Create user notifications if user_ids are provided
    IF p_user_ids IS NOT NULL AND p_user_ids != '' THEN
        SET @sql = CONCAT('
            INSERT IGNORE INTO user_notifications (user_id, notification_id)
            SELECT id, ', v_notification_id, '
            FROM users
            WHERE id IN (', p_user_ids, ')
            AND is_active = 1
        ');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
    
    SELECT v_notification_id AS notification_id;
END //
DELIMITER ;

-- Create the vw_user_notifications view
CREATE VIEW `vw_user_notifications` AS
SELECT 
    un.id,
    un.user_id,
    n.id AS notification_id,
    n.title,
    n.message,
    n.related_entity_type,
    n.related_entity_id,
    nt.name AS notification_type,
    n.created_at,
    un.is_read,
    un.read_at,
    n.expires_at
FROM 
    user_notifications un
JOIN 
    notifications n ON un.notification_id = n.id
JOIN 
    notification_types nt ON n.type_id = nt.id
WHERE 
    un.deleted_at IS NULL
    AND (n.expires_at IS NULL OR n.expires_at > NOW());

-- Create the after_user_insert trigger
DELIMITER //
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
    INSERT INTO user_notification_preferences (user_id, type_id)
    SELECT NEW.id, id 
    FROM notification_types
    WHERE is_active = 1;
END //
DELIMITER ;

-- Insert default notification types
INSERT IGNORE INTO `notification_types` (`name`, `description`) VALUES
('low_stock', 'Notifications for low stock items'),
('borrow_request', 'Notifications about borrow requests'),
('borrow_approved', 'Notifications when a borrow request is approved'),
('borrow_rejected', 'Notifications when a borrow request is rejected'),
('due_date_reminder', 'Reminders for upcoming due dates'),
('overdue_notice', 'Notices for overdue items'),
('maintenance_reminder', 'Reminders for scheduled maintenance'),
('system_alert', 'Important system-wide alerts'),
('new_asset_assigned', 'Notification when a new asset is assigned to user'),
('asset_returned', 'Notification when an asset is returned');

-- Create notification preferences for existing users
-- Create notification preferences for existing users
INSERT IGNORE INTO user_notification_preferences (user_id, type_id)
SELECT u.id, nt.id
FROM users u
CROSS JOIN notification_types nt
WHERE NOT EXISTS (
    SELECT 1 
    FROM user_notification_preferences unp 
    WHERE unp.user_id = u.id AND unp.type_id = nt.id
);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Output success message
SELECT 'Notification system database changes applied successfully!' AS message;