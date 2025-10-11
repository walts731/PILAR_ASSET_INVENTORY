-- Create inter-department borrowing tables

-- Drop existing views first to avoid conflicts
DROP VIEW IF EXISTS `approval_queue`;
DROP VIEW IF EXISTS `overdue_borrowings`;
DROP VIEW IF EXISTS `active_borrowings`;

-- Borrowing Request Status Table
CREATE TABLE IF NOT EXISTS `borrowing_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default statuses
INSERT INTO `borrowing_status` (`name`, `description`) VALUES
('pending', 'Request is pending approval'),
('approved', 'Request has been approved'),
('rejected', 'Request has been rejected'),
('cancelled', 'Request was cancelled'),
('in_progress', 'Items are being prepared'),
('ready_for_pickup', 'Items are ready for pickup'),
('in_transit', 'Items are in transit'),
('completed', 'Borrowing process completed'),
('returned', 'Items have been returned'),
('overdue', 'Items are overdue for return'),
('damaged', 'Items were returned damaged'),
('lost', 'Items were lost');

-- Borrowing Requests Table
CREATE TABLE IF NOT EXISTS `borrowing_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_number` varchar(50) NOT NULL,
  `requested_by` int(11) NOT NULL COMMENT 'User ID who created the request',
  `requested_for` int(11) DEFAULT NULL COMMENT 'User ID who will use the items (if different from requester)',
  `requesting_office_id` int(11) NOT NULL,
  `purpose` text NOT NULL,
  `request_date` datetime NOT NULL,
  `needed_by_date` date DEFAULT NULL,
  `expected_return_date` date NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`),
  KEY `requested_by` (`requested_by`),
  KEY `requested_for` (`requested_for`),
  KEY `requesting_office_id` (`requesting_office_id`),
  KEY `status_id` (`status_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `borrowing_requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `borrowing_requests_ibfk_2` FOREIGN KEY (`requested_for`) REFERENCES `users` (`id`),
  CONSTRAINT `borrowing_requests_ibfk_3` FOREIGN KEY (`requesting_office_id`) REFERENCES `offices` (`id`),
  CONSTRAINT `borrowing_requests_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `borrowing_status` (`id`),
  CONSTRAINT `borrowing_requests_ibfk_5` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Borrowing Request Items Table
CREATE TABLE IF NOT EXISTS `borrowing_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `returned_quantity` int(11) DEFAULT 0,
  `condition_before` text DEFAULT NULL COMMENT 'Condition before borrowing',
  `condition_after` text DEFAULT NULL COMMENT 'Condition after return',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `borrowing_request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `borrowing_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `borrowing_request_items_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Borrowing Request Approvals Table
CREATE TABLE IF NOT EXISTS `borrowing_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approval_level` int(11) NOT NULL COMMENT '1=First level, 2=Second level, etc.',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `borrowing_approvals_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `borrowing_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `borrowing_approvals_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Borrowing Request History Table
CREATE TABLE IF NOT EXISTS `borrowing_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `performed_by` (`performed_by`),
  CONSTRAINT `borrowing_history_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `borrowing_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `borrowing_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add columns to track borrowing if they don't exist
SET @dbname = DATABASE();
SET @tablename = "assets";

-- Add is_borrowed column if it doesn't exist
SET @columnname = "is_borrowed";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ",
         "ADD COLUMN ", @columnname, " TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`")
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add current_borrowing_request_id column if it doesn't exist
SET @columnname = "current_borrowing_request_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ",
         "ADD COLUMN ", @columnname, " INT(11) DEFAULT NULL AFTER `is_borrowed`")
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add key if it doesn't exist
SET @index_name = 'current_borrowing_request_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @index_name)
  ) = 0,
  CONCAT("ALTER TABLE ", @tablename, " ",
         "ADD KEY ", @index_name, " (", @columnname, ")"),
  "SELECT 1"
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add foreign key constraint if it doesn't exist
SET @constraint_name = 'assets_ibfk_borrowing';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (CONSTRAINT_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (CONSTRAINT_NAME = @constraint_name)
  ) = 0,
  CONCAT("ALTER TABLE ", @tablename, " ",
         "ADD CONSTRAINT ", @constraint_name, " ",
         "FOREIGN KEY (current_borrowing_request_id) REFERENCES borrowing_requests(id) ON DELETE SET NULL"),
  "SELECT 1"
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Create a view for active borrowings
CREATE VIEW `active_borrowings` AS
SELECT 
    br.id,
    br.request_number,
    br.request_date,
    br.expected_return_date,
    br.actual_return_date,
    bs.name as status,
    u1.fullname as requested_by,
    u2.fullname as requested_for,
    o.name as requesting_office,
    COUNT(bri.id) as total_items,
    GROUP_CONCAT(DISTINCT a.asset_name) as asset_names
FROM borrowing_requests br
JOIN borrowing_status bs ON br.status_id = bs.id
JOIN users u1 ON br.requested_by = u1.id
LEFT JOIN users u2 ON br.requested_for = u2.id
JOIN offices o ON br.requesting_office_id = o.id
JOIN borrowing_request_items bri ON br.id = bri.request_id
JOIN assets a ON bri.asset_id = a.id
WHERE bs.name NOT IN ('returned', 'cancelled', 'rejected')
GROUP BY br.id;

-- Create a view for overdue borrowings
CREATE VIEW `overdue_borrowings` AS
SELECT 
    br.*,
    DATEDIFF(CURRENT_DATE, br.expected_return_date) as days_overdue
FROM active_borrowings br
WHERE br.expected_return_date < CURRENT_DATE 
AND br.status NOT IN ('returned', 'cancelled', 'rejected');

-- Create a view for approval queue
CREATE VIEW `approval_queue` AS
SELECT 
    br.id,
    br.request_number,
    br.request_date,
    br.expected_return_date,
    u1.fullname as requested_by,
    o.name as requesting_office,
    COUNT(bri.id) as total_items,
    GROUP_CONCAT(DISTINCT a.asset_name) as asset_names
FROM borrowing_requests br
JOIN users u1 ON br.requested_by = u1.id
JOIN offices o ON br.requesting_office_id = o.id
JOIN borrowing_request_items bri ON br.id = bri.request_id
JOIN assets a ON bri.asset_id = a.id
WHERE br.status_id = (SELECT id FROM borrowing_status WHERE name = 'pending')
GROUP BY br.id;
