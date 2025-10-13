-- Create user_notification_preferences table
CREATE TABLE IF NOT EXISTS `user_notification_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preferences` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_notification_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default preferences for existing users
INSERT IGNORE INTO `user_notification_preferences` (`user_id`, `preferences`)
SELECT 
    u.id,
    JSON_OBJECT(
        'email_notifications', true,
        'desktop_notifications', true,
        'sound_alert', true,
        'notification_types', JSON_OBJECT(
            'low_stock', true,
            'borrow_request', true,
            'borrow_approved', true,
            'borrow_rejected', true,
            'due_date_reminder', true,
            'overdue_notice', true,
            'maintenance_reminder', true,
            'system_alert', true,
            'new_asset_assigned', true,
            'asset_returned', true
        )
    )
FROM users u
LEFT JOIN user_notification_preferences unp ON u.id = unp.user_id
WHERE unp.id IS NULL;
