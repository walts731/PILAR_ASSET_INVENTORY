-- Create user_roles junction table for many-to-many relationship between users and roles
CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `assigned_by` INT DEFAULT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add a color column to the roles table for Discord-like role colors
ALTER TABLE `roles` 
ADD COLUMN `color` VARCHAR(7) DEFAULT '#99AAB5' AFTER `description`,
ADD COLUMN `is_hoisted` TINYINT(1) DEFAULT 0 AFTER `color`,
ADD COLUMN `position` INT DEFAULT 0 AFTER `is_hoisted`;

-- Set initial positions based on role hierarchy
UPDATE `roles` SET 
    `position` = CASE 
        WHEN `name` = 'SYSTEM_ADMIN' THEN 100
        WHEN `name` = 'MAIN_ADMIN' THEN 80
        WHEN `name` = 'MAIN_EMPLOYEE' THEN 60
        WHEN `name` = 'MAIN_USER' THEN 40
        ELSE 0 
    END,
    `color` = CASE 
        WHEN `name` = 'SYSTEM_ADMIN' THEN '#FF0000'
        WHEN `name` = 'MAIN_ADMIN' THEN '#3498DB'
        WHEN `name` = 'MAIN_EMPLOYEE' THEN '#2ECC71'
        WHEN `name` = 'MAIN_USER' THEN '#99AAB5'
        ELSE '#99AAB5'
    END,
    `is_hoisted` = CASE 
        WHEN `name` IN ('SYSTEM_ADMIN', 'MAIN_ADMIN') THEN 1 
        ELSE 0 
    END;
