-- Add preferences JSON column to user_notification_preferences table
ALTER TABLE `user_notification_preferences`
ADD COLUMN `preferences` JSON DEFAULT NULL COMMENT 'JSON object containing user notification preferences';

-- Update existing records with default preferences
UPDATE `user_notification_preferences`
SET `preferences` = JSON_OBJECT(
    'email_notifications', 1,
    'desktop_notifications', 1,
    'sound_alert', 1,
    'notification_types', JSON_OBJECT(
        'low_stock', 1,
        'borrow_request', 1,
        'borrow_approved', 1,
        'borrow_rejected', 1,
        'due_date_reminder', 1,
        'overdue_notice', 1,
        'maintenance_reminder', 1,
        'system_alert', 1,
        'new_asset_assigned', 1,
        'asset_returned', 1
    )
);
