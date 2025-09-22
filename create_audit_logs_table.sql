-- Create audit_logs table for system activity tracking
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `affected_table` varchar(50) DEFAULT NULL,
  `affected_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_module` (`module`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some sample audit log entries
INSERT INTO `audit_logs` (`user_id`, `username`, `action`, `module`, `details`, `affected_table`, `affected_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'admin', 'LOGIN', 'Authentication', 'User successfully logged into the system', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', NOW()),
(1, 'admin', 'CREATE', 'Assets', 'Created new asset: Laptop Dell XPS 15', 'assets', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'admin', 'UPDATE', 'Assets', 'Updated asset property number', 'assets', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 2 HOURS)),
(1, 'admin', 'CREATE', 'Red Tags', 'Created red tag for unserviceable asset', 'red_tags', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 3 HOURS)),
(1, 'admin', 'GENERATE', 'Reports', 'Generated inventory report for All Offices', 'reports', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 4 HOURS)),
(1, 'admin', 'DELETE', 'Assets', 'Deleted asset from No Property Tag section', 'assets', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 5 HOURS)),
(1, 'admin', 'CREATE', 'Users', 'Created new user account', 'users', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'admin', 'UPDATE', 'Settings', 'Updated system configuration', 'system', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'admin', 'CREATE', 'ICS Form', 'Created ICS form for asset transfer', 'ics_form', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(1, 'admin', 'PRINT', 'Red Tags', 'Bulk printed 5 red tags', 'red_tags', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', DATE_SUB(NOW(), INTERVAL 2 DAYS));
