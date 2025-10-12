-- Guest Borrowing Tables

-- Guest Borrowing Requests Table
CREATE TABLE IF NOT EXISTS `guest_borrowing_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_number` varchar(50) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `guest_contact` varchar(50) DEFAULT NULL,
  `guest_organization` varchar(255) DEFAULT NULL,
  `purpose` text NOT NULL,
  `request_date` datetime NOT NULL,
  `needed_by_date` date DEFAULT NULL,
  `expected_return_date` date NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','in_progress','ready_for_pickup','in_transit','completed','returned','overdue','damaged','lost') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`),
  KEY `guest_email` (`guest_email`),
  KEY `status` (`status`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `guest_borrowing_requests_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Guest Borrowing Request Items Table
CREATE TABLE IF NOT EXISTS `guest_borrowing_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `returned_quantity` int(11) DEFAULT 0,
  `condition_before` text DEFAULT NULL,
  `condition_after` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `guest_borrowing_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `guest_borrowing_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guest_borrowing_items_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Guest Borrowing History Table
CREATE TABLE IF NOT EXISTS `guest_borrowing_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `performed_by` (`performed_by`),
  CONSTRAINT `guest_borrowing_history_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `guest_borrowing_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guest_borrowing_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add guest_borrowing_request_id to assets table
ALTER TABLE `assets` 
ADD COLUMN IF NOT EXISTS `guest_borrowing_request_id` INT(11) DEFAULT NULL AFTER `current_borrowing_request_id`,
ADD KEY IF NOT EXISTS `guest_borrowing_request_id` (`guest_borrowing_request_id`),
ADD CONSTRAINT `assets_ibfk_guest_borrowing` 
  FOREIGN KEY IF NOT EXISTS (`guest_borrowing_request_id`) 
  REFERENCES `guest_borrowing_requests` (`id`) 
  ON DELETE SET NULL;
