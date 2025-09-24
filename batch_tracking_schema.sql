-- ================================================================
-- BATCH/LOT TRACKING IMPLEMENTATION
-- Database Schema for PILAR Asset Inventory System
-- ================================================================

-- Create batches table for tracking batch/lot information
CREATE TABLE IF NOT EXISTS `batches` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `batch_number` VARCHAR(50) NOT NULL UNIQUE,
  `batch_name` VARCHAR(255) NOT NULL,
  `asset_id` INT(11) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `supplier` VARCHAR(255) DEFAULT NULL,
  `manufacturer` VARCHAR(255) DEFAULT NULL,
  `manufacture_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `production_date` DATE DEFAULT NULL,
  `lot_number` VARCHAR(100) DEFAULT NULL,
  `batch_size` INT(11) NOT NULL DEFAULT 1,
  `unit_cost` DECIMAL(12,2) DEFAULT NULL,
  `total_value` DECIMAL(12,2) DEFAULT NULL,
  `storage_location` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `quality_status` ENUM('pending', 'approved', 'rejected', 'quarantined') DEFAULT 'pending',
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_batch_number` (`batch_number`),
  INDEX `idx_asset_id` (`asset_id`),
  INDEX `idx_category_id` (`category_id`),
  INDEX `idx_expiry_date` (`expiry_date`),
  INDEX `idx_manufacture_date` (`manufacture_date`),
  INDEX `idx_quality_status` (`quality_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create batch_items table for tracking individual items within batches
CREATE TABLE IF NOT EXISTS `batch_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `batch_id` INT(11) NOT NULL,
  `item_number` VARCHAR(50) NOT NULL,
  `serial_number` VARCHAR(255) DEFAULT NULL,
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `inventory_tag` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('available', 'borrowed', 'in_use', 'damaged', 'disposed', 'expired', 'quarantined') DEFAULT 'available',
  `current_location` VARCHAR(255) DEFAULT NULL,
  `office_id` INT(11) DEFAULT NULL,
  `employee_id` INT(11) DEFAULT NULL,
  `condition_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_batch_item` (`batch_id`, `item_number`),
  INDEX `idx_batch_id` (`batch_id`),
  INDEX `idx_serial_number` (`serial_number`),
  INDEX `idx_status` (`status`),
  INDEX `idx_office_id` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create batch_transactions table for tracking all movements and transactions
CREATE TABLE IF NOT EXISTS `batch_transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `batch_id` INT(11) NOT NULL,
  `batch_item_id` INT(11) DEFAULT NULL,
  `transaction_type` ENUM('received', 'issued', 'borrowed', 'returned', 'transferred', 'disposed', 'expired', 'damaged', 'quarantined', 'released') NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `from_location` VARCHAR(255) DEFAULT NULL,
  `to_location` VARCHAR(255) DEFAULT NULL,
  `from_office_id` INT(11) DEFAULT NULL,
  `to_office_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `recipient_user_id` INT(11) DEFAULT NULL,
  `reference_id` VARCHAR(100) DEFAULT NULL,
  `reference_type` ENUM('borrow_request', 'transfer', 'disposal', 'adjustment') DEFAULT NULL,
  `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_batch_id` (`batch_id`),
  INDEX `idx_batch_item_id` (`batch_item_id`),
  INDEX `idx_transaction_type` (`transaction_type`),
  INDEX `idx_transaction_date` (`transaction_date`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_reference_id` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create batch_receipts table for tracking receiving information
CREATE TABLE IF NOT EXISTS `batch_receipts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `batch_id` INT(11) NOT NULL,
  `receipt_number` VARCHAR(100) NOT NULL,
  `supplier_id` INT(11) DEFAULT NULL,
  `invoice_number` VARCHAR(100) DEFAULT NULL,
  `delivery_date` DATE NOT NULL,
  `received_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `received_by` INT(11) DEFAULT NULL,
  `quantity_received` INT(11) NOT NULL,
  `quantity_accepted` INT(11) NOT NULL,
  `quantity_rejected` INT(11) DEFAULT 0,
  `rejection_reason` TEXT DEFAULT NULL,
  `quality_check_status` ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
  `quality_check_date` DATE DEFAULT NULL,
  `quality_check_by` INT(11) DEFAULT NULL,
  `certificate_path` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_receipt_batch` (`batch_id`, `receipt_number`),
  INDEX `idx_receipt_number` (`receipt_number`),
  INDEX `idx_delivery_date` (`delivery_date`),
  INDEX `idx_received_by` (`received_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add batch tracking fields to existing assets table
ALTER TABLE `assets`
  ADD COLUMN IF NOT EXISTS `enable_batch_tracking` TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `default_batch_size` INT(11) DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `batch_expiry_required` TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `batch_manufacturer_required` TINYINT(1) DEFAULT 0;

-- Add batch tracking fields to existing borrow_requests table
ALTER TABLE `borrow_requests`
  ADD COLUMN IF NOT EXISTS `batch_id` INT(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `batch_item_id` INT(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `expiry_date` DATE DEFAULT NULL;

-- Add foreign key constraints after all tables are created
ALTER TABLE `batches`
  ADD CONSTRAINT IF NOT EXISTS `fk_batch_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT IF NOT EXISTS `fk_batch_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_batch_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

ALTER TABLE `batch_items`
  ADD CONSTRAINT IF NOT EXISTS `fk_batch_item_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT IF NOT EXISTS `fk_batch_item_office` FOREIGN KEY (`office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_batch_item_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL;

ALTER TABLE `batch_transactions`
  ADD CONSTRAINT IF NOT EXISTS `fk_transaction_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT IF NOT EXISTS `fk_transaction_item` FOREIGN KEY (`batch_item_id`) REFERENCES `batch_items`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_transaction_from_office` FOREIGN KEY (`from_office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_transaction_to_office` FOREIGN KEY (`to_office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_transaction_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_transaction_recipient` FOREIGN KEY (`recipient_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

ALTER TABLE `batch_receipts`
  ADD CONSTRAINT IF NOT EXISTS `fk_receipt_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT IF NOT EXISTS `fk_receipt_receiver` FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_receipt_checker` FOREIGN KEY (`quality_check_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

ALTER TABLE `borrow_requests`
  ADD CONSTRAINT IF NOT EXISTS `fk_borrow_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_borrow_batch_item` FOREIGN KEY (`batch_item_id`) REFERENCES `batch_items`(`id`) ON DELETE SET NULL;

-- Create view for batch summary
CREATE OR REPLACE VIEW `batch_summary` AS
SELECT
  b.id,
  b.batch_number,
  b.batch_name,
  b.asset_id,
  a.asset_name,
  b.batch_size,
  b.expiry_date,
  b.quality_status,
  b.created_at,
  COUNT(bi.id) as items_created,
  SUM(CASE WHEN bi.status = 'available' THEN 1 ELSE 0 END) as items_available,
  SUM(CASE WHEN bi.status = 'borrowed' THEN 1 ELSE 0 END) as items_borrowed,
  SUM(CASE WHEN bi.status = 'expired' THEN 1 ELSE 0 END) as items_expired,
  SUM(CASE WHEN bi.status = 'damaged' THEN 1 ELSE 0 END) as items_damaged
FROM `batches` b
LEFT JOIN `assets` a ON b.asset_id = a.id
LEFT JOIN `batch_items` bi ON b.id = bi.batch_id
GROUP BY b.id;

-- Create view for expiring batches alert
CREATE OR REPLACE VIEW `expiring_batches` AS
SELECT
  b.id,
  b.batch_number,
  b.batch_name,
  b.asset_id,
  a.asset_name,
  b.expiry_date,
  DATEDIFF(b.expiry_date, CURDATE()) as days_until_expiry,
  COUNT(bi.id) as total_items,
  SUM(CASE WHEN bi.status = 'available' THEN 1 ELSE 0 END) as available_items
FROM `batches` b
LEFT JOIN `assets` a ON b.asset_id = a.id
LEFT JOIN `batch_items` bi ON b.id = bi.batch_id
WHERE b.expiry_date IS NOT NULL
  AND b.expiry_date > CURDATE()
  AND DATEDIFF(b.expiry_date, CURDATE()) <= 90
GROUP BY b.id
ORDER BY b.expiry_date ASC;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_assets_batch_tracking` ON `assets`(`enable_batch_tracking`);
CREATE INDEX IF NOT EXISTS `idx_batch_items_status` ON `batch_items`(`status`);
CREATE INDEX IF NOT EXISTS `idx_batch_transactions_date` ON `batch_transactions`(`transaction_date`);
CREATE INDEX IF NOT EXISTS `idx_batch_receipts_date` ON `batch_receipts`(`received_date`);
