-- ================================================================
-- Database Improvements for PILAR ASSET INVENTORY
-- Fixed version that handles foreign key constraints properly
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Section 1: Drop and Recreate Foreign Keys and Indexes in the correct order

-- First, drop all foreign key constraints
ALTER TABLE `borrow_requests` DROP FOREIGN KEY IF EXISTS `borrow_requests_ibfk_1`;
ALTER TABLE `borrow_requests` DROP FOREIGN KEY IF EXISTS `borrow_requests_ibfk_2`;
ALTER TABLE `borrow_requests` DROP FOREIGN KEY IF EXISTS `borrow_requests_ibfk_3`;
ALTER TABLE `assets` DROP FOREIGN KEY IF EXISTS `assets_ibfk_1`;
ALTER TABLE `assets` DROP FOREIGN KEY IF EXISTS `fk_assets_office`;

-- Now it's safe to drop the indexes
DROP INDEX IF EXISTS `idx_borrow_requests_user_id` ON `borrow_requests`;
DROP INDEX IF EXISTS `idx_borrow_requests_asset_id` ON `borrow_requests`;
DROP INDEX IF EXISTS `idx_borrow_requests_office_id` ON `borrow_requests`;
DROP INDEX IF EXISTS `idx_borrow_requests_status` ON `borrow_requests`;
DROP INDEX IF EXISTS `idx_borrow_requests_requested_at` ON `borrow_requests`;
DROP INDEX IF EXISTS `idx_assets_office_status` ON `assets`;
DROP INDEX IF EXISTS `idx_assets_status` ON `assets`;

-- Recreate the indexes
CREATE INDEX `idx_borrow_requests_user_id` ON `borrow_requests`(`user_id`);
CREATE INDEX `idx_borrow_requests_asset_id` ON `borrow_requests`(`asset_id`);
CREATE INDEX `idx_borrow_requests_office_id` ON `borrow_requests`(`office_id`);
CREATE INDEX `idx_borrow_requests_status` ON `borrow_requests`(`status`);
CREATE INDEX `idx_borrow_requests_requested_at` ON `borrow_requests`(`requested_at`);
CREATE INDEX `idx_assets_office_status` ON `assets`(`office_id`, `status`);
CREATE INDEX `idx_assets_status` ON `assets`(`status`);

-- Recreate the foreign key constraints
ALTER TABLE `borrow_requests` 
  ADD CONSTRAINT `fk_borrow_user` FOREIGN KEY (`user_id`) 
  REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `borrow_requests` 
  ADD CONSTRAINT `fk_borrow_asset` FOREIGN KEY (`asset_id`) 
  REFERENCES `assets`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `borrow_requests` 
  ADD CONSTRAINT `fk_borrow_office` FOREIGN KEY (`office_id`) 
  REFERENCES `offices`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `assets` 
  ADD CONSTRAINT `fk_assets_office` FOREIGN KEY (`office_id`) 
  REFERENCES `offices`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ================================================================
-- Section 2: Borrowing Process Overhaul
-- ================================================================

-- Drop existing triggers that might conflict with column modification
DROP TRIGGER IF EXISTS `tr_borrow_requests_validation`;
DROP TRIGGER IF EXISTS `tr_borrow_requests_update_validation`;

-- Enhance the `borrow_requests` table
ALTER TABLE `borrow_requests`
  ADD COLUMN IF NOT EXISTS `purpose` TEXT NULL AFTER `quantity`,
  ADD COLUMN IF NOT EXISTS `due_date` DATE NULL AFTER `purpose`,
  MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'borrowed', 'returned', 'cancelled') NOT NULL DEFAULT 'pending';

-- Create a new table to track individual items in a borrow request
CREATE TABLE IF NOT EXISTS `borrow_request_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `borrow_request_id` INT(11) NOT NULL,
  `asset_item_id` INT(11) NOT NULL,
  `status` ENUM('assigned', 'returned') NOT NULL DEFAULT 'assigned',
  `returned_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_borrow_request_id` (`borrow_request_id`),
  INDEX `idx_asset_item_id` (`asset_item_id`),
  FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`asset_item_id`) REFERENCES `asset_items`(`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- Section 3: Triggers and Views
-- ================================================================

DELIMITER $$

-- Recreate trigger for borrow_requests validation with new status
CREATE TRIGGER `tr_borrow_requests_validation`
BEFORE INSERT ON `borrow_requests`
FOR EACH ROW
BEGIN
    IF NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity must be greater than 0';
    END IF;
    IF NEW.status NOT IN ('pending', 'approved', 'rejected', 'borrowed', 'returned', 'cancelled') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid status value';
    END IF;
END$$

-- Recreate trigger for borrow_requests updates with new status
CREATE TRIGGER `tr_borrow_requests_update_validation`
BEFORE UPDATE ON `borrow_requests`
FOR EACH ROW
BEGIN
    IF NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity must be greater than 0';
    END IF;
    IF NEW.status NOT IN ('pending', 'approved', 'rejected', 'borrowed', 'returned', 'cancelled') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid status value';
    END IF;
    IF NEW.updated_at IS NULL OR NEW.updated_at = OLD.updated_at THEN
      SET NEW.updated_at = CURRENT_TIMESTAMP;
    END IF;
END$$

DELIMITER ;

-- Recreate or replace views
CREATE OR REPLACE VIEW `active_borrowing_stats` AS
SELECT 
    o.office_name,
    COUNT(br.id) as total_borrowed,
    SUM(br.quantity) as total_quantity_borrowed,
    COUNT(DISTINCT br.user_id) as unique_borrowers
FROM `borrow_requests` br
JOIN `offices` o ON br.office_id = o.id
WHERE br.status = 'borrowed'
GROUP BY o.id, o.office_name;

CREATE OR REPLACE VIEW `overdue_items` AS
SELECT 
    br.id,
    u.fullname as borrower_name,
    a.asset_name,
    br.quantity,
    br.approved_at,
    DATEDIFF(NOW(), br.approved_at) as days_borrowed,
    o.office_name
FROM `borrow_requests` br
JOIN `users` u ON br.user_id = u.id
JOIN `assets` a ON br.asset_id = a.id
JOIN `offices` o ON br.office_id = o.id
WHERE br.status = 'borrowed' AND br.due_date IS NOT NULL AND br.due_date < CURDATE();


SET FOREIGN_KEY_CHECKS = 1;