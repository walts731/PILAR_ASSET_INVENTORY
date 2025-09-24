-- Update schema from inventory_pilar (20) to (21)
-- Server target: MariaDB 10.4+

SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;

-- 1) Existing table changes
-- 1.1) assets: add batch-tracking columns
ALTER TABLE `assets`
  ADD COLUMN IF NOT EXISTS `enable_batch_tracking` TINYINT(1) DEFAULT 0 AFTER `additional_images`,
  ADD COLUMN IF NOT EXISTS `default_batch_size` INT(11) DEFAULT 1 AFTER `enable_batch_tracking`,
  ADD COLUMN IF NOT EXISTS `batch_expiry_required` TINYINT(1) DEFAULT 0 AFTER `default_batch_size`,
  ADD COLUMN IF NOT EXISTS `batch_manufacturer_required` TINYINT(1) DEFAULT 0 AFTER `batch_expiry_required`;

-- 1.2) assets: add indexes
ALTER TABLE `assets`
  ADD KEY `idx_assets_batch_tracking` (`enable_batch_tracking`);

-- 1.3) borrow_requests: add batch linkage and expiry
ALTER TABLE `borrow_requests`
  ADD COLUMN IF NOT EXISTS `batch_id` INT(11) DEFAULT NULL AFTER `updated_at`,
  ADD COLUMN IF NOT EXISTS `batch_item_id` INT(11) DEFAULT NULL AFTER `batch_id`,
  ADD COLUMN IF NOT EXISTS `expiry_date` DATE DEFAULT NULL AFTER `batch_item_id`;

-- 1.4) borrow_requests: indexes (only for new batch linkage)
ALTER TABLE `borrow_requests`
  ADD KEY `fk_borrow_batch` (`batch_id`),
  ADD KEY `fk_borrow_batch_item` (`batch_item_id`);

-- 1.5) Existing secondary indexes already present in v20; no action required.


-- 2) New batch tracking tables
CREATE TABLE IF NOT EXISTS `batches` (
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
  UNIQUE KEY `batch_number` (`batch_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `batches`
  ADD KEY `idx_batch_number` (`batch_number`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_expiry_date` (`expiry_date`),
  ADD KEY `idx_manufacture_date` (`manufacture_date`),
  ADD KEY `idx_quality_status` (`quality_status`),
  ADD KEY `fk_batch_creator` (`created_by`);

CREATE TABLE IF NOT EXISTS `batch_items` (
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
  UNIQUE KEY `unique_batch_item` (`batch_id`,`item_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `batch_items`
  ADD KEY `idx_batch_id` (`batch_id`),
  ADD KEY `idx_serial_number` (`serial_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_office_id` (`office_id`),
  ADD KEY `fk_batch_item_employee` (`employee_id`),
  ADD KEY `idx_batch_items_status` (`status`);

CREATE TABLE IF NOT EXISTS `batch_receipts` (
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
  UNIQUE KEY `unique_receipt_batch` (`batch_id`,`receipt_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `batch_receipts`
  ADD KEY `idx_receipt_number` (`receipt_number`),
  ADD KEY `idx_delivery_date` (`delivery_date`),
  ADD KEY `idx_received_by` (`received_by`),
  ADD KEY `fk_receipt_checker` (`quality_check_by`),
  ADD KEY `idx_batch_receipts_date` (`received_date`);

CREATE TABLE IF NOT EXISTS `batch_transactions` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `batch_transactions`
  ADD KEY `idx_batch_id` (`batch_id`),
  ADD KEY `idx_batch_item_id` (`batch_item_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reference_id` (`reference_id`),
  ADD KEY `fk_transaction_from_office` (`from_office_id`),
  ADD KEY `fk_transaction_to_office` (`to_office_id`),
  ADD KEY `fk_transaction_recipient` (`recipient_user_id`),
  ADD KEY `idx_batch_transactions_date` (`transaction_date`);


-- 3) Views (drop stand-ins if any and create/replace views)
DROP VIEW IF EXISTS `active_borrowing_stats`;
DROP TABLE IF EXISTS `active_borrowing_stats`;
CREATE OR REPLACE VIEW `active_borrowing_stats` AS
SELECT o.office_name AS office_name,
       COUNT(br.id) AS total_borrowed,
       SUM(br.quantity) AS total_quantity_borrowed,
       COUNT(DISTINCT br.user_id) AS unique_borrowers
FROM borrow_requests br
JOIN offices o ON br.office_id = o.id
WHERE br.status = 'borrowed'
GROUP BY o.id, o.office_name;

DROP VIEW IF EXISTS `batch_summary`;
DROP TABLE IF EXISTS `batch_summary`;
CREATE OR REPLACE VIEW `batch_summary` AS
SELECT b.id AS id,
       b.batch_number AS batch_number,
       b.batch_name AS batch_name,
       b.asset_id AS asset_id,
       a.asset_name AS asset_name,
       b.batch_size AS batch_size,
       b.expiry_date AS expiry_date,
       b.quality_status AS quality_status,
       b.created_at AS created_at,
       COUNT(bi.id) AS items_created,
       SUM(CASE WHEN bi.status = 'available' THEN 1 ELSE 0 END) AS items_available,
       SUM(CASE WHEN bi.status = 'borrowed' THEN 1 ELSE 0 END) AS items_borrowed,
       SUM(CASE WHEN bi.status = 'expired' THEN 1 ELSE 0 END) AS items_expired,
       SUM(CASE WHEN bi.status = 'damaged' THEN 1 ELSE 0 END) AS items_damaged
FROM batches b
LEFT JOIN assets a ON b.asset_id = a.id
LEFT JOIN batch_items bi ON b.id = bi.batch_id
GROUP BY b.id;

DROP VIEW IF EXISTS `expiring_batches`;
DROP TABLE IF EXISTS `expiring_batches`;
CREATE OR REPLACE VIEW `expiring_batches` AS
SELECT b.id AS id,
       b.batch_number AS batch_number,
       b.batch_name AS batch_name,
       b.asset_id AS asset_id,
       a.asset_name AS asset_name,
       b.expiry_date AS expiry_date,
       TO_DAYS(b.expiry_date) - TO_DAYS(CURDATE()) AS days_until_expiry,
       COUNT(bi.id) AS total_items,
       SUM(CASE WHEN bi.status = 'available' THEN 1 ELSE 0 END) AS available_items
FROM batches b
LEFT JOIN assets a ON b.asset_id = a.id
LEFT JOIN batch_items bi ON b.id = bi.batch_id
WHERE b.expiry_date IS NOT NULL
  AND b.expiry_date > CURDATE()
  AND TO_DAYS(b.expiry_date) - TO_DAYS(CURDATE()) <= 90
GROUP BY b.id
ORDER BY b.expiry_date ASC;

DROP VIEW IF EXISTS `overdue_items`;
DROP TABLE IF EXISTS `overdue_items`;
CREATE OR REPLACE VIEW `overdue_items` AS
SELECT br.id AS id,
       u.fullname AS borrower_name,
       a.asset_name AS asset_name,
       br.quantity AS quantity,
       br.approved_at AS approved_at,
       TO_DAYS(CURRENT_TIMESTAMP()) - TO_DAYS(br.approved_at) AS days_borrowed,
       o.office_name AS office_name
FROM borrow_requests br
JOIN users u ON br.user_id = u.id
JOIN assets a ON br.asset_id = a.id
JOIN offices o ON br.office_id = o.id
WHERE br.status = 'borrowed'
  AND TO_DAYS(CURRENT_TIMESTAMP()) - TO_DAYS(br.approved_at) > 30;


-- 4) Foreign keys for new batch tables and new relations
ALTER TABLE `batches`
  ADD CONSTRAINT `fk_batch_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_batch_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_batch_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `batch_items`
  ADD CONSTRAINT `fk_batch_item_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_batch_item_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_batch_item_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

ALTER TABLE `batch_receipts`
  ADD CONSTRAINT `fk_receipt_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_receipt_checker` FOREIGN KEY (`quality_check_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_receipt_receiver` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `batch_transactions`
  ADD CONSTRAINT `fk_transaction_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transaction_item` FOREIGN KEY (`batch_item_id`) REFERENCES `batch_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_from_office` FOREIGN KEY (`from_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_to_office` FOREIGN KEY (`to_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaction_recipient` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `fk_borrow_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_batch_item` FOREIGN KEY (`batch_item_id`) REFERENCES `batch_items` (`id`) ON DELETE SET NULL;


SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
