-- Create roles table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create permissions table
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create role_permissions junction table
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default roles if they don't exist
INSERT IGNORE INTO `roles` (`name`, `description`) VALUES 
('SYSTEM_ADMIN', 'Has full access to all system features and configurations'),
('MAIN_ADMIN', 'Can manage assets, users, and basic system settings'),
('MAIN_EMPLOYEE', 'Can view and borrow assets'),
('MAIN_USER', 'Basic user with limited access');

-- Insert common permissions
INSERT IGNORE INTO `permissions` (`name`, `description`) VALUES 
('view_dashboard', 'Can view the dashboard'),
('manage_users', 'Can add, edit, and delete users'),
('manage_assets', 'Can add, edit, and delete assets'),
('view_reports', 'Can view system reports'),
('generate_reports', 'Can generate and export reports'),
('manage_roles', 'Can manage user roles and permissions'),
('borrow_assets', 'Can borrow assets'),
('approve_borrow_requests', 'Can approve or reject borrow requests'),
('manage_categories', 'Can manage asset categories'),
('system_settings', 'Can modify system settings');

-- Assign default permissions to SYSTEM_ADMIN (all permissions)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id 
FROM roles r, permissions p 
WHERE r.name = 'SYSTEM_ADMIN';

-- Assign basic permissions to MAIN_ADMIN
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id 
FROM roles r, permissions p 
WHERE r.name = 'MAIN_ADMIN' 
AND p.name IN ('view_dashboard', 'manage_users', 'manage_assets', 'view_reports', 'generate_reports', 'borrow_assets', 'approve_borrow_requests', 'manage_categories');

-- Assign basic permissions to MAIN_EMPLOYEE
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id 
FROM roles r, permissions p 
WHERE r.name = 'MAIN_EMPLOYEE' 
AND p.name IN ('view_dashboard', 'borrow_assets');

-- Assign basic permissions to MAIN_USER
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id 
FROM roles r, permissions p 
WHERE r.name = 'MAIN_USER' 
AND p.name IN ('view_dashboard');
