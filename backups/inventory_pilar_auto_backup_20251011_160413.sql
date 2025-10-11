-- Simple SQL Backup for inventory_pilar
-- Generated at: 2025-10-11T16:04:13+02:00

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

--
-- Structure for table `activity_log`
--
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `module` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `activity_log` (5 rows)
--
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('6','1','Added 20 IT Equipment to inventory','2025-04-02 09:05:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('7','1','Requested 15 Office Supplies','2025-04-02 10:10:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('8','1','Borrowed 5 IT Equipment','2025-04-02 11:15:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('9','1','Transferred 10 Office Supplies to Admin','2025-04-02 12:20:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('10','1','Added 30 IT Equipment to inventory','2025-04-02 13:25:00','Inventory Management');

--
-- Structure for table `app_settings`
--
DROP TABLE IF EXISTS `app_settings`;
CREATE TABLE `app_settings` (
  `key` varchar(64) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `archives`
--
DROP TABLE IF EXISTS `archives`;
CREATE TABLE `archives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `filter_status` varchar(50) DEFAULT NULL,
  `filter_office` varchar(50) DEFAULT NULL,
  `filter_category` varchar(50) DEFAULT NULL,
  `filter_start_date` date DEFAULT NULL,
  `filter_end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `archives` (8 rows)
--
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('1','12','Export CSV','','4','','0000-00-00','0000-00-00','2025-04-16 16:39:18','asset_report_20250416_113918.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('2','12','Export CSV','','4','','0000-00-00','0000-00-00','2025-04-21 18:55:23','asset_report_20250421_135523.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('3','1','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 18:58:08','assets_report_20250421_135808.pdf');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('4','1','Export CSV','','','','0000-00-00','0000-00-00','2025-04-21 18:58:09','asset_report_20250421_135809.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('5','1','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 18:58:21','assets_report_20250421_135821.pdf');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('6','12','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 18:59:41','assets_report_20250421_135941.pdf');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('7','12','Export CSV','','4','','0000-00-00','0000-00-00','2025-04-21 19:05:58','asset_report_20250421_140558.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('8','12','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 19:06:05','assets_report_20250421_140605.pdf');

--
-- Structure for table `asset_lifecycle_events`
--
DROP TABLE IF EXISTS `asset_lifecycle_events`;
CREATE TABLE `asset_lifecycle_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `event_type` enum('ACQUIRED','ASSIGNED','TRANSFERRED','DISPOSAL_LISTED','DISPOSED','RED_TAGGED') NOT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `from_employee_id` int(11) DEFAULT NULL,
  `to_employee_id` int(11) DEFAULT NULL,
  `from_office_id` int(11) DEFAULT NULL,
  `to_office_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_asset` (`asset_id`),
  KEY `idx_type` (`event_type`),
  KEY `idx_ref` (`ref_table`,`ref_id`),
  CONSTRAINT `fk_lifecycle_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `asset_requests`
--
DROP TABLE IF EXISTS `asset_requests`;
CREATE TABLE `asset_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(2555) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `office_id` int(11) NOT NULL,
  PRIMARY KEY (`request_id`),
  KEY `asset_id` (`asset_name`(768)),
  KEY `user_id` (`user_id`),
  KEY `fk_office` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `assets`
--
DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(100) NOT NULL,
  `category` int(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `added_stock` int(11) DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `status` enum('available','borrowed','in use','damaged','disposed','unserviceable','unavailable','lost','pending','serviceable') NOT NULL DEFAULT 'serviceable',
  `acquisition_date` date DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `end_user` varchar(255) DEFAULT NULL,
  `red_tagged` tinyint(1) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qr_code` varchar(255) DEFAULT NULL,
  `type` enum('asset','consumable') NOT NULL DEFAULT 'asset',
  `image` varchar(255) DEFAULT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `ics_id` int(11) DEFAULT NULL,
  `par_id` int(11) DEFAULT NULL,
  `ris_id` int(11) DEFAULT NULL,
  `asset_new_id` int(11) DEFAULT NULL,
  `inventory_tag` varchar(255) DEFAULT NULL,
  `additional_images` text DEFAULT NULL COMMENT 'JSON array storing paths to up to 4 additional images for the asset',
  `enable_batch_tracking` tinyint(1) DEFAULT 0,
  `default_batch_size` int(11) DEFAULT 1,
  `batch_expiry_required` tinyint(1) DEFAULT 0,
  `batch_manufacturer_required` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `idx_assets_office_status` (`office_id`,`status`),
  KEY `idx_assets_status` (`status`),
  KEY `idx_assets_ics_id` (`ics_id`),
  KEY `idx_assets_asset_new_id` (`asset_new_id`),
  KEY `idx_assets_par_id` (`par_id`),
  KEY `idx_assets_batch_tracking` (`enable_batch_tracking`),
  KEY `idx_assets_ris_id` (`ris_id`),
  CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`category`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_assets_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_assets_ics` FOREIGN KEY (`ics_id`) REFERENCES `ics_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_assets_par` FOREIGN KEY (`par_id`) REFERENCES `par_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `assets_archive`
--
DROP TABLE IF EXISTS `assets_archive`;
CREATE TABLE `assets_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) DEFAULT NULL,
  `asset_name` varchar(100) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `red_tagged` tinyint(1) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `value` decimal(10,2) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) DEFAULT NULL,
  `end_user` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `inventory_tag` varchar(255) DEFAULT NULL,
  `asset_new_id` int(11) DEFAULT NULL,
  `additional_images` text DEFAULT NULL,
  `deletion_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `assets_new`
--
DROP TABLE IF EXISTS `assets_new`;
CREATE TABLE `assets_new` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(50) NOT NULL,
  `office_id` int(11) NOT NULL DEFAULT 0,
  `par_id` int(11) DEFAULT NULL,
  `ics_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_description` (`description`),
  KEY `idx_assets_new_office_id` (`office_id`),
  KEY `idx_assets_new_par_id` (`par_id`),
  CONSTRAINT `fk_assets_new_par` FOREIGN KEY (`par_id`) REFERENCES `par_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure for table `audit_logs`
--
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
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
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `audit_logs` (2 rows)
--
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('1','17','nami','LOGOUT','Authentication','User \'nami\' logged out successfully',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','2025-10-11 21:03:44');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('2','1','ompdc','LOGIN','Authentication','User \'ompdc\' logged in successfully (Role: super_admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','2025-10-11 21:04:07');

--
-- Structure for table `backups`
--
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `path` text NOT NULL,
  `size_bytes` bigint(20) DEFAULT NULL,
  `storage` enum('local','cloud','both') DEFAULT 'local',
  `status` enum('success','failed') DEFAULT 'success',
  `triggered_by` enum('manual','scheduled') DEFAULT 'manual',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `batch_items`
--
DROP TABLE IF EXISTS `batch_items`;
CREATE TABLE `batch_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `item_number` varchar(50) NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `inventory_tag` varchar(255) DEFAULT NULL,
  `status` enum('available','borrowed','in_use','damaged','disposed','expired','quarantined') DEFAULT 'available',
  `current_location` varchar(255) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `condition_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_batch_item` (`batch_id`,`item_number`),
  KEY `idx_batch_id` (`batch_id`),
  KEY `idx_serial_number` (`serial_number`),
  KEY `idx_status` (`status`),
  KEY `idx_office_id` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `batch_receipts`
--
DROP TABLE IF EXISTS `batch_receipts`;
CREATE TABLE `batch_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `received_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `received_by` int(11) DEFAULT NULL,
  `quantity_received` int(11) NOT NULL,
  `quantity_accepted` int(11) NOT NULL,
  `quantity_rejected` int(11) DEFAULT 0,
  `rejection_reason` text DEFAULT NULL,
  `quality_check_status` enum('pending','passed','failed') DEFAULT 'pending',
  `quality_check_date` date DEFAULT NULL,
  `quality_check_by` int(11) DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_receipt_batch` (`batch_id`,`receipt_number`),
  KEY `idx_receipt_number` (`receipt_number`),
  KEY `idx_delivery_date` (`delivery_date`),
  KEY `idx_received_by` (`received_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `batch_transactions`
--
DROP TABLE IF EXISTS `batch_transactions`;
CREATE TABLE `batch_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `batch_item_id` int(11) DEFAULT NULL,
  `transaction_type` enum('received','issued','borrowed','returned','transferred','disposed','expired','damaged','quarantined','released') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `from_location` varchar(255) DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL,
  `from_office_id` int(11) DEFAULT NULL,
  `to_office_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient_user_id` int(11) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `reference_type` enum('borrow_request','transfer','disposal','adjustment') DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_batch_id` (`batch_id`),
  KEY `idx_batch_item_id` (`batch_item_id`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_reference_id` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `batches`
--
DROP TABLE IF EXISTS `batches`;
CREATE TABLE `batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(50) NOT NULL,
  `batch_name` varchar(255) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `production_date` date DEFAULT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `batch_size` int(11) NOT NULL DEFAULT 1,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `total_value` decimal(12,2) DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `quality_status` enum('pending','approved','rejected','quarantined') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_number` (`batch_number`),
  KEY `idx_batch_number` (`batch_number`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_expiry_date` (`expiry_date`),
  KEY `idx_manufacture_date` (`manufacture_date`),
  KEY `idx_quality_status` (`quality_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `borrow_requests`
--
DROP TABLE IF EXISTS `borrow_requests`;
CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','borrowed','returned','pending_approval') NOT NULL DEFAULT 'pending',
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `return_remarks` text DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `batch_id` int(11) DEFAULT NULL,
  `batch_item_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_inter_department` tinyint(1) NOT NULL DEFAULT 0,
  `source_office_id` int(11) DEFAULT NULL,
  `requested_by_user_id` int(11) DEFAULT NULL,
  `requested_for_office_id` int(11) DEFAULT NULL,
  `approved_by_office_head` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by_source_office` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_borrow_requests_user_id` (`user_id`),
  KEY `idx_borrow_requests_asset_id` (`asset_id`),
  KEY `idx_borrow_requests_office_id` (`office_id`),
  KEY `idx_borrow_requests_status` (`status`),
  KEY `idx_borrow_requests_requested_at` (`requested_at`),
  KEY `fk_borrow_batch` (`batch_id`),
  KEY `fk_borrow_batch_item` (`batch_item_id`),
  KEY `fk_borrow_requests_source_office` (`source_office_id`),
  KEY `fk_borrow_requests_requested_by` (`requested_by_user_id`),
  KEY `fk_borrow_requests_requested_for_office` (`requested_for_office_id`),
  CONSTRAINT `fk_borrow_requests_requested_by` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_borrow_requests_requested_for_office` FOREIGN KEY (`requested_for_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_borrow_requests_source_office` FOREIGN KEY (`source_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `categories`
--
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_code` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `type` enum('asset','consumables') NOT NULL DEFAULT 'asset',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `categories` (5 rows)
--
INSERT INTO `categories` (`id`,`category_name`,`category_code`,`status`,`type`) VALUES ('1','Office Equipments','OFFEQ','1','asset');
INSERT INTO `categories` (`id`,`category_name`,`category_code`,`status`,`type`) VALUES ('2','Furnitures & Fixtures','FUR','1','asset');
INSERT INTO `categories` (`id`,`category_name`,`category_code`,`status`,`type`) VALUES ('4','Vehicles','VEH','1','asset');
INSERT INTO `categories` (`id`,`category_name`,`category_code`,`status`,`type`) VALUES ('5','Machinery & Equipment','MACH','1','asset');
INSERT INTO `categories` (`id`,`category_name`,`category_code`,`status`,`type`) VALUES ('6','Information & Communication Technology','ICT','1','asset');

--
-- Structure for table `category`
--
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `consumption_log`
--
DROP TABLE IF EXISTS `consumption_log`;
CREATE TABLE `consumption_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `quantity_consumed` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `dispensed_by_user_id` int(11) NOT NULL,
  `consumption_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  KEY `recipient_user_id` (`recipient_user_id`),
  KEY `dispensed_by_user_id` (`dispensed_by_user_id`),
  KEY `fk_consumption_log_office` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `doc_no`
--
DROP TABLE IF EXISTS `doc_no`;
CREATE TABLE `doc_no` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `document_number` varchar(50) NOT NULL,
  PRIMARY KEY (`doc_id`),
  UNIQUE KEY `document_number` (`document_number`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `doc_no` (5 rows)
--
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('1','GS21A-003');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('2','GS21A-005');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('3','GS22A-001');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('4','GSP-2024-08-0001-1');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('5','MO21A-012');

--
-- Structure for table `email_notifications`
--
DROP TABLE IF EXISTS `email_notifications`;
CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `error_message` text DEFAULT NULL,
  `related_asset_id` int(11) DEFAULT NULL,
  `related_mr_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `employees`
--
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_no` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('permanent','casual','contractual','job_order','probationary','resigned','retired') NOT NULL,
  `clearance_status` enum('cleared','uncleared') DEFAULT 'uncleared',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `employee_no` (`employee_no`),
  KEY `fk_employees_office` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `employees` (91 rows)
--
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('1','EMP0001','Juan A. Dela Cruz',NULL,'permanent','uncleared','2025-08-31 21:25:29','emp_68b45b59bbe19.jpg','2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('2','EMP0002','Maria Santos',NULL,'permanent','uncleared','2025-09-01 08:39:29','emp_68b4f95154506.jpg','7');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('3','EMP0003','Pedro Reyes',NULL,'contractual','uncleared','2025-09-01 08:50:43','emp_68b4fbf33d3ad.jpg','2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('8','EMP0004','Ryan Bang',NULL,'permanent','uncleared','2025-09-20 19:03:27',NULL,'7');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('9','EMP0005','John Smith',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('10','EMP0006','Emily Johnson',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('11','EMP0007','Jessica Davis',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('12','EMP0008','Daniel Wilson',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'15');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('13','EMP0009','Sophia Martinez',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('14','EMP0010','David Anderson',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('15','EMP0011','Olivia Thomas',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('16','EMP0012','James Taylor',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'21');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('17','EMP0013','Emma Moore',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('18','EMP0014','William Jackson',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('19','EMP0015','Ava White',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('20','EMP0016','Alexander Harris',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('21','EMP0017','Isabella Martin',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('22','EMP0018','Benjamin Thompson',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('23','EMP0019','Mia Garcia',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('24','EMP0020','Ethan Martinez',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'21');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('25','EMP0021','Amelia Lewis',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('26','EMP0022','Harper Walker',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('27','EMP0023','Lucas Hall',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('28','EMP0024','Evelyn Allen',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('29','EMP0025','Mason Young',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('30','EMP0026','Abigail King',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('31','EMP0027','James Scott',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('32','EMP0028','Ella Green',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'15');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('33','EMP0029','Henry Adams',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('34','EMP0030','Sebastian Gonzalez',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('35','EMP0031','Victoria Nelson',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('36','EMP0032','Jackson Carter',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('37','EMP0033','Grace Mitchell',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('38','EMP0034','Owen Perez',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('39','EMP0035','Lily Roberts',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('40','EMP0036','Jacob Turner',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('41','EMP0037','Hannah Phillips',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('42','EMP0038','Samuel Campbell',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'21');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('43','EMP0039','Zoe Parker',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('44','EMP0040','Mateo Evans',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('45','EMP0041','Aria Edwards',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'38');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('46','EMP0042','Levi Collins',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'38');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('47','EMP0043','Nora Stewart',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('48','EMP0044','Wyatt Sanchez',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('49','EMP0045','Camila Morris',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('50','EMP0046','Carter Rogers',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('51','EMP0047','Penelope Reed',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('52','EMP0048','Julian Cook',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('53','EMP0049','Riley Morgan',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'15');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('54','EMP0050','Nathan Bell',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('55','EMP0051','Lillian Murphy',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('56','EMP0052','Aurora Rivera',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('57','EMP0053','Isaac Cooper',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('58','EMP0054','Violet Richardson',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('59','EMP0055','Stella Howard',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('60','EMP0056','Brooklyn Torres',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('61','EMP0057','Leo Peterson',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('62','EMP0058','Hannah Gray',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('63','EMP0059','Anthony Ramirez',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('64','EMP0060','Addison James',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('65','EMP0061','Madison Brooks',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('66','EMP0062','Joshua Kelly',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('67','EMP0063','Eli Price',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('68','EMP0064','Paisley Bennett',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'38');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('69','EMP0065','Gabriel Wood',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('70','EMP0066','Caleb Ross',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('71','EMP0067','Aurora Henderson',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'38');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('72','EMP0068','Ryan Coleman',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('73','EMP0069','Scarlett Jenkins',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('74','EMP0070','Luke Perry',NULL,'retired','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('75','EMP0071','Nora Powell',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'15');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('76','EMP0072','Hannah Patterson',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('77','EMP0073','Cameron Hughes',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('78','EMP0074','Violet Flores',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('79','EMP0075','Connor Washington',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'21');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('80','EMP0076','Grace Butler',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'15');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('81','EMP0077','Wyatt Simmons',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('82','EMP0078','Lillian Foster',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('83','EMP0079','Brayden Gonzales',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('84','EMP0080','Elena Bryant',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('85','EMP0081','Zoe Russell',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('86','EMP0082','Aaron Griffin',NULL,'resigned','uncleared','2025-09-24 14:41:21',NULL,'47');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('87','EMP0083','Hazel Diaz',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'4');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('88','EMP0084','Charles Hayes',NULL,'contractual','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('89','EMP0085','Aurora Myers',NULL,'job_order','uncleared','2025-09-24 14:41:21',NULL,'14');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('90','EMP0086','Thomas Ford',NULL,'permanent','uncleared','2025-09-24 14:41:21',NULL,'3');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('91','EMP0087','Walton Loneza','waltonloneza@gmail.com','permanent','uncleared','2025-10-09 09:25:57',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('92','EMP0088','Juan Dela Cruz','waltielappy@gmail.com','permanent','uncleared','2025-10-09 09:40:19',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('93','EMP0089','Maria Clara','','contractual','uncleared','2025-10-09 09:40:19',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('94','EMP0090','Pedro Santos','','resigned','uncleared','2025-10-09 09:40:19',NULL,'33');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`email`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('95','EMP0091','tin marcellana','llenaresascristine61@gmail.com','permanent','uncleared','2025-10-09 15:32:33',NULL,'33');

--
-- Structure for table `form_thresholds`
--
DROP TABLE IF EXISTS `form_thresholds`;
CREATE TABLE `form_thresholds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ics_max` decimal(15,2) NOT NULL DEFAULT 50000.00,
  `par_min` decimal(15,2) NOT NULL DEFAULT 50000.00,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `form_thresholds` (1 rows)
--
INSERT INTO `form_thresholds` (`id`,`ics_max`,`par_min`,`updated_at`) VALUES ('1','50000.00','50000.00','2025-10-03 13:03:10');

--
-- Structure for table `forms`
--
DROP TABLE IF EXISTS `forms`;
CREATE TABLE `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `forms` (5 rows)
--
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('3','Property Acknowledgement Receipt (PAR)','PAR','par_form.php','2025-08-05 09:17:00');
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('4','Inventory Custodian Slip (ICS)','ICS','ics_form.php','2025-08-05 09:17:00');
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('6','Requisition & Issue Slip (RIS)','RIS','ris_form.php','2025-08-05 09:17:00');
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('7','Inventory & Inspection Report of Unserviceable Property (IIRUP)','IIRUP','iirup_form.php','2025-08-12 19:53:40');
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('9','Inventory Transfer Receipt (ITR)','ITR','itr_form.php\r\n','2025-09-24 15:32:02');

--
-- Structure for table `fuel_out`
--
DROP TABLE IF EXISTS `fuel_out`;
CREATE TABLE `fuel_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fo_date` date NOT NULL,
  `fo_time_in` time NOT NULL,
  `fo_fuel_no` varchar(100) DEFAULT NULL,
  `fo_plate_no` varchar(100) DEFAULT NULL,
  `fo_request` varchar(255) DEFAULT NULL,
  `fo_liters` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fo_fuel_type` varchar(100) DEFAULT NULL,
  `fo_vehicle_type` varchar(100) DEFAULT NULL,
  `fo_receiver` varchar(255) NOT NULL,
  `fo_time_out` time DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fo_date` (`fo_date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `fuel_out` (2 rows)
--
INSERT INTO `fuel_out` (`id`,`fo_date`,`fo_time_in`,`fo_fuel_no`,`fo_plate_no`,`fo_request`,`fo_liters`,`fo_fuel_type`,`fo_vehicle_type`,`fo_receiver`,`fo_time_out`,`created_by`,`created_at`) VALUES ('5','2025-09-27','15:42:00','SEP27-364','YAWA B75','MOTORPOOL','3.00','Diesel','BACKHOE','J.LUMIBAO','15:42:00','17','2025-09-27 17:42:43');
INSERT INTO `fuel_out` (`id`,`fo_date`,`fo_time_in`,`fo_fuel_no`,`fo_plate_no`,`fo_request`,`fo_liters`,`fo_fuel_type`,`fo_vehicle_type`,`fo_receiver`,`fo_time_out`,`created_by`,`created_at`) VALUES ('6','2025-02-19','07:30:00','FEB25-363','1135','MOTORPOOL','30.00','Unleaded','SINOTRUCK','A.LOBRIGO',NULL,'17','2025-09-28 09:33:50');

--
-- Structure for table `fuel_records`
--
DROP TABLE IF EXISTS `fuel_records`;
CREATE TABLE `fuel_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime NOT NULL,
  `fuel_type` varchar(50) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `storage_location` varchar(255) NOT NULL,
  `delivery_receipt` varchar(100) DEFAULT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `received_by` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `date_time` (`date_time`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `fuel_records` (6 rows)
--
INSERT INTO `fuel_records` (`id`,`date_time`,`fuel_type`,`quantity`,`unit_price`,`total_cost`,`storage_location`,`delivery_receipt`,`supplier_name`,`received_by`,`remarks`,`created_by`,`created_at`) VALUES ('2','2025-09-27 11:26:00','Diesel','2.00','55.00','110.00','Storage Room','34567568977578800','Elton John Moises','Roy Ricacho','For vehicles','17','2025-09-27 10:26:39');
INSERT INTO `fuel_records` (`id`,`date_time`,`fuel_type`,`quantity`,`unit_price`,`total_cost`,`storage_location`,`delivery_receipt`,`supplier_name`,`received_by`,`remarks`,`created_by`,`created_at`) VALUES ('3','2025-09-27 13:38:00','Diesel','2.00','55.00','110.00','Storage Room','34526567709800','Elton John Moises','Roy Ricacho','For Ambulance','17','2025-09-27 15:39:02');
INSERT INTO `fuel_records` (`id`,`date_time`,`fuel_type`,`quantity`,`unit_price`,`total_cost`,`storage_location`,`delivery_receipt`,`supplier_name`,`received_by`,`remarks`,`created_by`,`created_at`) VALUES ('4','2025-09-28 07:21:00','Kerosene','30.00','56.00','1680.00','Storage Room','34910046678231','James Smith','Roy Ricacho','Reserve Stock','17','2025-09-28 09:23:22');
INSERT INTO `fuel_records` (`id`,`date_time`,`fuel_type`,`quantity`,`unit_price`,`total_cost`,`storage_location`,`delivery_receipt`,`supplier_name`,`received_by`,`remarks`,`created_by`,`created_at`) VALUES ('5','2025-09-28 07:24:00','Premium','60.00','54.00','3240.00','Storage Room','236588177450900','Mark Levi','Roy Ricacho','Reserve Stock','17','2025-09-28 09:25:43');
INSERT INTO `fuel_records` (`id`,`date_time`,`fuel_type`,`quantity`,`unit_price`,`total_cost`,`storage_location`,`delivery_receipt`,`supplier_name`,`received_by`,`remarks`,`created_by`,`created_at`) VALUES ('6','2025-09-28 07:26:00','Unleaded','100.00','52.00','5200.00','Storage Room','23454676878445','Elton John Moises','Roy Ricacho','Reserve Stock','17','2025-09-28 09:27:24');
INSERT INTO `fuel_records` (`id`,`date_time`,`fuel_type`,`quantity`,`unit_price`,`total_cost`,`storage_location`,`delivery_receipt`,`supplier_name`,`received_by`,`remarks`,`created_by`,`created_at`) VALUES ('7','2025-09-28 07:28:00','Diesel','50.00','52.00','2600.00','Storage Room','3233511774566','Jake Paul','Roy Ricacho','Restock','17','2025-09-28 09:29:40');

--
-- Structure for table `fuel_stock`
--
DROP TABLE IF EXISTS `fuel_stock`;
CREATE TABLE `fuel_stock` (
  `fuel_type_id` int(11) NOT NULL,
  `quantity` decimal(14,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `fuel_type_id` (`fuel_type_id`),
  CONSTRAINT `fuel_stock_ibfk_1` FOREIGN KEY (`fuel_type_id`) REFERENCES `fuel_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `fuel_stock` (4 rows)
--
INSERT INTO `fuel_stock` (`fuel_type_id`,`quantity`,`updated_at`) VALUES ('1','51.00','2025-09-28 09:29:40');
INSERT INTO `fuel_stock` (`fuel_type_id`,`quantity`,`updated_at`) VALUES ('2','30.00','2025-09-28 09:23:22');
INSERT INTO `fuel_stock` (`fuel_type_id`,`quantity`,`updated_at`) VALUES ('3','70.00','2025-09-28 09:33:50');
INSERT INTO `fuel_stock` (`fuel_type_id`,`quantity`,`updated_at`) VALUES ('4','60.00','2025-09-28 09:25:43');

--
-- Structure for table `fuel_types`
--
DROP TABLE IF EXISTS `fuel_types`;
CREATE TABLE `fuel_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `fuel_types` (4 rows)
--
INSERT INTO `fuel_types` (`id`,`name`,`is_active`) VALUES ('1','Diesel','1');
INSERT INTO `fuel_types` (`id`,`name`,`is_active`) VALUES ('2','Kerosene','1');
INSERT INTO `fuel_types` (`id`,`name`,`is_active`) VALUES ('3','Unleaded','1');
INSERT INTO `fuel_types` (`id`,`name`,`is_active`) VALUES ('4','Premium','1');

--
-- Structure for table `generated_reports`
--
DROP TABLE IF EXISTS `generated_reports`;
CREATE TABLE `generated_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `template_id` int(11) NOT NULL,
  `generated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `google_drive_settings`
--
DROP TABLE IF EXISTS `google_drive_settings`;
CREATE TABLE `google_drive_settings` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `client_id` text DEFAULT NULL,
  `client_secret` text DEFAULT NULL,
  `redirect_uri` text DEFAULT NULL,
  `folder_id` varchar(128) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `google_drive_settings` (1 rows)
--
INSERT INTO `google_drive_settings` (`id`,`client_id`,`client_secret`,`redirect_uri`,`folder_id`,`created_at`,`updated_at`) VALUES ('1','','','http://localhost/PILAR_ASSET_INVENTORY/SYSTEM_ADMIN/drive_oauth_callback.php','','2025-09-24 01:05:27','2025-09-27 07:34:18');

--
-- Structure for table `google_drive_tokens`
--
DROP TABLE IF EXISTS `google_drive_tokens`;
CREATE TABLE `google_drive_tokens` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `refresh_token` text DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `google_drive_tokens` (1 rows)
--
INSERT INTO `google_drive_tokens` (`id`,`refresh_token`,`access_token`,`expires_at`,`created_at`,`updated_at`) VALUES ('1',NULL,NULL,NULL,'2025-09-24 01:05:27',NULL);

--
-- Structure for table `ics_form`
--
DROP TABLE IF EXISTS `ics_form`;
CREATE TABLE `ics_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(255) DEFAULT NULL,
  `fund_cluster` varchar(100) DEFAULT NULL,
  `ics_no` varchar(100) DEFAULT NULL,
  `received_from_name` varchar(255) NOT NULL,
  `received_from_position` varchar(255) NOT NULL,
  `received_by_name` varchar(255) NOT NULL,
  `received_by_position` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `office_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `ics_items`
--
DROP TABLE IF EXISTS `ics_items`;
CREATE TABLE `ics_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `ics_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `ics_no` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `estimated_useful_life` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `ics_id` (`ics_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `iirup_form`
--
DROP TABLE IF EXISTS `iirup_form`;
CREATE TABLE `iirup_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_image` varchar(255) DEFAULT NULL,
  `accountable_officer` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `office` varchar(100) NOT NULL,
  `footer_accountable_officer` varchar(100) NOT NULL,
  `footer_authorized_official` varchar(100) NOT NULL,
  `footer_designation_officer` varchar(100) NOT NULL,
  `footer_designation_official` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `iirup_items`
--
DROP TABLE IF EXISTS `iirup_items`;
CREATE TABLE `iirup_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `iirup_id` int(11) DEFAULT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `accumulated_depreciation` decimal(12,2) DEFAULT NULL,
  `accumulated_impairment_losses` decimal(12,2) DEFAULT NULL,
  `carrying_amount` decimal(12,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `sale` varchar(255) DEFAULT NULL,
  `transfer` varchar(255) DEFAULT NULL,
  `destruction` varchar(255) DEFAULT NULL,
  `others` varchar(255) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `appraised_value` decimal(12,2) DEFAULT NULL,
  `or_no` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `dept_office` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `red_tag` varchar(255) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `idx_iirup_id` (`iirup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `iirup_temp_storage`
--
DROP TABLE IF EXISTS `iirup_temp_storage`;
CREATE TABLE `iirup_temp_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`form_data`)),
  `asset_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`asset_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 7 day),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `infrastructure_inventory`
--
DROP TABLE IF EXISTS `infrastructure_inventory`;
CREATE TABLE `infrastructure_inventory` (
  `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
  `classification_type` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `nature_occupancy` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_constructed_acquired_manufactured` date DEFAULT NULL,
  `property_no_or_reference` varchar(100) DEFAULT NULL,
  `acquisition_cost` decimal(15,2) DEFAULT NULL,
  `market_appraisal_insurable_interest` decimal(15,2) DEFAULT NULL,
  `date_of_appraisal` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `image_1` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `image_4` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`inventory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `infrastructure_inventory` (1 rows)
--
INSERT INTO `infrastructure_inventory` (`inventory_id`,`classification_type`,`item_description`,`nature_occupancy`,`location`,`date_constructed_acquired_manufactured`,`property_no_or_reference`,`acquisition_cost`,`market_appraisal_insurable_interest`,`date_of_appraisal`,`remarks`,`image_1`,`image_2`,`image_3`,`image_4`) VALUES ('1','BUILDING','Multi Purpose bldg.','Gymnasium','LGU-Complex','2025-09-04','BLDNG22-32','6792388.00','777406.50','2025-09-04','','uploads/1756949402_397369.jpg','uploads/1756949402_ChatGPT Image Jul 17, 2025, 08_24_14 AM.png',NULL,NULL);

--
-- Structure for table `inter_department_approvals`
--
DROP TABLE IF EXISTS `inter_department_approvals`;
CREATE TABLE `inter_department_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approval_type` enum('office_head','source_office') NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `fk_ida_approver` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ida_request` FOREIGN KEY (`request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `inventory_actions`
--
DROP TABLE IF EXISTS `inventory_actions`;
CREATE TABLE `inventory_actions` (
  `action_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_name` varchar(255) NOT NULL,
  `office_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`action_id`),
  KEY `office_id` (`office_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `itr_form`
--
DROP TABLE IF EXISTS `itr_form`;
CREATE TABLE `itr_form` (
  `itr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(150) NOT NULL,
  `fund_cluster` varchar(100) DEFAULT NULL,
  `from_accountable_officer` varchar(150) NOT NULL,
  `to_accountable_officer` varchar(150) NOT NULL,
  `itr_no` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `transfer_type` enum('donation','reassignment','relocate','others') NOT NULL,
  `reason_for_transfer` text DEFAULT NULL,
  `approved_by` varchar(150) DEFAULT NULL,
  `approved_designation` varchar(150) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `released_by` varchar(150) DEFAULT NULL,
  `released_designation` varchar(150) DEFAULT NULL,
  `released_date` date DEFAULT NULL,
  `received_by` varchar(150) DEFAULT NULL,
  `received_designation` varchar(150) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  PRIMARY KEY (`itr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure for table `itr_items`
--
DROP TABLE IF EXISTS `itr_items`;
CREATE TABLE `itr_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `itr_id` int(10) unsigned NOT NULL,
  `item_no` int(11) DEFAULT 1,
  `date_acquired` date DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `asset_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `condition_of_PPE` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `idx_itr_id` (`itr_id`),
  KEY `idx_asset_id` (`asset_id`),
  CONSTRAINT `fk_itr_items_assets` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_itr_items_itr_form` FOREIGN KEY (`itr_id`) REFERENCES `itr_form` (`itr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure for table `legal_document_history`
--
DROP TABLE IF EXISTS `legal_document_history`;
CREATE TABLE `legal_document_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `document_type` enum('privacy_policy','terms_of_service') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `version` varchar(50) NOT NULL,
  `effective_date` date NOT NULL,
  `updated_by` int(11) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`),
  KEY `document_type` (`document_type`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_legal_history_doc_version` (`document_id`,`version`),
  CONSTRAINT `legal_document_history_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `legal_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `legal_document_history_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `legal_documents`
--
DROP TABLE IF EXISTS `legal_documents`;
CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_type` enum('privacy_policy','terms_of_service') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `version` varchar(50) NOT NULL DEFAULT '1.0',
  `effective_date` date NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `legal_documents_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `legal_documents` (11 rows)
--
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('1','privacy_policy','Privacy Policy','<h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\n<p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\n<ul>\n    <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\n    <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\n    <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\n    <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\n<p>We use your information to:</p>\n<ul>\n    <li>Provide and maintain the asset inventory management system</li>\n    <li>Authenticate users and maintain account security</li>\n    <li>Track asset movements and maintain audit trails</li>\n    <li>Send important system notifications and updates</li>\n    <li>Improve system functionality and user experience</li>\n    <li>Comply with legal and regulatory requirements</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\n<p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\n<ul>\n    <li>With authorized personnel within your organization</li>\n    <li>When required by law or legal process</li>\n    <li>To protect the security and integrity of our systems</li>\n    <li>With your explicit consent</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\n<p>We implement appropriate security measures to protect your information:</p>\n<ul>\n    <li>Encrypted password storage using industry-standard hashing</li>\n    <li>Secure session management with timeout controls</li>\n    <li>Regular security audits and monitoring</li>\n    <li>Access controls based on user roles and permissions</li>\n    <li>Secure data transmission using HTTPS protocols</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>','1.0','2025-09-28','2025-09-29 09:56:50','1','0','2025-09-28 20:40:44');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('2','terms_of_service','Terms of Service','<h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\r\n<p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\r\n<p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\r\n<ul>\r\n    <li>Track and manage organizational assets</li>\r\n    <li>Maintain detailed asset records and histories</li>\r\n    <li>Provide role-based access controls</li>\r\n    <li>Generate reports and analytics</li>\r\n    <li>Ensure compliance with asset management policies</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\r\n<p><strong>Account Security:</strong></p>\r\n<ul>\r\n    <li>You are responsible for maintaining the confidentiality of your login credentials</li>\r\n    <li>You must notify administrators immediately of any unauthorized access</li>\r\n    <li>You agree to use strong passwords and enable security features when available</li>\r\n    <li>You are liable for all activities that occur under your account</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\r\n<p>You agree not to:</p>\r\n<ul>\r\n    <li>Attempt to gain unauthorized access to any part of the System</li>\r\n    <li>Interfere with or disrupt the System operation</li>\r\n    <li>Use the System for any illegal or unauthorized purpose</li>\r\n    <li>Reverse engineer, decompile, or disassemble any part of the System</li>\r\n    <li>Introduce viruses, malware, or other harmful code</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\r\n<p>For questions about these Terms of Service, please contact:</p>\r\n<div class=\"bg-light p-3 rounded\">\r\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\r\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\r\n    Phone: +1 (555) 123-4567<br>\r\n    Address: [Your Organization Address]\r\n</div>','1.0','2025-09-28','2025-09-29 10:03:58','1','0','2025-09-28 20:40:44');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('3','privacy_policy','Privacy Policy','<h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\n<p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\n<ul>\n    <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\n    <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\n    <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\n    <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\n<p>We use your information to:</p>\n<ul>\n    <li>Provide and maintain the asset inventory management system</li>\n    <li>Authenticate users and maintain account security</li>\n    <li>Track asset movements and maintain audit trails</li>\n    <li>Send important system notifications and updates</li>\n    <li>Improve system functionality and user experience</li>\n    <li>Comply with legal and regulatory requirements</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\n<p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\n<ul>\n    <li>With authorized personnel within your organization</li>\n    <li>When required by law or legal process</li>\n    <li>To protect the security and integrity of our systems</li>\n    <li>With your explicit consent</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\n<p>We implement appropriate security measures to protect your information:</p>\n<ul>\n    <li>Encrypted password storage using industry-standard hashing</li>\n    <li>Secure session management with timeout controls</li>\n    <li>Regular security audits and monitoring</li>\n    <li>Access controls based on user roles and permissions</li>\n    <li>Secure data transmission using HTTPS protocols</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>','1.0','2025-09-28','2025-09-29 09:56:50','1','0','2025-09-28 20:43:48');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('4','terms_of_service','Terms of Service','<h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\n<p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\n<p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\n<ul>\n    <li>Track and manage organizational assets</li>\n    <li>Maintain detailed asset records and histories</li>\n    <li>Provide role-based access controls</li>\n    <li>Generate reports and analytics</li>\n    <li>Ensure compliance with asset management policies</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\n<p><strong>Account Security:</strong></p>\n<ul>\n    <li>You are responsible for maintaining the confidentiality of your login credentials</li>\n    <li>You must notify administrators immediately of any unauthorized access</li>\n    <li>You agree to use strong passwords and enable security features when available</li>\n    <li>You are liable for all activities that occur under your account</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\n<p>You agree not to:</p>\n<ul>\n    <li>Attempt to gain unauthorized access to any part of the System</li>\n    <li>Interfere with or disrupt the System operation</li>\n    <li>Use the System for any illegal or unauthorized purpose</li>\n    <li>Reverse engineer, decompile, or disassemble any part of the System</li>\n    <li>Introduce viruses, malware, or other harmful code</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>For questions about these Terms of Service, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>','1.0','2025-09-28','2025-09-29 10:03:58','1','0','2025-09-28 20:43:48');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('5','privacy_policy','Privacy Policy','<div class=\"privacy-content\">\n    <p class=\"text-muted mb-4\">\n        <strong>Effective Date:</strong> <?= date(\'F j, Y\'); ?><br>\n        <strong>Last Updated:</strong> <?= date(\'F j, Y\'); ?>\n    </p>\n\n    <h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\n    <p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\n    <ul>\n        <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\n        <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\n        <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\n        <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\n    <p>We use your information to:</p>\n    <ul>\n        <li>Provide and maintain the asset inventory management system</li>\n        <li>Authenticate users and maintain account security</li>\n        <li>Track asset movements and maintain audit trails</li>\n        <li>Send important system notifications and updates</li>\n        <li>Improve system functionality and user experience</li>\n        <li>Comply with legal and regulatory requirements</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\n    <p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\n    <ul>\n        <li>With authorized personnel within your organization</li>\n        <li>When required by law or legal process</li>\n        <li>To protect the security and integrity of our systems</li>\n        <li>With your explicit consent</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\n    <p>We implement appropriate security measures to protect your information:</p>\n    <ul>\n        <li>Encrypted password storage using industry-standard hashing</li>\n        <li>Secure session management with timeout controls</li>\n        <li>Regular security audits and monitoring</li>\n        <li>Access controls based on user roles and permissions</li>\n        <li>Secure data transmission using HTTPS protocols</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Data Retention</h6>\n    <p>We retain your information for as long as necessary to:</p>\n    <ul>\n        <li>Provide the services you\'ve requested</li>\n        <li>Maintain audit trails as required by regulations</li>\n        <li>Comply with legal obligations</li>\n        <li>Resolve disputes and enforce agreements</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">6. Your Rights</h6>\n    <p>You have the right to:</p>\n    <ul>\n        <li>Access and review your personal information</li>\n        <li>Request corrections to inaccurate data</li>\n        <li>Request deletion of your account (subject to legal requirements)</li>\n        <li>Receive information about data breaches that may affect you</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">7. Cookies and Tracking</h6>\n    <p>We use cookies and similar technologies to:</p>\n    <ul>\n        <li>Maintain your login session</li>\n        <li>Remember your preferences</li>\n        <li>Provide \"Remember Me\" functionality</li>\n        <li>Analyze system usage for improvements</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">8. Changes to This Policy</h6>\n    <p>We may update this Privacy Policy from time to time. We will notify users of any material changes by:</p>\n    <ul>\n        <li>Posting the updated policy on this page</li>\n        <li>Sending email notifications for significant changes</li>\n        <li>Updating the \"Last Updated\" date at the top of this policy</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">9. Contact Information</h6>\n    <p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\n    <div class=\"bg-light p-3 rounded\">\n        <strong>PILAR Asset Inventory System Administrator</strong><br>\n        Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n        Phone: +1 (555) 123-4567<br>\n        Address: [Your Organization Address]\n    </div>\n</div>','1','0000-00-00','2025-09-29 09:56:50','1','0','2025-09-28 21:12:18');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('6','terms_of_service','Terms of Service','<div class=\"terms-content\">\n    <p class=\"text-muted mb-4\">\n        <strong>Effective Date:</strong> <?= date(\'F j, Y\'); ?><br>\n        <strong>Last Updated:</strong> <?= date(\'F j, Y\'); ?>\n    </p>\n\n    <h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\n    <p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\n    <p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\n    <ul>\n        <li>Track and manage organizational assets</li>\n        <li>Maintain detailed asset records and histories</li>\n        <li>Provide role-based access controls</li>\n        <li>Generate reports and analytics</li>\n        <li>Ensure compliance with asset management policies</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\n    <p><strong>Account Security:</strong></p>\n    <ul>\n        <li>You are responsible for maintaining the confidentiality of your login credentials</li>\n        <li>You must notify administrators immediately of any unauthorized access</li>\n        <li>You agree to use strong passwords and enable security features when available</li>\n        <li>You are liable for all activities that occur under your account</li>\n    </ul>\n    \n    <p><strong>Authorized Use:</strong></p>\n    <ul>\n        <li>Access is granted only to authorized personnel</li>\n        <li>You may only access data and functions appropriate to your assigned role</li>\n        <li>Sharing of login credentials is strictly prohibited</li>\n        <li>You must comply with your organization\'s asset management policies</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\n    <p>You agree not to:</p>\n    <ul>\n        <li>Attempt to gain unauthorized access to any part of the System</li>\n        <li>Interfere with or disrupt the System\'s operation</li>\n        <li>Use the System for any illegal or unauthorized purpose</li>\n        <li>Reverse engineer, decompile, or disassemble any part of the System</li>\n        <li>Introduce viruses, malware, or other harmful code</li>\n        <li>Access or attempt to access accounts belonging to other users</li>\n        <li>Export or share sensitive asset data without proper authorization</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Data Accuracy and Integrity</h6>\n    <p>Users are responsible for:</p>\n    <ul>\n        <li>Ensuring the accuracy of data entered into the System</li>\n        <li>Promptly updating asset information when changes occur</li>\n        <li>Reporting discrepancies or errors to system administrators</li>\n        <li>Following established procedures for asset management</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">6. System Availability</h6>\n    <p>While we strive to maintain continuous service:</p>\n    <ul>\n        <li>The System may be temporarily unavailable for maintenance</li>\n        <li>We do not guarantee 100% uptime or availability</li>\n        <li>Scheduled maintenance will be announced in advance when possible</li>\n        <li>Emergency maintenance may occur without prior notice</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">7. Intellectual Property</h6>\n    <p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p>\n    <ul>\n        <li>All software, designs, and documentation remain our property</li>\n        <li>You receive a limited license to use the System for its intended purpose</li>\n        <li>You may not copy, modify, or distribute any part of the System</li>\n        <li>Your organization retains ownership of data entered into the System</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">8. Privacy and Data Protection</h6>\n    <p>Your privacy is important to us:</p>\n    <ul>\n        <li>Please review our Privacy Policy for details on data handling</li>\n        <li>We implement security measures to protect your information</li>\n        <li>You consent to data processing as described in our Privacy Policy</li>\n        <li>We comply with applicable data protection regulations</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">9. Limitation of Liability</h6>\n    <p>To the maximum extent permitted by law:</p>\n    <ul>\n        <li>We provide the System \"as is\" without warranties</li>\n        <li>We are not liable for indirect, incidental, or consequential damages</li>\n        <li>Our total liability is limited to the amount paid for System access</li>\n        <li>You agree to indemnify us against claims arising from your use of the System</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">10. Termination</h6>\n    <p>These terms remain in effect until terminated:</p>\n    <ul>\n        <li>Your access may be suspended or terminated for violations of these terms</li>\n        <li>You may request account termination by contacting administrators</li>\n        <li>Upon termination, you must cease all use of the System</li>\n        <li>Certain provisions of these terms survive termination</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">11. Changes to Terms</h6>\n    <p>We reserve the right to modify these terms:</p>\n    <ul>\n        <li>Changes will be posted on this page with an updated effective date</li>\n        <li>Continued use after changes constitutes acceptance</li>\n        <li>Material changes will be communicated to users</li>\n        <li>You should review these terms periodically</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">12. Governing Law</h6>\n    <p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">13. Contact Information</h6>\n    <p>For questions about these Terms of Service, please contact:</p>\n    <div class=\"bg-light p-3 rounded\">\n        <strong>PILAR Asset Inventory System Administrator</strong><br>\n        Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n        Phone: +1 (555) 123-4567<br>\n        Address: [Your Organization Address]\n    </div>\n\n    <div class=\"alert alert-info mt-4\">\n        <i class=\"bi bi-info-circle me-2\"></i>\n        <strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.\n    </div>\n</div>','1','0000-00-00','2025-09-29 10:03:58','1','0','2025-09-28 21:12:18');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('14','terms_of_service','Terms of Service','<p><strong>Effective Date:</strong> </p><p><strong>Last Updated:</strong> </p><h6>1. Acceptance of Terms</h6><p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p><p><br></p><h6>2. System Description</h6><p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p><ul><li>Track and manage organizational assets</li><li>Maintain detailed asset records and histories</li><li>Provide role-based access controls</li><li>Generate reports and analytics</li><li>Ensure compliance with asset management policies</li></ul><h6>3. User Accounts and Responsibilities</h6><p><strong>Account Security:</strong></p><ul><li>You are responsible for maintaining the confidentiality of your login credentials</li><li>You must notify administrators immediately of any unauthorized access</li><li>You agree to use strong passwords and enable security features when available</li><li>You are liable for all activities that occur under your account</li></ul><p><strong>Authorized Use:</strong></p><ul><li>Access is granted only to authorized personnel</li><li>You may only access data and functions appropriate to your assigned role</li><li>Sharing of login credentials is strictly prohibited</li><li>You must comply with your organization\'s asset management policies</li></ul><h6>4. Prohibited Activities</h6><p>You agree not to:</p><ul><li>Attempt to gain unauthorized access to any part of the System</li><li>Interfere with or disrupt the System\'s operation</li><li>Use the System for any illegal or unauthorized purpose</li><li>Reverse engineer, decompile, or disassemble any part of the System</li><li>Introduce viruses, malware, or other harmful code</li><li>Access or attempt to access accounts belonging to other users</li><li>Export or share sensitive asset data without proper authorization</li></ul><h6>5. Data Accuracy and Integrity</h6><p>Users are responsible for:</p><ul><li>Ensuring the accuracy of data entered into the System</li><li>Promptly updating asset information when changes occur</li><li>Reporting discrepancies or errors to system administrators</li><li>Following established procedures for asset management</li></ul><h6>6. System Availability</h6><p>While we strive to maintain continuous service:</p><ul><li>The System may be temporarily unavailable for maintenance</li><li>We do not guarantee 100% uptime or availability</li><li>Scheduled maintenance will be announced in advance when possible</li><li>Emergency maintenance may occur without prior notice</li></ul><h6>7. Intellectual Property</h6><p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p><ul><li>All software, designs, and documentation remain our property</li><li>You receive a limited license to use the System for its intended purpose</li><li>You may not copy, modify, or distribute any part of the System</li><li>Your organization retains ownership of data entered into the System</li></ul><h6>8. Privacy and Data Protection</h6><p>Your privacy is important to us:</p><ul><li>Please review our Privacy Policy for details on data handling</li><li>We implement security measures to protect your information</li><li>You consent to data processing as described in our Privacy Policy</li><li>We comply with applicable data protection regulations</li></ul><h6>9. Limitation of Liability</h6><p>To the maximum extent permitted by law:</p><ul><li>We provide the System \"as is\" without warranties</li><li>We are not liable for indirect, incidental, or consequential damages</li><li>Our total liability is limited to the amount paid for System access</li><li>You agree to indemnify us against claims arising from your use of the System</li></ul><h6>10. Termination</h6><p>These terms remain in effect until terminated:</p><ul><li>Your access may be suspended or terminated for violations of these terms</li><li>You may request account termination by contacting administrators</li><li>Upon termination, you must cease all use of the System</li><li>Certain provisions of these terms survive termination</li></ul><h6>11. Changes to Terms</h6><p>We reserve the right to modify these terms:</p><ul><li>Changes will be posted on this page with an updated effective date</li><li>Continued use after changes constitutes acceptance</li><li>Material changes will be communicated to users</li><li>You should review these terms periodically</li></ul><h6>12. Governing Law</h6><p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p><p><br></p><h6>13. Contact Information</h6><p>For questions about these Terms of Service, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p> Email: <a href=\"mailto:admin@pilar-system.com\" target=\"_blank\">admin@pilar-system.com</a></p><p> Phone: +1 (555) 123-4567</p><p> Address: [Your Organization Address]calongay</p><p><strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.</p>','1','2025-09-29','2025-09-29 10:05:27','1','0','2025-09-29 10:03:58');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('15','terms_of_service','Terms of Service','<p><strong>Effective Date:</strong></p><p><strong>Last Updated:</strong></p><h6>1. Acceptance of Terms</h6><p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p><p><br></p><h6>2. System Description</h6><p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p><ul><li>Track and manage organizational assets</li><li>Maintain detailed asset records and histories</li><li>Provide role-based access controls</li><li>Generate reports and analytics</li><li>Ensure compliance with asset management policies</li></ul><h6>3. User Accounts and Responsibilities</h6><p><strong>Account Security:</strong></p><ul><li>You are responsible for maintaining the confidentiality of your login credentials</li><li>You must notify administrators immediately of any unauthorized access</li><li>You agree to use strong passwords and enable security features when available</li><li>You are liable for all activities that occur under your account</li></ul><p><strong>Authorized Use:</strong></p><ul><li>Access is granted only to authorized personnel</li><li>You may only access data and functions appropriate to your assigned role</li><li>Sharing of login credentials is strictly prohibited</li><li>You must comply with your organization\'s asset management policies</li></ul><h6>4. Prohibited Activities</h6><p>You agree not to:</p><ul><li>Attempt to gain unauthorized access to any part of the System</li><li>Interfere with or disrupt the System\'s operation</li><li>Use the System for any illegal or unauthorized purpose</li><li>Reverse engineer, decompile, or disassemble any part of the System</li><li>Introduce viruses, malware, or other harmful code</li><li>Access or attempt to access accounts belonging to other users</li><li>Export or share sensitive asset data without proper authorization</li></ul><h6>5. Data Accuracy and Integrity</h6><p>Users are responsible for:</p><ul><li>Ensuring the accuracy of data entered into the System</li><li>Promptly updating asset information when changes occur</li><li>Reporting discrepancies or errors to system administrators</li><li>Following established procedures for asset management</li></ul><h6>6. System Availability</h6><p>While we strive to maintain continuous service:</p><ul><li>The System may be temporarily unavailable for maintenance</li><li>We do not guarantee 100% uptime or availability</li><li>Scheduled maintenance will be announced in advance when possible</li><li>Emergency maintenance may occur without prior notice</li></ul><h6>7. Intellectual Property</h6><p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p><ul><li>All software, designs, and documentation remain our property</li><li>You receive a limited license to use the System for its intended purpose</li><li>You may not copy, modify, or distribute any part of the System</li><li>Your organization retains ownership of data entered into the System</li></ul><h6>8. Privacy and Data Protection</h6><p>Your privacy is important to us:</p><ul><li>Please review our Privacy Policy for details on data handling</li><li>We implement security measures to protect your information</li><li>You consent to data processing as described in our Privacy Policy</li><li>We comply with applicable data protection regulations</li></ul><h6>9. Limitation of Liability</h6><p>To the maximum extent permitted by law:</p><ul><li>We provide the System \"as is\" without warranties</li><li>We are not liable for indirect, incidental, or consequential damages</li><li>Our total liability is limited to the amount paid for System access</li><li>You agree to indemnify us against claims arising from your use of the System</li></ul><h6>10. Termination</h6><p>These terms remain in effect until terminated:</p><ul><li>Your access may be suspended or terminated for violations of these terms</li><li>You may request account termination by contacting administrators</li><li>Upon termination, you must cease all use of the System</li><li>Certain provisions of these terms survive termination</li></ul><h6>11. Changes to Terms</h6><p>We reserve the right to modify these terms:</p><ul><li>Changes will be posted on this page with an updated effective date</li><li>Continued use after changes constitutes acceptance</li><li>Material changes will be communicated to users</li><li>You should review these terms periodically</li></ul><h6>12. Governing Law</h6><p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p><p><br></p><h6>13. Contact Information</h6><p>For questions about these Terms of Service, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email: <a href=\"mailto:admin@pilar-system.com\" target=\"_blank\">admin@pilar-system.com</a></p><p>Phone: +1 (555) 123-4567</p><p>Address: Calongay </p><p><strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.</p>','1','2025-09-29','2025-09-29 10:12:21','1','0','2025-09-29 10:05:27');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('16','privacy_policy','Privacy Policy','<p><strong>Effective Date:</strong>&nbsp;September 29, 2025</p><p><strong>Last Updated:</strong>&nbsp;September 29, 2025</p><h6>1. Information We Collect</h6><p>When you use the PILAR Asset Inventory System, we collect the following information:</p><ul><li><strong>Account Information:</strong>&nbsp;Username, full name, email address, and role assignments</li><li><strong>System Usage Data:</strong>&nbsp;Login times, asset management activities, and audit logs</li><li><strong>Technical Information:</strong>&nbsp;IP addresses, browser type, and session data for security purposes</li><li><strong>Asset Data:</strong>&nbsp;Information about assets you manage within the system</li></ul><h6>2. How We Use Your Information</h6><p>We use your information to:</p><ul><li>Provide and maintain the asset inventory management system</li><li>Authenticate users and maintain account security</li><li>Track asset movements and maintain audit trails</li><li>Send important system notifications and updates</li><li>Improve system functionality and user experience</li><li>Comply with legal and regulatory requirements</li></ul><h6>3. Information Sharing</h6><p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p><ul><li>With authorized personnel within your organization</li><li>When required by law or legal process</li><li>To protect the security and integrity of our systems</li><li>With your explicit consent</li></ul><h6>4. Data Security</h6><p>We implement appropriate security measures to protect your information:</p><ul><li>Encrypted password storage using industry-standard hashing</li><li>Secure session management with timeout controls</li><li>Regular security audits and monitoring</li><li>Access controls based on user roles and permissions</li><li>Secure data transmission using HTTPS protocols</li></ul><h6>5. Data Retention</h6><p>We retain your information for as long as necessary to:</p><ul><li>Provide the services you\'ve requested</li><li>Maintain audit trails as required by regulations</li><li>Comply with legal obligations</li><li>Resolve disputes and enforce agreements</li></ul><h6>6. Your Rights</h6><p>You have the right to:</p><ul><li>Access and review your personal information</li><li>Request corrections to inaccurate data</li><li>Request deletion of your account (subject to legal requirements)</li><li>Receive information about data breaches that may affect you</li></ul><h6>7. Cookies and Tracking</h6><p>We use cookies and similar technologies to:</p><ul><li>Maintain your login session</li><li>Remember your preferences</li><li>Provide \"Remember Me\" functionality</li><li>Analyze system usage for improvements</li></ul><h6>8. Changes to This Policy</h6><p>We may update this Privacy Policy from time to time. We will notify users of any material changes by:</p><ul><li>Posting the updated policy on this page</li><li>Sending email notifications for significant changes</li><li>Updating the \"Last Updated\" date at the top of this policy</li></ul><h6>9. Contact Information</h6><p>If you have questions about this Privacy Policy or our data practices, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email:&nbsp;<a href=\"mailto:admin@pilar-system.com\" target=\"_blank\" style=\"color: rgb(13, 110, 253);\">admin@pilar-system.com</a></p><p>Phone: +1 (555) 123-4567</p><p>Address: Calongay</p>','1.0','2025-09-29','2025-09-29 10:11:28','1','0','2025-09-29 10:07:05');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('17','privacy_policy','Privacy Policy','<p><strong>Effective Date:</strong>&nbsp;September 29, 2025</p><p><strong>Last Updated:</strong>&nbsp;September 29, 2025</p><h6>1. Information We Collect</h6><p>When you use the PILAR Asset Inventory System, we collect the following information:</p><ul><li><strong>Account Information:</strong>&nbsp;Username, full name, email address, and role assignments</li><li><strong>System Usage Data:</strong>&nbsp;Login times, asset management activities, and audit logs</li><li><strong>Technical Information:</strong>&nbsp;IP addresses, browser type, and session data for security purposes</li><li><strong>Asset Data:</strong>&nbsp;Information about assets you manage within the system</li></ul><h6>2. How We Use Your Information</h6><p>We use your information to:</p><ul><li>Provide and maintain the asset inventory management system</li><li>Authenticate users and maintain account security</li><li>Track asset movements and maintain audit trails</li><li>Send important system notifications and updates</li><li>Improve system functionality and user experience</li><li>Comply with legal and regulatory requirements</li></ul><h6>3. Information Sharing</h6><p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p><ul><li>With authorized personnel within your organization</li><li>When required by law or legal process</li><li>To protect the security and integrity of our systems</li><li>With your explicit consent</li></ul><h6>4. Data Security</h6><p>We implement appropriate security measures to protect your information:</p><ul><li>Encrypted password storage using industry-standard hashing</li><li>Secure session management with timeout controls</li><li>Regular security audits and monitoring</li><li>Access controls based on user roles and permissions</li><li>Secure data transmission using HTTPS protocols</li></ul><h6>5. Data Retention</h6><p>We retain your information for as long as necessary to:</p><ul><li>Provide the services you\'ve requested</li><li>Maintain audit trails as required by regulations</li><li>Comply with legal obligations</li><li>Resolve disputes and enforce agreements</li></ul><h6>6. Your Rights</h6><p>You have the right to:</p><ul><li>Access and review your personal information</li><li>Request corrections to inaccurate data</li><li>Request deletion of your account (subject to legal requirements)</li><li>Receive information about data breaches that may affect you</li></ul><h6>7. Cookies and Tracking</h6><p>We use cookies and similar technologies to:</p><ul><li>Maintain your login session</li><li>Remember your preferences</li><li>Provide \"Remember Me\" functionality</li><li>Analyze system usage for improvements</li></ul><h6>8. Changes to This Policy</h6><p>We may update this Privacy Policy from time to time. We will notify users of any material changes by:</p><ul><li>Posting the updated policy on this page</li><li>Sending email notifications for significant changes</li><li>Updating the \"Last Updated\" date at the top of this policy</li></ul><h6>9. Contact Information</h6><p>If you have questions about this Privacy Policy or our data practices, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email:&nbsp;<span style=\"color: rgb(0, 29, 53);\">pilarsor.mayor@gmail.com</span></p><p>Phone: <span style=\"color: rgb(0, 29, 53);\">0909 899 6012</span></p><p>Address: <span style=\"color: rgb(0, 29, 53);\">LGU-Pilar Complex, Calongay, Pilar, Sorsogon</span></p>','1.0','2025-09-29','2025-09-29 10:11:28','1','1','2025-09-29 10:11:28');
INSERT INTO `legal_documents` (`id`,`document_type`,`title`,`content`,`version`,`effective_date`,`last_updated`,`updated_by`,`is_active`,`created_at`) VALUES ('18','terms_of_service','Terms of Service','<p><strong>Effective Date:</strong></p><p><strong>Last Updated:</strong></p><h6>1. Acceptance of Terms</h6><p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p><p><br></p><h6>2. System Description</h6><p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p><ul><li>Track and manage organizational assets</li><li>Maintain detailed asset records and histories</li><li>Provide role-based access controls</li><li>Generate reports and analytics</li><li>Ensure compliance with asset management policies</li></ul><h6>3. User Accounts and Responsibilities</h6><p><strong>Account Security:</strong></p><ul><li>You are responsible for maintaining the confidentiality of your login credentials</li><li>You must notify administrators immediately of any unauthorized access</li><li>You agree to use strong passwords and enable security features when available</li><li>You are liable for all activities that occur under your account</li></ul><p><strong>Authorized Use:</strong></p><ul><li>Access is granted only to authorized personnel</li><li>You may only access data and functions appropriate to your assigned role</li><li>Sharing of login credentials is strictly prohibited</li><li>You must comply with your organization\'s asset management policies</li></ul><h6>4. Prohibited Activities</h6><p>You agree not to:</p><ul><li>Attempt to gain unauthorized access to any part of the System</li><li>Interfere with or disrupt the System\'s operation</li><li>Use the System for any illegal or unauthorized purpose</li><li>Reverse engineer, decompile, or disassemble any part of the System</li><li>Introduce viruses, malware, or other harmful code</li><li>Access or attempt to access accounts belonging to other users</li><li>Export or share sensitive asset data without proper authorization</li></ul><h6>5. Data Accuracy and Integrity</h6><p>Users are responsible for:</p><ul><li>Ensuring the accuracy of data entered into the System</li><li>Promptly updating asset information when changes occur</li><li>Reporting discrepancies or errors to system administrators</li><li>Following established procedures for asset management</li></ul><h6>6. System Availability</h6><p>While we strive to maintain continuous service:</p><ul><li>The System may be temporarily unavailable for maintenance</li><li>We do not guarantee 100% uptime or availability</li><li>Scheduled maintenance will be announced in advance when possible</li><li>Emergency maintenance may occur without prior notice</li></ul><h6>7. Intellectual Property</h6><p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p><ul><li>All software, designs, and documentation remain our property</li><li>You receive a limited license to use the System for its intended purpose</li><li>You may not copy, modify, or distribute any part of the System</li><li>Your organization retains ownership of data entered into the System</li></ul><h6>8. Privacy and Data Protection</h6><p>Your privacy is important to us:</p><ul><li>Please review our Privacy Policy for details on data handling</li><li>We implement security measures to protect your information</li><li>You consent to data processing as described in our Privacy Policy</li><li>We comply with applicable data protection regulations</li></ul><h6>9. Limitation of Liability</h6><p>To the maximum extent permitted by law:</p><ul><li>We provide the System \"as is\" without warranties</li><li>We are not liable for indirect, incidental, or consequential damages</li><li>Our total liability is limited to the amount paid for System access</li><li>You agree to indemnify us against claims arising from your use of the System</li></ul><h6>10. Termination</h6><p>These terms remain in effect until terminated:</p><ul><li>Your access may be suspended or terminated for violations of these terms</li><li>You may request account termination by contacting administrators</li><li>Upon termination, you must cease all use of the System</li><li>Certain provisions of these terms survive termination</li></ul><h6>11. Changes to Terms</h6><p>We reserve the right to modify these terms:</p><ul><li>Changes will be posted on this page with an updated effective date</li><li>Continued use after changes constitutes acceptance</li><li>Material changes will be communicated to users</li><li>You should review these terms periodically</li></ul><h6>12. Governing Law</h6><p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p><p><br></p><h6>13. Contact Information</h6><p>For questions about these Terms of Service, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email: <span style=\"color: rgb(0, 29, 53);\">pilarsor.mayor@gmail.com</span></p><p>Phone: <span style=\"color: rgb(0, 29, 53);\">0909 899 6012</span></p><p>Address: <strong>LGU-Pilar Complex, Calongay, Pilar, Sorsogon</strong></p><p><br></p><p><strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.</p>','1','2025-09-29','2025-09-29 10:12:21','1','1','2025-09-29 10:12:21');

--
-- Structure for table `mr_details`
--
DROP TABLE IF EXISTS `mr_details`;
CREATE TABLE `mr_details` (
  `mr_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `office_location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `model_no` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `serviceable` tinyint(1) DEFAULT 0,
  `unserviceable` tinyint(1) DEFAULT 0,
  `unit_quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `acquisition_date` date NOT NULL,
  `acquisition_cost` decimal(12,2) NOT NULL,
  `person_accountable` varchar(255) DEFAULT NULL,
  `end_user` varchar(255) DEFAULT NULL,
  `acquired_date` date DEFAULT NULL,
  `counted_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `asset_id` int(11) DEFAULT NULL,
  `inventory_tag` varchar(50) NOT NULL,
  PRIMARY KEY (`mr_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `notification_types`
--
DROP TABLE IF EXISTS `notification_types`;
CREATE TABLE `notification_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `notifications`
--
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
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

--
-- Structure for table `offices`
--
DROP TABLE IF EXISTS `offices`;
CREATE TABLE `offices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `office_name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `head_user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `office_name` (`office_name`),
  KEY `fk_offices_head_user` (`head_user_id`),
  CONSTRAINT `fk_offices_head_user` FOREIGN KEY (`head_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `offices` (45 rows)
--
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('1','MPDC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('2','IT Office',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('3','OMASS',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('4','Supply Office',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('5','OMAD',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('7','RHU Office',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('9','Main',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('11','OMSWD',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('13','OBAC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('14','COA',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('15','COMELEC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('16','CSOLAR',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('17','DILG',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('18','MENRU',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('19','GAD',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('20','GS-Motorpool',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('21','ABC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('22','SEF-DEPED',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('23','HRMO',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('24','KALAHI',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('25','LIBRARY',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('26','OMAC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('27','OMA',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('28','OMBO',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('29','MCR',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('30','MDRRMO',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('31','OME',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('32','MHO',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('33','OMM',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('34','MTC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('35','MTO-PORT-MARKET',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('36','NCDC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('37','OSCA',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('38','PAO',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('39','PiCC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('40','PIHC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('41','PIO-PESO',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('42','PNP',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('43','SB',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('44','SB-SEC',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('45','SK',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('46','TOURISM',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('47','OVM',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('48','BPLO',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');
INSERT INTO `offices` (`id`,`office_name`,`icon`,`head_user_id`,`description`,`created_at`,`updated_at`) VALUES ('49','7K',NULL,NULL,NULL,'2025-09-29 22:03:32','2025-09-29 22:03:32');

--
-- Structure for table `par_form`
--
DROP TABLE IF EXISTS `par_form`;
CREATE TABLE `par_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `received_by_name` varchar(255) DEFAULT NULL,
  `issued_by_name` varchar(255) DEFAULT NULL,
  `position_office_left` varchar(100) DEFAULT NULL,
  `position_office_right` varchar(100) DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(255) NOT NULL,
  `fund_cluster` varchar(100) NOT NULL,
  `par_no` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_received_left` date DEFAULT NULL,
  `date_received_right` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `par_items`
--
DROP TABLE IF EXISTS `par_items`;
CREATE TABLE `par_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `amount` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`item_id`),
  KEY `form_id` (`form_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `permission_audit_log`
--
DROP TABLE IF EXISTS `permission_audit_log`;
CREATE TABLE `permission_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'User whose permissions changed',
  `changed_by` int(11) NOT NULL COMMENT 'Admin who made the change',
  `action` enum('GRANT','REVOKE','ROLE_CHANGE') NOT NULL,
  `permission_id` int(11) DEFAULT NULL COMMENT 'Permission that was changed',
  `old_value` varchar(100) DEFAULT NULL,
  `new_value` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL COMMENT 'Reason for permission change',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `permission_id` (`permission_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_changed_by` (`changed_by`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `permission_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_audit_log_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_audit_log_ibfk_3` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Audit trail for permission changes';

--
-- Structure for table `permission_levels`
--
DROP TABLE IF EXISTS `permission_levels`;
CREATE TABLE `permission_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) NOT NULL COMMENT 'Level name (none, view, edit, delete, approve, manage)',
  `level_weight` int(11) NOT NULL COMMENT 'Weight for hierarchy (1=lowest, 5=highest)',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `level_name` (`level_name`),
  UNIQUE KEY `unique_level_weight` (`level_weight`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Permission level hierarchy';

--
-- Data for table `permission_levels` (6 rows)
--
INSERT INTO `permission_levels` (`id`,`level_name`,`level_weight`,`description`,`created_at`) VALUES ('1','none','0','No access to module','2025-10-03 09:25:48');
INSERT INTO `permission_levels` (`id`,`level_name`,`level_weight`,`description`,`created_at`) VALUES ('2','view','1','Can view/read data only','2025-10-03 09:25:48');
INSERT INTO `permission_levels` (`id`,`level_name`,`level_weight`,`description`,`created_at`) VALUES ('3','edit','2','Can view and create/edit data','2025-10-03 09:25:48');
INSERT INTO `permission_levels` (`id`,`level_name`,`level_weight`,`description`,`created_at`) VALUES ('4','delete','3','Can view, edit, and delete data','2025-10-03 09:25:48');
INSERT INTO `permission_levels` (`id`,`level_name`,`level_weight`,`description`,`created_at`) VALUES ('5','approve','4','Can approve/reject actions (for workflows)','2025-10-03 09:25:48');
INSERT INTO `permission_levels` (`id`,`level_name`,`level_weight`,`description`,`created_at`) VALUES ('6','manage','5','Full control including permissions management','2025-10-03 09:25:48');

--
-- Structure for table `permissions`
--
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `permissions` (37 rows)
--
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('1','view_dashboard','View the dashboard',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('2','view_users','View users',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('3','view_roles','View roles',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('4','view_permissions','View permissions',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('5','view_assets','View assets',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('6','view_categories','View categories',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('7','view_locations','View locations',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('8','view_suppliers','View suppliers',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('9','view_status','View status',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('10','view_types','View types',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('11','view_users_create','Create users',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('12','view_users_edit','Edit users',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('13','view_users_delete','Delete users',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('14','view_roles_create','Create roles',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('15','view_roles_edit','Edit roles',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('16','view_roles_delete','Delete roles',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('17','view_permissions_create','Create permissions',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('18','view_permissions_edit','Edit permissions',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('19','view_permissions_delete','Delete permissions',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('20','view_assets_create','Create assets',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('21','view_assets_edit','Edit assets',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('22','view_assets_delete','Delete assets',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('23','view_categories_create','Create categories',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('24','view_categories_edit','Edit categories',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('25','view_categories_delete','Delete categories',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('26','view_locations_create','Create locations',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('27','view_locations_edit','Edit locations',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('28','view_locations_delete','Delete locations',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('29','view_suppliers_create','Create suppliers',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('30','view_suppliers_edit','Edit suppliers',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('31','view_suppliers_delete','Delete suppliers',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('32','view_status_create','Create status',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('33','view_status_edit','Edit status',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('34','view_status_delete','Delete status',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('35','view_types_create','Create types',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('36','view_types_edit','Edit types',NULL,'2025-10-10 10:29:03');
INSERT INTO `permissions` (`id`,`name`,`description`,`category`,`created_at`) VALUES ('37','view_types_delete','Delete types',NULL,'2025-10-10 10:29:03');

--
-- Structure for table `red_tags`
--
DROP TABLE IF EXISTS `red_tags`;
CREATE TABLE `red_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `red_tag_number` varchar(20) NOT NULL,
  `control_no` varchar(50) DEFAULT NULL,
  `asset_id` int(11) NOT NULL,
  `iirup_id` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `tagged_by` int(11) NOT NULL,
  `item_location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `removal_reason` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  KEY `iirup_id` (`iirup_id`),
  KEY `tagged_by` (`tagged_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `remember_tokens`
--
DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `report_generation_settings`
--
DROP TABLE IF EXISTS `report_generation_settings`;
CREATE TABLE `report_generation_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frequency` enum('weekly','monthly','daily') NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `day_of_month` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `report_generation_settings` (2 rows)
--
INSERT INTO `report_generation_settings` (`id`,`frequency`,`day_of_week`,`day_of_month`) VALUES ('1','weekly','Monday','3');
INSERT INTO `report_generation_settings` (`id`,`frequency`,`day_of_week`,`day_of_month`) VALUES ('16','weekly','Monday','3');

--
-- Structure for table `report_templates`
--
DROP TABLE IF EXISTS `report_templates`;
CREATE TABLE `report_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `header_html` text DEFAULT NULL,
  `subheader_html` text DEFAULT NULL,
  `footer_html` text DEFAULT NULL,
  `left_logo_path` varchar(255) DEFAULT NULL,
  `right_logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_created_by` (`created_by`),
  KEY `fk_updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `report_templates` (8 rows)
--
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('2','Inventory Custodian Slip','<div style=\"font-family:\"Times New Roman\"; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:left;\"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\">Hello World<div><b>inventory report</b></div><div><i>as of&nbsp;$dynamic_month&nbsp;$dynamic_year</i></div></div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\"><div style=\"font-family:; font-size:; text-align:left;\"><div style=\"font-family:; font-size:; text-align:;\">name:&nbsp;[blank]&nbsp; position:&nbsp;[blank]</div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:left;\"><div style=\"font-family:; font-size:; text-align:;\">signature:&nbsp;[blank]</div></div></div></div></div>','../uploads/6867dfb04e6d4_Laptop Dell XPS 15_QR (1).png',NULL,'2025-07-04 21:05:36','2025-07-08 10:25:01','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('3','Property Acknowledgement Receipt','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Arial; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:16px;=\"\" text-align:start;\"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\">REPUBLIC OF THE PHILIPPINES<div><b>PROPERTY ACKNOWLEDGEMENT RECEIPT</b></div><div><i>As of&nbsp;$dynamic_month&nbsp;$dynamic_year</i></div></div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Poppins, sans-serif; font-size:16px; text-align:start;\"><div style=\"  \"><div style=\"  \">name:&nbsp;[blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;subject:&nbsp;[blank]</div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Poppins, sans-serif; font-size:16px; text-align:start;\"><div style=\"  \"><div style=\"  \">signature:&nbsp;[blank]<div>property:&nbsp;[blank]</div></div></div></div></div></div>','../uploads/68693f21f1b44_logo.jpg','../uploads/68693f21f245d_logo.jpg','2025-07-05 22:05:05','2025-07-08 08:50:38','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('4','Inventory Transfer Report','<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\">REPUBLIC OF THE PHILIPPINES<div><b>INVENTORY TRANSFER REPORT</b></div><div><i>As of&nbsp;</i>&nbsp;$dynamic_month&nbsp;$dynamic_year</div></div></div></div></div>','<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"  \">name:&nbsp;[blank]&nbsp;</div></div></div></div>','<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"  \">signature:&nbsp;[blank]</div></div></div></div>','../uploads/686942fdd82cc_logo.jpg','../uploads/right_1752067662_37.png','2025-07-05 22:21:33','2025-07-07 19:54:56','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('5','Memorandum Report','<div style=\"font-family:Tahoma; font-size:16px; text-align:center;\">\n    <b>Republic of the Philippines</b><br>\n    Municipality of Pilar\n</div>\n','<div style=\"font-size:12px; text-align:right;\">\n    Prepared: $DYNAMIC_MONTH $DYNAMIC_YEAR\n</div>\n\\','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Tahoma; font-size:12px; text-align:left;\">signature:&nbsp;[blank]</div></div>','../uploads/686944de212f8_logo.jpg','../uploads/686944de218f6_logo.jpg','2025-07-05 22:29:34','2025-07-08 10:25:29','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('30','sample 3','<div style=\"font-size: 14px;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"font-family: Tahoma;\"><div style=\"\">Republic of the Philippines<div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div><b>Municipality of Pilar</b></div><div>Province of Sorsogon</div></div></div></div></div></div></div></div>','<div style=\"font-size: 18px; font-family: Georgia;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"text-align: left; font-family: Georgia; font-size: 12px;\"><div>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Annex A.3</div>Entity name:<u>LGU-PILAR/OMSWD</u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Fund Cluster<div>From Acountable Officer/Agency Fund Cluster MARK JAYSON NAMIA/LGU-PILAR-OMPDC/OFFICE SUPPLY&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;ITR No: 24-09-1</div><div>To Accountable&nbsp; Offices/Agency/Fund Cluster: VLADIMIR ABOGADO&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Date: 3/12/25&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div><div>Transfer Type: (Check only)</div><div>[blank]donation&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;[blank]relocate</div><div>[blank]reaasignment&nbsp;[blank]others (specify)[blank]<br><div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div><u><br></u></div></div></div></div></div></div></div></div></div>','<div style=\"font-family: Georgia; font-size: 18px;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"text-align: left;\"><div style=\"\"><div style=\"font-size: 12px;\">[blank][blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;[blank]<br><div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div>&nbsp; &nbsp; &nbsp; name of accountable officer&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (designation)&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; department office&nbsp; &nbsp; &nbsp;</div></div></div></div></div></div></div></div>','../uploads/686f1e3ad26b5_PILAR LOGO TRANSPARENT.png','','2025-07-09 20:35:40','2025-07-09 20:35:40','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('31','sample 4','<div style=\"font-family:; font-size:; text-align:;\">yuebobceuob</div>','<div style=\"font-family:Arial; font-size:12px; text-align:left;\"><table class=\"table table-bordered\"><tbody><tr><td>hibicbocjsclsjcdkjdcdjcbjdbcdjb</td><td>hellolcdnckdckdcndkcndlkcndlnc</td><td>xsjhcbsjkcb</td></tr><tr><td>[blank]</td><td>[blank]</td><td>kjsbcjscjcsc</td></tr></tbody></table></div>','<div style=\"font-family:; font-size:; text-align:;\"><br><table class=\"table table-bordered\"><tbody><tr><td>[blank]kcnckdnckna;cndk</td><td><br></td><td>helljdowidwio</td></tr><tr><td>gievi bcsjbs</td><td>[blank]</td><td>[blank]</td></tr></tbody></table></div>','../uploads/686fdcf7c9274_logo.jpg','../uploads/686fdcf7c9cf8_38.png','2025-07-10 22:32:07','2025-07-10 22:32:07','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('32','SAMPLE 5 BORDER','<div style=\"font-family:; font-size:; text-align:;\">HEADER</div>','<div style=\"font-family:; font-size:; text-align:;\">HELLO<table class=\"table\"><tbody><tr><td>[blank]NAME NO BORDER</td><td>[blank]</td></tr></tbody></table></div>','<div style=\"font-family:; font-size:; text-align:;\">HELLO WITH BORDER</div>',NULL,NULL,'2025-07-10 22:35:29','2025-07-10 22:35:29','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('33','sample 6','<div style=\"font-family:\"Times New Roman\"; font-size:; text-align:;\">republic of the philippines</div>','<div style=\"font-family:; font-size:12px; text-align:;\"><table class=\"table\"><tbody><tr><td>name[blank]</td><td>date[blank]</td></tr></tbody></table></div>','<div style=\"font-family:; font-size:12px; text-align:;\"><table class=\"table\"><tbody><tr><td>signature[blank]</td><td>date[blank]</td></tr></tbody></table></div>','../uploads/6870b954756b1_logo.jpg','../uploads/6870b95475f65_PILAR LOGO TRANSPARENT.png','2025-07-11 14:12:20','2025-07-11 14:12:20','17','17');

--
-- Structure for table `returned_assets`
--
DROP TABLE IF EXISTS `returned_assets`;
CREATE TABLE `returned_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_request_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `return_date` datetime NOT NULL,
  `condition_on_return` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `office_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_request_id` (`borrow_request_id`),
  KEY `asset_id` (`asset_id`),
  KEY `user_id` (`user_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `returned_assets` (2 rows)
--
INSERT INTO `returned_assets` (`id`,`borrow_request_id`,`asset_id`,`user_id`,`return_date`,`condition_on_return`,`remarks`,`office_id`) VALUES ('7','13','18','17','2025-04-20 19:58:58','Good','Returned','9');
INSERT INTO `returned_assets` (`id`,`borrow_request_id`,`asset_id`,`user_id`,`return_date`,`condition_on_return`,`remarks`,`office_id`) VALUES ('8','1','2','12','2025-04-20 20:28:03','Good','Returned','4');

--
-- Structure for table `ris_form`
--
DROP TABLE IF EXISTS `ris_form`;
CREATE TABLE `ris_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `division` varchar(255) NOT NULL,
  `responsibility_center` varchar(255) NOT NULL,
  `responsibility_code` varchar(255) DEFAULT NULL,
  `ris_no` varchar(100) NOT NULL,
  `sai_no` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `approved_by_name` varchar(255) NOT NULL,
  `approved_by_designation` varchar(255) NOT NULL,
  `approved_by_date` date NOT NULL,
  `received_by_name` varchar(255) NOT NULL,
  `received_by_designation` varchar(255) NOT NULL,
  `received_by_date` date NOT NULL,
  `footer_date` date NOT NULL,
  `reason_for_transfer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `requested_by_name` varchar(255) DEFAULT NULL,
  `requested_by_designation` varchar(255) DEFAULT NULL,
  `requested_by_date` date DEFAULT NULL,
  `issued_by_name` varchar(255) DEFAULT NULL,
  `issued_by_designation` varchar(255) DEFAULT NULL,
  `issued_by_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `ris_items`
--
DROP TABLE IF EXISTS `ris_items`;
CREATE TABLE `ris_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ris_form_id` int(11) NOT NULL,
  `stock_no` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ris_form_id` (`ris_form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `role_permissions`
--
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `role` enum('MAIN_ADMIN','SYSTEM_ADMIN','OFFICE_ADMIN','MAIN_USER') NOT NULL COMMENT 'Role name',
  `permission_id` int(11) NOT NULL COMMENT 'Permission ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  KEY `idx_role` (`role`),
  KEY `idx_role_permissions_role_id` (`role_id`),
  CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Updated to use role_id instead of role enum for better referential integrity. Migration applied on 2023-10-07.';

--
-- Data for table `role_permissions` (53 rows)
--
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('1','1','SYSTEM_ADMIN','5','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('2','1','SYSTEM_ADMIN','20','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('3','1','SYSTEM_ADMIN','22','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('4','1','SYSTEM_ADMIN','21','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('5','1','SYSTEM_ADMIN','6','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('6','1','SYSTEM_ADMIN','23','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('7','1','SYSTEM_ADMIN','25','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('8','1','SYSTEM_ADMIN','24','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('9','1','SYSTEM_ADMIN','1','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('10','1','SYSTEM_ADMIN','7','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('11','1','SYSTEM_ADMIN','26','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('12','1','SYSTEM_ADMIN','28','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('13','1','SYSTEM_ADMIN','27','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('14','1','SYSTEM_ADMIN','4','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('15','1','SYSTEM_ADMIN','17','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('16','1','SYSTEM_ADMIN','19','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('17','1','SYSTEM_ADMIN','18','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('18','1','SYSTEM_ADMIN','3','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('19','1','SYSTEM_ADMIN','14','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('20','1','SYSTEM_ADMIN','16','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('21','1','SYSTEM_ADMIN','15','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('22','1','SYSTEM_ADMIN','9','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('23','1','SYSTEM_ADMIN','32','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('24','1','SYSTEM_ADMIN','34','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('25','1','SYSTEM_ADMIN','33','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('26','1','SYSTEM_ADMIN','8','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('27','1','SYSTEM_ADMIN','29','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('28','1','SYSTEM_ADMIN','31','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('29','1','SYSTEM_ADMIN','30','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('30','1','SYSTEM_ADMIN','10','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('31','1','SYSTEM_ADMIN','35','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('32','1','SYSTEM_ADMIN','37','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('33','1','SYSTEM_ADMIN','36','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('34','1','SYSTEM_ADMIN','2','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('35','1','SYSTEM_ADMIN','11','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('36','1','SYSTEM_ADMIN','13','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('37','1','SYSTEM_ADMIN','12','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('64','2','MAIN_ADMIN','5','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('65','2','MAIN_ADMIN','6','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('66','2','MAIN_ADMIN','1','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('67','2','MAIN_ADMIN','7','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('68','2','MAIN_ADMIN','10','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('69','2','MAIN_ADMIN','2','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('71','3','','5','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('72','3','','6','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('73','3','','1','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('74','3','','7','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('75','3','','10','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('78','4','MAIN_USER','5','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('79','4','MAIN_USER','6','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('80','4','MAIN_USER','1','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('81','4','MAIN_USER','7','2025-10-10 10:29:24');
INSERT INTO `role_permissions` (`id`,`role_id`,`role`,`permission_id`,`created_at`) VALUES ('82','4','MAIN_USER','10','2025-10-10 10:29:24');

--
-- Structure for table `roles`
--
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#99AAB5',
  `is_hoisted` tinyint(1) DEFAULT 0,
  `position` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `roles` (4 rows)
--
INSERT INTO `roles` (`id`,`name`,`description`,`color`,`is_hoisted`,`position`,`created_at`,`updated_at`) VALUES ('1','SYSTEM_ADMIN','Has full access to all system features and configurations','#FF0000','1','100','2025-10-03 12:05:48','2025-10-07 08:54:41');
INSERT INTO `roles` (`id`,`name`,`description`,`color`,`is_hoisted`,`position`,`created_at`,`updated_at`) VALUES ('2','MAIN_ADMIN','Can manage assets, users, and basic system settings','#3498DB','1','80','2025-10-03 12:05:48','2025-10-07 08:54:41');
INSERT INTO `roles` (`id`,`name`,`description`,`color`,`is_hoisted`,`position`,`created_at`,`updated_at`) VALUES ('3','MAIN_EMPLOYEE','Can view and borrow assets','#2ECC71','0','60','2025-10-03 12:05:48','2025-10-07 08:54:41');
INSERT INTO `roles` (`id`,`name`,`description`,`color`,`is_hoisted`,`position`,`created_at`,`updated_at`) VALUES ('4','MAIN_USER','Basic user with limited access','#99AAB5','0','40','2025-10-03 12:05:48','2025-10-07 08:54:41');

--
-- Structure for table `rpcppe_form`
--
DROP TABLE IF EXISTS `rpcppe_form`;
CREATE TABLE `rpcppe_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_image` varchar(255) DEFAULT NULL,
  `accountable_officer` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `agency_office` varchar(255) NOT NULL,
  `member_inventory` varchar(255) NOT NULL,
  `chairman_inventory` varchar(255) NOT NULL,
  `mayor` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `rpcppe_form` (1 rows)
--
INSERT INTO `rpcppe_form` (`id`,`header_image`,`accountable_officer`,`destination`,`agency_office`,`member_inventory`,`chairman_inventory`,`mayor`,`created_at`) VALUES ('1','header_1755919258.png','','','OMAD Office','','','','2025-08-14 21:52:25');

--
-- Structure for table `settings`
--
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `system`
--
DROP TABLE IF EXISTS `system`;
CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `system_title` varchar(255) NOT NULL,
  `default_user_password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `system` (1 rows)
--
INSERT INTO `system` (`id`,`logo`,`system_title`,`default_user_password`) VALUES ('1','1759282594_logo.png','Pilar Inventory Management System','PilarINVENTORY@1');

--
-- Structure for table `system_info`
--
DROP TABLE IF EXISTS `system_info`;
CREATE TABLE `system_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `developer_name` varchar(255) NOT NULL,
  `developer_email` varchar(255) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `credits` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `system_info` (1 rows)
--
INSERT INTO `system_info` (`id`,`system_name`,`description`,`developer_name`,`developer_email`,`version`,`credits`,`created_at`) VALUES ('1','Pilar Asset Inventory Management System','This system manages and tracks assets across different offices. It supports inventory categorization, QR code tracking, report generation, and user role-based access.','Walton John Loneza \r\nJoshua Mari Francis Escano \r\nElton John B. Moises','waltonloneza@example.com','1.0','Developed by BU Polangui Capstone Team for the Municipality of Pilar, Sorsogon.','2025-08-03 17:30:00');

--
-- Structure for table `system_logs`
--
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `datetime` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `system_logs` (5 rows)
--
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('1','4','4','Asset Management','Added new asset: HP Laptop (ID: 4), Category: Electronics','::1','2025-04-06 17:48:07');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('2','12','4','Assets','Added asset: Desktop Computer Set','::1','2025-04-21 06:35:28');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('3','12','4','Categories','Added new category: Luminaires','::1','2025-04-21 13:28:02');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('4','12','4','Assets','Added asset: Generator','::1','2025-04-21 14:01:52');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('5','12','4','Categories','Added new category: Luminaires','::1','2025-04-21 14:02:08');

--
-- Structure for table `tag_counters`
--
DROP TABLE IF EXISTS `tag_counters`;
CREATE TABLE `tag_counters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_type` enum('red_tag','ics_no','itr_no','par_no','ris_no','inventory_tag','asset_code','serial_no','sai_no','control_no') DEFAULT NULL,
  `year_period` varchar(10) NOT NULL COMMENT 'Year or period (e.g., 2025)',
  `prefix_hash` varchar(32) NOT NULL COMMENT 'MD5 hash of current prefix for reset detection',
  `current_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tag_year_prefix` (`tag_type`,`year_period`,`prefix_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `tag_counters` (21 rows)
--
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('1','red_tag','2025','bdff4249657cb5c4a8513161435a8a8e','0','2025-10-03 15:46:29','2025-10-03 15:46:29');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('2','ics_no','2025','e5d9346982a55968ea3b22a01dac13b5','0','2025-10-03 15:46:29','2025-10-03 15:46:29');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('3','itr_no','2025','f5f9c7790bcc99b43b568aa63986e2c0','0','2025-10-03 15:46:29','2025-10-03 15:46:29');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('5','ris_no','2025','b601fa317a2a3d4f7860d95d06fe6c6b','0','2025-10-03 15:46:29','2025-10-03 15:46:29');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('7','red_tag','global','73debfcaea0af900d0b2d69faba25d93','27','2025-10-03 15:56:22','2025-10-11 14:21:57');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('8','ics_no','global','059d8400ee29e46df1145dbdff55eafa','38','2025-10-03 15:56:22','2025-10-11 13:45:41');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('9','itr_no','global','4cc6d846d5327e1355419fc2767b8bbf','9','2025-10-03 15:56:22','2025-10-11 14:15:28');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('11','ris_no','global','b222f0296897fbec7d2f734d844e887b','7','2025-10-03 15:56:22','2025-10-10 16:45:48');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('16','','global','08054846bbc9933fd0395f8be516a9f9','0','2025-10-03 19:18:28','2025-10-03 19:18:28');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('17','serial_no','global','92666505ce75444ee14be2ebc2f10a60','117','2025-10-03 19:32:17','2025-10-11 13:46:34');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('19','inventory_tag','global','b64d0fc24a6aed24f5297319a28b91bd','123','2025-10-03 19:48:04','2025-10-11 13:46:34');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('20','','global','efc1ef8c2b016e45c48cf5aaf93bb11f','0','2025-10-04 08:36:33','2025-10-04 08:36:33');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('21','sai_no','global','efc1ef8c2b016e45c48cf5aaf93bb11f','8','2025-10-04 08:38:12','2025-10-10 16:45:48');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('22','control_no','global','8114336b915d05a8b429543dfe9ef9fb','24','2025-10-04 09:12:20','2025-10-11 14:21:57');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('28','','','','26','2025-10-07 12:38:13','2025-10-10 13:26:23');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('29','inventory_tag','','','26','2025-10-07 12:38:13','2025-10-10 13:26:23');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('95','asset_code','global','d41d8cd98f00b204e9800998ecf8427e','0','2025-10-10 07:29:56','2025-10-10 07:29:56');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('96','par_no','global','d41d8cd98f00b204e9800998ecf8427e','1','2025-10-10 07:31:27','2025-10-10 17:08:42');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('97','asset_code','global','8dfebf110ea9d91ef8bb29a0dda4c7a1','17','2025-10-10 11:56:21','2025-10-11 13:46:34');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('98','asset_code','global','523669b89db5b71820e7d690fbd15a34','2','2025-10-10 12:42:54','2025-10-10 13:25:22');
INSERT INTO `tag_counters` (`id`,`tag_type`,`year_period`,`prefix_hash`,`current_count`,`created_at`,`updated_at`) VALUES ('101','asset_code','','','4','2025-10-10 13:26:22','2025-10-10 13:26:23');

--
-- Structure for table `tag_formats`
--
DROP TABLE IF EXISTS `tag_formats`;
CREATE TABLE `tag_formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_type` enum('red_tag','ics_no','itr_no','par_no','ris_no','inventory_tag','asset_code','serial_no','sai_no','control_no') DEFAULT NULL,
  `format_template` varchar(255) NOT NULL COMMENT 'Template like PAR-{YYYY}-{####}',
  `current_number` int(11) DEFAULT 1 COMMENT 'Current increment number',
  `prefix` varchar(100) DEFAULT '' COMMENT 'Static prefix part',
  `suffix` varchar(100) DEFAULT '' COMMENT 'Static suffix part',
  `increment_digits` int(11) DEFAULT 4 COMMENT 'Number of digits for increment (e.g., 4 = 0001)',
  `date_format` varchar(50) DEFAULT 'YYYY' COMMENT 'Date format in template (YYYY, MM, DD)',
  `reset_on_change` tinyint(1) DEFAULT 1 COMMENT 'Reset counter when prefix/format changes',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_type` (`tag_type`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `tag_formats` (10 rows)
--
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('1','red_tag','RT-{####}','1','RT-','','4','','1','1','2025-10-03 15:23:18','2025-10-03 15:53:59');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('2','ics_no','{OFFICE}-{###}','1','ICS-','','5','YY','1','1','2025-10-03 15:23:18','2025-10-10 08:23:28');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('3','itr_no','ITR-{####}-{OFFICE}-{#}','1','ITR-','','4','','1','1','2025-10-03 15:23:18','2025-10-10 08:04:45');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('4','par_no','{OFFICE}-{###}','1',NULL,'','0',NULL,'1','1','2025-10-03 15:23:18','2025-10-10 07:31:27');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('5','ris_no','{YYYY}-{###}-{OFFICE}','1','RIS-','','4','YY','1','1','2025-10-03 15:23:18','2025-10-10 08:24:31');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('6','inventory_tag','PS-5S-03-F02-01-{##}-{##}','1','PS-5S-03-F02-01','','3','','1','1','2025-10-03 15:23:18','2025-10-03 19:58:27');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('9','asset_code','{CODE}-{####}-{MM}','1',NULL,'','0',NULL,'1','1','2025-10-03 19:04:46','2025-10-10 07:29:56');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('11','serial_no','{YY}-SN-{######}','1','SN','','6','YY','1','1','2025-10-03 19:24:32','2025-10-03 19:32:57');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('12','sai_no','SAI-{YYYY}-{####}','1','SAI-','','4','YYYY','1','1','2025-10-04 08:36:33','2025-10-10 07:55:25');
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_template`,`current_number`,`prefix`,`suffix`,`increment_digits`,`date_format`,`reset_on_change`,`is_active`,`created_at`,`updated_at`) VALUES ('15','control_no','CTRL-{YYYY}-{####}','1','CTRL-','','4','YYYY','1','1','2025-10-04 09:12:20','2025-10-04 09:12:20');

--
-- Structure for table `temp_iirup_items`
--
DROP TABLE IF EXISTS `temp_iirup_items`;
CREATE TABLE `temp_iirup_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `date_acquired` date DEFAULT NULL,
  `particulars` text DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit` varchar(50) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `office` varchar(255) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  CONSTRAINT `temp_iirup_items_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `temp_iirup_items` (1 rows)
--
INSERT INTO `temp_iirup_items` (`id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`quantity`,`unit`,`unit_cost`,`office`,`code`,`created_at`) VALUES ('14','54','2025-10-02','Laptop i7','PROP-0009','1','unit','70000.00','OMBO','25-ICT-0001','2025-10-11 14:25:37');

--
-- Structure for table `unit`
--
DROP TABLE IF EXISTS `unit`;
CREATE TABLE `unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `unit` (22 rows)
--
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('1','pcs');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('2','box');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('3','set');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('4','pack');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('5','dozen');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('6','liter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('7','milliliter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('8','kilogram');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('9','gram');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('10','meter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('11','centimeter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('12','inch');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('13','foot');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('14','yard');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('15','gallon');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('16','tablet');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('17','bottle');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('18','roll');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('19','can');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('20','tube');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('21','unit');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('22','reams');

--
-- Structure for table `user_notification_preferences`
--
DROP TABLE IF EXISTS `user_notification_preferences`;
CREATE TABLE `user_notification_preferences` (
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

--
-- Structure for table `user_notifications`
--
DROP TABLE IF EXISTS `user_notifications`;
CREATE TABLE `user_notifications` (
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

--
-- Structure for table `user_permissions`
--
DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`,`permission`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `user_permissions` (4 rows)
--
INSERT INTO `user_permissions` (`user_id`,`permission`) VALUES ('26','fuel_inventory');
INSERT INTO `user_permissions` (`user_id`,`permission`) VALUES ('28','fuel_inventory');
INSERT INTO `user_permissions` (`user_id`,`permission`) VALUES ('29','fuel_inventory');
INSERT INTO `user_permissions` (`user_id`,`permission`) VALUES ('30','restrict_user_management');

--
-- Structure for table `user_roles`
--
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','user','office_user','office_admin') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_profile.png',
  `session_timeout` int(11) DEFAULT 1800,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `users` (28 rows)
--
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('1','OMPDC','Mark Jayson Namia','waltielappy67@gmail.com','$2y$10$PjQBLH0.VE3gnzvEqc9YXOhDu.wuUFpAYK1Ze/NnGOi6S3DcIdaGm','super_admin','active','2025-04-01 20:01:47','f1a3abf461035dcc73348aa1789b454af62eb176488d01831ab5e0bb7d00a65b','2025-09-28 19:27:22',NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('2','user2','Mark John','john2@example.com','hashed_password','user','active','2025-04-03 11:31:57',NULL,NULL,'1','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('4','user4','Steve Jobs','mark4@example.com','hashed_password','user','active','2025-04-03 11:31:57',NULL,NULL,'3','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('5','johndoe','Elon Musk','johndoe@example.com','password123','admin','inactive','2025-04-03 11:45:50',NULL,NULL,'1','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('6','janesmith','Mark Zuckerberg','janesmith@example.com','password123','admin','active','2025-04-03 11:45:50',NULL,NULL,'2','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('7','tomgreen','Tom Jones','tomgreen@example.com','password123','admin','active','2025-04-03 11:45:50',NULL,NULL,'1','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('8','marybrown','Ed Caluag','marybrown@example.com','password123','office_user','active','2025-04-03 11:45:50',NULL,NULL,'3','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('9','peterwhite','Peter White','peterwhite@example.com','password123','admin','active','2025-04-03 11:45:50',NULL,NULL,'2','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('10','walt','Walton Loneza','waltielappy@gmail.com','$2y$10$j5gUPrRPP0w0REknIdYrce.l5ZItK3c5WJXX3eC2OSQHtJ/YchHey','admin','active','2025-04-04 08:31:30','b5cc3402f531db55aa9a15e82108f7c5079c41eab242c994f2d78720638d13da','2025-09-28 16:18:36',NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('12','walts','Walton Loneza','wjll@bicol-u.edu.ph','$2y$10$tsOlFU9fjwi/DLRKdGkqL.aIXhKnlFxnNbA8ZoXeMbEiAhoe.sg/i','office_admin','inactive','2025-04-07 21:13:29',NULL,NULL,'4','WIN_20240930_21_49_09_Pro.jpg','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('15','josh','Joshua Escano','jmfte@gmail.com','$2y$10$IFmIX3WZ0YOxdf41EYzX6.IF51IKEg0bL0kmyORCI8dod42v.JeN6','office_user','inactive','2025-04-09 07:49:07','5a8b600a59a80f2bf5028ae258b3aae8','2025-04-09 09:49:07','4','josh.jpg','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('16','elton','Elton John B. Moises','ejbm@bicol-u.edu.ph','$2y$10$Botz5wCa9biZrVT7IdEDau.uVBcw3ByoD75pX2BYYe7dtutigluY.','user','inactive','2025-04-13 13:01:46',NULL,NULL,'9','profile_16_1749816479.jpg','600');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('17','nami','Mark Jayson Namia','mjn@gmail.com','$2y$10$2MIZlmP380wS0sj/cOfqbe20HkPz234S49cJEj2omrrTjBasHVqyO','admin','active','2025-04-13 22:43:51',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('18','kiimon','Seynatour Kiimon','sk@gmail.com','$2y$10$UGpyMRA79O2OKhKfZDEf5O9CyXkMFlhDsVpWdELXMYnMtdFIV0mSC','office_user','deleted','2025-04-21 04:36:04','6687598406441374aeffbc338a60f728','2025-04-21 06:36:04','4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('19','geely','Geely Mitsubishi','waltielappy123@gmail.com','$2y$10$uVrAvdjC3GsGheiqmZSuF.r.oBbcHdOceQaV.E5LChrNNc/p20/FC','user','inactive','2025-06-24 13:54:34',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('21','miki','Miki Matsubara','mikimat@gmail.com','$2y$10$hE2SgXv.RQahXlmHCv4MEeBfBLqkaY7/w9OVyZbnuy83LMMPrFDHa','user','active','2025-06-24 14:01:30',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('22','Toyoki','Toyota Suzuki','toyoki@gmail.com','$2y$10$dLNw4hqEJbKpB5Hc7Mmhr.AjH4dOiMIUg9BqGDkiLnnx3rw89KBfS','user','active','2025-06-24 14:23:43',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('23','jet','Jet Kawasaki','kawaisaki@gmail.com','$2y$10$JmxsfOnmMH/nJbxWUbuSqODWoHTMx8RZn/Zxg38EFpGlvhqCtP3b6','user','active','2025-06-24 14:24:56',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('24','juan','Juan A. Dela Cruz','juandelacruz@gmail.com','$2y$10$NO/J3fBNaHSu/5HNM2vp/.hbb.u1NRzLSo8AQWh55P/TmnkUUv.Xe','office_admin','active','2025-09-14 09:29:57',NULL,NULL,'3','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('26','waltielappy@gmail.com','Seynatour Kiimon','wjll2022-2920-98466@bicol-u.edu.ph','$2y$10$UcjNuBTMzbToTt2gi4Dr2Oc/93pdffaCkrp3U2zZ8JvtE1nYHKRry','user','active','2025-09-27 11:39:35',NULL,NULL,'49','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('28','matt','Matt Monro','matt@gmail.com','$2y$10$FJP03nb6a4qqz4PRArmV2.8hlWtzT.shXDt4In8f8jBXhNbAhno56','user','deleted','2025-09-27 11:41:23',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('29','jack','Jack Daniels','jack@gmail.com','$2y$10$Gta6jYCePQr3UXEDOKWvdOtmznoA5.v/rIUxCw0vg5x5WH7bOYiZW','user','active','2025-09-27 11:59:33',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('30','mike','Mike Tyson','mike@gmail.com','$2y$10$FyofC5mTLdO.LAPel/wN0u.cR.BcYxanPhlfkt5n9CCT.JtJ.yTmW','user','active','2025-09-27 13:24:00',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('31','walton','Walter Loneza','waltonloneza@gmail.com','$2y$10$7jucgW6Qw9cQEq/aYJp7cOEHWrF/T2VA9o9QlCzYapek./Pl91snW','user','active','2025-09-28 20:04:03',NULL,NULL,'49','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('32','michael','Michael Jackson','notlawsfinds@gmail.com','$2y$10$2pPV8VpaXhFzQIIDfeJHQOZsu/Ffijefruuw.Ve80FP3iKW9/Ea4y','office_admin','active','2025-09-29 12:15:58',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('33','joshua','Joshua Mari Escano','joshuamarifrancis@gmail.com','$2y$10$EfJTyR7xOmi5v9sylVRq7O4S/lHyFxuexWktQcnkvrImulAL.UzZq','user','active','2025-09-29 21:32:40',NULL,NULL,'49','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('34','bumblebee','Bumblebee','bumblee25@gmail.com','$2y$10$K3uMNyl/0NUXRaBxNm9Gr.udTa8r2a0JwdtmAJHYcG/eNXyPfznQe','user','active','2025-10-05 14:29:38',NULL,NULL,'49','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('35','kara','karabasa','karabasa601@gmail.com','$2y$10$DFjWpW.I3pg/TL4z61AYsORyanD.hWnMsTCCcyA/k/7H4PNa4V1Pe','user','active','2025-10-05 20:25:10',NULL,NULL,'49','default_profile.png','1800');

SET FOREIGN_KEY_CHECKS=1;
